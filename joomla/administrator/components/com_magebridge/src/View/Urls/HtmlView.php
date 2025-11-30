<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\View\Urls;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use Yireo\View\ViewList;

class HtmlView extends ViewList
{
    public function display($tpl = null)
    {
        // Add CSS files like BaseHtmlView
        $this->addCss('backend.css', 'media/com_magebridge/css/');
        $this->addCss('backend-j35.css', 'media/com_magebridge/css/');

        $bridge = BridgeModel::getInstance();
        $debug  = DebugModel::getInstance();

        if ($bridge->getApiState() !== null) {
            $message = null;

            switch (strtoupper($bridge->getApiState())) {
                case 'EMPTY METADATA':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_EMPTY_METADATA');
                    break;

                case 'AUTHENTICATION FAILED':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_AUTHENTICATION_FAILED');
                    break;

                case 'INTERNAL ERROR':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_INTERNAL_ERROR');
                    break;

                case 'FAILED LOAD':
                    $message = Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_FAILED_LOAD');
                    break;

                default:
                    $message = sprintf(Text::_('COM_MAGEBRIDGE_VIEW_API_ERROR_GENERIC'), (string) $bridge->getApiState());
                    break;
            }

            if ($message !== null) {
                $debug->feedback($message);
            }
        }

        $input = $this->app->getInput();

        if (
            (int) ConfigModel::load('debug') === 1
            && $input->getCmd('tmpl') !== 'component'
            && in_array($input->getCmd('view'), ['config', 'home'], true)
        ) {
            $debug->feedback(Text::_('COM_MAGEBRIDGE_VIEW_API_DEBUGGING_ENABLED'));
        }

        parent::display($tpl);
    }
}
