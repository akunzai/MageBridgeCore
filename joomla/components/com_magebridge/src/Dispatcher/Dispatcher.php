<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

/**
 * Dispatcher for the MageBridge site component.
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Extension namespace.
     *
     * @var string
     */
    protected $namespace = 'MageBridge\\Component\\MageBridge\\Site';

    public function __construct(CMSApplicationInterface $app, Input $input, ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($app, $input, $factory);
    }

    protected function loadLanguage(): void
    {
        $language = $this->app->getLanguage();
        $language->load('com_magebridge', JPATH_SITE)
            || $language->load('com_magebridge', JPATH_SITE . '/components/com_magebridge');
    }

    public function dispatch(): void
    {
        \MageBridge\Component\MageBridge\Site\Model\DebugModel::init();

        if ($this->input->getInt('sso') === 1) {
            $this->input->set('task', 'ssoCheck');
        }

        if ($this->input->get('url')) {
            $this->input->set('task', 'proxy');
        }

        $task = $this->input->getCmd('task');
        $this->input->set('task', $task);

        $controller = $this->input->getCmd('controller');

        if ($controller !== null && $controller !== '') {
            $this->input->set('task', $this->normaliseControllerTask($controller, $task ?? ''));
            $this->input->set('controller', null);
        }

        if (!$this->input->getCmd('view')) {
            $this->input->set('view', 'root');
        }

        parent::dispatch();
    }

    private function normaliseControllerTask(string $controller, string $task): string
    {
        $controller = strtolower($controller);
        $task = $task ?: 'display';

        if (str_contains($task, '.')) {
            return $task;
        }

        if ($controller === 'jsonrpc') {
            if ($task === 'display') {
                $task = 'call';
            }

            return 'jsonrpc.' . $task;
        }

        if ($controller === 'sso') {
            return 'sso.' . $task;
        }

        return $task;
    }
}
