<?php

namespace PhlyBlog\Console;

use Laminas\View\View;
use PhlyBlog\Compiler;
use PhlyBlog\Compiler\FileWriter;
use PhlyBlog\Compiler\Listener\Archives;
use PhlyBlog\Compiler\Listener\Authors;
use PhlyBlog\Compiler\Listener\ByDate;
use PhlyBlog\Compiler\Listener\ByMonth;
use PhlyBlog\Compiler\Listener\ByYear;
use PhlyBlog\Compiler\Listener\Entries;
use PhlyBlog\Compiler\Listener\ListenerInterface;
use PhlyBlog\Compiler\Listener\Tags;
use PhlyBlog\Compiler\PhpFileFilter;
use PhlyBlog\Compiler\ResponseFile;
use PhlyBlog\Compiler\ResponseStrategy;
use PhlyBlog\CompilerOptions;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CompileCommand extends Command
{
    private const COMMAND_FLAGS = [
        'entries',
        'archive',
        'year',
        'month',
        'day',
        'tag',
        'author',
    ];

    /** @var array */
    private $config;

    /** @var Compiler */
    private $compiler;

    /** @var CompilerOptions */
    private $compilerOptions;

    /** @var ContainerInterface */
    private $container;

    /** @var ResponseFile */
    private $responseFile;

    /** @var Tags */
    private $tags;

    /** @var View */
    private $view;

    /** @var FileWriter */
    private $writer;

    public function __construct(
        array $config,
        ContainerInterface $container,
        View $view
    ) {
        $this->config          = $config;
        $this->container       = $container;
        $this->view            = $view;
        $this->compilerOptions = new CompilerOptions($config['options'] ?? []);
        $this->responseFile    = new ResponseFile();
        $this->writer          = new FileWriter();

        new ResponseStrategy($this->writer, $this->responseFile, $view);

        $this->compiler = new Compiler(
            new PhpFileFilter($config['posts_path'] ?? getcwd() . '/data/blog/')
        );

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Compile blog');
        $this->setHelp(<<< 'HELP'
            Compile blog posts into view scripts and feeds.

            The default action is to compile all entries and archives, pagination of
            entry listings (both via normal archive viewing, by each tag, and by each
            author).

            Use the various tags to select a subset of items to compile.

            HELP);
        $this->addOption('entries', 'e', InputOption::VALUE_NONE, 'Compile entries');
        $this->addOption('archive', 'c', InputOption::VALUE_NONE, 'Compile paginated archive (and feed)');
        $this->addOption('year', 'y', InputOption::VALUE_NONE, 'Compile paginated entries by year');
        $this->addOption('month', 'm', InputOption::VALUE_NONE, 'Compile paginated entries by month');
        $this->addOption('day', 'd', InputOption::VALUE_NONE, 'Compile paginated entries by day');
        $this->addOption('tag', 't', InputOption::VALUE_NONE, 'Compile paginated entries by tag (and feeds)');
        $this->addOption('author', 'r', InputOption::VALUE_NONE, 'Compile paginated entries by author (and feeds)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);
        $flags = $this->getFlags($input);
        $tags  = $this->getTags();

        // Compile blog entries
        $io->title('Compiling Blog');
        $io->write('<info>Compiling and sorting entries...</info>');
        $io->progressStart();
        $this->compiler->compile();
        $io->progressFinish();

        // Compile tag cloud
        $this->generateTagCloud($io, $tags);

        // Compile everything else
        foreach ($this->getListeners($flags, $tags) as $type => $listener) {
            $io->write(sprintf('<info>Compiling %s</info>', $type));
            $io->progressStart();
            $listener->compile();
            $io->progressFinish();
        }

        return 0;
    }

    private function getFlags(InputInterface $input): array
    {
        $flags = [];
        $all   = true;

        foreach (self::COMMAND_FLAGS as $flag) {
            $flags[$flag] = $input->getOption($flag);
            $all = $all && ! $flags[$flag];
        }

        $flags['all'] = $all;

        return $flags;
    }

    private function getTags(): Tags
    {
        $tags = new Tags(
            $this->view,
            $this->writer,
            $this->responseFile,
            $this->compilerOptions
        );
        $tags->attach($this->compiler->getEventManager());

        return $tags;
    }

    private function generateTagCloud(SymfonyStyle $io, Tags $tags): void
    {
        if (
            ! isset($this->config['cloud_callback'])
            || ! is_callable($this->config['cloud_callback'])
        ) {
            return;
        }

        $tagCloudGenerator = $this->config['cloud_callback'];
        $io->write('<info>Creating and rendering tag cloud</info>');
        $io->progressStart();
        $tagCloudGenerator($tags->getTagCloud(), $this->config, $this->container);
        $io->progressFinish();
    }

    /** @return ListenerInterface[] */
    private function getListeners(array $flags, Tags $tags): array
    {
        $listeners    = [];
        $view         = $this->view;
        $compiler     = $this->compiler;
        $writer       = $this->writer;
        $responseFile = $this->responseFile;
        $options      = $this->compilerOptions;

        if ($flags['all'] || $flags['entries']) {
            $entries = new Entries($view, $responseFile, $options);
            $entries->attach($compiler->getEventManager());
            $listeners['entries'] = $entries;
        }

        if ($flags['all'] || $flags['archive']) {
            $archive = new Archives($view, $writer, $responseFile, $options);
            $archive->attach($compiler->getEventManager());
            $listeners['archives'] = $archive;
        }

        if ($flags['all'] || $flags['year']) {
            $byYear = new ByYear($view, $writer, $responseFile, $options);
            $byYear->attach($compiler->getEventManager());
            $listeners['entries by year'] = $byYear;
        }

        if ($flags['all'] || $flags['month']) {
            $byMonth = new ByMonth($view, $writer, $responseFile, $options);
            $byMonth->attach($compiler->getEventManager());
            $listeners['entries by month'] = $byMonth;
        }

        if ($flags['all'] || $flags['day']) {
            $byDay = new ByDate($view, $writer, $responseFile, $options);
            $byDay->attach($compiler->getEventManager());
            $listeners['entries by day'] = $byDay;
        }

        if ($flags['all'] || $flags['author']) {
            $byAuthor = new Authors($view, $writer, $responseFile, $options);
            $byAuthor->attach($compiler->getEventManager());
            $listeners['entries by author'] = $byAuthor;
        }

        if ($flags['all'] || $flags['tag']) {
            $listeners['entries by tag'] = $this->tags;
        }

        return $listeners;
    }
}
