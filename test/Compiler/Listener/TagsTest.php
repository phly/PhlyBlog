<?php

namespace PhlyBlogTest\Compiler\Listener;

use Laminas\Tag\Cloud;
use PhlyBlog\Compiler\Listener\Tags;
use PHPUnit\Framework\TestCase;

use function sprintf;
use function str_replace;

class TagsTest extends TestCase
{
    use TestHelper;

    /** @var Tags */
    private $tags;

    protected function setUp(): void
    {
        $this->injectScaffolds();
        $this->tags = new Tags(
            $this->view,
            $this->writer,
            $this->file,
            $this->options
        );
        $this->tags->attach($this->compiler->getEventManager());
    }

    public function testCreatesNoFilesPriorToCompilation(): void
    {
        $this->tags->compile();
        self::assertEmpty($this->writer->files);
    }

    public function testCreatesFilesFollowingCompilation(): void
    {
        $this->compiler->compile();
        $this->tags->createTagPages();

        self::assertNotEmpty($this->writer->files);

        $filenameTemplate = $this->options->getByTagFilenameTemplate();
        $filenameTemplate = str_replace('-p%d', '', $filenameTemplate);
        $tagTitleTemplate = $this->options->getByTagTitle();
        foreach ($this->expected['tags'] as $tag) {
            $filename = sprintf($filenameTemplate, $tag);
            self::assertArrayHasKey($filename, $this->writer->files);
            $tagTitle = sprintf($tagTitleTemplate, $tag);
            self::assertStringContainsString(
                $tagTitle,
                $this->writer->files[$filename]
            );
        }
    }

    public function testCreatesFeedsFollowingCompilation(): void
    {
        $this->compiler->compile();
        $this->tags->createTagFeeds('atom');
        $this->tags->createTagFeeds('rss');

        self::assertNotEmpty($this->writer->files);

        $filenameTemplate = $this->options->getTagFeedFilenameTemplate();
        $tagTitleTemplate = $this->options->getTagFeedTitleTemplate();
        foreach (['atom', 'rss'] as $type) {
            foreach ($this->expected['tags'] as $tag) {
                $filename = sprintf($filenameTemplate, $tag, $type);
                self::assertArrayHasKey($filename, $this->writer->files);
                $tagTitle = sprintf($tagTitleTemplate, $tag);
                self::assertStringContainsString(
                    $tagTitle,
                    $this->writer->files[$filename]
                );
            }
        }
    }

    public function testCanCreateTagCloudFollowingCompilation(): void
    {
        $this->compiler->compile();
        $cloud = $this->tags->getTagCloud();
        self::assertInstanceOf(Cloud::class, $cloud);
        $markup = $cloud->render();
        foreach ($this->expected['tags'] as $tag) {
            self::assertStringContainsString($tag, $markup);
        }
    }
}
