<?php

namespace PhlyBlogTest\Compiler\TestAsset;

use PhlyBlog\Compiler\WriterInterface;

class MockWriter implements WriterInterface
{
    public $files = [];

    public function write($filename, $data): void
    {
        $this->files[$filename] = $data;
    }
}
