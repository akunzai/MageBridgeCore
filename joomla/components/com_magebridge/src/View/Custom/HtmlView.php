<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\View\Custom;

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
        $request = (string) $params->get('request');

        $this->setRequest($request);

        if ((int) ConfigModel::load('enable_canonical') === 1) {
            $uri      = UrlHelper::route($request);
            /** @var CMSApplication */
            $app = Factory::getApplication();
            $document = $app->getDocument();
            $document->setMetaData('canonical', $uri);
        }

        $this->setBlock('content');

        parent::display($tpl);
    }
}
