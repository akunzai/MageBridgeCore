<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

// Import required libraries
JLoader::import('joomla.html.html');
JLoader::import('joomla.access.access');
JLoader::import('joomla.form.formfield');

/**
 * Generic Form Field-class
 */
abstract class MageBridgeFormFieldAbstract extends \Joomla\CMS\Form\FormField
{
    /** @var MageBridgeModelBridge */
    protected $bridge;

    /** @var MageBridgeModelRegister */
    protected $register;

    /** @var  MageBridgeModelDebug */
    protected $debugger;

    /**
     * MageBridgeFormFieldAbstract constructor.
     *
     * @param null $form
     */
    public function __construct($form = null)
    {
        $this->bridge   = MageBridgeModelBridge::getInstance();
        $this->register = MageBridgeModelRegister::getInstance();
        $this->debugger = MageBridgeModelDebug::getInstance();

        parent::__construct($form);
    }

    /**
     * Method to wrap the protected getInput() method
     *
     * @return string
     */
    public function getHtmlInput()
    {
        return $this->getInput();
    }

    /**
     * Method to set the name
     *
     * @param mixed $value
     */
    public function setName($value = null)
    {
        $this->name = $value;
    }

    /**
     * Method to set the value
     *
     * @param mixed $value
     */
    public function setValue($value = null)
    {
        $this->value = $value;
    }

    /**
     * @param $warning
     */
    protected function warning($warning, $variable = null)
    {
        if (!empty($variable)) {
            $warning .= ': ' .  var_export($variable, true);
        }

        $this->debugger->warning($warning);
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    protected function getConfig($name)
    {
        return MageBridgeModelConfig::load($name);
    }
}
