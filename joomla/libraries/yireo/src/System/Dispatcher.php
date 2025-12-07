<?php

declare(strict_types=1);

namespace Yireo\System;

defined('_JEXEC') or die();

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Yireo\Controller\Controller;
use Yireo\Helper\PathHelper;

/**
 * Yireo Dispatcher.
 */
class Dispatcher
{
    public static function dispatch()
    {
        // Fetch URL-variables
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $jinput = $app->getInput();
        $option = $jinput->getCmd('option');
        $view = $jinput->getCmd('view');

        // Construct the controller-prefix
        $prefix = ucfirst(preg_replace('/^com_/', '', $option));

        // Check for a corresponding view-controller
        if (!empty($view)) {
            $controllerFile = PathHelper::getAdministratorPath() . '/components/' . $option . '/controllers/' . $view . '.php';

            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $controllerClass = $prefix . 'Controller' . ucfirst($view);

                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                }
            }
        }

        // Return to the default component-controller
        if (empty($controller)) {
            $controllerFile = PathHelper::getAdministratorPath() . '/components/' . $option . '/controller.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $controllerClass = $prefix . 'Controller';

                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                }
            }
        }

        // Default to YireoController
        if (empty($controller)) {
            $controller = new Controller();
        }

        // Perform the Request task
        $controller->execute($jinput->getCmd('task'));
        $controller->redirect();
    }
}
