<?php

namespace PhlyBlogTest\Compiler;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\View\View;
use PhlyBlog\Compiler\ResponseFile;
use PhlyBlog\Compiler\ResponseStrategy;
use PhlyBlogTest\Compiler\TestAsset\MockWriter;
use PhlyBlogTest\ReflectionUtil;
use PHPUnit\Framework\TestCase;

use function reset;

class ResponseStrategyTest extends TestCase
{
    /** @var MockWriter */
    private $writer;
    /** @var ResponseFile */
    private $file;
    /** @var View */
    protected $view;
    /** @var ResponseStrategy */
    private $strategy;

    protected function setUp(): void
    {
        $this->writer = new MockWriter();
        $this->file   = new ResponseFile();
        $this->view   = new View();

        $this->strategy = new ResponseStrategy(
            $this->writer,
            $this->file,
            $this->view
        );
    }

    public function testAddsResponseStrategyToView(): void
    {
        $events    = $this->view->getEventManager();
        $listeners = $this->getResponseListeners($events);
        $found     = false;
        foreach ($listeners as $listener) {
            if ([$this->strategy, 'onResponse'] == $listener) {
                $found = true;
                break;
            }
        }
        self::assertTrue($found, 'Listener not found');
    }

    public function testPassesFilenameAndDataToWriter(): void
    {
        $event = new MvcEvent();
        $event->setResult('data');
        $this->file->setFilename('some/file/name');
        $this->strategy->onResponse($event);
        self::assertArrayHasKey('some/file/name', $this->writer->files);
        self::assertEquals('data', $this->writer->files['some/file/name']);
    }

    public function testNormalizesWhitespaceInFilenames(): void
    {
        $event = new MvcEvent();
        $event->setResult('data');
        $this->file->setFilename('some file name');
        $this->strategy->onResponse($event);
        self::assertArrayHasKey('some+file+name', $this->writer->files);
        self::assertEquals('data', $this->writer->files['some+file+name']);
    }

    public function testStripsPageSuffixFromFilenamesRepresentingFirstPage(): void
    {
        $event = new MvcEvent();
        $event->setResult('data');
        $this->file->setFilename('tag-p1.html');
        $this->strategy->onResponse($event);
        self::assertArrayHasKey('tag.html', $this->writer->files);
        self::assertEquals('data', $this->writer->files['tag.html']);
    }

    private function getResponseListeners(EventManagerInterface $events)
    {
        $value     = ReflectionUtil::getProperty($events, 'events');
        $listeners = $value['response'];
        $listeners = reset($listeners); // unwrap priorities
        $listeners = reset($listeners); // unwrap callables
        return $listeners;
    }
}
