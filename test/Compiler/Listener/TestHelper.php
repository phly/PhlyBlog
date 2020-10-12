<?php

namespace PhlyBlogTest\Compiler\Listener;

use Laminas\Router\Http\TreeRouteStack;
use Laminas\View\Renderer;
use Laminas\View\Resolver;
use Laminas\View\View;
use PhlyBlog\Compiler;
use PhlyBlog\CompilerOptions;
use PhlyBlogTest\Compiler\TestAsset\MockWriter;

use function file_get_contents;
use function json_decode;

trait TestHelper
{
    /** @var View */
    private $view;
    /** @var CompilerOptions */
    private $options;
    /** @var Compiler\ResponseFile */
    private $file;
    /** @var MockWriter */
    private $writer;
    /** @var Compiler\ResponseStrategy */
    private $strategy;
    /** @var Compiler */
    private $compiler;
    /** @var mixed */
    private $metadata;
    /** @var mixed */
    private $expected;

    private function injectScaffolds()
    {
        $options = include __DIR__ . '/../../../config/module.config.php';

        $router = TreeRouteStack::factory($options['router']);

        $resolver = new Resolver\TemplatePathStack();
        $resolver->addPath(__DIR__ . '/../../../view');
        $renderer = new Renderer\PhpRenderer();
        $renderer->setResolver($resolver);
        $renderer->plugin('url')->setRouter($router);

        $this->view = new View();
        $this->view->addRenderingStrategy(
            function ($e) use ($renderer) {
                return $renderer;
            }
        );

        $this->options  = new CompilerOptions($options['blog']['options']);
        $this->file     = new Compiler\ResponseFile();
        $this->writer   = new MockWriter();
        $this->strategy = new Compiler\ResponseStrategy(
            $this->writer,
            $this->file,
            $this->view
        );
        $this->compiler = new Compiler(
            new Compiler\PhpFileFilter(__DIR__ . '/../../_posts')
        );
        $json           = file_get_contents(
            __DIR__ . '/../../_posts/metadata.json'
        );
        $this->metadata = json_decode($json, true);
        $this->expected = include __DIR__ . '/../../_posts/metadata.php';
    }
}
