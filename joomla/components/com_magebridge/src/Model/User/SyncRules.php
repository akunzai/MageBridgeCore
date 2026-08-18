<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\User;

defined('_JEXEC') or die;

/**
 * Pure UserPlugin sync / SSO / logout rules.
 */
final class SyncRules
{
    /**
     * @return array<string, string>
     */
    public static function subscribedEvents(): array
    {
        return [
            'onUserAfterDelete' => 'onUserAfterDelete',
            'onUserBeforeSave' => 'onUserBeforeSave',
            'onUserAfterSave' => 'onUserAfterSave',
            'onUserLogin' => 'onUserLogin',
            'onUserAfterLogin' => 'onUserAfterLogin',
            'onUserLogout' => 'onUserLogout',
            'onUserAfterLogout' => 'onUserAfterLogout',
        ];
    }

    /**
     * @param array<string, mixed> $oldUser
     *
     * @return array{0: int|string, 1: array{email: mixed}}
     */
    public static function originalEmailSnapshot(array $oldUser): array
    {
        return [$oldUser['id'] ?? 0, ['email' => $oldUser['email'] ?? null]];
    }

    /**
     * @param array<string, mixed> $user
     * @param array<int|string, array{email: mixed}> $originalById
     *
     * @return array<string, mixed>
     */
    public static function mergeOriginalData(array $user, array $originalById): array
    {
        $id = $user['id'] ?? 0;

        if (isset($originalById[$id])) {
            $user['original_data'] = $originalById[$id];
        }

        return $user;
    }

    public static function shouldCopyUsernameFromEmail(
        bool $isSite,
        mixed $usernameFromEmail,
        string $username,
        string $email
    ): bool {
        return $isSite && (int) $usernameFromEmail === 1 && $username !== $email;
    }

    public static function shouldSyncAfterSave(mixed $enableUsersync): bool
    {
        return (int) $enableUsersync === 1;
    }

    public static function shouldSyncOnLogin(mixed $enableUsersync, bool $isSite): bool
    {
        return (int) $enableUsersync === 1 && $isSite;
    }

    /**
     * onUserAfterLogin uses loose != 1.
     */
    public static function shouldStartSsoAfterLogin(mixed $enableSso): bool
    {
        return $enableSso == 1;
    }

    /**
     * onUserAfterLogout uses strict !== 1 and isset(username).
     *
     * @param array<string, mixed> $options
     */
    public static function shouldStartSsoAfterLogout(mixed $enableSso, array $options): bool
    {
        return $enableSso === 1 && isset($options['username']);
    }

    public static function shouldSsoOnClient(bool $isClient, mixed $authEnabled): bool
    {
        return $isClient && (int) $authEnabled === 1;
    }

    /**
     * link_to_magento == 0 means call the bridge logout.
     */
    public static function shouldBridgeLogout(mixed $linkToMagento): bool
    {
        return (int) $linkToMagento === 0;
    }

    /**
     * @return list<string>
     */
    public static function logoutCookies(): array
    {
        return [
            'om_frontend',
            'frontend',
            'user_allowed_save_cookie',
            'persistent_shopping_cart',
            'mb_postlogin',
        ];
    }
}
