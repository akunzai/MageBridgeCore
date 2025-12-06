<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Bridge;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use MageBridge\Component\MageBridge\Site\Library\MageBridge;

class Segment
{
    /** @var array<string, self> */
    protected static array $instances = [];

    protected $register;

    protected $bridge;

    protected $app;

    protected $doc;

    public static function getInstance(?string $name = null)
    {
        $name ??= 'MageBridgeModelBridgeSegment';

        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new $name();
        }

        return self::$instances[$name];
    }

    public function __construct()
    {
        $this->register = MageBridge::getRegister();
        $this->bridge   = MageBridge::getBridge();
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $this->app      = $app;
        $this->doc      = $app->getDocument();
    }

    public function getResponseById($id = null)
    {
        return $this->register->getById($id);
    }

    protected function getResponse($type = '', $name = null, $arguments = null, $id = null)
    {
        return $this->register->get($type, $name, $arguments, $id);
    }
}
