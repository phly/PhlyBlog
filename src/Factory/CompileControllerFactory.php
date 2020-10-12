<?php

declare(strict_types=1);

namespace PhlyBlog\Factory;

use Laminas\View\View;
use PhlyBlog\CompileController;
use Psr\Container\ContainerInterface;

final class CompileControllerFactory
{
    public function __invoke(ContainerInterface $container): CompileController
    {
        $console = $container->get('Console');

        $config = $container->get('config');
        $config = $config['blog'] ?? [];

        $request  = $container->get('BlogRequest');
        $response = $container->get('BlogResponse');
        $view     = new View();
        $view->setRequest($request);
        $view->setResponse($response);

        return new CompileController(
            $console,
            $view,
            $container,
            $config
        );
    }
}
