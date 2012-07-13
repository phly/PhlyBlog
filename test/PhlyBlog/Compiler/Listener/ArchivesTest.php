<?php
namespace PhlyBlog\Compiler\Listener;

use PHPUnit_Framework_TestCase as TestCase;

class ArchivesTest extends TestCase
{
    public function setUp()
    {
        TestHelper::injectScaffolds($this);
        $this->archives = new Archives($this->view, $this->writer, $this->file, $this->options);
        $this->compiler->getEventManager()->attach($this->archives);
    }

    public function testCreatesNoFilesPriorToCompilation()
    {
        $this->archives->compile();
        $this->assertTrue(empty($this->writer->files));
    }

    public function testCreatesFilesFollowingCompilation()
    {
        $this->compiler->compile();
        $this->archives->createArchivePages();

        $expected = ceil(count($this->metadata) / 10);
        $this->assertEquals($expected, count($this->writer->files));

        $count = 1;
        foreach ($this->writer->files as $filename => $content) {
            if ($count == 1) {
                continue;
            }
            $this->assertContains('-p' . $count, $filename);
            $this->assertContains('Previous', $content);
            $count++;
        }
    }

    public function testCreatesFeedsFollowingCompilation()
    {
        $this->compiler->compile();
        $this->archives->compile();

        $this->assertFalse(empty($this->writer->files));

        $feedFilenameTemplate = $this->options->getFeedFilename();
        $title                = $this->options->getFeedTitle();
        foreach (array('atom', 'rss') as $type) {
            $filename = sprintf($feedFilenameTemplate, $type);
            $this->assertArrayHasKey($filename, $this->writer->files);
            $this->assertContains($title, $this->writer->files[$filename]);
        }
    }
}
