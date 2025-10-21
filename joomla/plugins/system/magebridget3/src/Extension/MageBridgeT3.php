<?php

declare(strict_types=1);

namespace MageBridge\Plugin\System\MageBridgeT3;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;

/**
 * MageBridge JoomlArt T3 System Plugin.
 *
 * @since  3.0.0
 */
class MageBridgeT3 extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise' => 'onAfterInitialise',
            'onAfterRoute' => 'onAfterRoute',
        ];
    }

    /**
     * Event onAfterInitialise.
     */
    public function onAfterInitialise()
    {
        // Get rid of annoying cookies
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $cookie = $app->getTemplate() . '_layouts';
        unset($_COOKIE[$cookie]);
    }

    /**
     * Event onAfterRoute.
     */
    public function onAfterRoute()
    {
        // Don't do anything if MageBridge is not enabled
        if (!$this->isEnabled()) {
            return false;
        }

        /** @var CMSApplication */
        $app = Factory::getApplication();

        // Change the layout only for MageBridge-pages
        $view = $app->input->getCmd('view');
        $request = $app->input->getString('request');

        if ($view == 'root') {
            // Magento homepage
            if (empty($request)) {
                $app->input->set('layouts', $this->params->get('layout_homepage', 'full-width'));

                // Magento customer or sales pages
            } elseif (preg_match('/^(customer|sales)/', $request)) {
                $app->input->set('layouts', $this->params->get('layout_customer', 'full-width'));

                // Magento product-pages
            } elseif (preg_match('/^catalog\/product/', $request)) {
                $app->input->set('layouts', $this->params->get('layout_product', 'full-width'));

                // Magento category-pages
            } elseif (preg_match('/^catalog\/category/', $request)) {
                $app->input->set('layouts', $this->params->get('layout_category', 'full-width'));

                // Magento cart-pages
            } elseif (preg_match('/^checkout\/cart/', $request)) {
                $app->input->set('layouts', $this->params->get('layout_cart', 'full-width'));

                // Magento checkout-pages
            } elseif (preg_match('/^checkout/', $request)) {
                $app->input->set('layouts', $this->params->get('layout_checkout', 'full-width'));
            }
        }
    }

    /**
     * Simple check to see if MageBridge exists.
     */
    private function isEnabled(): bool
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();

        if (!$app->isClient('site')) {
            return false;
        }

        $template = $app->getTemplate();
        if (!preg_match('/^ja_/', $template)) {
            return false;
        }

        if ($app->input->getCmd('option') != 'com_magebridge') {
            return false;
        }

        return class_exists(ConfigModel::class);
    }
}
