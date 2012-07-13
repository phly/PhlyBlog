<?php
namespace PhlyBlog\Compiler\Listener;

use DateTime;
use DateTimezone;
use PHPUnit_Framework_TestCase as TestCase;

class ByDateTest extends TestCase
{
    public function setUp()
    {
        TestHelper::injectScaffolds($this);
        $this->byDate = new ByDate($this->view, $this->writer, $this->file, $this->options);
        $this->compiler->getEventManager()->attach($this->byDate);

        $this->dates = array();
        $self = $this;
        $this->compiler->getEventManager()->attach('compile', function($e) use ($self) {
            $entry = $e->getEntry();
            if ($entry->isDraft() || !$entry->isPublic()) {
                return;
            }

            $created = $entry->getCreated();
            $tz      = $entry->getTimezone();
            $date    = new DateTime();
            $date->setTimezone(new DateTimezone($tz));
            $date->setTimestamp($created);
            $month   = $date->format('Y/m/d');
            $self->dates[$month] = $date;
        });
    }

    public function testCreatesNoFilesPriorToCompilation()
    {
        $this->byDate->compile();
        $this->assertTrue(empty($this->writer->files));
    }

    public function testCreatesFilesFollowingCompilation()
    {
        $this->compiler->compile();
        $this->byDate->compile();

        $this->assertFalse(empty($this->writer->files));
        $this->assertFalse(empty($this->dates));

        $filenameTemplate = $this->options->getByDayFilenameTemplate();
        $filenameTemplate = str_replace('-p%d', '', $filenameTemplate);
        $dateTitleTemplate = $this->options->getByDayTitle();
        foreach ($this->dates as $day => $date) {
            $filename = sprintf($filenameTemplate, $day);
            $this->assertArrayHasKey($filename, $this->writer->files);
            $dateTitle = sprintf($dateTitleTemplate, $date->format('d F Y'));
            $this->assertContains($dateTitle, $this->writer->files[$filename]);
        }
    }
}
