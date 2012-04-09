<?php
use PhlyBlog\Module;
use PhlyBlog\Compiler;
use PhlyBlog\CompilerOptions;
use Zend\Console\Exception as GetoptException;
use Zend\Console\Getopt;
use Zend\Loader\AutoloaderFactory;
use Zend\Module\Listener;
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
$locator  = $application->getLocator();
$view     = $locator->get('Zend\View\View');
$view->events()->clearListeners('renderer');
$view->events()->clearListeners('response');

// Setup renderer for layout, and layout view model
if ($config['blog']['view_callback'] && is_callable($config['blog']['view_callback'])) {
    $callable = $config['blog']['view_callback'];
    call_user_func($callable, $view, $config, $locator);
}

// Prepare compiler and grab tag cloud
$options   = new CompilerOptions($config['blog']['options']);
$postFiles = new Compiler\PhpFileFilter($config['blog']['posts_path']);
$writer    = new Compiler\FileWriter();
$compiler  = new Compiler($postFiles, $view, $writer, $options);

// Create tag cloud
if ($config['blog']['cloud_callback'] 
    && is_callable($config['blog']['cloud_callback'])
) {
    $callable = $config['blog']['cloud_callback'];
    echo "Creating and rendering tag cloud...";
    $cloud = $compiler->compileTagCloud();
    call_user_func($callable, $cloud, $view, $config, $locator);
    echo "DONE!\n";
}

// compile!

if ($all || $archive) {
    echo "Compiling paginated entries...";
    $compiler->compilePaginatedEntries();
    echo "DONE!\n";

    echo "Compiling main Atom feed...";
    $compiler->compileRecentFeed('atom');
    echo "DONE!\n";

    echo "Compiling main RSS feed...";
    $compiler->compileRecentFeed('rss');
    echo "DONE!\n";
}

if ($all || $byYear) {
    echo "Compiling paginated entries by year...";
    $compiler->compilePaginatedEntriesByYear();
    echo "DONE!\n";
}

if ($all || $byMonth) {
    echo "Compiling paginated entries by month...";
    $compiler->compilePaginatedEntriesByMonth();
    echo "DONE!\n";
}

if ($all || $byDay) {
    echo "Compiling paginated entries by date...";
    $compiler->compilePaginatedEntriesByDate();
    echo "DONE!\n";
}

if ($all || $byTag) {
    echo "Compiling paginated entries by tag...";
    $compiler->compilePaginatedEntriesByTag();
    echo "DONE!\n";

    echo "Compiling Atom tag feeds...";
    $compiler->compileTagFeeds('atom');
    echo "DONE!\n";

    echo "Compiling RSS tag feeds...";
    $compiler->compileTagFeeds('rss');
    echo "DONE!\n";
}

if ($all || $byAuthor) {
    echo "Compiling paginated entries by author...";
    $compiler->compilePaginatedEntriesByAuthor();
    echo "DONE!\n";

    echo "Compiling Atom author feeds...";
    $compiler->compileAuthorFeeds('atom');
    echo "DONE!\n";

    echo "Compiling RSS author feeds...";
    $compiler->compileAuthorFeeds('rss');
    echo "DONE!\n";
}

if ($all || $entries) {
    echo "Compiling entries...";
    $compiler->compileEntryViewScripts();
    echo "DONE!\n";
}
