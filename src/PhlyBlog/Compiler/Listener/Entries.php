<?php
namespace PhlyBlog\Compiler\Listener;

use DomainException;
use PhlyBlog\CompilerOptions;
use PhlyBlog\Compiler\Event;
use PhlyBlog\Compiler\ResponseFile;
use Zend\EventManager\EventManagerInterface as Events;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\View\View;
use Zend\View\Model\ViewModel;

class Entries implements ListenerAggregateInterface
{
    protected $entries;
    protected $listeners = array();
    protected $options;
    protected $responseFile;
    protected $view;

    public function __construct(View $view, ResponseFile $responseFile, CompilerOptions $options)
    {
        $this->view         = $view;
        $this->responseFile = $responseFile;
        $this->options      = $options;
    }

    public function attach(Events $events)
    {
        $this->listeners[] = $events->attach('compile', array($this, 'onCompile'));
    }

    public function detach(Events $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function onCompile(Event $e)
    {
        $entry = $e->getEntry();
        $this->entries[] = $entry;
    }

    public function compile()
    {
        $this->createEntries();
    }

    public function createEntries($template = null)
    {
        if (!$this->entries) {
            return;
        }

        if (null === $template) {
            $template = $this->options->getEntryTemplate();
            if (empty($template)) {
                throw new DomainException('No template provided for individual entries');
            }
        }
        $filenameTemplate = $this->options->getEntryFilenameTemplate();

        foreach ($this->entries as $entry) {
            $filename = sprintf($filenameTemplate, $entry->getId());
            $this->responseFile->setFilename($filename);

            $model = new ViewModel(array(
                'entry' => $entry,
            ));
            $model->setTemplate($template);

            $this->view->render($model);
        }
    }
}
