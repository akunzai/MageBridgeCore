<?php

declare(strict_types=1);

namespace MageBridge\Plugin\Community\MageBridge;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\User;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use MageBridge\Component\MageBridge\Site\Library\MageBridge;

/**
 * MageBridge JomSocial Plugin.
 *
 * @since  3.0.0
 */
class JomSocialPlugin extends CMSPlugin implements SubscriberInterface
{
    public $name = 'Shop';
    public $_name = 'shop';
    public $_user = null;

    /**
     * @var Registry
     */
    public $params;

    /**
     * Returns an array of events this subscriber will listen to.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onUserDetailsUpdate' => 'onUserDetailsUpdate',
            'onAfterProfileUpdate' => 'onAfterProfileUpdate',
        ];
    }

    /**
     * Load the parameters.
     *
     * @return Registry
     */
    private function getParams()
    {
        return $this->params;
    }

    /**
     * Return the MageBridge user-object.
     *
     * @return mixed
     */
    private function getUser()
    {
        if (!class_exists('MageBridge\Component\MageBridge\Site\Library\MageBridge')) {
            return null;
        }

        return \MageBridge\Component\MageBridge\Site\Library\MageBridge::getUser();
    }

    /**
     * JomSocial event "onUserDetailsUpdate".
     *
     * @param User $user
     */
    public function onUserDetailsUpdate($user = null)
    {
        return; // This is already picked up by the default Joomla! user-events
    }

    /**
     * JomSocial event "onAfterProfileUpdate".
     */
    public function onAfterProfileUpdate($user_id, $update_success = false)
    {
        // Don't continue if the profile failed to update
        if ($update_success == false) {
            return;
        }

        // Don't continue if this plugin is set to disable syncing
        if ($this->getParams()->get('enable_tab', 1) == 0) {
            return;
        }

        // Fetch the user and sync it
        if (!class_exists('CFactory')) {
            return;
        }

        $user = \CFactory::getUser($user_id);
        if (!empty($user)) {
            $this->syncUser($user);
        }
    }

    /**
     * Sync a user with Magento.
     *
     * @param object $user
     */
    private function syncUser($user)
    {
        // Get the MageBridge user
        $magebridgeUser = $this->getUser();

        // Sync the user data
        if (!empty($magebridgeUser)) {
            $magebridgeUser->syncFromJoomla($user->id);
        }
    }
}
