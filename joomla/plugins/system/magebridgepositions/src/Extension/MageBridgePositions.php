<?php

declare(strict_types=1);

namespace MageBridge\Plugin\System\MageBridgePositions;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;

/**
 * MageBridge Positions System Plugin.
 *
 * @since  3.0.0
 */
class MageBridgePositions extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise' => 'onAfterInitialise',
        ];
    }

    /**
     * Event onAfterInitialise.
     */
    public function onAfterInitialise()
    {
        // Don't do anything if MageBridge is not enabled
        if (!$this->isEnabled()) {
            return false;
        }

        // Perform actions on the frontend
        $application = Factory::getApplication();

        if ($application->isClient('site')) {
            $this->overrideModuleHelper();
        }
    }

    /**
     * Override the module helper.
     */
    public function overrideModuleHelper()
    {
        // Detect whether we can load the module-helper
        $classes = get_declared_classes();

        if (!in_array('JModuleHelper', $classes) && !in_array('jmodulehelper', $classes)) {
            $loadModuleHelper = true;
        } else {
            $loadModuleHelper = false;
        }

        // Load the module-helper override
        if ($loadModuleHelper) {
            $this->loadModuleHelperOverride();
        }
    }

    /**
     * Load the module helper override.
     */
    private function loadModuleHelperOverride()
    {
        $helperPath = __DIR__ . '/../rewrite/32/cms/application/module/helper.php';

        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }

    /**
     * Check if MageBridge is enabled.
     */
    private function isEnabled(): bool
    {
        if (!class_exists(BridgeModel::class)) {
            return false;
        }

        $bridge = BridgeModel::getInstance();

        if ($bridge->isOffline()) {
            return false;
        }

        return true;
    }
}
