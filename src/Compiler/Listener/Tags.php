<?php

namespace PhlyBlog\Compiler\Listener;

use DomainException;
use InvalidArgumentException;
use Laminas\Tag\Cloud as TagCloud;
use PhlyBlog\Compiler\Event;
use PhlyBlog\Compiler\SortedEntries;

use function count;
use function in_array;
use function iterator_to_array;
use function sprintf;
use function str_replace;
use function strtolower;

class Tags extends AbstractList
{
    protected $tagCloud;
    protected $tags = [];

    public function onCompile(Event $e)
    {
        $entry = $e->getEntry();
        if (! $entry->isPublic()) {
            return;
        }

        foreach ($entry->getTags() as $tag) {
            if (! isset($this->tags[$tag])) {
                $this->tags[$tag] = new SortedEntries();
            }
            $this->tags[$tag]->insert($entry, $entry->getCreated());
        }
    }

    public function onCompileEnd(Event $e)
    {
        foreach ($this->tags as $tag => $heap) {
            $this->tags[$tag] = iterator_to_array($heap);
        }
    }

    public function compile()
    {
        $this->createTagPages();
        $this->createTagFeeds('rss');
        $this->createTagFeeds('atom');
    }

    public function getTagCloud()
    {
        if ($this->tagCloud) {
            return $this->tagCloud;
        }

        $tagUrlTemplate = $this->options->getTagCloudUrlTemplate();
        $cloudOptions   = $this->options->getTagCloudOptions();

        $tags = [];
        foreach ($this->tags as $tag => $list) {
            $tags[$tag] = [
                'title'  => $tag,
                'weight' => count($list),
                'params' => [
                    'url' => sprintf(
                        $tagUrlTemplate,
                        str_replace(' ', '+', $tag)
                    ),
                ],
            ];
        }
        $options['tags'] = $tags;

        $this->tagCloud = new TagCloud($options);
        return $this->tagCloud;
    }

    public function createTagPages($template = null)
    {
        if (null === $template) {
            $template = $this->options->getByTagTemplate();
            if (empty($template)) {
                throw new DomainException('No template provided for listing entries by tag');
            }
        }

        $filenameTemplate = $this->options->getByTagFilenameTemplate();
        $urlTemplate      = $this->options->getByTagUrlTemplate();
        $titleTemplate    = $this->options->getByTagTitle();

        foreach ($this->tags as $tag => $list) {
            $this->iterateAndRenderList(
                $list,
                $filenameTemplate,
                [$tag],
                sprintf($titleTemplate, $tag),
                $urlTemplate,
                $tag,
                $template
            );
        }
    }

    public function createTagFeeds($type)
    {
        $type = strtolower($type);
        if (! in_array($type, ['atom', 'rss'])) {
            throw new InvalidArgumentException(
                'Feed type must be "atom" or "rss"'
            );
        }

        $filenameTemplate = $this->options->getTagFeedFilenameTemplate();
        $blogLinkTemplate = $this->options->getTagFeedBlogLinkTemplate();
        $feedLinkTemplate = $this->options->getTagFeedFeedLinkTemplate();
        $titleTemplate    = $this->options->getTagFeedTitleTemplate();

        foreach ($this->tags as $tag => $list) {
            $title    = sprintf($titleTemplate, $tag);
            $filename = sprintf($filenameTemplate, $tag, $type);
            $blogLink = sprintf($blogLinkTemplate, str_replace(' ', '+', $tag));
            $feedLink = sprintf($feedLinkTemplate, str_replace(' ', '+', $tag), $type);

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
