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

class EntriesTest extends TestCase
{
    public function setUp()
    {
        $options = include __DIR__ . '/../../../../config/module.config.php';

        $router = TreeRouteStack::factory($options['di']['instance']['Zend\Mvc\Router\RouteStack']['parameters']);

        $resolver = new Resolver\TemplatePathStack();
        $resolver->addPath(__DIR__ . '/../../../../view');
        $renderer = new Renderer\PhpRenderer();
        $renderer->setResolver($resolver);
        $renderer->plugin('url')->setRouter($router);

        $this->view = new View;
        $this->view->addRenderingStrategy(function($e) use ($renderer) {
            return $renderer;
        });

        $this->options = new CompilerOptions($options['blog']['options']);
        $this->file    = new Compiler\ResponseFile();
        $this->entries = new Entries($this->view, $this->file, $this->options);

        $this->writer = new TestAsset\MockWriter;
        $this->strategy = new Compiler\ResponseStrategy($this->writer, $this->file, $this->view);
        $this->compiler = new Compiler(new Compiler\PhpFileFilter(__DIR__ . '/../../_posts'));
        $this->compiler->events()->attach($this->entries);
        $json           = file_get_contents(__DIR__ . '/../../_posts/metadata.json');
        $this->metadata = json_decode($json, true);
    }

    public function testCreatesNoFilesPriorToCompilation()
    {
        $this->entries->createEntries();
        $this->assertTrue(empty($this->writer->files));
    }

    public function testCanCreateFilesFollowingCompilation()
    {
        $expected = 0;
        foreach ($this->metadata as $entry) {
            if ($entry['draft']) {
                continue;
            }
            $expected++;
        }
        $this->compiler->compile();
        $this->entries->createEntries();
        $this->assertEquals($expected, count($this->writer->files));
    }

    public function testFilesCreatedContainExpectedArtifacts()
    {
        $this->compiler->compile();
        $this->entries->createEntries();

        $filenameTemplate = $this->options->getEntryFilenameTemplate();
        foreach ($this->metadata as $entry) {
            if ($entry['draft']) {
                continue;
            }
            $id = $entry['id'];

            $filename = sprintf($filenameTemplate, $id);
            $this->assertArrayHasKey($filename, $this->writer->files);
            $content = $this->writer->files[$filename];
            $this->assertContains($entry['title'], $content);
        }
    }
}
