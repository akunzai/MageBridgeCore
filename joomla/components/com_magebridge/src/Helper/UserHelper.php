<?php

/**
 * Joomla! component MageBridge.
 *
 * @author	Yireo (info@yireo.com)
 * @copyright Copyright 2016
 * @license   GNU Public License
 *
 * @link	  https://www.yireo.com
 */

namespace MageBridge\Component\MageBridge\Site\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\UserModel;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper for dealing with Joomla!/Magento customer.
 */
class UserHelper
{
    /**
     * Helper-method to return the default Joomla! usergroup ID.
     *
     * @return int
     */
    public static function getDefaultJoomlaGroupid()
    {
        $usergroup = ConfigModel::load('usergroup');

        if (!empty($usergroup)) {
            return $usergroup;
        }

        $params = ComponentHelper::getParams('com_users');
        $group_id = $params->get('new_usertype');

        return $group_id;
    }

    /**
     * Helper-method to determine whether an user is a backend user.
     *
     * @param mixed $user User object or identifier
     * @param string $type Either object, email or username
     *
     * @return bool
     */
    public static function isBackendUser($user = null, $type = 'object')
    {
        // Check for empty user
        if (empty($user)) {
            return false;
        }

        // Get the right instance
        if ($user instanceof User == false) {
            $userModel = UserModel::getInstance();
            if ($type == 'email') {
                $user = $userModel->loadByEmail($user);
            }

            if ($type == 'username') {
                $user = $userModel->loadByUsername($user);
            }
        }

        // Check the legacy usertype parameter
        if (!empty($user->usertype) && (stristr($user->usertype, 'administrator') || stristr($user->usertype, 'manager'))) {
            return false;
        }

        // Check for ACL access
        if (method_exists($user, 'authorise') && $user->authorise('core.admin')) {
            return true;
        }

        return false;
    }

    /**
     * Helper-method to return the Magento customergroup based on the current Joomla! usergroup.
     *
     * @return string|null
     */
    public static function getMagentoGroupId($user)
    {
        static $rows = null;

        if (!is_array($rows)) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $db->setQuery('SELECT * FROM #__magebridge_usergroups WHERE `published`=1 ORDER BY `ordering`');
            $rows = $db->loadObjectList();
        }

        if (!empty($rows)) {
            $usergroups = (isset($user['groups'])) ? $user['groups'] : [];

            foreach ($rows as $row) {
                if (in_array($row->joomla_group, $usergroups)) {
                    return $row->magento_group;
                }

                if (!empty($row->params)) {
                    $params = new Registry($row->params);
                    $paramUsergroups = $params->get('usergroup');

                    if (!empty($paramUsergroups)) {
                        foreach ($paramUsergroups as $paramUsergroup) {
                            if (in_array($paramUsergroup, $usergroups)) {
                                return $row->magento_group;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Helper-method to return the Joomla! usergroup based on the current Magento customergroup.
     *
     * @return array
     */
    public static function getJoomlaGroupIds($customer, $current_groups = [])
    {
        if (!isset($customer['group_id'])) {
            return [];
        }

        static $rows = null;

        if (!is_array($rows)) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $query = 'SELECT `magento_group`,`joomla_group`,`params` ' . ' FROM #__magebridge_usergroups WHERE `published`=1 ORDER BY `ordering`';
            $db->setQuery($query);
            $rows = $db->loadObjectList();
        }

        if (!empty($rows)) {
            foreach ($rows as $row) {
                if ($row->magento_group == $customer['group_id']) {
                    $override_existing = false;
                    $new_groups = [$row->joomla_group];

                    if (!empty($row->params)) {
                        $params = new Registry($row->params);
                        $override_existing = (bool) $params->get('override_existing');

                        $extra_groups = $params->get('usergroup');

                        if (!empty($extra_groups)) {
                            foreach ($extra_groups as $extra_group) {
                                $new_groups[] = $extra_group;
                            }
                        }
                        $new_groups = array_unique($new_groups);
                    }

                    if ($override_existing == true) {
                        return $new_groups;
                    } else {
                        return array_merge($current_groups, $new_groups);
                    }
                }
            }
        }

        return $current_groups;
    }

    /**
     * Helper-method to convert user data into a valid user record.
     *
     * @param mixed $user User data
     *
     * @return mixed $user
     */
    public static function convert($user)
    {
        $rt = 'object';

        if (is_array($user)) {
            $rt = 'array';

            foreach ($user as $name => $value) {
                if (empty($value)) {
                    unset($user[$name]);
                }
            }

            $user = ArrayHelper::toObject($user);
        }

        $name = (isset($user->name)) ? $user->name : null;
        $firstname = (isset($user->firstname)) ? $user->firstname : null;
        $lastname = (isset($user->lastname)) ? $user->lastname : null;
        $username = (isset($user->username)) ? $user->username : null;

        // Generate an username
        if (empty($username)) {
            $username = $user->email;
        }

        // Generate a firstname and lastname
        if (!empty($name) && (empty($firstname) && empty($lastname))) {
            $array = explode(' ', $name);
            $firstname = array_shift($array);
            $lastname = implode(' ', $array);
        }

        // Generate a name
        if (empty($name) && (!empty($firstname) && !empty($lastname))) {
            if (ConfigModel::load('realname_with_space')) {
                $name = $firstname . $lastname;
            } else {
                $name = $firstname . ' ' . $lastname;
            }
        } else {
            if (empty($name)) {
                $name = $username;
            }
        }

        // Insert the values back into the object
        $user->name = trim($name);
        $user->username = trim($username);
        $user->firstname = trim($firstname);
        $user->lastname = trim($lastname);
        $user->admin = (int) self::isBackendUser($username, 'username');

        // Return either an array or an object
        if ($rt == 'array') {
            return ArrayHelper::fromObject($user);
        }

        return $user;
    }
}
