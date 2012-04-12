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

class TagsTest extends TestCase
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

        $this->options  = new CompilerOptions($options['blog']['options']);
        $this->file     = new Compiler\ResponseFile();
        $this->writer   = new TestAsset\MockWriter;
        $this->strategy = new Compiler\ResponseStrategy($this->writer, $this->file, $this->view);
        $this->compiler = new Compiler(new Compiler\PhpFileFilter(__DIR__ . '/../../_posts'));
        $json           = file_get_contents(__DIR__ . '/../../_posts/metadata.json');
        $this->metadata = json_decode($json, true);
        $this->expected = include(__DIR__ . '/../../_posts/metadata.php');

        $this->tags = new Tags($this->view, $this->writer, $this->file, $this->options);
        $this->compiler->events()->attach($this->tags);
    }

    public function testCreatesNoFilesPriorToCompilation()
    {
        $this->tags->createTagPages();
        $this->assertTrue(empty($this->writer->files));
        $this->tags->createTagFeeds('rss');
        $this->tags->createTagFeeds('atom');
        $this->assertTrue(empty($this->writer->files));
    }

    public function testCreatesFilesFollowingCompilation()
    {
        $this->compiler->compile();
        $this->tags->createTagPages();

        $this->assertFalse(empty($this->writer->files));

        $filenameTemplate = $this->options->getByTagFilenameTemplate();
        $filenameTemplate = str_replace('-p%d', '', $filenameTemplate);
        $tagTitleTemplate = $this->options->getByTagTitle();
        foreach ($this->expected['tags'] as $tag) {
            $filename = sprintf($filenameTemplate, $tag);
            $this->assertArrayHasKey($filename, $this->writer->files);
            $tagTitle = sprintf($tagTitleTemplate, $tag);
            $this->assertContains($tagTitle, $this->writer->files[$filename]);
        }
    }

    public function testCreatesFeedsFollowingCompilation()
    {
        $this->compiler->compile();
        $this->tags->createTagFeeds('atom');
        $this->tags->createTagFeeds('rss');

        $this->assertFalse(empty($this->writer->files));

        $filenameTemplate = $this->options->getTagFeedFilenameTemplate();
        $tagTitleTemplate = $this->options->getTagFeedTitleTemplate();
        foreach (array('atom', 'rss') as $type) {
            foreach ($this->expected['tags'] as $tag) {
                $filename = sprintf($filenameTemplate, $tag, $type);
                $this->assertArrayHasKey($filename, $this->writer->files);
                $tagTitle = sprintf($tagTitleTemplate, $tag);
                $this->assertContains($tagTitle, $this->writer->files[$filename]);
            }
        }
    }

    public function testCanCreateTagCloudFollowingCompilation()
    {
        $this->compiler->compile();
        $cloud = $this->tags->getTagCloud();
        $this->assertInstanceOf('Zend\Tag\Cloud', $cloud);
        $markup = $cloud->render();
        foreach ($this->expected['tags'] as $tag) {
            $this->assertContains($tag, $markup);
        }
    }
}

