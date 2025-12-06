<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\View\Offline;

defined('_JEXEC') or die;

use MageBridge\Component\MageBridge\Site\View\BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public string $offline_message = '';

    public function display($tpl = null)
    {
        $this->offline_message = $this->getOfflineMessage();

        parent::display($tpl);
    }
}
