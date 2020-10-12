<?php

namespace PhlyBlog\Console;

use Psr\Container\ContainerInterface;

class CompileCommandFactory
{
    public function __invoke(ContainerInterface $container): CompileCommand
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['blog'] ?? [];

        return new CompileCommand(
            $config,
            $container,
            $container->get(View::class)
        );
    }
}
