<?php
namespace PhlyBlog\Compiler;

use DateTime;
use PhlyBlog\EntryEntity as Entry;
use Zend\EventManager\Event as BaseEvent;

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
