<?php
namespace PhlyBlog\Compiler;

use PHPUnit_Framework_TestCase as TestCase;

class FileWriterTest extends TestCase
{
    public function setUp()
    {
        $this->writer   = new FileWriter();
        $this->basePath = sys_get_temp_dir() . '/file_writer';
        $this->cleanup();
    }

    public function tearDown()
    {
        $this->cleanup();
    }

    public function cleanup()
    {
        if (!is_dir($this->basePath)) {
            return;
        }
        
        $this->rrmdir($this->basePath);
    }

    public function rrmdir($dir) 
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

    public function testCreatesFileAndDirectorySpecified()
    {
        $filename = $this->basePath . '/foo.bar';
        $data     = 'data';
        $this->writer->write($filename, $data);
        $this->assertTrue(file_exists($filename));
        $contents = file_get_contents($filename);
        $this->assertEquals($data, $contents);
    }
}
