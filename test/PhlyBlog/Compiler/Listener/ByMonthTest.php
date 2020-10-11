<?php

namespace PhlyBlogTest\Compiler\Listener;

use DateTime;
use DateTimeZone;
use PhlyBlog\Compiler\Listener\ByMonth;
use PHPUnit\Framework\TestCase;

use function sprintf;
use function str_replace;

class ByMonthTest extends TestCase
{
    use TestHelper;

    /** @var ByMonth */
    private $byMonth;
    /** @var array */
    private $months;

    protected function setUp(): void
    {
        $this->injectScaffolds();
        $this->byMonth = new ByMonth(
            $this->view,
            $this->writer,
            $this->file,
            $this->options
        );
        $this->compiler->getEventManager()->attach($this->byMonth);

        $this->months = [];
        $self         = $this;
        $this->compiler->getEventManager()->attach(
            'compile',
            function ($e) use ($self) {
                $entry = $e->getEntry();
                if ($entry->isDraft() || ! $entry->isPublic()) {
                    return;
                }

                $created = $entry->getCreated();
                $tz      = $entry->getTimezone();
                $date    = new DateTime();
                $date->setTimezone(new DateTimeZone($tz));
                $date->setTimestamp($created);
                $month                = $date->format('Y/m');
                $self->months[$month] = $date;
            }
        );
    }

    public function testCreatesNoFilesPriorToCompilation(): void
    {
        $this->byMonth->compile();
        self::assertEmpty($this->writer->files);
    }

    public function testCreatesFilesFollowingCompilation(): void
    {
        $this->compiler->compile();
        $this->byMonth->compile();

        self::assertNotEmpty($this->writer->files);
        self::assertNotEmpty($this->months);

        $filenameTemplate   = $this->options->getByMonthFilenameTemplate();
        $filenameTemplate   = str_replace('-p%d', '', $filenameTemplate);
        $monthTitleTemplate = $this->options->getByMonthTitle();
        foreach ($this->months as $month => $date) {
            $filename = sprintf($filenameTemplate, $month);
            self::assertArrayHasKey($filename, $this->writer->files);
            $monthTitle = sprintf($monthTitleTemplate, $date->format('F Y'));
            self::assertStringContainsString(
                $monthTitle,
                $this->writer->files[$filename]
            );
        }
    }
}
