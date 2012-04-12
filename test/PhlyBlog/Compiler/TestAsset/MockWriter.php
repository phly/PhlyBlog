<?php
namespace PhlyBlog\Compiler\TestAsset;

use PhlyBlog\Compiler\WriterInterface;

class MockWriter implements WriterInterface
{
    public $files = array();

    public function write($filename, $data)
    {
        $this->files[$filename] = $data;
    }
}

