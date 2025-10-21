<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;

\defined('MAGEBRIDGE_DEBUG_TRACE') || \define('MAGEBRIDGE_DEBUG_TRACE', 1);
\defined('MAGEBRIDGE_DEBUG_NOTICE') || \define('MAGEBRIDGE_DEBUG_NOTICE', 2);
\defined('MAGEBRIDGE_DEBUG_WARNING') || \define('MAGEBRIDGE_DEBUG_WARNING', 3);
\defined('MAGEBRIDGE_DEBUG_ERROR') || \define('MAGEBRIDGE_DEBUG_ERROR', 4);
\defined('MAGEBRIDGE_DEBUG_FEEDBACK') || \define('MAGEBRIDGE_DEBUG_FEEDBACK', 5);
\defined('MAGEBRIDGE_DEBUG_PROFILER') || \define('MAGEBRIDGE_DEBUG_PROFILER', 6);

\defined('MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA') || \define('MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA', 'joomla');
\defined('MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO') || \define('MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO', 'magento');
\defined('MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA_JSONRPC') || \define('MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA_JSONRPC', 'joomla_jsonrpc');
\defined('MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO_JSONRPC') || \define('MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO_JSONRPC', 'magento_jsonrpc');

final class DebugModel
{
    public const MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA = 'joomla';
    public const MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO = 'magento';
    public const MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA_JSONRPC = 'joomla_jsonrpc';
    public const MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO_JSONRPC = 'magento_jsonrpc';

    private static ?self $instance = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $data = [];

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function init(): void
    {
        static $flag = false;

        if ($flag === false) {
            $flag = true;

            if (self::isDebug() && (int) ConfigModel::load('debug_display_errors') === 1) {
                ini_set('display_errors', '1');
            }
        }
    }

