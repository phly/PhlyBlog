<?php
namespace PhlyBlog\Compiler\Listener;

use PHPUnit_Framework_TestCase as TestCase;

class EntriesTest extends TestCase
{
    public function setUp()
    {
        TestHelper::injectScaffolds($this);
        $this->entries = new Entries($this->view, $this->file, $this->options);
        $this->compiler->getEventManager()->attach($this->entries);
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
