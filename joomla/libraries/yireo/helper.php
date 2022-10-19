<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo (info@yireo.com)
 * @package YireoLib
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the loader
require_once dirname(__FILE__) . '/loader.php';

/**
 * Yireo Helper
 * @subpackage Yireo
 */
class YireoHelper
{
    /**
     * Helper-method to parse the data defined in this component
     *
     * @param string $name
     * @param string $option
     * @return mixed
     */
    public static function getData($name = null, $option = null)
    {
        if (empty($option)) {
            $option = JFactory::getApplication()->input->getCmd('option');
        }

        $file = JPATH_ADMINISTRATOR . '/components/' . $option . '/helpers/abstract.php';

        if (is_file($file)) {
            require_once $file;
            $class = 'HelperAbstract';

            if (class_exists($class)) {
                $object = new $class();
                $data = $object->getStructure();
                if (isset($data[$name])) {
                    return $data[$name];
                }
            }
        }

        return null;
    }

    /**
     * Helper-method to return the HTML-ending of a form
     *
     * @param int $id
     * @return void
     */
    public static function getFormEnd($id = 0)
    {
        echo '<input type="hidden" name="option" value="' . JFactory::getApplication()->input->getCmd('option') . '" />';
        echo '<input type="hidden" name="cid[]" value="' . $id . '" />';
        echo '<input type="hidden" name="task" value="" />';
        echo JHtml::_('form.token');
    }

    /**
     * Helper-method to check whether the current Joomla! version equals some value
     *
     * @param string|array $version
     * @return bool
     */
    public static function isJoomla($version)
    {
        if (!is_array($version)) {
            $version = [$version];
        }

        foreach ($version as $v) {
            if (version_compare(JVERSION, $v, 'eq')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper-method to compare with current Joomla! version
     *
     * @param null
     * @return bool
     */
    public static function compareJoomlaVersion($version, $comparison)
    {
        return version_compare(JVERSION, $version, $comparison);
    }

    /**
     * Method to get the current version
     *
     * @return string
     */
    public static function getCurrentVersion()
    {
        $option = JFactory::getApplication()->input->getCmd('option');
        $name = preg_replace('/^com_/', '', $option);
        $file = JPATH_ADMINISTRATOR . '/components/' . $option . '/' . $name . '.xml';
        $data = JInstaller::parseXMLInstallFile($file);
        return $data['version'];
    }

    /**
     * Convert an object or string to JRegistry
     *
     * @param mixed $params
     * @param string $file
     * @return JRegistry
     */
    public static function toRegistry($params = null, $file = null)
    {
        if ($params instanceof JRegistry) {
            return $params;
        }

        if (is_string($params)) {
            $params = trim($params);
        }

        $registry = new JRegistry();

        if (!empty($params) && is_string($params)) {
            $registry->loadString($params);
        }

        if (!empty($params) && is_array($params)) {
            $registry->loadArray($params);
        }

        if (is_file($file) && is_readable($file)) {
            $fileContents = file_get_contents($file);
        } else {
            $fileContents = null;
        }

        if (preg_match('/\.xml$/', $fileContents)) {
            $registry->loadFile($file, 'XML');
        } elseif (preg_match('/\.json$/', $fileContents)) {
            $registry->loadFile($file, 'JSON');
        }

        $params = $registry;

        return $params;
    }

    /**
     * Add in Bootstrap
     *
     * @return void
     */
    public static function bootstrap()
    {
        JHtml::_('bootstrap.framework');
        self::jquery();
    }

    /**
     * Method to check whether Bootstrap is used
     *
     * @return bool
     */
    public static function hasBootstrap()
    {
        $application = JFactory::getApplication();

        if (method_exists($application, 'get') && $application->get('bootstrap') == true) {
            return true;
        }

        return false;
    }

    /**
     * Add in jQuery
     *
     * @return void
     */
    public static function jquery()
    {
        // Do not load when having no HTML-document
        /** @var Joomla\CMS\Document\HtmlDocument */
        $document = JFactory::getDocument();

        if (stristr(get_class($document), 'html') == false) {
            return;
        }

        // Load jQuery using the framework
        return JHtml::_('jquery.framework');

        // Check if jQuery is loaded already
        $application = JFactory::getApplication();

        if (method_exists($application, 'get') && $application->get('jquery') == true) {
            return;
        }

        // Do not load this for specific extensions
        if (JFactory::getApplication()->input->getCmd('option') == 'com_virtuemart') {
            return false;
        }

        // Load jQuery
        $option = JFactory::getApplication()->input->getCmd('option');

        if (file_exists(JPATH_SITE . '/media/' . $option . '/js/jquery.js')) {
            $document->addScript(JURI::root() . 'media/' . $option . '/js/jquery.js');
            $document->addCustomTag('<script type="text/javascript">jQuery.noConflict();</script>');

            // Set the flag that jQuery has been loaded
            if (method_exists($application, 'set')) {
                $application->set('jquery', true);
            }
        }
    }

    /**
     * Helper-method to load additional language-files
     *
     * @return void
     */
    public static function loadLanguageFile()
    {
        $application = JFactory::getApplication();
        $language = JFactory::getLanguage();
        $extension = 'lib_yireo';

        $folder = ($application->isClient('site')) ? JPATH_SITE : JPATH_ADMINISTRATOR;
        $tag = $language->getTag();
        $reload = true;
        $language->load($extension, $folder, $tag, $reload);
    }
}
