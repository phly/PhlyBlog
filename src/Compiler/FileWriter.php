<?php

namespace PhlyBlog\Compiler;

use function dirname;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function mkdir;

class FileWriter implements WriterInterface
{
    public function write($filename, $data)
    {
        // Ensure the directory exists before writing to it
        $dir = dirname($filename);
        if (! file_exists($dir) || ! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($filename, $data);
    }
}
