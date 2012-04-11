<?php
namespace PhlyBlog\Compiler;

use Zend\View\View;

class ResponseStrategy
{
    protected $file;
    protected $writer;

    public function __construct(WriterInterface $writer, ResponseFile $file, View $view)
    {
        $this->writer = $writer;
        $this->file   = $file;

        $view->addResponseStrategy(array($this, 'onResponse'));
    }

    public function onResponse($e)
    {
        $result = $e->getResult();
        $file   = $this->file->getFilename();
        $dir    = dirname($file);
        if (!file_exists($dir) || !is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (preg_match('/-p1.html$/', $file)) {
            $file = preg_replace('/-p1(\.html)$/', '$1', $file);
        }
        $file = str_replace(' ', '+', $file);
        $writer->write($file, $result);
    }
}
