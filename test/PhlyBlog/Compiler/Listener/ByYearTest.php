<?php
namespace PhlyBlog\Compiler\Listener;

use PHPUnit_Framework_TestCase as TestCase;

class ByYearTest extends TestCase
{
    public function setUp()
    {
        TestHelper::injectScaffolds($this);
        $this->byYear = new ByYear($this->view, $this->writer, $this->file, $this->options);
        $this->compiler->getEventManager()->attach($this->byYear);
    }

    public function testCreatesNoFilesPriorToCompilation()
    {
        $this->byYear->compile();
        $this->assertTrue(empty($this->writer->files));
    }

    public function testCreatesFilesFollowingCompilation()
    {
        $this->compiler->compile();
        $this->byYear->compile();

        $this->assertFalse(empty($this->writer->files));

        $filenameTemplate = $this->options->getByYearFilenameTemplate();
        $filenameTemplate = str_replace('-p%d', '', $filenameTemplate);
        $yearTitleTemplate = $this->options->getByYearTitle();
        foreach ($this->expected['years'] as $year) {
            $filename = sprintf($filenameTemplate, $year);
            $this->assertArrayHasKey($filename, $this->writer->files);
            $yearTitle = sprintf($yearTitleTemplate, $year);
            $this->assertContains($yearTitle, $this->writer->files[$filename]);
        }
    }
}
