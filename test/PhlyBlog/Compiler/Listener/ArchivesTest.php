<?php

namespace PhlyBlogTest\Compiler\Listener;

use PhlyBlog\Compiler\Listener\Archives;
use PhlyBlogTest\ReflectionUtil;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function ceil;
use function count;
use function sprintf;

class ArchivesTest extends TestCase
{
    use TestHelper;

    /** @var Archives */
    private $archives;

    protected function setUp(): void
    {
        $this->injectScaffolds();
        $this->archives = new Archives(
            $this->view,
            $this->writer,
            $this->file,
            $this->options
        );
        $this->archives->attach($this->compiler->getEventManager());
    }

    public function testCreatesNoFilesPriorToCompilation(): void
    {
        $this->archives->compile();
        self::assertEmpty($this->writer->files);
    }

    public function testCreatesFilesFollowingCompilation(): void
    {
        $this->compiler->compile();
        $this->archives->createArchivePages();

        $expected = ceil(count($this->metadata) / 10);
        self::assertCount($expected, $this->writer->files);

        $count = 1;
        foreach ($this->writer->files as $filename => $content) {
            if ($count === 1) {
                continue;
            }
            self::assertStringContainsString('-p' . $count, $filename);
            self::assertContains('Previous', $content);
            $count++;
        }
    }

    public function testCreatesFeedsFollowingCompilation(): void
    {
        $this->compiler->compile();
        $this->archives->compile();

        self::assertNotEmpty($this->writer->files);

        $feedFilenameTemplate = $this->options->getFeedFilename();
        $title                = $this->options->getFeedTitle();
        foreach (['atom', 'rss'] as $type) {
            $filename = sprintf($feedFilenameTemplate, $type);
            self::assertArrayHasKey($filename, $this->writer->files);
            self::assertStringContainsString(
                $title,
                $this->writer->files[$filename]
            );
        }
    }

    public function testDetachListeners(): void
    {
        $currentListeners = ReflectionUtil::getProperty($this->archives, 'listeners');
        if (count($currentListeners) !== 2) {
            throw new RuntimeException(
                sprintf(
                    'Precondition failed: There should be exactly one listener attached, found %d',
                    count($currentListeners)
                )
            );
        }

        $this->archives->detach($this->compiler->getEventManager());

        self::assertCount(0, ReflectionUtil::getProperty($this->archives, 'listeners'));
    }
}
