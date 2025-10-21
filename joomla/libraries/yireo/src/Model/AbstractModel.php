<?php

declare(strict_types=1);

namespace Yireo\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Input\Input;
use Yireo\Model\Trait\Configurable;

/**
 * Yireo Abstract Model
 * Parent class for Joomla 5+ compatibility.
 *
 * @since  5.0.0
 */
abstract class AbstractModel extends BaseDatabaseModel
{
    use Configurable;

    /**
     * @var CMSApplicationInterface
     */
    protected $app;

    /**
     * @var Input
     */
    protected $input;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->config = $config;
        $this->app    = Factory::getApplication();
        $this->input  = $this->app->getInput();

        $this->handleAbstractDeprecated();
    }

    /**
     * Handle deprecated variables.
     */
    protected function handleAbstractDeprecated(): void
    {
    }

    /**
     * @return CMSApplicationInterface
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param CMSApplicationInterface $app
     */
    public function setApp($app): void
    {
        $this->app = $app;
    }

    /**
     * @return Input
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param Input $input
     */
    public function setInput($input): void
    {
        $this->input = $input;
    }
}
