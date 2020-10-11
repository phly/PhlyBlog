<?php

namespace PhlyBlogTest\Compiler\Listener;

use PhlyBlog\Compiler\Listener\ByYear;
use PHPUnit\Framework\TestCase;

use function sprintf;
use function str_replace;

class ByYearTest extends TestCase
{
    use TestHelper;

    /** @var ByYear */
    private $byYear;

    protected function setUp(): void
    {
        $this->injectScaffolds();
        $this->byYear = new ByYear(
            $this->view,
            $this->writer,
            $this->file,
            $this->options
        );
        $this->compiler->getEventManager()->attach($this->byYear);
    }

    public function testCreatesNoFilesPriorToCompilation(): void
    {
        $this->byYear->compile();
        self::assertEmpty($this->writer->files);
    }

    public function testCreatesFilesFollowingCompilation(): void
    {
        $this->compiler->compile();
        $this->byYear->compile();

        self::assertNotEmpty($this->writer->files);

        $filenameTemplate  = $this->options->getByYearFilenameTemplate();
        $filenameTemplate  = str_replace('-p%d', '', $filenameTemplate);
        $yearTitleTemplate = $this->options->getByYearTitle();
        foreach ($this->expected['years'] as $year) {
            $filename = sprintf($filenameTemplate, $year);
            self::assertArrayHasKey($filename, $this->writer->files);
            $yearTitle = sprintf($yearTitleTemplate, $year);
            self::assertStringContainsString(
                $yearTitle,
                $this->writer->files[$filename]
            );
        }
    }
}
