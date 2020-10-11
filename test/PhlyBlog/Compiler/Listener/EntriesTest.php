<?php

namespace PhlyBlogTest\Compiler\Listener;

use PhlyBlog\Compiler\Listener\Entries;
use PHPUnit\Framework\TestCase;

use function sprintf;

class EntriesTest extends TestCase
{
    use TestHelper;

    /** @var Entries */
    private $entries;

    protected function setUp(): void
    {
        $this->injectScaffolds();
        $this->entries = new Entries($this->view, $this->file, $this->options);
        $this->entries->attach($this->compiler->getEventManager());
    }

    public function testCreatesNoFilesPriorToCompilation(): void
    {
        $this->entries->createEntries();
        self::assertEmpty($this->writer->files);
    }

    public function testCanCreateFilesFollowingCompilation(): void
    {
        $expected = 0;
        foreach ($this->metadata as $entry) {
            if ($entry['draft']) {
                continue;
            }
            $expected++;
        }
        $this->compiler->compile();
        $this->entries->createEntries();
        self::assertCount($expected, $this->writer->files);
    }

    public function testFilesCreatedContainExpectedArtifacts(): void
    {
        $this->compiler->compile();
        $this->entries->createEntries();

        $filenameTemplate = $this->options->getEntryFilenameTemplate();
        foreach ($this->metadata as $entry) {
            if ($entry['draft']) {
                continue;
            }
            $id = $entry['id'];

            $filename = sprintf($filenameTemplate, $id);
            self::assertArrayHasKey($filename, $this->writer->files);
            $content = $this->writer->files[$filename];
            self::assertStringContainsString($entry['title'], $content);
        }
    }
}
