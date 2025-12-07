<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\View\Ajax;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\AbstractView;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\Register;

/**
 * Raw View for Ajax requests (format=raw).
 */
class RawView extends AbstractView
{
    public function display($tpl = null): void
    {
        /** @var CMSApplication */
        $app       = Factory::getApplication();
        $blockName = $app->getInput()->getString('block');

        if ($blockName !== '') {
            $register = Register::getInstance();
            $register->clean();
            $register->add('block', $blockName);

            DebugModel::getInstance()->notice('Building AJAX view for block "' . $blockName . '"');
            $bridge = BridgeModel::getInstance();
            $bridge->build();

            $block = $bridge->getBlock($blockName);

            if (is_array($block)) {
                $block = implode('', array_map('strval', $block));
            }

            echo (string) $block;
        }

        $app->close();
    }
}
