<?php
namespace PhlyBlog\Compiler\Listener;

use PHPUnit_Framework_TestCase as TestCase;
use PhlyBlog\Compiler;
use PhlyBlog\CompilerOptions;
use PhlyBlog\Compiler\TestAsset;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\View\View;
use Zend\View\Renderer;
use Zend\View\Resolver;

class TestHelper
{
    public static function injectScaffolds(TestCase $testCase)
    {
        $options = include __DIR__ . '/../../../../config/module.config.php';

        $router = TreeRouteStack::factory($options['router']);

        $resolver = new Resolver\TemplatePathStack();
        $resolver->addPath(__DIR__ . '/../../../../view');
        $renderer = new Renderer\PhpRenderer();
        $renderer->setResolver($resolver);
        $renderer->plugin('url')->setRouter($router);

        $testCase->view = new View;
        $testCase->view->addRenderingStrategy(function($e) use ($renderer) {
            return $renderer;
        });

        $testCase->options  = new CompilerOptions($options['blog']['options']);
        $testCase->file     = new Compiler\ResponseFile();
        $testCase->writer   = new TestAsset\MockWriter;
        $testCase->strategy = new Compiler\ResponseStrategy($testCase->writer, $testCase->file, $testCase->view);
        $testCase->compiler = new Compiler(new Compiler\PhpFileFilter(__DIR__ . '/../../_posts'));
        $json               = file_get_contents(__DIR__ . '/../../_posts/metadata.json');
        $testCase->metadata = json_decode($json, true);
        $testCase->expected = include(__DIR__ . '/../../_posts/metadata.php');
    }
}
