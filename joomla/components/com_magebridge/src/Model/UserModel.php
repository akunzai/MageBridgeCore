<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Table\User as UserTable;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Component\MageBridge\Site\Helper\TemplateHelper;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Helper\UserHelper as MageBridgeUserHelper;
use Joomla\Event\DispatcherInterface;

final class UserModel
{
    private static ?self $instance = null;

    /** @var CMSApplication */
    private $app;

    private $debug;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $this->app   = $app;
        $this->debug = DebugModel::getInstance();
    }

    public function create($user, $empty_password = false)
    {
        if (empty($user['email']) || $this->isValidEmail($user['email']) == false) {
            return false;
        }

        PluginHelper::importPlugin('user');

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $email = $user['email'];

        if (!empty($user['original_data']['email'])) {
            $email = $user['original_data']['email'];
        }

        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'));
        $query->from($db->quoteName('#__users'));
        $query->where($db->quoteName('email') . '=' . $db->quote($email));
        $db->setQuery($query);
        $result = $db->loadResult();

        if (empty($result)) {
            $data = [
                'id'       => 0,
                'name'     => $user['name'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'guest'    => 0,
            ];

            $now                  = new Date();
            $data['registerDate'] = $now->toSql();

            if ($empty_password == false) {
                if (!empty($user['password']) && is_string($user['password'])) {
                    $password = $user['password'];
                } else {
                    $password = UserHelper::genRandomPassword();
                }

                $encryptedPassword = UserHelper::hashPassword($password);
                $data['password']  = $encryptedPassword;
                $data['password2'] = $encryptedPassword;
            } else {
                $data['password']  = '';
                $data['password2'] = '';
            }

            $data['disable_events'] = 1;

            $this->debug->notice('Firing event onUserBeforeSave');
            $dispatcher = Factory::getContainer()->get(DispatcherInterface::class);
            $userEntity = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById((int) $data['id']);
            $event      = AbstractEvent::create('onUserBeforeSave', ['subject' => $userEntity, 'isNew' => true, 'data' => $data]);
            $dispatcher->dispatch('onUserBeforeSave', $event);

            $table = new UserTable(Factory::getContainer()->get(DatabaseInterface::class));
            $table->bind($data);
            $result = $table->store();

            $newuser    = $this->loadByEmail($user['email']);
            $data['id'] = $newuser->id;

            $this->debug->notice('Firing event onUserAfterSave');
            $savedUser  = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById((int) $data['id']);
            $afterEvent = AbstractEvent::create('onUserAfterSave', ['subject' => $savedUser, 'isNew' => true, 'success' => true, 'data' => $data]);
            $dispatcher->dispatch('onUserAfterSave', $afterEvent);

            if (isset($table->id) && $table->id > 0) {
                $query = $db->getQuery(true);
                $query->select('*');
                $query->from($db->quoteName('#__user_usergroup_map'));
                $query->where($db->quoteName('user_id') . ' = ' . $table->id);
                $db->setQuery($query);
                $rows = $db->loadObjectList();

                if (empty($rows)) {
                    $group_id = MageBridgeUserHelper::getDefaultJoomlaGroupid();

                    if (!empty($group_id)) {
                        $db->setQuery('INSERT INTO `#__user_usergroup_map` SET `user_id`=' . $table->id . ', `group_id`=' . $group_id);
                        $db->execute();
                    }
                }
            }

            return self::loadByEmail($user['email']);
        }

        return null;
    }

    public function synchronize($user)
    {
        if (isset($user['disable_events']) && $user['disable_events'] == 1) {
            return null;
        }

        $this->debug->notice('MageBridgeModelUser::synchronize() on user ' . $user['email']);

        if (empty($user['username'])) {
            $user['username'] = $user['email'];
        }

        if ($this->isValidEmail($user['email']) == false) {
            return false;
        }

        $user['joomla_id'] = (isset($user['id'])) ? $user['id'] : 0;

        $user = MageBridgeUserHelper::convert($user);

        if (empty($user['password_clear'])) {
            if (isset($user['password']) && !preg_match('/^\$/', $user['password']) && !preg_match('/^\{SHA256\}/', $user['password']) && !preg_match('/([a-z0-9]{32}):([a-zA-Z0-9]+)/', $user['password'])) {
                $user['password_clear'] = $user['password'];
            }
        }

        if (empty($user['password_clear'])) {
            $fields = ['password_clear', 'password', 'passwd'];
            $jform  = $this->app->getInput()->post->get('jform', []);

            foreach ($fields as $field) {
                $password = $this->app->getInput()->post->getString($field, '');

                if (empty($password) && is_array($jform) && !empty($jform[$field])) {
                    $password = $jform[$field];
                }

                if (!empty($password)) {
                    $user['password_clear'] = $password;
                    break;
                }
            }
        }

        unset($user['id'], $user['password'], $user['params'], $user['userType'], $user['sendEmail'], $user['option'], $user['task']);

        foreach ($user as $name => $value) {
            if (empty($value)) {
                unset($user[$name]);
            }
        }

        if (isset($user['password_clear'])) {
            if (empty($user['password_clear']) || !is_string($user['password_clear'])) {
                unset($user['password_clear']);
            } else {
                $user['password_clear'] = EncryptionHelper::encrypt($user['password_clear']);
            }
        }

        $user['website_id'] = ConfigModel::load('website');
        $user['default_customer_group'] = ConfigModel::load('customer_group');
        $user['customer_group'] = MageBridgeUserHelper::getMagentoGroupId($user);
        $user['disable_events'] = 1;

        $profileConnector = \MageBridgeConnectorProfile::getInstance();
        $user             = $profileConnector->modifyUserFields($user);

        $bridge   = BridgeModel::getInstance();
        $register = Register::getInstance();

        $id = $register->add('api', 'magebridge_user.save', $user);
        $bridge->build();
        $data = $register->getDataById($id);

        return $data;
    }

    public function delete($user)
    {
        $user['website_id'] = ConfigModel::load('website');

        $bridge   = BridgeModel::getInstance();
        $register = Register::getInstance();

        $id = $register->add('api', 'magebridge_user.delete', $user);
        $bridge->build();
        $data = $register->getDataById($id);

        return $data;
    }

    public function login($email = null)
    {
        if ($this->app->isClient('site') == false) {
            if (ConfigModel::load('enable_auth_backend') != 1) {
                return false;
            }

            $application_name = 'admin';
        } else {
            if (ConfigModel::load('enable_auth_frontend') != 1) {
                return false;
            }

            $application_name = 'site';
        }

        $email = EncryptionHelper::encrypt($email);

        $arguments = [
            'email'          => $email,
            'application'    => $application_name,
            'disable_events' => 1,
        ];

        $bridge   = BridgeModel::getInstance();
        $register = Register::getInstance();

        $id = $register->add('api', 'magebridge_user.login', $arguments);
        $bridge->build();
        $data = $register->getDataById($id);

        return $data;
    }

    public function authenticate($username = null, $password = null, $application = 'site')
    {
        $username = EncryptionHelper::encrypt($username);
        $password = EncryptionHelper::encrypt($password);

        $arguments = [
            'username'       => $username,
            'password'       => $password,
            'application'    => $application,
            'disable_events' => 1,
        ];

        $bridge   = BridgeModel::getInstance();
        $register = Register::getInstance();

        $id = $register->add('authenticate', null, $arguments);
        $bridge->build();
        $data = $register->getDataById($id);

        return $data;
    }

    public function loadByField($field = null, $value = null)
    {
        if (empty($field) || empty($value)) {
            return false;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'));
        $query->from($db->quoteName('#__users'));
        $query->where($db->quoteName($field) . '=' . $db->quote($value));

        $db->setQuery($query);
        $row = $db->loadObject();

        if (empty($row) || !isset($row->id) || !$row->id > 0) {
            return false;
        }

        $user_id = $row->id;
        $user    = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($user_id);

        if (empty($user->id)) {
            return false;
        }

        return $user;
    }

    public function loadByUsername($username = null)
    {
        return $this->loadByField('username', $username);
    }

    public function loadByEmail($email = null)
    {
        if ($this->isValidEmail($email) == false) {
            return false;
        }

        return $this->loadByField('email', $email);
    }

    public function allowSynchronization($user = null, $action = null)
    {
        if ($user instanceof User) {
            if (MageBridgeUserHelper::isBackendUser($user)) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function postlogin($user_email = null, $user_id = null, $throw_event = true, $allow_post = false)
    {
        if (empty($user_email) && ($user_id > 0) == false) {
            return false;
        }

        if (strstr($user_email, '%40')) {
            $user_email = urldecode($user_email);
        }

        if ($this->isValidEmail($user_email) == false) {
            return false;
        }

        if ($this->app->isClient('site') == false) {
            return false;
        }

        $post = $this->app->getInput()->post->getArray();

        if (!empty($post) && $allow_post == false) {
            return false;
        }

        $user = Factory::getApplication()->getIdentity();

        $changed = false;

        if ($user_id > 0 && $user->id != $user_id) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $query->select($db->quoteName('id'));
            $query->from($db->quoteName('#__users'));
            $query->where($db->quoteName('id') . '=' . (int) $user_id);

            $db->setQuery($query);
            $row = $db->loadObject();

            if (!empty($row)) {
                $user    = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($user_id);
                $changed = true;
            }
        }

        if (!empty($user_email) && $user->email != $user_email) {
            $user    = $this->loadByEmail($user_email);
            $changed = true;
        }

        if (!empty($user) && $user->id > 0 && isset($user->guest) && $user->guest == 1) {
            $changed = true;
        }

        if (!empty($user_email) && (empty($user) || empty($user->email))) {
            $data    = [
                'name'     => $user_email,
                'username' => $user_email,
                'email'    => $user_email,
            ];
            $user    = $this->create($data);
            $changed = true;
        }

        if ($user instanceof User) {
            $user->setLastVisit();
        }

        if (TemplateHelper::isPage('checkout/onepage') == true && TemplateHelper::isPage('checkout/onepage/success') == false) {
            $throw_event = false;
        } elseif (TemplateHelper::isPage('firecheckout') == true) {
            $throw_event = false;
        }

        if ($changed == true) {
            $this->debug->notice('Postlogin on user = ' . $user_email);
        }

        if ($throw_event == true && $changed == true && !empty($user)) {
            $options             = ['disable_bridge' => true, 'action' => 'core.login.site', 'return' => null];
            $options['remember'] = 1;

            $user = ArrayHelper::fromObject($user);

            $this->debug->notice('Firing event onUserLogin');
            PluginHelper::importPlugin('user');
            $dispatcher = Factory::getContainer()->get(DispatcherInterface::class);
            $loginEvent = AbstractEvent::create('onUserLogin', ['subject' => $user, 'options' => $options]);
            $dispatcher->dispatch('onUserLogin', $loginEvent);
        }

        return true;
    }

    public function isValidEmail($email)
    {
        if (MailHelper::isEmailAddress($email)) {
            return true;
        }

        return false;
    }
}
