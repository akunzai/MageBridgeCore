<?php

declare(strict_types=1);

namespace MageBridge\Plugin\User\MageBridgeFirstLast;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * User Plugin for Joomla! - MageBridge First Last name.
 *
 * @since  3.0.0
 */
class MageBridgeFirstLast extends CMSPlugin implements SubscriberInterface
{
    /**
     * @var array
     */
    protected $allowedContext = [
        'com_users.profile',
        'com_users.user',
        'com_users.registration',
        'com_admin.profile',
    ];

    /**
     * Returns an array of events this subscriber will listen to.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepareForm' => 'onContentPrepareForm',
            'onContentPrepareData' => 'onContentPrepareData',
            'onUserAfterSave' => 'onUserAfterSave',
            'onUserAfterDelete' => 'onUserAfterDelete',
            'onUserLoad' => 'onUserLoad',
        ];
    }

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->loadLanguage();
    }

    /**
     * Event onContentPrepareForm.
     *
     * @param Form $form
     * @param array $data
     *
     * @return bool
     */
    public function onContentPrepareForm($form, $data)
    {
        if (!($form instanceof Form)) {
            return false;
        }

        $context = $form->getName();

        if (!in_array($context, $this->allowedContext)) {
            return true;
        }

        Form::addFormPath(__DIR__ . '/../form');
        $form->loadFile('form', false);

        return true;
    }

    /**
     * Event onContentPrepareData.
     *
     * @param string $context
     * @param object $data
     *
     * @return bool
     */
    public function onContentPrepareData($context, $data)
    {
        if (!in_array($context, $this->allowedContext)) {
            return true;
        }

        if (is_object($data)) {
            $userId = $data->id ?? 0;

            if (!isset($data->magebridgefirstlast) && $userId > 0) {
                $fields = $this->getFields($userId);
                $data->magebridgefirstlast = [];

                foreach ($fields as $field) {
                    $fieldName = str_replace('magebridgefirstlast.', '', $field[0]);
                    $data->magebridgefirstlast[$fieldName] = json_decode($field[1], true);

                    if ($data->magebridgefirstlast[$fieldName] === null) {
                        $data->magebridgefirstlast[$fieldName] = $field[1];
                    }
                }
            }
        }

        if (empty($data->magebridgefirstlast['firstname']) && empty($data->magebridgefirstlast['lastname']) && !empty($data->name)) {
            $name = explode(' ', $data->name);

            if (count($name) >= 2) {
                $data->magebridgefirstlast['firstname'] = trim(array_shift($name));
                $data->magebridgefirstlast['lastname'] = trim(implode(' ', $name));
            }
        }

        if (!empty($data->magebridgefirstlast['firstname']) && !empty($data->magebridgefirstlast['lastname']) && empty($data->name)) {
            $data->name = $data->magebridgefirstlast['firstname'] . ' ' . $data->magebridgefirstlast['lastname'];
        }

        return true;
    }

    /**
     * Event onUserAfterSave.
     *
     * @param array $data
     * @param bool $isNew
     * @param bool $result
     * @param mixed $error
     *
     * @return bool
     */
    public function onUserAfterSave($data, $isNew, $result, $error)
    {
        $userId = ArrayHelper::getValue($data, 'id', 0, 'int');

        if ($userId && $result && isset($data['magebridgefirstlast']) && (count($data['magebridgefirstlast']))) {
            $this->deleteFields($userId);

            $ordering = 0;

            foreach ($data['magebridgefirstlast'] as $fieldName => $fieldValue) {
                $this->insertField($userId, $fieldName, $fieldValue, $ordering);
                $ordering++;
            }
        }

        // @todo: Add a setting for this
        if (!empty($data['magebridgefirstlast']['firstname']) && !empty($data['magebridgefirstlast']['lastname'])) {
            $this->setUserName($data['id'], $data['magebridgefirstlast']['firstname'], $data['magebridgefirstlast']['lastname']);
        }

        return true;
    }

    /**
     * Event onUserAfterDelete.
     *
     * @param array $user
     * @param bool $success
     * @param string $msg
     *
     * @return bool
     */
    public function onUserAfterDelete($user, $success, $msg)
    {
        if (!$success) {
            return false;
        }

        $userId = ArrayHelper::getValue($user, 'id', 0, 'int');

        if ($userId) {
            $this->deleteFields($userId);
        }

        return true;
    }

    /**
     * Event onUserLoad.
     *
     * @param User $user
     *
     * @return bool
     */
    public function onUserLoad($user)
    {
        if (empty($user) || empty($user->id)) {
            return false;
        }

        $fields = $this->getFields($user->id);

        foreach ($fields as $field) {
            $fieldName = str_replace('magebridgefirstlast.', '', $field[0]);
            $fieldValue = $field[1];
            // Set property directly instead of using deprecated set() method
            $user->$fieldName = $fieldValue;
        }

        return true;
    }

    /**
     * Method to get all profile fields from user.
     *
     * @param int $userId
     *
     * @return mixed
     */
    protected function getFields($userId)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $columns = ['profile_key', 'profile_value'];
        $query->select($db->quoteName($columns));
        $query->from($db->quoteName('#__user_profiles'));
        $query->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('magebridgefirstlast.%'));
        $query->where($db->quoteName('user_id') . ' = ' . (int) $userId);
        $query->order('ordering ASC');

        $db->setQuery($query);

        $results = $db->loadRowList();

        return $results;
    }

    /**
     * Delete all profile fields belonging to specific user.
     *
     * @param int $userId
     */
    protected function deleteFields($userId)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__user_profiles'))
            ->where($db->quoteName('user_id') . ' = ' . (int) $userId)
            ->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('magebridgefirstlast.%'));
        $db->setQuery($query);

        $db->execute();
    }

    /**
     * Insert a specific profile fields belonging to specific user.
     *
     * @param int $userId
     * @param string $name
     * @param string $value
     * @param int $ordering
     */
    protected function insertField($userId, $name, $value, $ordering)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $columns = ['user_id', 'profile_key', 'profile_value', 'ordering'];
        $values = [$userId, $db->quote('magebridgefirstlast.' . $name), $db->quote($value), $ordering];

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__user_profiles'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        $db->setQuery($query);

        $db->execute();
    }

    /**
     * Set the username.
     *
     * @param int $userId
     * @param string $firstname
     * @param string $lastname
     */
    protected function setUserName($userId, $firstname, $lastname)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__users'))
            ->set($db->quoteName('name') . '=' . $db->quote($firstname . ' ' . $lastname))
            ->where($db->quoteName('id') . '=' . (int) $userId);
        $db->setQuery($query);

        $db->execute();
    }
}
