<?php
namespace PhlyBlog\Compiler\Listener;

use InvalidArgumentException;
use DomainException;
use PhlyBlog\AuthorEntity;
use PhlyBlog\Compiler\Event;
use PhlyBlog\Compiler\SortedEntries;

class Authors extends AbstractList
{
    protected $authors = array();
    protected $entries;

    public function onCompile(Event $e)
    {
        $entry = $e->getEntry();
        if (!$entry->isPublic()) {
            return;
        }

        $author = $entry->getAuthor();
        if ($author instanceof AuthorEntity) {
            // If we have an AuthorEntity, populate our authors array with it
            $authorName = $author->getId();
            if (!isset($this->authors[$authorName]) || is_string($this->authors[$authorName])) {
                $this->authors[$authorName] = $author;
            }
            $author = $authorName;
        } else {
            // only populate our authors array if we cannot find another
            if (!isset($this->authors[$author])) {
                $this->authors[$author] = $author;
            }
        }
        if (!isset($this->entries[$author])) {
            $this->entries[$author] = new SortedEntries();
        }
        $this->entries[$author]->insert($entry, $entry->getCreated());
    }

    public function onCompileEnd(Event $e)
    {
        foreach ($this->entries as $author => $heap) {
            $this->entries[$author] = iterator_to_array($heap);
        }
    }

    public function compile()
    {
        $this->createAuthorPages();
        $this->createAuthorFeeds('rss');
        $this->createAuthorFeeds('atom');
    }

    public function createAuthorPages($template = null)
    {
        if (!$this->entries) {
            return;
        }

        if (null === $template) {
            $template = $this->options->getByAuthorTemplate();
            if (empty($template)) {
                throw new DomainException('No template provided for listing entries by author');
            }
        }

        $filenameTemplate = $this->options->getByAuthorFilenameTemplate();
        $urlTemplate      = $this->options->getByAuthorUrlTemplate();
        $titleTemplate    = $this->options->getByAuthorTitle();

        foreach ($this->entries as $author => $list) {
            $title = sprintf($titleTemplate, $author);
            if (isset($this->authors[$author])) {
                $authorName = $this->authors[$author];
                if ($authorName instanceof AuthorEntity) {
                    $authorName = $authorName->getName() ?: $author;
                }
                $title = sprintf($titleTemplate, $authorName);
            }
            $this->iterateAndRenderList(
                $list,
                $filenameTemplate,
                array($author),
                $title,
                $urlTemplate,
                $author,
                $template
            );
        }
    }

    public function createAuthorFeeds($type)
    {
        if (!$this->entries) {
            return;
        }

        $type = strtolower($type);
        if (!in_array($type, array('atom', 'rss'))) {
            throw new InvalidArgumentException('Feed type must be "atom" or "rss"');
        }

        $filenameTemplate = $this->options->getAuthorFeedFilenameTemplate();
        $blogLinkTemplate = $this->options->getAuthorFeedBlogLinkTemplate();
        $feedLinkTemplate = $this->options->getAuthorFeedFeedLinkTemplate();
        $titleTemplate    = $this->options->getAuthorFeedTitleTemplate();

        foreach ($this->entries as $author => $list) {
            $title = sprintf($titleTemplate, $author);
            if (isset($this->authors[$author])) {
                $authorName = $this->authors[$author];
                if ($authorName instanceof AuthorEntity) {
                    $authorName = $authorName->getName() ?: $author;
                }
                $title = sprintf($titleTemplate, $authorName);
            }

            $filename = sprintf($filenameTemplate, $author, $type);
            $blogLink = sprintf($blogLinkTemplate, str_replace(' ', '+', $author));
            $feedLink = sprintf($feedLinkTemplate, str_replace(' ', '+', $author), $type);

            $this->iterateAndGenerateFeed(
                $type,
                $list,
                $title,
                $blogLink,
                $feedLink,
                $filename
            );
        }
    }
}
