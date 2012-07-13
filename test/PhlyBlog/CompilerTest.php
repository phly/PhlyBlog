<?php
namespace PhlyBlog;

use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

class CompilerTest extends TestCase
{
    public function setUp()
    {
        $this->files    = new Compiler\PhpFileFilter(__DIR__ . '/_posts');
        $this->compiler = new Compiler($this->files);
        $json           = file_get_contents(__DIR__ . '/_posts/metadata.json');
        $this->metadata = json_decode($json, true);
    }

    public function testTriggersCompileEventForEachValidEntryFile()
    {
        $expected = 0;
        foreach ($this->metadata as $entry) {
            if ($entry['draft']) {
                continue;
            }
            $expected++;
        }

        $marker = new stdClass;
        $marker->count = 0;
        $this->compiler->getEventManager()->attach('compile', function($e) use ($marker) {
            $marker->count++;
        });

        $this->compiler->compile();

        $this->assertEquals($expected, $marker->count);
    }

    public function testCompileEventPassesEntryAndDate()
    {
        $self = $this;
        $this->compiler->getEventManager()->attach('compile', function($e) use ($self) {
            $entry = $e->getEntry();
            $self->assertInstanceOf('PhlyBlog\EntryEntity', $entry);

            $date = $e->getDate();
            $self->assertInstanceOf('DateTime', $date);
        });
        $this->compiler->compile();
    }

    public function testCompileEndEventIsTriggeredExactlyOnce()
    {
        $marker = new stdClass;
        $marker->count = 0;
        $this->compiler->getEventManager()->attach('compile.end', function($e) use ($marker) {
            $marker->count++;
        });

        $this->compiler->compile();
        $this->assertEquals(1, $marker->count);
    }

    public function testCompileEndEventReceivesEmptyEntryAndDate()
    {
        $self = $this;
        $this->compiler->getEventManager()->attach('compile.end', function($e) use ($self) {
            $entry = $e->getEntry();
            $date  = $e->getDate();
            $self->assertNull($entry);
            $self->assertNull($date);
        });

        $this->compiler->compile();
    }
}
