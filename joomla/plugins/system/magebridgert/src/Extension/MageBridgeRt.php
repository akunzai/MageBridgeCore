<?php

declare(strict_types=1);

namespace MageBridge\Plugin\System\MageBridgeRt;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use MageBridge\Component\MageBridge\Site\Helper\TemplateHelper;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use Yireo\Helper\PathHelper;

/**
 * MageBridge RocketTheme System Plugin.
 *
 * @since  3.0.0
 */
class MageBridgeRt extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterDispatch' => 'onAfterDispatch',
        ];
    }

    /**
     * Event onAfterDispatch.
     */
    public function onAfterDispatch()
    {
        // Don't do anything if MageBridge is not enabled
        if (!$this->isEnabled()) {
            return false;
        }

        // Load the application
        /** @var CMSApplication */
        $app = Factory::getApplication();

        // Don't do anything in other applications than the frontend
        if (!$app->isClient('site')) {
            return false;
        }

        // Load the blacklist settings
        $blacklist = $app->getConfig()->get('magebridge.script.blacklist');
        if (empty($blacklist)) {
            $blacklist = [];
        }
        $blacklist[] = '/rokbox.js';
        $blacklist[] = 'gantry/js/browser-engines.js';
        $app->getConfig()->set('magebridge.script.blacklist', $blacklist);

        // Load the whitelist settings
        $whitelist = $app->getConfig()->get('magebridge.script.whitelist');
        if (empty($whitelist)) {
            $whitelist = [];
        }
        $app->getConfig()->set('magebridge.script.whitelist', $whitelist);

        // Read the template-related files
        $ini = PathHelper::getThemesPath() . '/' . $app->getTemplate() . '/params.ini';
        $ini_content = @file_get_contents($ini);
        $xml = PathHelper::getThemesPath() . '/' . $app->getTemplate() . '/templateDetails.xml';

        // WARP-usage of "config" file
        if (!empty($ini_content)) {
            // Create the parameters object
            $params = new Registry($ini_content, $xml);

            // Load a specific stylesheet per color
            $color = $params->get('colorStyle');
            if (!empty($color)) {
                TemplateHelper::load('css', 'color-' . $color . '.css');
            }
        }

        // Check whether ProtoType is loaded, and add some fixes
        if (TemplateHelper::hasPrototypeJs()) {
            $document = $app->getDocument();
            $wa = $document->getWebAssetManager();
            if ($this->params->get('fix_submenu_wrapper', 1)) {
                $wa->addInlineStyle('div.fusion-submenu-wrapper { margin-top: -12px !important; }');
            }
            if ($this->params->get('fix_body_zindex', 1)) {
                $wa->addInlineStyle('div#rt-body-surround { z-index:0 !important; }');
            }
            $wa->addInlineStyle('div.style-panel-container {left: -126px;}');
        }
    }

    /**
     * Simple check to see if MageBridge exists.
     */
    private function isEnabled(): bool
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $template = $app->getTemplate();
        if (!preg_match('/^rt_/', $template)) {
            return false;
        }

        if (!class_exists(ConfigModel::class)) {
            return false;
        }

        return true;
    }
}
