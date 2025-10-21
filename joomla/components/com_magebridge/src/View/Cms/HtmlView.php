<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\View\Cms;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use MageBridge\Component\MageBridge\Site\Helper\MageBridgeHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\View\BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $params = MageBridgeHelper::getParams();

        $request = $params->get('request');
        /** @var CMSApplication */
        $app = Factory::getApplication();

        if ($request === null || $request === '') {
            $request = $app->getInput()->getString('request');
        }

        $request = preg_replace('/^([0-9]+)\:/', '', (string) $request);

        $this->setRequest($request);

        if ((int) ConfigModel::load('enable_canonical') === 1) {
            $uri      = UrlHelper::route($request);
            $document = $app->getDocument();
            $document->setMetaData('canonical', $uri);
        }

        $this->setBlock('content');

        parent::display($tpl);
    }
}
