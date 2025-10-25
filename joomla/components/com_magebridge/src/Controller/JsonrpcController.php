<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Input\Input;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Laminas\Json\Server\Error as JsonServerError;
use Laminas\Json\Server\Request as JsonServerRequest;
use Laminas\Json\Server\Response as JsonServerResponse;
use Laminas\Json\Server\Server as JsonServer;
use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use MageBridge\Component\MageBridge\Site\Library\Api as MageBridgeApi;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use RuntimeException;

class JsonrpcController extends BaseController
{
    /**
     * @var DebugModel
     */
    private $debug;

    private ?JsonServer $server = null;

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

        /** @var JsonServerRequest $request */
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
        $request->setParams(['params' => $params]);

        $this->server->handle($request);

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
        if ($this->server instanceof JsonServer) {
            return;
        }

        $autoloadPath = JPATH_SITE . '/components/com_magebridge/vendor/autoload.php';

        if (!class_exists(JsonServer::class, false)) {
            if (!is_file($autoloadPath)) {
                throw new RuntimeException('Missing Laminas JSON-RPC dependencies. Ensure vendor/autoload.php is bundled.');
            }

            require_once $autoloadPath;
        }

        $this->server = new JsonServer();
        $this->server->setClass(MageBridgeApi::class);
    }

    private function close(): void
    {
        $this->app->close();
    }

    private function error(string $message, int $code = 500): void
    {
        $error = new JsonServerError();
        $error->setCode($code);
        $error->setMessage($message);

        /** @var JsonServerResponse $response */
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
