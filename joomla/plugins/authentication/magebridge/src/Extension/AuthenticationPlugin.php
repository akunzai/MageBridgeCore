<?php

declare(strict_types=1);

namespace MageBridge\Plugin\Authentication\MageBridge;

defined('_JEXEC') or die;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;

/**
 * MageBridge Authentication Plugin.
 *
 * @since  3.0.0
 */
class AuthenticationPlugin extends CMSPlugin implements SubscriberInterface
{
    // MageBridge constants
    public const MAGEBRIDGE_AUTHENTICATION_FAILURE = 0;
    public const MAGEBRIDGE_AUTHENTICATION_SUCCESS = 1;
    public const MAGEBRIDGE_AUTHENTICATION_ERROR = 2;

    /**
     * Returns an array of events this subscriber will listen to.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onUserAuthenticate' => 'onUserAuthenticate',
        ];
    }

    /**
     * Constructor.
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->loadLanguage();
    }

    /**
     * Handle the event that is generated when an user tries to login.
     *
     * @param array $credentials
     * @param array $options
     * @param object $response
     */
    public function onUserAuthenticate($credentials, $options, &$response): bool
    {
        // Check if this authentication method is enabled
        if (!$this->params->get('enabled', 1)) {
            return false;
        }

        // Get the username and password
        $username = $credentials['username'] ?? '';
        $password = $credentials['password'] ?? '';

        if (empty($username) || empty($password)) {
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = 'JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED';
            return false;
        }

        // Try to authenticate with MageBridge
        $result = $this->authenticateMageBridge($username, $password);

        switch ($result) {
            case self::MAGEBRIDGE_AUTHENTICATION_SUCCESS:
                $response->status = Authentication::STATUS_SUCCESS;
                $response->username = $username;
                $response->fullname = $username;
                $response->error_message = '';
                return true;

            case self::MAGEBRIDGE_AUTHENTICATION_FAILURE:
                $response->status = Authentication::STATUS_FAILURE;
                $response->error_message = 'JGLOBAL_AUTH_INVALID_PASS';
                return false;

            case self::MAGEBRIDGE_AUTHENTICATION_ERROR:
            default:
                $response->status = Authentication::STATUS_FAILURE;
                $response->error_message = 'JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED';
                return false;
        }
    }

    /**
     * Authenticate a user through MageBridge.
     */
    private function authenticateMageBridge(string $username, string $password): int
    {
        // This is a simplified implementation - in reality this would
        // communicate with Magento through the MageBridge API
        try {
            // Check if MageBridge is available
            if (!class_exists(BridgeModel::class)) {
                return self::MAGEBRIDGE_AUTHENTICATION_ERROR;
            }

            // Get the bridge instance
            $bridge = BridgeModel::getInstance();

            // Attempt authentication (simplified)
            $result = $bridge->getAPI('customer.login', [
                'username' => $username,
                'password' => $password,
            ]);

            if ($result && isset($result['success']) && $result['success']) {
                return self::MAGEBRIDGE_AUTHENTICATION_SUCCESS;
            }

            return self::MAGEBRIDGE_AUTHENTICATION_FAILURE;
        } catch (\Exception $e) {
            return self::MAGEBRIDGE_AUTHENTICATION_ERROR;
        }
    }
}
