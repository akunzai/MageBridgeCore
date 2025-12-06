<?php

declare(strict_types=1);

namespace MageBridge\Plugin\System\MageBridgePre\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\UserModel;

/**
 * MageBridge Preloader System Plugin.
 *
 * @since  3.0.0
 */
class MageBridgePre extends CMSPlugin implements SubscriberInterface
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
        /** @var CMSApplication */
        $app = Factory::getApplication();

        // Check for postlogin-cookie
        if (isset($_COOKIE['mb_postlogin']) && !empty($_COOKIE['mb_postlogin'])) {
            // If the user is already logged in, remove the cookie
            $user = $app->getIdentity();
            if ($user->id > 0) {
                setcookie('mb_postlogin', '', time() - 3600, '/', '.' . Uri::getInstance()
                    ->toString(['host']));
            }

            // Otherwise decrypt the cookie and use it here
            $data = EncryptionHelper::decrypt($_COOKIE['mb_postlogin']);

            if (!empty($data)) {
                $customer_email = $data;
            }
        }

        // Perform a postlogin if needed
        $post = $app->input->post->getArray();

        if (empty($post)) {
            $postlogin_userevents = ($this->params->get('postlogin_userevents', 0) == 1) ? true : false;

            if (empty($customer_email)) {
                $customer_email = BridgeModel::getInstance()
                    ->getSessionData('customer/email');
            }

            if (!empty($customer_email)) {
                UserModel::getInstance()->postlogin($customer_email, null, $postlogin_userevents);
            }
        }
    }

    /**
     * Simple check to see if MageBridge exists.
     */
    private function isEnabled(): bool
    {
        return class_exists(ConfigModel::class);
    }
}
