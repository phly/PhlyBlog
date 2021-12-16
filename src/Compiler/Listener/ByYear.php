<?php

namespace PhlyBlog\Compiler\Listener;

use DomainException;
use Laminas\Stdlib\ArrayUtils;
use PhlyBlog\Compiler\Event;
use PhlyBlog\Compiler\SortedEntries;

use function sprintf;

class ByYear extends AbstractList
{
    protected $years = [];

    public function onCompile(Event $e)
    {
        $entry = $e->getEntry();
        if (! $entry->isPublic()) {
            return;
        }

        $date = $e->getDate();
        $year = $date->format('Y');

        if (! isset($this->years[$year])) {
            $this->years[$year] = new SortedEntries();
        }
        $this->years[$year]->insert($entry, $entry->getCreated());
    }

    public function onCompileEnd(Event $e)
    {
        foreach ($this->years as $year => $heap) {
            $this->years[$year] = ArrayUtils::iteratorToArray($heap);
        }
    }

    public function compile()
    {
        $this->createYearPages();
    }

    public function createYearPages($template = null)
    {
        if (null === $template) {
            $template = $this->options->getByYearTemplate();
            if (empty($template)) {
                throw new DomainException('No template provided for listing entries by year');
            }
        }

        $filenameTemplate = $this->options->getByYearFilenameTemplate();
        $urlTemplate      = $this->options->getByYearUrlTemplate();
        $titleTemplate    = $this->options->getByYearTitle();

        foreach ($this->years as $year => $list) {
            $this->iterateAndRenderList(
                $list,
                $filenameTemplate,
                [$year],
                sprintf($titleTemplate, $year),
                $urlTemplate,
                $year,
                $template
            );
        }
    }
}
