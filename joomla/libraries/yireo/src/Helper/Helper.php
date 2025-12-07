<?php

declare(strict_types=1);

namespace Yireo\Helper;

defined('_JEXEC') or die();

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\Registry\Registry;
use Yireo\Helper\PathHelper;

/**
 * Yireo Helper.
 */
class Helper
{
    /**
     * Helper-method to parse the data defined in this component.
     *
     * @param string $name
     * @param string $option
     *
     * @return mixed
     */
    public static function getData($name = null, $option = null)
    {
        if (empty($option)) {
            $option = Factory::getApplication()->getInput()->getCmd('option');
        }

        $file = PathHelper::getAdministratorPath() . '/components/' . $option . '/helpers/abstract.php';

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
     * Helper-method to return the HTML-ending of a form.
     *
     * @param int $id
     */
    public static function getFormEnd($id = 0)
    {
        echo '<input type="hidden" name="option" value="' . Factory::getApplication()->getInput()->getCmd('option') . '" />';
        echo '<input type="hidden" name="cid[]" value="' . $id . '" />';
        echo '<input type="hidden" name="task" value="" />';
        echo HTMLHelper::_('form.token');
    }



    /**
     * Method to get the current version.
     *
     * @return string
     */
    public static function getCurrentVersion()
    {
        $option = Factory::getApplication()->getInput()->getCmd('option');
        $file = PathHelper::getAdministratorPath() . '/components/' . $option . '/' . $option . '.xml';
        $data = Installer::parseXMLInstallFile($file);
        return $data['version'];
    }

    /**
     * Convert an object or string to Registry.
     *
     * @param mixed $params
     * @param string $file
     *
     * @return Registry
     */
    public static function toRegistry($params = null, $file = null)
    {
        if ($params instanceof Registry) {
            return $params;
        }

        if (is_string($params)) {
            $params = trim($params);
        }

        $registry = new Registry();

        if (!empty($params) && is_string($params)) {
            $registry->loadString($params);
        }

        if (!empty($params) && is_array($params)) {
            $registry->loadArray($params);
        }

        if ($file !== null && is_file($file) && is_readable($file)) {
            $fileContents = file_get_contents($file);

            if (preg_match('/\.xml$/', (string) $fileContents)) {
                $registry->loadFile($file, 'XML');
            } elseif (preg_match('/\.json$/', (string) $fileContents)) {
                $registry->loadFile($file, 'JSON');
            }
        }

        $params = $registry;

        return $params;
    }

    /**
     * Add in Bootstrap.
     */
    public static function bootstrap()
    {
        HTMLHelper::_('bootstrap.framework');
        self::jquery();
    }

    /**
     * Method to check whether Bootstrap is used.
     *
     * @return bool
     */
    public static function hasBootstrap()
    {
        $app = Factory::getApplication();

        if (method_exists($app, 'get') && $app->get('bootstrap') == true) {
            return true;
        }

        return false;
    }

    /**
     * Add in jQuery.
     */
    public static function jquery()
    {
        // Do not load when having no HTML-document
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $document = $app->getDocument();

        if (stristr(get_class($document), 'html') == false) {
            return;
        }

        // Load jQuery using the framework
        HTMLHelper::_('jquery.framework');

        // Check if jQuery is loaded already
        if (method_exists($app, 'get') && $app->get('jquery') == true) {
            return;
        }

        // Do not load this for specific extensions
        if ($app->getInput()->getCmd('option') == 'com_virtuemart') {
            return;
        }

        // Load jQuery
        $option = $app->getInput()->getCmd('option');

        if (file_exists(PathHelper::getSitePath() . '/media/' . $option . '/js/jquery.js')) {
            $wa = $document->getWebAssetManager();
            $wa->registerAndUseScript(
                'yireo.jquery',
                'media/' . $option . '/js/jquery.js',
                [],
                ['defer' => false],
                []
            );
            $wa->addInlineScript('jQuery.noConflict();');

            // Set the flag that jQuery has been loaded
            if (method_exists($app, 'set')) {
                $app->set('jquery', true);
            }
        }
    }

    /**
     * Helper-method to load additional language-files.
     */
    public static function loadLanguageFile()
    {
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $language = $app->getLanguage();
        $extension = 'lib_yireo';

        $folder = ($app->isClient('site')) ? PathHelper::getSitePath() : PathHelper::getAdministratorPath();
        $tag = $language->getTag();
        $reload = true;
        $language->load($extension, $folder, $tag, $reload);
    }
}
