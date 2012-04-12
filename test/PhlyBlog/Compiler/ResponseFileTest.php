<?php
namespace PhlyBlog\Compiler;

use PHPUnit_Framework_TestCase as TestCase;

class ResponseFileTest extends TestCase
{
    public function setUp()
    {
        $this->file = new ResponseFile();
    }

    public function testFilenameIsEmptyByDefault()
    {
        $this->assertNull($this->file->getFilename());
    }

    public function testFilenameIsMutable()
    {
        $this->file->setFilename('foo.bar');
        $this->assertEquals('foo.bar', $this->file->getFilename());
    }
}
