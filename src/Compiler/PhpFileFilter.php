<?php

namespace PhlyBlog\Compiler;

use DirectoryIterator;
use FilterIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function is_dir;
use function is_string;

/**
 * Usage:
 *
 * <code>
 * $files = new PhpFileFilter($path);
 *
 * // or
 * $dir   = new DirectoryIterator($path);
 * $files = new PhpFileIterator($dir);
 *
 * // or
 * $dir   = new RecursiveDirectoryIterator($path);
 * $files = new PhpFileIterator($dir);
 * </code>
 */
class PhpFileFilter extends FilterIterator
{
    public function __construct($dirOrIterator = '.')
    {
        if (is_string($dirOrIterator)) {
            if (! is_dir($dirOrIterator)) {
                throw new InvalidArgumentException(sprintf(
                    'Expected a valid directory name; received "%s"',
                    $dirOrIterator
                ));
            }

            $dirOrIterator = new RecursiveDirectoryIterator($dirOrIterator);
        }

        if (! $dirOrIterator instanceof DirectoryIterator) {
            throw new InvalidArgumentException('Expected a DirectoryIterator');
        }

        $iterator = $dirOrIterator instanceof RecursiveIterator
            ? new RecursiveIteratorIterator($dirOrIterator)
            : $dirOrIterator;

        parent::__construct($iterator);
        $this->rewind();
    }

    public function accept()
    {
        $current = $this->getInnerIterator()->current();
        if (! $current instanceof SplFileInfo) {
            return false;
        }

        if (! $current->isFile()) {
            return false;
        }

        $ext = $current->getExtension();
        if ($ext != 'php') {
            return false;
        }

        return true;
    }
}
