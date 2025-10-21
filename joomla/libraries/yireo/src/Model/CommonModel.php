<?php

declare(strict_types=1);

namespace Yireo\Model;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Yireo\Model\Trait\Formable;
use Yireo\Model\Trait\Identifiable;

/**
 * Yireo Common Model
 * Parent class for models that need additional features without JTable functionality.
 */
class CommonModel extends AbstractModel
{
    /**
     * Trait to implement ID behaviour.
     */
    use Identifiable;

    /**
     * Trait to implement form behaviour.
     */
    use Formable;

    protected DatabaseInterface $db;

    protected ?User $user = null;

    /**
     * Data container.
     */
    protected array|object $data = [];

    /**
     * Parameters.
     *
     * @var mixed
     */
    protected $params;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        // Call the parent constructor
        parent::__construct($config);

        $this->initCommon();

        // Create the component options
        $view      = $this->detectViewName();
        $option    = $this->getOption();
        $option_id = $option . '_' . $view . '_';
        $component = $this->getComponentNameFromOption($option);

        if ($this->app->isClient('site')) {
            $option_id .= $this->input->getInt('Itemid') . '_';
        }

        $this->setConfig('view', $view);
        $this->setConfig('option', $option);
        $this->setConfig('option_id', $option_id);
        $this->setConfig('component', $component);
        $this->setConfig('frontend_form', false);
        $this->setConfig('skip_table', true);

        $this->handleCommonDeprecated();
    }

    /**
     * @return mixed
     */
    protected function getComponentNameFromOption($option)
    {
        $component = preg_replace('/^com_/', '', $option);
        $component = preg_replace('/[^A-Z0-9_]/i', '', $component);
        $component = str_replace(' ', '', ucwords(str_replace('_', ' ', $component)));

        return $component;
    }

    /**
     * @return string
     */
    protected function detectViewName()
    {
        $classParts = explode('Model', get_class($this));
        $view       = (!empty($classParts[1])) ? strtolower($classParts[1]) : $this->input->getCmd('view');

        return $view;
    }

    /**
     * Inititalize system variables.
     */
    protected function initCommon()
    {
        $this->db   = Factory::getContainer()->get(DatabaseInterface::class);
        $this->user = Factory::getApplication()->getIdentity();
    }

    /**
     * Handle deprecated variables.
     */
    protected function handleCommonDeprecated()
    {
    }

    /**
     * Method to determine the component-name.
     *
     * @return string
     */
    protected function getOption()
    {
        if (empty($this->option)) {
            $classParts   = explode('Model', get_class($this));
            $comPart      = (!empty($classParts[0])) ? $classParts[0] : null;
            $comPart      = preg_replace('/([A-Z])/', '_\\1', $comPart);
            $comPart      = strtolower(preg_replace('/^_/', '', $comPart));
            $option       = (!empty($comPart) && $comPart != 'yireo') ? 'com_' . $comPart : $this->input->getCmd('option');
            $this->option = $option;
        }

        return $this->option;
    }

    /**
     * Method to override the parameters.
     *
     * @param mixed $params
     */
    public function setParams($params = null)
    {
        if (!empty($params)) {
            $this->params = $params;
        }
    }

    /**
     * @return Registry|null
     */
    public function getParams()
    {
        return $this->params;
    }
}
