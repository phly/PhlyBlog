<?php

namespace PhlyBlog\Console;

use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\View;
use Psr\Container\ContainerInterface;

class ViewFactory
{
    public function __invoke(ContainerInterface $container): View
    {
        $view     = new View();
        $renderer = $this->createRenderer($container);

        $view->setRequest(new Request());
        $view->setResponse(new Response());
        $view->addRenderingStrategy(
            function () use ($renderer) {
                return $renderer;
            },
            100
        );

        return $view;
    }

    private function createRenderer(ContainerInterface $container): PhpRenderer
    {
        $helpers  = $container->get('ViewHelperManager');
        $renderer = new PhpRenderer();

        $renderer->setHelperPluginManager($helpers);
        $renderer->setResolver($container->get('ViewResolver'));

        $config = $container->get('config');
        $layout = $config['view_manager']['layout'] ?? 'layout/layout';
        $model  = $this->getRootModel($container);
        $model->setTemplate($layout);
        $helpers->get('view_model')->setRoot($model);

        return $renderer;
    }

    private function getRootModel(ContainerInterface $container): ViewModel
    {
        if (! $container->has('MvcEvent')) {
            return new ViewModel();
        }

        $event = $container->get('MvcEvent');
        return $event->getViewModel();
    }
}
