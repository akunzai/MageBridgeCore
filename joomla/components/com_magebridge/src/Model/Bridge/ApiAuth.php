<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Bridge;

defined('_JEXEC') or die;

/**
 * Pure JSON-RPC API-auth rules used by JsonrpcController.
 */
final class ApiAuth
{
    public static function postedAuthIsPresent(mixed $auth): bool
    {
        return is_array($auth) && !empty($auth['api_user']) && !empty($auth['api_key']);
    }

    public static function credentialsMatch(string $apiUser, string $apiKey, string $configUser, string $configKey): bool
    {
        return $apiUser === $configUser && $apiKey === $configKey;
    }
}
