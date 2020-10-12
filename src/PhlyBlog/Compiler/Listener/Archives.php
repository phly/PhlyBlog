<?php

namespace PhlyBlog\Compiler\Listener;

use DomainException;
use InvalidArgumentException;
use Laminas\Stdlib\ArrayUtils;
use PhlyBlog\Compiler\Event;
use PhlyBlog\Compiler\SortedEntries;

use function in_array;
use function strtolower;

class Archives extends AbstractList
{
    protected $archives;

    public function onCompile(Event $e)
    {
        $entry = $e->getEntry();
        if (! $entry->isPublic()) {
            return;
        }

        if (null === $this->archives) {
            $this->archives = new SortedEntries();
        }
        $this->archives->insert($entry, $entry->getCreated());
    }

    public function onCompileEnd(Event $e)
    {
        $this->archives = ArrayUtils::iteratorToArray($this->archives ?? []);
    }

    public function compile()
    {
        $this->createArchivePages();
        $this->createArchiveFeed('rss');
        $this->createArchiveFeed('atom');
    }

    public function createArchivePages($template = null)
    {
        if (null === $template) {
            $template = $this->options->getEntriesTemplate();
            if (empty($template)) {
                throw new DomainException('No template provided for listing entries');
            }
        }
        $filenameTemplate = $this->options->getEntriesFilenameTemplate();
        $urlTemplate      = $this->options->getEntriesUrlTemplate();
        $title            = $this->options->getEntriesTitle();

        $this->iterateAndRenderList(
            $this->archives,
            $filenameTemplate,
            [],
            $title,
            $urlTemplate,
            false,
            $template
        );
    }

    public function createArchiveFeed($type, $title = '')
    {
        $type = strtolower($type);
        if (! in_array($type, ['atom', 'rss'])) {
            throw new InvalidArgumentException(
                'Feed type must be "atom" or "rss"'
            );
        }

        $filename = $this->options->getFeedFilename();
        $blogLink = $this->options->getFeedBlogLink();
        $feedLink = $this->options->getFeedFeedLink();
        $title    = $this->options->getFeedTitle();

        $this->iterateAndGenerateFeed(
            $type,
            $this->archives,
            $title,
            $blogLink,
            $feedLink,
            $filename
        );
    }
}
