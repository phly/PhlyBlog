<?php

namespace PhlyBlogTest\Compiler;

use PhlyBlog\Compiler\FileWriter;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function glob;
use function is_dir;
use function rmdir;
use function sys_get_temp_dir;
use function unlink;

class FileWriterTest extends TestCase
{
    /** @var FileWriter */
    private $writer;
    /** @var string */
    private $basePath;

    protected function setUp(): void
    {
        $this->writer   = new FileWriter();
        $this->basePath = sys_get_temp_dir() . '/file_writer';
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
    }

    public function cleanup(): void
    {
        if (! is_dir($this->basePath)) {
            return;
        }

        $this->rrmdir($this->basePath);
    }

    public function rrmdir($dir): void
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->rrmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }

    public function testCreatesFileAndDirectorySpecified(): void
    {
        $filename = $this->basePath . '/foo.bar';
        $data     = 'data';
        $this->writer->write($filename, $data);
        self::assertFileExists($filename);
        $contents = file_get_contents($filename);
        self::assertEquals($data, $contents);
    }
}
