<?php

namespace PhlyBlogTest\Compiler\Listener;

use DateTime;
use DateTimeZone;
use PhlyBlog\Compiler\Listener\ByDate;
use PHPUnit\Framework\TestCase;

use function sprintf;
use function str_replace;

class ByDateTest extends TestCase
{
    use TestHelper;

    /** @var ByDate */
    private $byDate;
    private $dates;

    protected function setUp(): void
    {
        $this->injectScaffolds();
        $this->byDate = new ByDate(
            $this->view,
            $this->writer,
            $this->file,
            $this->options
        );
        $this->compiler->getEventManager()->attach($this->byDate);

        $this->dates = [];
        $self        = $this;
        $this->compiler->getEventManager()->attach(
            'compile',
            function ($e) {
                $entry = $e->getEntry();
                if ($entry->isDraft() || ! $entry->isPublic()) {
                    return;
                }

                $created = $entry->getCreated();
                $tz      = $entry->getTimezone();
                $date    = new DateTime();
                $date->setTimezone(new DateTimeZone($tz));
                $date->setTimestamp($created);
                $month               = $date->format('Y/m/d');
                $this->dates[$month] = $date;
            }
        );
    }

    public function testCreatesNoFilesPriorToCompilation(): void
    {
        $this->byDate->compile();
        self::assertEmpty($this->writer->files);
    }

    public function testCreatesFilesFollowingCompilation(): void
    {
        $this->compiler->compile();
        $this->byDate->compile();

        self::assertNotEmpty($this->writer->files);
        self::assertNotEmpty($this->dates);

        $filenameTemplate  = $this->options->getByDayFilenameTemplate();
        $filenameTemplate  = str_replace('-p%d', '', $filenameTemplate);
        $dateTitleTemplate = $this->options->getByDayTitle();
        foreach ($this->dates as $day => $date) {
            $filename = sprintf($filenameTemplate, $day);
            self::assertArrayHasKey($filename, $this->writer->files);
            $dateTitle = sprintf($dateTitleTemplate, $date->format('d F Y'));
            self::assertStringContainsString(
                $dateTitle,
                $this->writer->files[$filename]
            );
        }
    }
}
