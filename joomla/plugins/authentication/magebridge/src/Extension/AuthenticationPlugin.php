<?php

declare(strict_types=1);

namespace MageBridge\Plugin\Authentication\MageBridge\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Event\User\AuthenticationEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;

/**
 * MageBridge Authentication Plugin.
 *
 * @since  3.0.0
 */
final class AuthenticationPlugin extends CMSPlugin implements SubscriberInterface
{
    // MageBridge constants
    public const MAGEBRIDGE_AUTHENTICATION_FAILURE = 0;
    public const MAGEBRIDGE_AUTHENTICATION_SUCCESS = 1;
    public const MAGEBRIDGE_AUTHENTICATION_ERROR = 2;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onUserAuthenticate' => 'onUserAuthenticate',
        ];
    }

    /**
     * Handle the event that is generated when a user tries to login.
     *
     * @param AuthenticationEvent $event Authentication event
     */
    public function onUserAuthenticate(AuthenticationEvent $event): void
    {
        $credentials = $event->getCredentials();
        $response = $event->getAuthenticationResponse();

        $response->type = 'MageBridge';

        // Check if this authentication method is enabled
        if (!$this->params->get('enabled', 1)) {
            return;
        }

        // Get the username and password
        $username = $credentials['username'] ?? '';
        $password = $credentials['password'] ?? '';

        $app = $this->getApplication();
        $lang = $app?->getLanguage();

        if (empty($username) || empty($password)) {
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = $lang?->_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED')
                ?? 'Empty password not allowed';

            return;
        }

        // Try to authenticate with MageBridge
        $result = $this->authenticateMageBridge($username, $password);

        switch ($result) {
            case self::MAGEBRIDGE_AUTHENTICATION_SUCCESS:
                $response->status = Authentication::STATUS_SUCCESS;
                $response->username = $username;
                $response->fullname = $username;
                $response->error_message = '';
                $event->stopPropagation();

                return;

            case self::MAGEBRIDGE_AUTHENTICATION_FAILURE:
                $response->status = Authentication::STATUS_FAILURE;
                $response->error_message = $lang?->_('JGLOBAL_AUTH_INVALID_PASS')
                    ?? 'Invalid password';

                return;

            case self::MAGEBRIDGE_AUTHENTICATION_ERROR:
            default:
                $response->status = Authentication::STATUS_FAILURE;
                $response->error_message = $lang?->_('JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED')
                    ?? 'Unknown access denied';

                return;
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
