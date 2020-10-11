<?php

namespace PhlyBlog;

use Laminas\Console\Adapter\AdapterInterface as Console;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Laminas\View\Model;
use Laminas\View\Renderer\PhpRenderer;
use PhlyBlog\Factory\CompileControllerFactory;

class Module implements ConsoleUsageProviderInterface
{
    public static $config;

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getServiceConfig(): array
    {
        return ['factories' => [
            'BlogRequest'  => function ($services) {
                return new Request();
            },
            'BlogResponse' => function ($services) {
                return new Response();
            },
            'BlogRenderer' => function ($services) {
                $helpers  = $services->get('ViewHelperManager');
                $resolver = $services->get('ViewResolver');

                $renderer = new PhpRenderer();
                $renderer->setHelperPluginManager($helpers);
                $renderer->setResolver($resolver);

                $config = $services->get('config');
                if ($services->has('MvcEvent')) {
                    $event = $services->get('MvcEvent');
                    $model = $event->getViewModel();
                } else {
                    $model = new Model\ViewModel();
                }
                $layout = 'layout/layout';
                if (isset($config['view_manager']['layout'])) {
                    $layout = $config['view_manager']['layout'];
                }
                $model->setTemplate($layout);
                $helpers->get('view_model')->setRoot($model);

                return $renderer;
            },
        ]];
    }

    public function getControllerConfig(): array
    {
        return ['factories' => [
            CompileController::class => CompileControllerFactory::class,
        ]];
    }

    public function getConsoleBanner(Console $console): string
    {
        return 'Phly Static Blog Generator';
    }

    public function getConsoleUsage(Console $console): array
    {
        return [
            'blog compile [--all|-a] [--entries|-e] [--archive|-c] [--year|-y] [--month|-m] [--day|-d] [--tag|-t] [--author|-r]' => 'Compile blog',
            ['--all|-a', 'Execute all actions (default)'],
            ['--entries|-e', 'Compile entries'],
            ['--archive|-c', 'Compile paginated archive (and feed)'],
            ['--year|-y', 'Compile paginated entries by year'],
            ['--month|-m', 'Compile paginated entries by month'],
            ['--day|-d', 'Compile paginated entries by day'],
            ['--tag|-t', 'Compile paginated entries by tag (and feeds)'],
            ['--author|-r', 'Compile paginated entries by author (and feeds)'],
        ];
    }

    public function onBootstrap($e): void
    {
        $app          = $e->getApplication();
        $services     = $app->getServiceManager();
        self::$config = $services->get('config');
    }

    public static function prepareCompilerView($view, $config, $services): void
    {
        $renderer = $services->get('BlogRenderer');
        $view->addRenderingStrategy(
            function ($e) use ($renderer) {
                return $renderer;
            }, 100
        );
    }
}
