<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Proxy;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Helper\ProxyHelper;
use MageBridge\Component\MageBridge\Site\Model\UserModel;

abstract class AbstractProxy
{
    public const CONNECTION_FALSE   = 0;
    public const CONNECTION_SUCCESS = 1;
    public const CONNECTION_ERROR   = 1;

    protected int $count = 2;

    protected string $state = '';

    protected int $init = self::CONNECTION_FALSE;

    /** @var BridgeModel */
    protected $bridge;

    /** @var DebugModel */
    protected $debug;

    /** @var CMSApplication */
    protected $app;

    /** @var ProxyHelper */
    protected $helper;

    /** @var Input */
    protected $input;

    /** @var \Joomla\Registry\Registry */
    protected $config;

    /** @var UserModel */
    protected $user;

    public static function getInstance()
    {
        static $instance;

        if ($instance === null) {
            $instance = new Proxy();
        }

        return $instance;
    }

    public function __construct()
    {
        $this->bridge = BridgeModel::getInstance();
        $this->debug  = DebugModel::getInstance();
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $this->app    = $app;
        $this->helper = new ProxyHelper($app);
        $this->input  = $app->input;
        $this->user   = UserModel::getInstance();
        $this->config = $app->getConfig();
    }

    public function encode($data)
    {
        $encoded = json_encode($data);

        if ($encoded === false) {
            if (is_string($data)) {
                $data = mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
            }

            $encoded = json_encode($data);

            if ($encoded === false) {
                $jsonError = function_exists('json_last_error') ? json_last_error() : 'unknown';

                if ($jsonError === JSON_ERROR_UTF8) {
                    $jsonError = 'Malformed UTF-8';
                } elseif ($jsonError === JSON_ERROR_SYNTAX) {
                    $jsonError = 'Syntax error';
                }

                $this->debug->error('PHP Error: json_encode failed with error "' . $jsonError . '"');
                $this->debug->trace('Data before json_encode', $data);
            }
        }

        return $encoded;
    }

    public function decode($data)
    {
        if (!is_string($data)) {
            return $data;
        }

        $decoded = json_decode($data, true);

        if ($decoded === false || $decoded === 1 || $decoded === $data) {
            return false;
        }

        return $decoded;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function incrementCount(): void
    {
        $this->count++;
    }
}
