<?php

namespace PhlyBlogTest\Compiler\Listener;

use PhlyBlog\Compiler\Listener\Authors;
use PHPUnit\Framework\TestCase;

use function sprintf;
use function str_replace;

class AuthorsTest extends TestCase
{
    use TestHelper;

    /** @var Authors */
    private $authors;

    protected function setUp(): void
    {
        $this->injectScaffolds();
        $this->authors = new Authors(
            $this->view,
            $this->writer,
            $this->file,
            $this->options
        );
        $this->compiler->getEventManager()->attach($this->authors);
    }

    public function testCreatesNoFilesPriorToCompilation(): void
    {
        $this->authors->compile();
        self::assertEmpty($this->writer->files);
    }

    public function testCreatesFilesFollowingCompilation(): void
    {
        $this->compiler->compile();
        $this->authors->compile();

        self::assertNotEmpty($this->writer->files);

        $filenameTemplate    = $this->options->getByAuthorFilenameTemplate();
        $filenameTemplate    = str_replace('-p%d', '', $filenameTemplate);
        $authorTitleTemplate = $this->options->getByAuthorTitle();
        foreach ($this->expected['authors'] as $author) {
            $filename = sprintf($filenameTemplate, $author['id']);
            self::assertArrayHasKey($filename, $this->writer->files);
            $authorTitle = sprintf($authorTitleTemplate, $author['name']);
            self::assertStringContainsString(
                $authorTitle,
                $this->writer->files[$filename]
            );
        }
    }

    public function testCreatesFeedsFollowingCompilation(): void
    {
        $this->compiler->compile();
        $this->authors->compile();

        self::assertNotEmpty($this->writer->files);

        $filenameTemplate    = $this->options->getAuthorFeedFilenameTemplate();
        $authorTitleTemplate = $this->options->getAuthorFeedTitleTemplate();
        foreach (['atom', 'rss'] as $type) {
            foreach ($this->expected['authors'] as $author) {
                $filename = sprintf($filenameTemplate, $author['id'], $type);
                self::assertArrayHasKey($filename, $this->writer->files);
                $authorTitle = sprintf($authorTitleTemplate, $author['name']);
                self::assertStringContainsString(
                    $authorTitle,
                    $this->writer->files[$filename]
                );
            }
        }
    }
}
