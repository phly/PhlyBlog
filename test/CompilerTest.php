<?php

namespace PhlyBlogTest;

use PhlyBlog\Compiler;
use PhlyBlog\Compiler\PhpFileFilter;
use PhlyBlog\EntryEntity;
use PHPUnit\Framework\TestCase;
use stdClass;

use function file_get_contents;
use function json_decode;

class CompilerTest extends TestCase
{
    /** @var Compiler */
    private $compiler;
    /** @var mixed */
    private $metadata;

    protected function setUp(): void
    {
        $files          = new PhpFileFilter(__DIR__ . '/_posts');
        $this->compiler = new Compiler($files);
        $json           = file_get_contents(__DIR__ . '/_posts/metadata.json');
        $this->metadata = json_decode($json, true);
    }

    public function testTriggersCompileEventForEachValidEntryFile(): void
    {
        $expected = 0;
        foreach ($this->metadata as $entry) {
            if ($entry['draft']) {
                continue;
            }
            $expected++;
        }

        $marker        = new stdClass();
        $marker->count = 0;
        $this->compiler->getEventManager()->attach('compile', function ($e) use ($marker) {
            $marker->count++;
        });

        $this->compiler->compile();

        self::assertEquals($expected, $marker->count);
    }

    public function testCompileEventPassesEntryAndDate(): void
    {
        $self = $this;
        $this->compiler->getEventManager()->attach(
            'compile',
            function ($e) use ($self) {
                $entry = $e->getEntry();
                $self->assertInstanceOf(EntryEntity::class, $entry);

                $date = $e->getDate();
                $self->assertInstanceOf('DateTime', $date);
            }
        );
        $this->compiler->compile();
    }

    public function testCompileEndEventIsTriggeredExactlyOnce(): void
    {
        $marker        = new stdClass();
        $marker->count = 0;
        $this->compiler->getEventManager()->attach(
            'compile.end',
            function ($e) use ($marker) {
                $marker->count++;
            }
        );

        $this->compiler->compile();
        self::assertEquals(1, $marker->count);
    }

    public function testCompileEndEventReceivesEmptyEntryAndDate(): void
    {
        $self = $this;
        $this->compiler->getEventManager()->attach(
            'compile.end',
            function ($e) use ($self) {
                $entry = $e->getEntry();
                $date  = $e->getDate();
                $self->assertNull($entry);
                $self->assertNull($date);
            }
        );

        $this->compiler->compile();
    }
}
