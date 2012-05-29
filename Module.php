<?php

namespace PhlyBlog;

use Traversable;
use Zend\Stdlib\ArrayUtils;

class Module
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
