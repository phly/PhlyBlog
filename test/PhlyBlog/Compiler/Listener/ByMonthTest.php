<?php
namespace PhlyBlog\Compiler\Listener;

use DateTime;
use DateTimezone;
use PHPUnit_Framework_TestCase as TestCase;

class ByMonthTest extends TestCase
{
    public function setUp()
    {
        TestHelper::injectScaffolds($this);
        $this->byMonth = new ByMonth($this->view, $this->writer, $this->file, $this->options);
        $this->compiler->getEventManager()->attach($this->byMonth);

        $this->months = array();
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
            $month   = $date->format('Y/m');
            $self->months[$month] = $date;
        });
    }

    public function testCreatesNoFilesPriorToCompilation()
    {
        $this->byMonth->compile();
        $this->assertTrue(empty($this->writer->files));
    }

    public function testCreatesFilesFollowingCompilation()
    {
        $this->compiler->compile();
        $this->byMonth->compile();

        $this->assertFalse(empty($this->writer->files));
        $this->assertFalse(empty($this->months));

        $filenameTemplate = $this->options->getByMonthFilenameTemplate();
        $filenameTemplate = str_replace('-p%d', '', $filenameTemplate);
        $monthTitleTemplate = $this->options->getByMonthTitle();
        foreach ($this->months as $month => $date) {
            $filename = sprintf($filenameTemplate, $month);
            $this->assertArrayHasKey($filename, $this->writer->files);
            $monthTitle = sprintf($monthTitleTemplate, $date->format('F Y'));
            $this->assertContains($monthTitle, $this->writer->files[$filename]);
        }
    }
}
