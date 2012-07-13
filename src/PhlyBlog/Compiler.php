<?php
namespace PhlyBlog;

use DateTime;
use DateTimezone;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;

class Compiler implements EventManagerAwareInterface
{
    protected $events;
    protected $files;

    public function __construct(Compiler\PhpFileFilter $files)
    {
        $this->files  = $files;
    }

    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;
        return $this;
    }

    public function getEventManager()
    {
        if (!$this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    /**
     * Prepare the list of entries
     * 
     * Loops through the filesystem tree, looking for PHP files
     * that return EntryEntity objects. For each returned, adds it
     * to:
     *
     * - $entries, which has all entries
     * - $byYear, a hash of year/SortedEntries pairs
     * - $byMonth, a hash of year-month/SortedEntries pairs
     * - $byDay, a hash of year-month-day/SortedEntries pairs
     * - $byTag, a hash of tag/SortedEntries pairs
     * - $byAuthor, a hash of author/SortedEntries pairs
     *
     * @return void
     */
    public function compile()
    {
        $event = new Compiler\Event();
        $event->setTarget($this);

        foreach ($this->files as $file) {
            $entry = include $file->getRealPath();
            if (!$entry instanceof EntryEntity) {
                continue;
            }

            if (!$entry->isValid()) {
                // if we have an invalid entry, we should not continue
                continue;
            }

            if ($entry->isDraft()) {
                continue;
            }

            $date = new DateTime();
            $date->setTimestamp($entry->getCreated())
                 ->setTimezone(new DateTimezone($entry->getTimezone()));

            $event->setEntry($entry);
            $event->setDate($date);
            $this->getEventManager()->trigger('compile', $event);
        }

        $event = new Compiler\Event();
        $event->setTarget($this);
        $this->getEventManager()->trigger('compile.end', $event);
    }
}
