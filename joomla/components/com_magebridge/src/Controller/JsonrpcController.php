<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;

class JsonrpcController extends BaseController
{
    /**
     * @var DebugModel
     */
    private $debug;

    /**
     * @var \Zend_Json_Server|null
     */
    private $server = null;

    public function __construct(
        array $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSApplicationInterface $app = null,
        ?Input $input = null
    ) {
        parent::__construct($config, $factory, $app, $input);

        $this->app = $app ?: Factory::getApplication();

        DebugModel::getDebugOrigin(DebugModel::MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA_JSONRPC);
        $this->debug = DebugModel::getInstance();
    }

    public function call(): void
    {
        ini_set('display_errors', '1');

        $this->init();

        /** @var \Zend_Json_Server_Request $request */
        $request = $this->server->getRequest();

        $params = $request->getParams();

        if (!isset($params['api_auth'])) {
            $this->debug->error('JSON-RPC Call: No authentication data');
            $this->error('No authentication data', 403);

            return;
        }

        if ($this->authenticate($params['api_auth']) === false) {
            $this->debug->error('JSON-RPC Call: Authentication failed');
            $this->error('Authentication failed', 401);

            return;
        }

        unset($params['api_auth']);
        $params = ['params' => $params];

        $request->setParams($params);

        $this->server->handle($this->server->getRequest());

        $this->close();
    }

    public function servicemap(): void
    {
        $this->init();
        $smd = $this->server->getServiceMap();

        header('Content-Type: application/json');
        echo $smd;

        $this->close();
    }

    private function init(): void
    {
        $library = JPATH_SITE . '/components/com_magebridge/libraries';
        require_once $library . '/api.php';

        if (!defined('ZEND_PATH')) {
            set_include_path($library . PATH_SEPARATOR . get_include_path());
        } else {
            set_include_path(ZEND_PATH . PATH_SEPARATOR . get_include_path());
        }

        require_once 'Zend/Json/Server.php';
        require_once 'Zend/Json/Server/Error.php';

        $this->server = new \Zend_Json_Server();
        $this->server->setClass('MageBridgeApi');
    }

    private function close(): void
    {
        $this->app->close();
    }

    private function error(string $message, int $code = 500): void
    {
        $error = new \Zend_Json_Server_Error();
        $error->setCode($code);
        $error->setMessage($message);

        /** @var \Zend_Json_Server_Response $response */
        $response = $this->server->getResponse();
        $response->setError($error);

        $this->server->setResponse($response);
        $this->server->handle();

        http_response_code($code);

        $this->close();
    }

    private function authenticate($auth): bool
    {
        if (empty($auth) || empty($auth['api_user']) || empty($auth['api_key'])) {
            return false;
        }

        $apiUser = EncryptionHelper::decrypt($auth['api_user']);
        $apiKey  = EncryptionHelper::decrypt($auth['api_key']);

        $configUser = ConfigModel::load('api_user');
        $configKey = ConfigModel::load('api_key');

        if ($apiUser !== $configUser) {
            $this->debug->error('JSON-RPC: API-authentication failed: Username "' . $apiUser . '" did not match');
            return false;
        }

        if ($apiKey !== $configKey) {
            $this->debug->error('JSON-RPC: API-authentication failed: Key "' . $apiKey . '" did not match');
            return false;
        }

        return true;
    }
}
