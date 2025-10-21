<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Dispatcher;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;

/**
 * Dispatcher for the MageBridge administrator component.
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * The extension namespace.
     *
     * @var string
     */
    protected $namespace = 'MageBridge\\Component\\MageBridge\\Administrator';

    /**
     * Constructor.
     *
     * @param CMSApplicationInterface $app the application object
     * @param Input $input the input object
     * @param DatabaseInterface|null $db the database object
     */
    public function __construct(CMSApplicationInterface $app, Input $input, ?DatabaseInterface $db = null)
    {
        // @phpstan-ignore-next-line
        parent::__construct($app, $input, $db);
    }

    /**
     * Loads the component language files.
     */
    protected function loadLanguage(): void
    {
        $language = $this->app->getLanguage();
        $language->load('com_magebridge', JPATH_ADMINISTRATOR)
            || $language->load('com_magebridge', JPATH_ADMINISTRATOR . '/components/' . $this->option);
    }

    /**
     * Checks the access to the component.
     */
    protected function checkAccess(): void
    {
        if (!\MageBridge\Component\MageBridge\Administrator\Helper\Acl::isAuthorized()) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * Dispatch the component.
     */
    public function dispatch(): void
    {
        // Ensure debugging is initialised as early as possible.
        DebugModel::init();

        // Default view fall back to home when not set.
        if (!$this->input->getCmd('view')) {
            $this->input->set('view', 'home');
        }

        // Handle SSO redirect task override.
        if ($this->input->getInt('sso') === 1) {
            $this->input->set('task', 'ssoCheck');
        }

        parent::dispatch();
    }
}
