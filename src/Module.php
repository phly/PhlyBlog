<?php

namespace PhlyBlog;

class Module
{
    public static $config;

    public function getConfig()
    {
        $configProvider = new ConfigProvider();
        return $configProvider();
    }

    public function onBootstrap($e): void
    {
        $app          = $e->getApplication();
        $services     = $app->getServiceManager();
        self::$config = $services->get('config');
    }
}
