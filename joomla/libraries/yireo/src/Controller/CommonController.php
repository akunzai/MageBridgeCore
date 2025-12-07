<?php

declare(strict_types=1);

namespace Yireo\Controller;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Input\Input;
use Yireo\Exception\Controller\NotFound;
use Yireo\Helper\Helper;
use Yireo\Helper\PathHelper;

/**
 * Yireo Common Controller.
 */
class CommonController extends AbstractController
{
    /**
     * @var \Joomla\CMS\Application\CMSApplication
     */
    protected $app;

    /**
     * @var Input
     */
    protected $input;

    /**
     * Value of the last message.
     *
     * @var string
     */
    protected $msg = '';

    /**
     * Type of the last message.
     *
     * @var string
     *
     * @values    error|notice|message
     */
    protected $msg_type = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Define variables
        /** @var \Joomla\CMS\Application\CMSApplication $app */
        $app = Factory::getApplication();
        $this->app = $app;
        $this->input = $app->getInput();

        // Add model paths
        $this->addModelPaths();

        // Load additional language-files
        Helper::loadLanguageFile();

        // Call the parent constructor
        parent::__construct();
    }

    /**
     * Add model paths for either backend or frontend.
     */
    protected function addModelPaths()
    {
        // Note: setModelPath method not available in Joomla 5 BaseController
        // Model paths are handled differently in Joomla 5
    }

    /**
     * @throws NotFound
     *
     * @return mixed
     */
    public static function getControllerInstance($option, $name)
    {
        // Check for a child controller
        $componentPath = PathHelper::getAdministratorPath() . '/components/' . $option;
        if (is_file($componentPath . '/controllers/' . $name . '.php')) {
            require_once $componentPath . '/controllers/' . $name . '.php';

            $controllerClass = ucfirst($option) . 'Controller' . ucfirst($name);

            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();

                return $controller;
            }
        }

        return self::getDefaultControllerInstance($option, $name);
    }

    /**
     * @throws NotFound
     *
     * @return mixed
     */
    public static function getDefaultControllerInstance($option, $name)
    {
        // Require the base controller
        $componentPath = PathHelper::getAdministratorPath() . '/components/' . $option;
        if (is_file($componentPath . '/controller.php')) {
            require_once $componentPath . '/controller.php';
        }

        $controllerClass = ucfirst($option) . 'Controller';

        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();

            return $controller;
        }

        throw new NotFound(Text::_('LIB_YIREO_NO_CONTROLLER_FOUND'));
    }
}
