<?php
namespace PhlyBlog\Compiler\Listener;

use DomainException;
use PhlyBlog\Compiler\Event;
use PhlyBlog\Compiler\SortedEntries;

class ByMonth extends AbstractList
{
    protected $months = array();

    public function onCompile(Event $e)
    {
        $entry = $e->getEntry();
        if (!$entry->isPublic()) {
            return;
        }

        $date  = $e->getDate();
        $month = $date->format('Y/m');

        if (!isset($this->months[$month])) {
            $this->months[$month] = new SortedEntries();
        }
        $this->months[$month]->insert($entry, $entry->getCreated());
    }

    public function onCompileEnd(Event $e)
    {
        foreach ($this->months as $month => $heap) {
            $this->months[$month] = iterator_to_array($heap);
        }
    }

    public function compile()
    {
        $this->createMonthPages();
    }

    public function createMonthPages($template = null)
    {
        if (null === $template) {
            $template = $this->options->getByMonthTemplate();
            if (empty($template)) {
                throw new DomainException('No template provided for listing entries by month');
            }
        }

        $filenameTemplate = $this->options->getByMonthFilenameTemplate();
        $urlTemplate      = $this->options->getByMonthUrlTemplate();
        $titleTemplate    = $this->options->getByMonthTitle();

        foreach ($this->months as $month => $list) {
            // Get the year and month digits
            list($year, $monthDigit) = explode('/', $month, 2);

            $this->iterateAndRenderList(
                $list,
                $filenameTemplate,
                array($month),
                sprintf($titleTemplate, date('F', strtotime($year . '-' . $monthDigit . '-01')) . ' ' . $year),
                $urlTemplate,
                $month,
                $template
            );
        }
    }
}
