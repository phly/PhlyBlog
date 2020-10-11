<?php

namespace PhlyBlog\Compiler\Listener;

use DomainException;
use Laminas\Stdlib\ArrayUtils;
use PhlyBlog\Compiler\Event;
use PhlyBlog\Compiler\SortedEntries;

use function date;
use function explode;
use function sprintf;
use function strtotime;

class ByDate extends AbstractList
{
    protected $days = [];

    public function onCompile(Event $e)
    {
        $entry = $e->getEntry();
        if (! $entry->isPublic()) {
            return;
        }

        $date = $e->getDate();
        $day  = $date->format('Y/m/d');

        if (! isset($this->days[$day])) {
            $this->days[$day] = new SortedEntries();
        }
        $this->days[$day]->insert($entry, $entry->getCreated());
    }

    public function onCompileEnd(Event $e)
    {
        foreach ($this->days as $day => $heap) {
            $this->days[$day] = ArrayUtils::iteratorToArray($heap);
        }
    }

    public function compile()
    {
        $this->createDayPages();
    }

    public function createDayPages($template = null)
    {
        if (null === $template) {
            $template = $this->options->getByDayTemplate();
            if (empty($template)) {
                throw new DomainException('No template provided for listing entries by day');
            }
        }

        $filenameTemplate = $this->options->getByDayFilenameTemplate();
        $urlTemplate      = $this->options->getByDayUrlTemplate();
        $titleTemplate    = $this->options->getByDayTitle();

        foreach ($this->days as $day => $list) {
            // Get the year, month, and day digits
            [$year, $month, $date] = explode('/', $day, 3);

            $this->iterateAndRenderList(
                $list,
                $filenameTemplate,
                [$day],
                sprintf(
                    $titleTemplate,
                    $date . ' ' . date(
                        'F',
                        strtotime($year . '-' . $month . '-' . $date)
                    ) . ' ' . $year
                ),
                $urlTemplate,
                $day,
                $template
            );
        }
    }
}
