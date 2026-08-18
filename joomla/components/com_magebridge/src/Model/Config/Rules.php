<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Config;

defined('_JEXEC') or die;

/**
 * Pure config-check rules used by ConfigModel::check().
 */
final class Rules
{
    /**
     * @return list<string>
     */
    public static function requiredElements(): array
    {
        return ['host', 'website', 'api_user', 'api_key'];
    }

    public static function requiredSettingIsEmpty(string $element, mixed $value, bool $configAllEmpty): bool
    {
        return $configAllEmpty === false
            && in_array($element, self::requiredElements(), true)
            && empty($value);
    }

    public static function hostnameHasIllegalCharacters(mixed $value): bool
    {
        return $value !== null && preg_match('/([^a-zA-Z0-9.\-_:]+)/', (string) $value) === 1;
    }

    public static function hostnameLooksLikeIp(mixed $value): bool
    {
        return $value !== null && preg_match('/([0-9.]+)/', (string) $value) === 1;
    }

    public static function apiWidgetsAreDisabled(mixed $value): bool
    {
        return (int) $value !== 1;
    }

    public static function bridgeIsOffline(mixed $value): bool
    {
        return (int) $value === 1;
    }

    public static function websiteIdIsNonNumeric(mixed $value): bool
    {
        return $value !== null && $value !== '' && !is_numeric($value);
    }

    public static function basedirHasIllegalCharacters(mixed $value): bool
    {
        return preg_match('/([a-zA-Z0-9.\-_]+)/', (string) $value) === 0;
    }

    public static function basedirCollidesWithRootAlias(
        string $basedir,
        mixed $rootRoute,
        string $joomlaHost,
        string $magentoHost
    ): bool {
        return !empty($rootRoute) && $rootRoute === $basedir && $joomlaHost === $magentoHost;
    }
}
