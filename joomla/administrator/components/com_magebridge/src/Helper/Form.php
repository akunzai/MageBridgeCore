<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseInterface;
use MageBridge\Component\MageBridge\Administrator\Field\AbstractField;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Form Helper.
 */
class Form
{
    /**
     * Method to get the HTML of a certain field.
     */
    public static function getField(string $type, string $name, mixed $value = null, string $array = 'magebridge'): ?string
    {
        $fileType = (string) preg_replace('/^magebridge\./', '', $type);
        include_once JPATH_ADMINISTRATOR . '/components/com_magebridge/fields/' . $fileType . '.php';

        $field = FormHelper::loadFieldType($type);

        if (!$field instanceof AbstractField) {
            $message = sprintf(Text::_('COM_MAGEBRIDGE_UNKNOWN_FIELD'), $type);

            /** @var CMSApplicationInterface $application */
            $application = Factory::getApplication();
            $application->enqueueMessage($message, 'error');

            return null;
        }

        $field->setName($name);
        $field->setValue($value);

        return $field->getHtmlInput();
    }

    /**
     * Get an object-list of all Joomla! usergroups.
     *
     * @return array<int, object>
     */
    public static function getUsergroupOptions(): array
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('id', 'value'),
                    $db->quoteName('title', 'text'),
                ]
            )
            ->from($db->quoteName('#__usergroups'))
            ->where($db->quoteName('parent_id') . ' > 0');

        return $db->setQuery($query)->loadObjectList();
    }
}
