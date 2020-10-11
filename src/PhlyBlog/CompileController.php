<?php

namespace PhlyBlog;

use Laminas\Console\Adapter\AdapterInterface as Console;
use Laminas\Console\ColorInterface as Color;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\View;
use RuntimeException;
use Traversable;

use function array_merge;
use function call_user_func;
use function get_class;
use function gettype;
use function is_array;
use function is_callable;
use function is_object;
use function sprintf;
use function str_repeat;
use function strlen;

class CompileController extends AbstractActionController
{
    public $config = [];
    public $view;

    protected $compiler;
    protected $compilerOptions;
    protected $console;
    protected $responseFile;
    protected $writer;

    protected $defaultOptions
        = [
            'all'     => true,
            'entries' => false,
            'archive' => false,
            'year'    => false,
            'month'   => false,
            'day'     => false,
            'tag'     => false,
            'author'  => false,
        ];

    public function setConfig($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (! is_array($config)) {
            throw new RuntimeException(
                sprintf(
                    'Expected array or Traversable PhlyBlog configuration; received %s',
                    is_object($config) ? get_class($config) : gettype($config)
                )
            );
        }
        $this->config = $config;
    }

    public function setConsole(Console $console)
    {
        $this->console = $console;
    }

    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);
        $events->attach('dispatch', function ($e) {
            $controller = $e->getTarget();
            $config     = $controller->config;
            if ($config['view_callback'] && is_callable($config['view_callback'])) {
                $callable = $config['view_callback'];
                $view     = $controller->view;
                $locator  = $controller->getServiceLocator();
                call_user_func($callable, $view, $config, $locator);
            }
        }, 100);
    }

    public function getFlags()
    {
        $options = $this->params()->fromRoute();
        $test    = [
            ['long' => 'all', 'short' => 'a'],
            ['long' => 'entries', 'short' => 'e'],
            ['long' => 'archive', 'short' => 'c'],
            ['long' => 'year', 'short' => 'y'],
            ['long' => 'month', 'short' => 'm'],
            ['long' => 'day', 'short' => 'd'],
            ['long' => 'tag', 'short' => 't'],
            ['long' => 'author', 'short' => 'r'],
        ];
        foreach ($test as $spec) {
            $long  = $spec['long'];
            $short = $spec['short'];
            if (
                (! isset($options[$long]) || ! $options[$long])
                && (isset($options[$short]) && $options[$short])
            ) {
                $options[$long] = true;
                unset($options[$short]);
            }
        }

        $options = array_merge($this->defaultOptions, $options);
        if (
            $options['entries']
            || $options['archive']
            || $options['year']
            || $options['month']
            || $options['day']
            || $options['tag']
            || $options['author']
        ) {
            $options['all'] = false;
        }

        return $options;
    }

    public function setView(View $view)
    {
        $this->view = $view;
    }

    public function getWriter()
    {
        if ($this->writer) {
            return $this->writer;
        }
        $this->writer = new Compiler\FileWriter();
        return $this->writer;
    }

    public function getResponseFile()
    {
        if ($this->responseFile) {
            return $this->responseFile;
        }
        $this->responseFile = new Compiler\ResponseFile();
        return $this->responseFile;
    }

    public function getCompilerOptions()
    {
        if ($this->compilerOptions) {
            return $this->compilerOptions;
        }

        $this->compilerOptions = new CompilerOptions($this->config['options']);
        return $this->compilerOptions;
    }

    public function getCompiler()
    {
        if ($this->compiler) {
            return $this->compiler;
        }

        $view             = $this->view;
        $writer           = $this->getWriter();
        $responseFile     = $this->getResponseFile();
        $responseStrategy = new Compiler\ResponseStrategy($writer, $responseFile, $view);
        $postFiles        = new Compiler\PhpFileFilter($this->config['posts_path']);

        $this->compiler = new Compiler($postFiles);
        return $this->compiler;
    }

    public function attachTags()
    {
        $tags = new Compiler\Listener\Tags($this->view, $this->getWriter(), $this->getResponseFile(), $this->getCompilerOptions());
        $this->getCompiler()->getEventManager()->attach($tags);
        return $tags;
    }

    public function attachListeners(array $flags, $tags)
    {
        $listeners    = [];
        $view         = $this->view;
        $compiler     = $this->getCompiler();
        $writer       = $this->getWriter();
        $responseFile = $this->getResponseFile();
        $options      = $this->getCompilerOptions();

        if ($flags['all'] || $flags['entries']) {
            $entries = new Compiler\Listener\Entries($view, $responseFile, $options);
            $compiler->getEventManager()->attach($entries);
            $listeners['entries'] = $entries;
        }

        if ($flags['all'] || $flags['archive']) {
            $archive = new Compiler\Listener\Archives($view, $writer, $responseFile, $options);
            $compiler->getEventManager()->attach($archive);
            $listeners['archives'] = $archive;
        }

        if ($flags['all'] || $flags['year']) {
            $byYear = new Compiler\Listener\ByYear($view, $writer, $responseFile, $options);
            $compiler->getEventManager()->attach($byYear);
            $listeners['entries by year'] = $byYear;
        }

        if ($flags['all'] || $flags['month']) {
            $byMonth = new Compiler\Listener\ByMonth($view, $writer, $responseFile, $options);
            $compiler->getEventManager()->attach($byMonth);
            $listeners['entries by month'] = $byMonth;
        }

        if ($flags['all'] || $flags['day']) {
            $byDay = new Compiler\Listener\ByDate($view, $writer, $responseFile, $options);
            $compiler->getEventManager()->attach($byDay);
            $listeners['entries by day'] = $byDay;
        }

        if ($flags['all'] || $flags['author']) {
            $byAuthor = new Compiler\Listener\Authors($view, $writer, $responseFile, $options);
            $compiler->getEventManager()->attach($byAuthor);
            $listeners['entries by author'] = $byAuthor;
        }

        if ($flags['all'] || $flags['tag']) {
            $listeners['entries by tag'] = $tags;
        }

        return $listeners;
    }

    public function compileAction()
    {
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException(sprintf(
                '%s may only be called from the console',
                __METHOD__
            ));
        }

        $flags     = $this->getFlags();
        $compiler  = $this->getCompiler();
        $tags      = $this->attachTags();
        $listeners = $this->attachListeners($flags, $tags);

        // Compile
        $width = $this->console->getWidth();
        $this->console->write("Compiling and sorting entries", Color::BLUE);
        $compiler->compile();
        $this->reportDone($width, 29);

        // Create tag cloud
        if (
            $this->config['cloud_callback']
            && is_callable($this->config['cloud_callback'])
        ) {
            $callable = $this->config['cloud_callback'];
            $this->console->write(
                "Creating and rendering tag cloud",
                Color::BLUE
            );
            $cloud = $tags->getTagCloud();
            call_user_func(
                $callable,
                $cloud,
                $this->view,
                $this->config,
                $this->getServiceLocator()
            );
            $this->reportDone($width, 32);
        }

        // compile various artifacts
        foreach ($listeners as $type => $listener) {
            $message = "Compiling " . $type;
            $this->console->write($message, Color::BLUE);
            $listener->compile();
            $this->reportDone($width, strlen($message));
        }
    }

    protected function reportDone($width, $used)
    {
        if (($used + 8) > $width) {
            $this->console->writeLine('');
            $used = 0;
        }
        $spaces = $width - $used - 8;
        $this->console->writeLine(str_repeat('.', $spaces) . '[ DONE ]', Color::GREEN);
    }
}
