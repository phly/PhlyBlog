<?php

namespace PhlyBlog\Compiler;

use DateTime;
use Laminas\EventManager\Event as BaseEvent;
use PhlyBlog\EntryEntity as Entry;

class Event extends BaseEvent
{
    protected $date;
    protected $entry;

    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
        return $this;
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function setDate(DateTime $date)
    {
        $this->date = $date;
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }
}
