<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\View;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;
use MageBridge\Component\MageBridge\Site\Library\MageBridge;
use MageBridge\Component\MageBridge\Site\Helper\DebugHelper;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use Yireo\View\AbstractView;

class BaseHtmlView extends AbstractView
{
    protected ?string $blockName = null;
    protected ?string $block = null;
    protected CMSApplication $app;
    protected DatabaseInterface $db;
    protected ?User $user;
    protected Document $doc;
    protected Input $input;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $viewName      = strtolower($this->getName());
        $componentPath = \defined('JPATH_COMPONENT')
            ? (string) constant('JPATH_COMPONENT')
            : JPATH_SITE . '/components/com_magebridge';
        $templatePath  = $componentPath . '/tmpl/' . $viewName;

        if (is_dir($templatePath)) {
            $this->addTemplatePath($templatePath);
        }

        /** @var CMSApplication */
        $app = Factory::getApplication();
        $this->db    = Factory::getContainer()->get(DatabaseInterface::class);
        $this->doc   = $app->getDocument();
        $this->user  = $app->getIdentity();
        $this->app   = $app;
        $this->input = $this->app->getInput();
    }

    public function display($tpl = null)
    {
        $debugHelper = new DebugHelper();
        $debugHelper->addDebugBar();

        $this->block = $this->build();

        if (!empty($this->block)) {
            $this->block = $this->addFixes((string) $this->block);
        }

        parent::display($tpl);
    }

    protected function build()
    {
        static $block = null;

        if ($block === null) {
            $register = Register::getInstance();
            $register->add('headers');
            $register->add('block', $this->blockName);

            if ((int) ConfigModel::load('enable_breadcrumbs') === 1) {
                $request = UrlHelper::getRequest();

                if (!empty($request)) {
                    $register->add('breadcrumbs');
                }
            }

            DebugModel::getInstance()->notice('Building view');
            $bridge = MageBridge::getBridge();
            $bridge->build();
            $bridge->setHeaders();

            $application = Factory::getApplication();

            if ($application->isClient('site') && (int) ConfigModel::load('enable_breadcrumbs') === 1) {
                $bridge->setBreadcrumbs();
            }

            $block = $bridge->getBlock($this->blockName);

            if (empty($block)) {
                DebugModel::getInstance()->warning('JView: Empty block: ' . $this->blockName);
                $block = Text::_($this->getOfflineMessage());
            }
        }

        return $block;
    }

    public function setBlock(string $blockName): void
    {
        $this->blockName = $blockName;
    }

    public function setRequest(string $request): void
    {
        $segments = explode('/', $request);

        foreach ($segments as $index => $segment) {
            $segments[$index] = preg_replace('/^([a-zA-Z0-9]+)\:/', '\\1-', $segment);
        }

        $request = implode('/', $segments);

        UrlHelper::setRequest($request);
    }

    public function getBlockContent(): ?string
    {
        return $this->block;
    }

    public function getOfflineMessageText(): string
    {
        return Text::_($this->getOfflineMessage());
    }

    protected function addFixes(string $html): string
    {
        /** @var CMSApplication */
        $application = Factory::getApplication();
        $file        = JPATH_BASE . '/templates/' . $application->getTemplate() . '/html/com_magebridge/fixes.php';

        if (!file_exists($file)) {
            $file = JPATH_SITE . '/components/com_magebridge/tmpl/fixes.php';
        }

        require_once $file;

        return $html;
    }

    protected function getOfflineMessage(): string
    {
        return (string) ConfigModel::load('offline_message');
    }
}
