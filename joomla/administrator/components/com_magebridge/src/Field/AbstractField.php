<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;

abstract class AbstractField extends FormField
{
    protected $bridge;

    protected $register;

    protected $debugger;

    public function __construct($form = null)
    {
        $this->bridge   = BridgeModel::getInstance();
        $this->register = Register::getInstance();
        $this->debugger = DebugModel::getInstance();

        parent::__construct($form);
    }

    public function getHtmlInput()
    {
        return $this->getInput();
    }

    public function setName($value = null): void
    {
        $this->name = $value;
    }

    public function setValue($value = null): void
    {
        $this->value = $value;
    }

    protected function warning($warning, $variable = null): void
    {
        if (!empty($variable)) {
            $warning .= ': ' . var_export($variable, true);
        }

        $this->debugger->warning($warning);
    }

    protected function getConfig($name)
    {
        return ConfigModel::load($name);
    }
}
