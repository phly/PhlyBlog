<?php
namespace PhlyBlog;

use RuntimeException;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayUtils;
use Zend\View\View;

class CompileController extends AbstractActionController
{
    public $config = array();

    protected $compilerOptions;
    protected $responseFile;
    protected $view;
    protected $writer;

    protected $defaultOptions = array(
        'all'     => true,
        'entries' => false,
        'archive' => false,
        'year'    => false,
        'month'   => false,
        'day'     => false,
        'tag'     => false,
        'author'  => false,
    );

    public function setConfig($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (!is_array($config)) {
            throw new RuntimeException(sprintf(
                'Expected array or Traversable PhlyBlog configuration; received %s',
                (is_object($config) ? get_class($config) : gettype($config))
            ));
        }
        $this->config = $config;
    }

    public function setEventManager(EventManagerInterface $events)
    {
        parent::setEventManager($events);
        $events->attach('dispatch', function ($e) {
            $controller = $e->getTarget();
            $config     = $controller->config;
            if ($config['view_callback'] && is_callable($config['view_callback'])) {
                $callable = $config['view_callback'];
                $view     = $controller->getView();
                $locator  = $controller->getServiceLocator();
                call_user_func($callable, $view, $config, $locator);
            }
        }, 100);
    }

    public function getFlags()
    {
        $options = $this->params();
        $options = array_merge($this->defaultOptions, $options);
        if ($options['entries'] 
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

    public function getView()
    {
        if ($this->view) {
            return $this->view;
        }
        $this->view = $view = new View();
        $view->setRequest($this->getRequest);
        $view->setResponse($this->getResponse);
        return $view;
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
        if ($this->options) {
            return $this->options;
        }

        $this->options = new CompilerOptions($this->config['options']);
        return $this->options;
    }

    public function getCompiler()
    {
        if ($this->compiler) {
            return $this->compiler;
        }

        $view             = $this->getView();
        $writer           = $this->getWriter();
        $responseFile     = $this->getResponseFile();
        $responseStrategy = new Compiler\ResponseStrategy($writer, $responseFile, $view);
        $postFiles        = new Compiler\PhpFileFilter($config['posts_path']);

        $this->compiler   = new Compiler($postFiles);
        return $this->compiler;
    }

    public function attachTags()
    {
        $tags = new Compiler\Listener\Tags($this->getView(), $this->getWriter(), $this->getResponseFile(), $this->getCompilerOptions());
        $this->getCompiler()->getEventManager()->attach($tags);
        return $tags;
    }

    public function attachListeners(array $flags)
    {
        $listeners    = array();
        $view         = $this->getView();
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
        $flags     = $this->getFlags();
        $compiler  = $this->getCompiler();
        $tags      = $this->attachTags();
        $listeners = $this->attachListeners($flags);

        // Compile
        echo "Compiling and sorting entries...";
        $compiler->compile();
        echo "DONE!\n";

        // Create tag cloud
        if ($this->config['cloud_callback'] 
            && is_callable($this->config['cloud_callback'])
        ) {
            $callable = $this->config['cloud_callback'];
            echo "Creating and rendering tag cloud...";
            $cloud = $tags->getTagCloud();
            call_user_func($callable, $cloud, $this->getView(), $this->config, $this->getServiceLocator());
            echo "DONE!\n";
        }

        // compile various artifacts
        foreach ($listeners as $type => $listener) {
            echo "Compiling " . $type . "...";
            $listener->compile();
            echo "DONE!\n";
        }
    }
}
