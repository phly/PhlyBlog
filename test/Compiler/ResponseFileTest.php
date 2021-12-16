<?php

namespace PhlyBlogTest\Compiler;

use PhlyBlog\Compiler\ResponseFile;
use PHPUnit\Framework\TestCase;

class ResponseFileTest extends TestCase
{
    /** @var ResponseFile */
    private $file;

    protected function setUp(): void
    {
        $this->file = new ResponseFile();
    }

    public function testFilenameIsEmptyByDefault(): void
    {
        self::assertNull($this->file->getFilename());
    }

    public function testFilenameIsMutable(): void
    {
        $this->file->setFilename('foo.bar');
        self::assertEquals('foo.bar', $this->file->getFilename());
    }
}
