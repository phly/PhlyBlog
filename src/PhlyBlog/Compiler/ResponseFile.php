<?php
namespace PhlyBlog\Compiler;

use stdClass;
use Zend\View\View;

class ResponseFile
{
    protected $filename;

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }
}

