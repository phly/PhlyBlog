<?php

namespace PhlyBlog;

use Laminas\EventManager\EventManagerInterface;
use PhlyBlog\Compiler\PhpFileFilter;
use Psr\Container\ContainerInterface;

class CompilerFactory
{
    public function __invoke(ContainerInterface $container): Compiler
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['blog'] ?? [];

        return new Compiler(
            new PhpFileFilter($config['posts_path'] ?? getcwd() . '/data/blog/'),
            $container->get(EventManagerInterface::class)
        );
    }
}
