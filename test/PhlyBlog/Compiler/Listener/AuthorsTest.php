<?php
namespace PhlyBlog\Compiler\Listener;

use PHPUnit_Framework_TestCase as TestCase;

class AuthorsTest extends TestCase
{
    public function setUp()
    {
        TestHelper::injectScaffolds($this);
        $this->authors = new Authors($this->view, $this->writer, $this->file, $this->options);
        $this->compiler->getEventManager()->attach($this->authors);
    }

    public function testCreatesNoFilesPriorToCompilation()
    {
        $this->authors->compile();
        $this->assertTrue(empty($this->writer->files));
    }

    public function testCreatesFilesFollowingCompilation()
    {
        $this->compiler->compile();
        $this->authors->compile();

        $this->assertFalse(empty($this->writer->files));

        $filenameTemplate = $this->options->getByAuthorFilenameTemplate();
        $filenameTemplate = str_replace('-p%d', '', $filenameTemplate);
        $authorTitleTemplate = $this->options->getByAuthorTitle();
        foreach ($this->expected['authors'] as $author) {
            $filename = sprintf($filenameTemplate, $author['id']);
            $this->assertArrayHasKey($filename, $this->writer->files);
            $authorTitle = sprintf($authorTitleTemplate, $author['name']);
            $this->assertContains($authorTitle, $this->writer->files[$filename]);
        }
    }

    public function testCreatesFeedsFollowingCompilation()
    {
        $this->compiler->compile();
        $this->authors->compile();

        $this->assertFalse(empty($this->writer->files));

        $filenameTemplate    = $this->options->getAuthorFeedFilenameTemplate();
        $authorTitleTemplate = $this->options->getAuthorFeedTitleTemplate();
        foreach (array('atom', 'rss') as $type) {
            foreach ($this->expected['authors'] as $author) {
                $filename = sprintf($filenameTemplate, $author['id'], $type);
                $this->assertArrayHasKey($filename, $this->writer->files);
                $authorTitle = sprintf($authorTitleTemplate, $author['name']);
                $this->assertContains($authorTitle, $this->writer->files[$filename]);
            }
        }
    }
}
