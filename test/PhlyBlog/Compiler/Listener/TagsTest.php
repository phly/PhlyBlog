<?php
namespace PhlyBlog\Compiler\Listener;

use PHPUnit_Framework_TestCase as TestCase;

class TagsTest extends TestCase
{
    public function setUp()
    {
        TestHelper::injectScaffolds($this);
        $this->tags = new Tags($this->view, $this->writer, $this->file, $this->options);
        $this->compiler->getEventManager()->attach($this->tags);
    }

    public function testCreatesNoFilesPriorToCompilation()
    {
        $this->tags->compile();
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

