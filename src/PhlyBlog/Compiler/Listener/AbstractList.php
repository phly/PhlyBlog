<?php
namespace PhlyBlog\Compiler\Listener;

use PhlyBlog\AuthorEntity;
use PhlyBlog\CompilerOptions;
use PhlyBlog\Compiler\Event;
use PhlyBlog\Compiler\ResponseFile;
use PhlyBlog\Compiler\WriterInterface;
use Zend\EventManager\EventManagerInterface as Events;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Feed\Writer\Feed as FeedWriter;
use Zend\Paginator\Adapter\ArrayAdapter as ArrayPaginator;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;
use Zend\View\View;

abstract class AbstractList implements ListenerAggregateInterface, ListenerInterface
{
    protected $listeners = array();
    protected $options;
    protected $responseFile;
    protected $view;
    protected $writer;

    public function __construct(View $view, WriterInterface $writer, ResponseFile $responseFile, CompilerOptions $options)
    {
        $this->view         = $view;
        $this->writer       = $writer;
        $this->responseFile = $responseFile;
        $this->options      = $options;
    }

    public function attach(Events $events)
    {
        $this->listeners[] = $events->attach('compile', array($this, 'onCompile'));
        $this->listeners[] = $events->attach('compile.end', array($this, 'onCompileEnd'));
    }

    public function detach(Events $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    abstract public function onCompile(Event $e);
    abstract public function onCompileEnd(Event $e);

    /**
     * Retrieve configured paginator
     *
     * We need following configuration
     * - How many entries to include per page
     * - How many pages to show in the paginator
     * - Template for view script
     * - Partial for paginator control
     * 
     * @param  Iterator|array $it 
     * @return Paginator
     * @throws DomainException
     */
    protected function getPaginator(array $list)
    {
        $paginator = new Paginator(new ArrayPaginator($list));
        $paginator->setItemCountPerPage($this->options->getPaginatorItemCountPerPage());
        $paginator->setPageRange($this->options->getPaginatorPageRange());
        return $paginator;
    }

    protected function iterateAndRenderList(
        $list, 
        $filenameTemplate, 
        array $filenameSubs, 
        $title, 
        $urlTemplate,
        $substitution, 
        $template
    ) {
        if (!is_array($list) || empty($list)) {
            return;
        }

        // Get a paginator for this list
        $paginator = $this->getPaginator($list);

        // Loop through pages
        $pageCount = count($paginator);
        for ($i = 1; $i <= $pageCount; $i++) {
            $paginator->setCurrentPageNumber($i);

            $substitutions   = $filenameSubs;
            $substitutions[] = $i;
            $filename = vsprintf($filenameTemplate, $substitutions);

            // Generate this page
            $model = new ViewModel(array(
                'title'         => $title,
                'entries'       => $paginator,
                'paginator_url' => $urlTemplate,
                'substitution'  => $substitution,
            ));
            $model->setTemplate($template);

            $this->responseFile->setFilename($filename);
            $this->view->render($model);
            
            // This hack ensures that the paginator is reset for each page
            if ($i <= $pageCount) {
                $paginator = $this->getPaginator($list);
            }
        }
    }

    protected function iterateAndGenerateFeed(
        $type,
        $list,
        $title,
        $blogLink,
        $feedLinkTemplate,
        $filenameTemplate
    ) {
        if (!is_array($list) || empty($list)) {
            return;
        }

        $blogLink         = $this->options->getFeedHostname() . $blogLink;
        $feedLinkTemplate = $this->options->getFeedHostname() . $feedLinkTemplate;
        $linkTemplate     = $this->options->getFeedHostname() . $this->options->getEntryLinkTemplate();

        // Get a paginator
        $paginator = $this->getPaginator($list);
        $paginator->setCurrentPageNumber(1);

        $feed = new FeedWriter();
        $feed->setTitle($title);
        $feed->setLink($blogLink);
        $feed->setFeedLink(sprintf($feedLinkTemplate, $type), $type);

        if ('rss' == $type) {
            $feed->setDescription($title);
        }

        $authorUri   = $this->options->getFeedAuthorUri();
        if (empty($authorUri)) {
            $authorUri = $blogLink;
        }
        $defaultAuthor = array(
            'name'  => $this->options->getFeedAuthorName(),
            'email' => $this->options->getFeedAuthorEmail(),
            'uri'   => $authorUri,
        );

        $latest = false;
        foreach ($paginator as $post) {
            if (!$latest) {
                $latest = $post;
            }

            $authorDetails = $defaultAuthor;
            $author        = $post->getAuthor();
            if ($author instanceof AuthorEntity && $author->isValid()) {
                $authorDetails = array(
                    'name'  => $author->getName(),
                    'email' => $author->getEmail(),
                    'uri'   => $author->getUrl(),
                );
            }

            $entry = $feed->createEntry();
            $entry->setTitle($post->getTitle());
            $entry->setLink(sprintf($linkTemplate, $post->getId()));

            $entry->addAuthor($authorDetails);
            $entry->setDateModified($post->getUpdated());
            $entry->setDateCreated($post->getCreated());
            $entry->setContent($post->getBody() . $post->getExtended());

            $feed->addEntry($entry);
        }

        // Set feed date
        $feed->setDateModified($latest->getUpdated());

        // Write feed to file
        $file = sprintf($filenameTemplate, $type);
        $file = str_replace(' ', '+', $file);
        $this->writer->write($file, $feed->export($type));
    }
}