    public static function beforeBuild(): void
    {
        static $flag = false;

        if ($flag === false) {
            $flag = true;
            $bridge = BridgeModel::getInstance();
            $debug  = self::getInstance();

            $debug->notice('API session: ' . $bridge->getApiSession());
            $debug->notice('Magento session: ' . $bridge->getMageSession());
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getBridgeData(): void
    {
        static $flag = false;

        if ($flag === false) {
            $flag = true;

            $bridge = BridgeModel::getInstance();
            $data   = $bridge->getDebug();

            if (!empty($data) && is_array($data)) {
                foreach ($data as $entry) {
                    if (!is_array($entry)) {
                        continue;
                    }

                    $entry['origin'] = MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO;
                    $entry['time']   = time();

                    $this->addInternal($entry);
                }
            }
        }
    }

    public function clean(): void
    {
        $this->data = [];
    }

    public static function getDebugOrigin($value = null): string
    {
        static $debugType = MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA;

        if (!empty($value)) {
            $debugType = $value;
        }

        return $debugType;
    }

    public function add($type = MAGEBRIDGE_DEBUG_NOTICE, $message = null, $section = null, $origin = null, $time = null): bool
    {
        $application = Factory::getApplication();

        if ($application->isClient('administrator') && $type === MAGEBRIDGE_DEBUG_FEEDBACK) {
            if (!empty($message)) {
                $application->enqueueMessage($message, 'warning');
            }

            return true;
        }

        if (!self::isDebug()) {
            return false;
        }

        if (empty($message)) {
            return false;
        }

        if (!$time > 0) {
            $time = time();
        }

        if (empty($origin)) {
            $origin = self::getDebugOrigin();
        }

        if (empty($section)) {
            $section = '';
        }

        $data = [
            'type'    => $type,
            'message' => $message,
            'section' => $section,
            'origin'  => $origin,
            'time'    => $time,
        ];

        return $this->addInternal($data);
    }

    public function trace($message = null, $variable = null, $section = null, $origin = null, $time = null): bool
    {
        if (!empty($variable)) {
            $message .= ': ' . var_export($variable, true);
        }

        return $this->add(MAGEBRIDGE_DEBUG_TRACE, $message, $section, $origin, $time);
    }

    public function notice($message = null, $section = null, $origin = null, $time = null): bool
    {
        return $this->add(MAGEBRIDGE_DEBUG_NOTICE, $message, $section, $origin, $time);
    }

    public function warning($message = null, $section = null, $origin = null, $time = null): bool
    {
        return $this->add(MAGEBRIDGE_DEBUG_WARNING, $message, $section, $origin, $time);
    }

    public function error($message = null, $section = null, $origin = null, $time = null): bool
    {
        return $this->add(MAGEBRIDGE_DEBUG_ERROR, $message, $section, $origin, $time);
    }

    public function feedback($message = null, $section = null, $origin = null, $time = null): bool
    {
        return $this->add(MAGEBRIDGE_DEBUG_FEEDBACK, $message, $section, $origin, $time);
    }

    public function profiler($message = null, $section = null, $origin = null, $time = null): bool
    {
        return $this->add(MAGEBRIDGE_DEBUG_PROFILER, $message, $section, $origin, $time);
    }

    public static function isDebug(): bool
    {
        static $debug = null;

        if ($debug === null) {
            $debug = false;
            $configDebug = (int) ConfigModel::load('debug');

            if ($configDebug === 1 && !empty($_SERVER['REMOTE_ADDR'])) {
                $ips = ConfigModel::load('debug_ip');

                if (strlen((string) $ips) > 0) {
                    $ipArray = explode(',', (string) $ips);

                    foreach ($ipArray as $ip) {
                        if (trim($ip) === $_SERVER['REMOTE_ADDR']) {
                            $debug = true;
                            break;
                        }
                    }
                } else {
                    $debug = true;
                }
            }
        }

        return (bool) $debug;
    }

    public function getUniqueId($length = 30): string
    {
        static $unique = null;

        if ($unique === null) {
            $alphNums  = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $newString = str_shuffle(str_repeat($alphNums, rand(1, $length)));
            $unique    = substr($newString, rand(0, strlen($newString) - $length), $length);
        }

        return $unique;
    }

    private function addInternal(array $data): bool
    {
        foreach (['section', 'type'] as $index) {
            if (!array_key_exists($index, $data)) {
                $data[$index] = null;
            }
        }

        if (!self::isDebug()) {
            return false;
        }

        $debugLevel = ConfigModel::load('debug_level');

        if ($debugLevel === 'error' && $data['type'] != MAGEBRIDGE_DEBUG_ERROR) {
            return false;
        }

        if ($debugLevel === 'profiler' && $data['type'] != MAGEBRIDGE_DEBUG_PROFILER) {
            return false;
        }

        $this->data[] = $data;

        switch (ConfigModel::load('debug_log')) {
            case 'db':
                $this->writeDb($data);
                break;

            case 'file':
                $this->writeLog($data);
                break;

            case 'both':
                $this->writeDb($data);
                $this->writeLog($data);
                break;
        }

        return true;
    }

    private function writeLog(?array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        /** @var CMSApplication */
        $app = Factory::getApplication();
        $config  = $app->getConfig();
        $logPath = $config->get('log_path');

        if (empty($logPath)) {
            $logPath = JPATH_SITE . '/logs';
        }

        $file = $logPath . '/magebridge.txt';

        // Check if file exists and is writable, or if directory is writable for new file
        if (file_exists($file)) {
            if (!is_writable($file)) {
                return false;
            }
        } elseif (!is_writable($logPath)) {
            return false;
        }

        $message = '[' . ($data['origin'] ?? '') . '] ';
        $message .= ($data['section'] ?? '') . ' ';
        $message .= '(' . date('Y-m-d H:i:s', $data['time']) . ') ';
        $message .= ($data['type'] ?? '') . ': ';
        $message .= ($data['message'] ?? '') . "\n";

        file_put_contents($file, $message, FILE_APPEND);

        return true;
    }

    private function writeDb(?array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $db          = Factory::getContainer()->get(DatabaseInterface::class);
        $remoteAddr  = $_SERVER['REMOTE_ADDR'] ?? null;
        $httpAgent   = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $uniqueId    = $this->getUniqueId();

        $values = [
            'message'     => $data['message'],
            'type'        => $data['type'],
            'origin'      => $data['origin'],
            'section'     => $data['section'],
            'timestamp'   => date('Y-m-d H:i:s', $data['time']),
            'remote_addr' => $remoteAddr,
            'session'     => $uniqueId,
            'http_agent'  => $httpAgent,
        ];

        $queryParts = [];

        foreach ($values as $name => $value) {
            $queryParts[] = '`' . $name . '`=' . $db->quote($value);
        }

        $query = 'INSERT INTO `#__magebridge_log` SET ' . implode(', ', $queryParts);

        $db->setQuery($query);

        $result = $db->execute();

        return (bool) $result;
    }
}
