<?php

/**
 * Joomla! component MageBridge.
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link      https://www.yireo.com
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use MageBridge\Component\MageBridge\Site\Library\MageBridge;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Profile-connector class.
 */
class MageBridgeConnectorProfile extends MageBridgeConnector
{
    /**
     * Singleton variable.
     */
    private static $_instance = null;

    /**
     * Constants.
     */
    public const CONVERT_TO_JOOMLA = 1;
    public const CONVERT_TO_MAGENTO = 2;

    /**
     * Singleton method.
     *
     * @return MageBridgeConnectorProfile
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Method to do something when changing the profile from Magento.
     *
     * @param Joomla\CMS\User\User $user
     * @param array $customer
     * @param array $address
     *
     * @return mixed
     */
    public function onSave($user = null, $customer = null, $address = null)
    {
        // Merge the address data into the customer field
        if (!empty($address)) {
            foreach ($address as $name => $value) {
                $name            = 'address_' . $name;
                $customer[$name] = $value;
            }
        }

        // Import the plugins
        PluginHelper::importPlugin('magebridgeprofile');
        $event = AbstractEvent::create(
            'onMageBridgeProfileSave',
            ['subject' => $this, 'user' => $user, 'customer' => $customer]
        );
        $dispatcher = Factory::getContainer()->get(DispatcherInterface::class);
        $dispatcher->dispatch('onMageBridgeProfileSave', $event);
    }

    /**
     * Method to execute when the user-data need to be synced.
     *
     * @param array $user
     *
     * @return array
     */
    public function modifyUserFields($user)
    {
        $user_id = null;

        if (isset($user['joomla_id'])) {
            $user_id = (int) $user['joomla_id'];
        }

        if (empty($user_id) && isset($user['id'])) {
            $user_id = (int) $user['id'];
        }

        if (!$user_id > 0) {
            return $user;
        }

        // Import the plugins
        PluginHelper::importPlugin('magebridgeprofile');
        $event = AbstractEvent::create(
            'onMageBridgeProfileModifyFields',
            ['subject' => $this, 'user_id' => $user_id, 'user' => &$user]
        );
        $dispatcher = Factory::getContainer()->get(DispatcherInterface::class);
        $dispatcher->dispatch('onMageBridgeProfileModifyFields', $event);

        return $user;
    }

    /**
     * Method to execute when the profile is saved.
     *
     * @param int $user_id
     *
     * @return bool
     */
    public function synchronize($user_id = 0)
    {
        // Exit if there is no user_id
        if (empty($user_id)) {
            return false;
        }

        // Get a general user-array from Joomla! itself
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = "SELECT `name`,`username`,`email` FROM `#__users` WHERE `id`=" . (int) $user_id;
        $db->setQuery($query);
        $user = $db->loadAssoc();

        // Exit if this is giving us no result
        if (empty($user)) {
            return false;
        }

        // Sync this user-record with the bridge
        DebugModel::getInstance()
            ->trace('Synchronizing user', $user);
        MageBridge::getUser()
            ->synchronize($user);

        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $session = $app->getSession();
        $session->set('com_magebridge.task_queue', []);

        return true;
    }
}
