<?php
use PhlyBlog\Module;
use PhlyBlog\Compiler;
use PhlyBlog\CompilerOptions;
use PhlyBlog\Compiler\Listener;
use PhlyBlog\Compiler\ResponseFile;
use PhlyBlog\Compiler\ResponseStrategy;
use Zend\Console\Exception as GetoptException;
use Zend\Console\Getopt;
use Zend\Loader\AutoloaderFactory;
use Zend\Module\Manager as ModuleManager;
use Zend\Mvc\Application;
use Zend\Mvc\Bootstrap;
use Zend\View\Model\ViewModel;

// Options
// Assumes that $argv is already in scope
try {
    $options = new Getopt(array(
        'help|h'    => 'Print this usage message',
        'all|a'     => 'Execute all actions (default)',
        'entries|e' => 'Compile entries',
        'archive|c' => 'Compile paginated archive (and feed)',
        'year|y'    => 'Compile paginated entries by year',
        'month|m'   => 'Compile paginated entries by month',
        'day|d'     => 'Compile paginated entries by day',
        'tag|t'     => 'Compile paginated entries by tag (and feeds)',
        'author|r'  => 'Compile paginated entries by author (and feeds)',
    ), $argv);
} catch (GetoptException $e) {
    file_put_contents('php://stderr', $e->getUsageMessage());
}

if ($options->getOption('h')) {
    echo $options->getUsageMessage();
    exit(0);
}

$all      = true;
$entries  = false;
$archive  = false;
$byYear   = false;
$byMonth  = false;
$byDay    = false;
$byTag    = false;
$byAuthor = false;

if (!isset($options->a)
    && (isset($options->e)
       || isset($options->c)
       || isset($options->y)
       || isset($options->m)
       || isset($options->d)
       || isset($options->t)
       || isset($options->r)
    )
) {
    $all = false;
    if (isset($options->e)) {
        $entries = true;
    }
    if (isset($options->c)) {
        $archive = true;
    }
    if (isset($options->y)) {
        $byYear = true;
    }
    if (isset($options->m)) {
        $byMonth = true;
    }
    if (isset($options->d)) {
        $byDay = true;
    }
    if (isset($options->t)) {
        $byTag = true;
    }
    if (isset($options->r)) {
        $byAuthor = true;
    }
}

// Get locator, and grab renderer and view from it
$config   = Module::$config;
$locator  = $application->getServiceManager();
$view     = $locator->get('View');
$view->events()->clearListeners('renderer');
$view->events()->clearListeners('response');


// Setup renderer for layout, and layout view model
if ($config['blog']['view_callback'] && is_callable($config['blog']['view_callback'])) {
    $callable = $config['blog']['view_callback'];
    call_user_func($callable, $view, $config, $locator);
}

// Prepare compiler and grab tag cloud
$writer           = new Compiler\FileWriter();
$responseFile     = new ResponseFile();
$responseStrategy = new ResponseStrategy($writer, $responseFile, $view);

$options   = new CompilerOptions($config['blog']['options']);

$postFiles = new Compiler\PhpFileFilter($config['blog']['posts_path']);
$compiler  = new Compiler($postFiles);

$listeners = array();
$tags = new Listener\Tags($view, $writer, $responseFile, $options);
$compiler->events()->attach($tags);

if ($all || $entries) {
    $entries = new Listener\Entries($view, $responseFile, $options);
    $compiler->events()->attach($entries);
    $listeners['entries'] = $entries;
}

if ($all || $archive) {
    $archive = new Listener\Archives($view, $writer, $responseFile, $options);
    $compiler->events()->attach($archive);
    $listeners['archives'] = $archive;
}

if ($all || $byYear) {
    $byYear = new Listener\ByYear($view, $writer, $responseFile, $options);
    $compiler->events()->attach($byYear);
    $listeners['entries by year'] = $byYear;
}

if ($all || $byMonth) {
    $byMonth = new Listener\ByMonth($view, $writer, $responseFile, $options);
    $compiler->events()->attach($byMonth);
    $listeners['entries by month'] = $byMonth;
}

if ($all || $byDay) {
    $byDay = new Listener\ByDate($view, $writer, $responseFile, $options);
    $compiler->events()->attach($byDay);
    $listeners['entries by day'] = $byDay;
}

if ($all || $byAuthor) {
    $byAuthor = new Listener\Authors($view, $writer, $responseFile, $options);
    $compiler->events()->attach($byAuthor);
    $listeners['entries by author'] = $byAuthor;
}

if ($all || $byTag) {
    $listeners['entries by tag'] = $tags;
}

// Compile
echo "Compiling and sorting entries...";
$compiler->compile();
echo "DONE!\n";

// Create tag cloud
if ($config['blog']['cloud_callback'] 
    && is_callable($config['blog']['cloud_callback'])
) {
    $callable = $config['blog']['cloud_callback'];
    echo "Creating and rendering tag cloud...";
    $cloud = $tags->getTagCloud();
    call_user_func($callable, $cloud, $view, $config, $locator);
    echo "DONE!\n";
}

// compile various artifacts
foreach ($listeners as $type => $listener) {
    echo "Compiling " . $type . "...";
    $listener->compile();
    echo "DONE!\n";
}
