<?php
namespace PhlyBlog\Compiler;

use PhlyBlog\EntryEntity as Entry;
use Zend\EventManager\Event as BaseEvent;

class Event extends BaseEvent
{
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
}
