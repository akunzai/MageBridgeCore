<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Helper\TemplateHelper;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;

final class DebugHelper
{
    private $bridge;

    private $register;

    private $request;

    private $app;

    public function __construct()
    {
        $this->bridge   = BridgeModel::getInstance();
        $this->register = Register::getInstance();
        $this->request  = UrlHelper::getRequest();
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $this->app = $app;
    }

    public function isDebugBarAllowed(): bool
    {
        if (strtolower($_SERVER['REQUEST_METHOD'] ?? '') === 'post') {
            return false;
        }

        if (!DebugModel::isDebug()) {
            return false;
        }

        if (!ConfigModel::load('debug_bar')) {
            return false;
        }

        return true;
    }

    public function addDebugBar(): void
    {
        if (!$this->isDebugBarAllowed()) {
            return;
        }

        if (ConfigModel::load('debug_bar_request')) {
            $this->addGenericInformation();
            $this->addPageInformation();
        }

        $this->addStore();
        $this->addCurrentCategoryId();
        $this->addCurrentProductId();
        $this->addDebugBarParts();
    }

    public function addGenericInformation(): void
    {
        $request = $this->request;
        $url     = $this->bridge->getMagentoUrl() . $request;

        if ($request === '') {
            $request = '[empty]';
        }

        $itemId     = $this->app->getInput()->getInt('Itemid');
        $rootItemId = $this->getRootItemId();
        $menuMsg    = 'Menu-Item: ' . $itemId;

        if ($rootItemId === $itemId) {
            $menuMsg .= ' (Root Menu-Item)';
        }

        $this->app->enqueueMessage($menuMsg, 'notice');
        $this->app->enqueueMessage(sprintf(Text::_('Page request: %s'), $request), 'notice');
        $this->app->enqueueMessage(sprintf(Text::_('Original request: %s'), UrlHelper::getOriginalRequest()), 'notice');
        $this->app->enqueueMessage(sprintf(Text::_('Received request: %s'), $this->bridge->getSessionData('request')), 'notice');
        $this->app->enqueueMessage(sprintf(Text::_('Received referer: %s'), $this->bridge->getSessionData('referer')), 'notice');
        $this->app->enqueueMessage(sprintf(Text::_('Current referer: %s'), $this->bridge->getHttpReferer()), 'notice');
        $this->app->enqueueMessage(sprintf(Text::_('Magento request: <a href="%s" target="_new">%s</a>'), $url, $url), 'notice');
        $this->app->enqueueMessage(sprintf(Text::_('Magento session: %s'), $this->bridge->getMageSession()), 'notice');
    }

    private function getRootItemId()
    {
        $rootItem = UrlHelper::getRootItem();

        return $rootItem ? $rootItem->id : false;
    }

    public function addPageInformation(): void
    {
        $map = [
            'isCategoryPage' => 'MageBridgeTemplateHelper::isCategoryPage() == TRUE',
            'isProductPage'  => 'MageBridgeTemplateHelper::isProductPage() == TRUE',
            'isCatalogPage'  => 'MageBridgeTemplateHelper::isCatalogPage() == TRUE',
            'isCustomerPage' => 'MageBridgeTemplateHelper::isCustomerPage() == TRUE',
            'isCartPage'     => 'MageBridgeTemplateHelper::isCartPage() == TRUE',
            'isCheckoutPage' => 'MageBridgeTemplateHelper::isCheckoutPage() == TRUE',
            'isSalesPage'    => 'MageBridgeTemplateHelper::isSalesPage() == TRUE',
            'isHomePage'     => 'MageBridgeTemplateHelper::isHomePage() == TRUE',
        ];

        foreach ($map as $method => $message) {
            if (TemplateHelper::$method()) {
                $this->app->enqueueMessage(Text::_($message), 'notice');
            }
        }
    }

    public function addStore(): void
    {
        if (!ConfigModel::load('debug_bar_store')) {
            return;
        }

        $this->app->enqueueMessage(
            sprintf(
                Text::_('Magento store loaded: %s (%s)'),
                $this->bridge->getSessionData('store_name'),
                $this->bridge->getSessionData('store_code')
            ),
            'notice'
        );
    }

    public function addCurrentCategoryId(): void
    {
        $categoryId = (int) $this->bridge->getSessionData('current_category_id');

        if ($categoryId > 0) {
            $this->app->enqueueMessage(sprintf(Text::_('Magento category: %d'), $categoryId), 'notice');
        }
    }

    public function addCurrentProductId(): void
    {
        $productId = (int) $this->bridge->getSessionData('current_product_id');

        if ($productId > 0) {
            $this->app->enqueueMessage(sprintf(Text::_('Magento product: %d'), $productId), 'notice');
        }
    }

    public function addDebugBarParts(): bool
    {
        if (!ConfigModel::load('debug_bar_parts')) {
            return false;
        }

        $i        = 0;
        $segments = $this->register->getRegister();

        foreach ($segments as $segment) {
            if (!isset($segment['status']) || (int) $segment['status'] !== 1) {
                continue;
            }

            $type = $segment['type'] ?? null;
            $name = $segment['name'] ?? null;

            switch ($type) {
                case 'breadcrumbs':
                case 'meta':
                case 'debug':
                case 'headers':
                case 'events':
                    $this->app->enqueueMessage(sprintf(Text::_('Magento [%d]: %s'), $i, ucfirst((string) $type)), 'notice');
                    break;
                case 'api':
                    $this->app->enqueueMessage(sprintf(Text::_('Magento [%d]: API resource "%s"'), $i, (string) $name), 'notice');
                    break;
                case 'block':
                    $this->app->enqueueMessage(sprintf(Text::_('Magento [%d]: Block "%s"'), $i, (string) $name), 'notice');
                    break;
                default:
                    $this->app->enqueueMessage(
                        sprintf(Text::_('Magento [%d]: type %s, name %s'), $i, (string) $type, (string) $name),
                        'notice'
                    );
                    break;
            }

            $i++;
        }

        return true;
    }
}
