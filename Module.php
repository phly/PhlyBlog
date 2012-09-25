<?php

namespace PhlyBlog;

use Traversable;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Stdlib\ArrayUtils;

class Module implements ConsoleUsageProviderInterface
{
    public static $config;

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getConsoleUsage(Console $console)
    {
        return array(
            'blog compile [--all|-a] [--entries|-e] [--archive|-c] [--year|-y] [--month|-m] [--day|-d] [--tag|-t] [--author|-r]' => 'Compile blog:
    --all|-a: Execute all actions (default)
    --entries|-e: Compile entries
    --archive|-c: Compile paginated archive (and feed)
    --year|-y: Compile paginated entries by year
    --month|-m: Compile paginated entries by month
    --day|-d: Compile paginated entries by day
    --tag|-t: Compile paginated entries by tag (and feeds)
    --author|-r: Compile paginated entries by author (and feeds)
',
        );
    }

    public function onBootstrap($e)
    {
        $app          = $e->getApplication();
        $services     = $app->getServiceManager();
        self::$config = $services->get('config');
        if (self::$config instanceof Traversable) {
            self::$config = ArrayUtils::iteratorToArray(self::$config);
        }
    }

    public static function prepareCompilerView($view, $config, $services)
    {
        $renderer = $services->get('ViewRenderer');
        $view->addRenderingStrategy(function($e) use ($renderer) {
            return $renderer;
        }, 100);
    }
}
