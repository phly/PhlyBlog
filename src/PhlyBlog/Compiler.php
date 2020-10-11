<?php

namespace PhlyBlog;

use DateTime;
use DateTimeZone;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use RuntimeException;

class Compiler implements EventManagerAwareInterface
{
    protected $events;
    protected $files;

    public function __construct(Compiler\PhpFileFilter $files)
    {
        $this->files = $files;
    }

    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(
            [
                self::class,
                static::class,
            ]
        );
        $this->events = $events;
        return $this;
    }

    public function getEventManager()
    {
        if (! $this->events) {
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
     */
    public function compile()
    {
        $event = new Compiler\Event();
        $event->setTarget($this);

        foreach ($this->files as $file) {
            $entry = include $file->getRealPath();
            if (! $entry instanceof EntryEntity) {
                continue;
            }

            if (! $entry->isValid()) {
                // If we have an invalid entry, we should not continue
                throw new RuntimeException(sprintf(
                    "Not valid post file: \n%s",
                    implode("\n", array_map(function (array $errorMessages) {
                        $message = array_shift($errorMessages);
                        return '- ' . $message;
                    }, $entry->getErrorMessages()))
                ));
            }

            if ($entry->isDraft()) {
                continue;
            }

            $date = new DateTime();
            $date->setTimestamp($entry->getCreated())
                ->setTimezone(new DateTimeZone($entry->getTimezone()));

            $event->setEntry($entry);
            $event->setDate($date);
            $this->getEventManager()->trigger('compile', $event);
        }

        $event = new Compiler\Event();
        $event->setTarget($this);
        $this->getEventManager()->trigger('compile.end', $event);
    }
}
