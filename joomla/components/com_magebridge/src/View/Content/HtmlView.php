<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\View\Content;

defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\View\BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected ?string $logout_url = null;
    protected ?Registry $params = null;

    public function display($tpl = null)
    {
        $params = $this->getParams();

        switch ($this->getLayout()) {
            case 'logout':
                $intermediatePage = (int) $params->get('intermediate_page');

                if ($intermediatePage !== 1) {
                    $this->setRequest('customer/account/logout');
                } else {
                    $this->logout_url = UrlHelper::route('customer/account/logout');
                }

                break;

            default:
                $this->setRequest(UrlHelper::getLayoutUrl($this->getLayout()));
                break;
        }

        $this->setBlock('content');

        parent::display($tpl);
    }

    public function getParams(): Registry
    {
        if ($this->params === null) {
            /** @var SiteApplication */
            $app         = Factory::getApplication();
            $params       = $app->getParams();
            $this->params = $params;
        }

        return $this->params;
    }

    public function getLogoutUrl(): ?string
    {
        return $this->logout_url;
    }
}
