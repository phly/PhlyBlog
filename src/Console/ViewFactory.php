<?php

namespace PhlyBlog\Console;

use Laminas\Mvc\Application;
use Laminas\Mvc\Service\ViewHelperManagerFactory;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\View;
use Laminas\View\ViewEvent;
use Psr\Container\ContainerInterface;

class ViewFactory
{
    public function __invoke(ContainerInterface $container): View
    {
        // This part is necessary to ensure that the router and MvcEvent are
        // populated for helpers such as the Url helper.
        $application = $container->get('Application');
        if ($application instanceof Application) {
            $application->bootstrap();
        }

        // Prepare the initial view state
        $view = $container->get(View::class);

        /** @var RendererInterface $renderer */
        $renderer = $container->get(RendererInterface::class);

        // Reset the helper plugin manager on each iteration
        $view->addRenderingStrategy(function () use ($container, $renderer) {
            if (method_exists($renderer, 'setHelperPluginManager')) {
                $renderer->setHelperPluginManager(
                    (new ViewHelperManagerFactory())($container, 'ViewHelperManager')
                );
            }
            return $renderer;
        }, 100);

        // Create the layout view model
        $config = $container->get('config');
        $layout = new ViewModel();
        $layout->setTemplate($config['view_manager']['layout'] ?? 'layout/layout');

        // Render content within the layout
        $view->addResponseStrategy(function (ViewEvent $event) use ($renderer, $layout) {
            $layout->setVariable('content', $event->getResult());
            $event->setResult($renderer->render($layout));
        }, 100);

        return $view;
    }
}
