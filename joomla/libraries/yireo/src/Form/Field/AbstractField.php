<?php

declare(strict_types=1);

namespace Yireo\Form\Field;

defined('_JEXEC') or die();

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;

/**
 * Abstract form field class.
 */
class AbstractField extends FormField
{
    /**
     * Method to instantiate the form field object.
     *
     * @param Form $form
     */
    public function __construct($form = null)
    {
        parent::__construct($form);
    }

    /*
     * Method to get the template associated with this form-field
     *
     * @param string $layoutName
     * @param array $variables
     *
     * @return string
     */
    protected function getTemplate($layoutName, $variables)
    {
        // Determine the layout-name
        $overrideName = $this->getAttribute('template');

        if (!empty($overrideName)) {
            $layoutName = $overrideName;
        }

        if (!preg_match('/\.php$/', $layoutName)) {
            $layoutName .= '.php';
        }

        // Load the template script (and allow for overrides)
        $layoutFile = dirname(__DIR__, 2) . '/form/fields/tmpl/' . $layoutName;
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $templateDir = JPATH_THEMES . '/' . $app->getTemplate();
        $templateOverride = $templateDir . '/html/form/fields/' . $layoutName;

        if (is_file($templateOverride) && is_readable($templateOverride)) {
            $layoutFile = $templateOverride;
        }

        if (is_file($layoutFile) == false || is_readable($layoutFile) == false) {
            return null;
        }

        // Redefine the variables
        foreach ($variables as $name => $value) {
            $$name = $value;
        }

        // Read the template
        ob_start();
        include $layoutFile;
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /*
     * Method to add CSS to this field
     * @return string
     */
    protected function addStylesheet($stylesheet)
    {
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $doc = $app->getDocument();
        $wa = $doc->getWebAssetManager();
        $wa->registerAndUseStyle('yireo-' . md5($stylesheet), $stylesheet);
    }

    /*
     * Method to add JavaScript to this field
     * @return string
     */
    protected function addScript($script)
    {
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $doc = $app->getDocument();
        $wa = $doc->getWebAssetManager();
        $wa->registerAndUseScript('yireo-' . md5($script), $script);
    }

    /*
     * Method to turn an associative array into an HTML-attribute-string
     *
     * @param array $array
     *
     * @return string
     */
    protected function getAttributeString($array)
    {
        $strings = [];

        if (!empty($array)) {
            foreach ($array as $name => $value) {
                if (is_bool($value)) {
                    $value = (int) $value;
                }

                if (empty($value)) {
                    continue;
                }

                $strings[] = $name . '="' . $value . '"';
            }
        }

        return implode(' ', $strings);
    }

    /*
     * Method to get the value of a certain attribute
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if (isset($this->element[$name])) {
            return $this->element[$name];
        }

        return $default;
    }

    /*
     * Method to get the HTML ID from the HTML name
     *
     * @param string $name
     *
     * @return string
     */
    public function getHtmlId($name)
    {
        $id = $name;

        if (preg_match('/([a-zA-Z0-9\-\_]+)\[([a-zA-Z0-9\-\_]+)\]/', $id, $match)) {
            $id = $match[1] . '_' . $match[2] . '_';
        }

        return $id;
    }
}
