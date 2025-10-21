<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Helper\MageBridgeHelper;

// Load legacy connector classes
require_once JPATH_SITE . '/components/com_magebridge/connector.php';
require_once JPATH_SITE . '/components/com_magebridge/connectors/store.php';

final class StoreHelper
{
    private static ?self $instance = null;

    private ?string $appType = null;

    private ?string $appValue = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getAppType(): ?string
    {
        if ($this->appType === null) {
            $this->setApp();
        }

        return $this->appType;
    }

    public function getAppValue(): ?string
    {
        if ($this->appValue === null) {
            $this->setApp();
        }

        return $this->appValue;
    }

    private function setApp(): void
    {
        if ($this->appType !== null && $this->appValue !== null) {
            return;
        }

        /** @var CMSApplication */
        $application = Factory::getApplication();
        $store       = MageBridgeHelper::getParams()->get('store');
        $website     = MageBridgeHelper::getParams()->get('website');

        if (!empty($store) && ($parts = explode(':', $store)) && count($parts) === 2) {
            if ($parts[0] === 'v') {
                $this->setState('store', $parts[1], $application);

                return;
            }

            if ($parts[0] === 'g') {
                $this->setState('group', $parts[1], $application);

                return;
            }
        } elseif (!empty($website)) {
            $this->setState('website', $website, $application);

            return;
        }

        if (PluginHelper::isEnabled('magebridgestore', 'get')) {
            $storedStore = $application->getUserState('___store');

            if (!empty($storedStore)) {
                $this->setState('store', $storedStore, $application);

                return;
            }

            $savedType = $application->getUserState('magebridge.store.type');
            $savedName = $application->getUserState('magebridge.store.name');

            if (!empty($savedType) && !empty($savedName)) {
                $this->setState($savedType, $savedName, $application);

                return;
            }
        }

        if ($application->isClient('site')) {
            $storeConfig = \MageBridgeConnectorStore::getInstance()->getStore();

            if (!empty($storeConfig)) {
                $this->appType  = $storeConfig['type'];
                $this->appValue = $storeConfig['name'];

                return;
            }
        }

        $storeview  = ConfigModel::load('storeview');
        $storegroup = ConfigModel::load('storegroup');
        $website    = ConfigModel::load('website');

        if ($application->isClient('administrator')) {
            if ($application->input->getCmd('view') === 'root') {
                $this->setState('website', 'admin', $application);
            } else {
                $this->setState('website', $website, $application);
            }

            return;
        }

        if (!empty($storeview)) {
            $this->setState('store', $storeview, $application);
        } elseif (!empty($storegroup)) {
            $this->setState('group', $storegroup, $application);
        } else {
            $this->setState('website', $website, $application);
        }
    }

    private function setState(string $type, ?string $value, $application): void
    {
        $this->appType  = $type;
        $this->appValue = $value;

        $application->setUserState('magebridge.store.type', $this->appType);
        $application->setUserState('magebridge.store.name', $this->appValue);
    }
}
