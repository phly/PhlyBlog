<?php
namespace PhlyBlog\Compiler;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Mvc\MvcEvent;
use Zend\View\View;

class ResponseStrategyTest extends TestCase
{
    public function setUp()
    {
        $this->writer = new TestAsset\MockWriter();
        $this->file   = new ResponseFile();
        $this->view   = new View();

        $this->strategy = new ResponseStrategy($this->writer, $this->file, $this->view);
    }

    public function testAddsResponseStrategyToView()
    {
        $events = $this->view->getEventManager();
        $listeners = $events->getListeners('response');
        $found = false;
        foreach ($listeners as $listener) {
            $callback = $listener->getCallback();
            if (array($this->strategy, 'onResponse') == $callback) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Listener not found');
    }

    public function testPassesFilenameAndDataToWriter()
    {
        $event = new MvcEvent();
        $event->setResult('data');
        $this->file->setFilename('some/file/name');
        $this->strategy->onResponse($event);
        $this->assertArrayHasKey('some/file/name', $this->writer->files);
        $this->assertEquals('data', $this->writer->files['some/file/name']);
    }

    public function testNormalizesWhitespaceInFilenames()
    {
        $event = new MvcEvent();
        $event->setResult('data');
        $this->file->setFilename('some file name');
        $this->strategy->onResponse($event);
        $this->assertArrayHasKey('some+file+name', $this->writer->files);
        $this->assertEquals('data', $this->writer->files['some+file+name']);
    }

    public function testStripsPageSuffixFromFilenamesRepresentingFirstPage()
    {
        $event = new MvcEvent();
        $event->setResult('data');
        $this->file->setFilename('tag-p1.html');
        $this->strategy->onResponse($event);
        $this->assertArrayHasKey('tag.html', $this->writer->files);
        $this->assertEquals('data', $this->writer->files['tag.html']);
    }
}
