<?php

declare(strict_types=1);

namespace Yireo\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Path compatibility layer for Joomla v5 and v6.
 *
 * This class provides a unified way to get Joomla path constants that works in both v5 and v6.
 * In Joomla v6, JPATH_* constants have been removed, and this class provides compatible alternatives.
 */
class PathHelper
{
    /**
     * Get Joomla site root path.
     *
     * @return string Site root path
     */
    public static function getSitePath(): string
    {
        if (\defined('JPATH_SITE')) {
            return (string) \constant('JPATH_SITE');
        }

        // Joomla 6.x fallback
        try {
            $app = Factory::getApplication();

            /** @var \Joomla\Registry\Registry|null $config */
            $config = method_exists($app, 'getConfig') ? $app->getConfig() : null;
            $path = $config?->get('absolute_path');

            if (!empty($path)) {
                return (string) $path;
            }
        } catch (\Throwable $e) {
            // Continue to next fallback
        }

        // Last resort: calculate path relative to this file
        return realpath(__DIR__ . '/../../../..');
    }

    /**
     * Get Joomla base path (usually same as site path).
     *
     * @return string Base path
     */
    public static function getBasePath(): string
    {
        if (\defined('JPATH_BASE')) {
            return (string) \constant('JPATH_BASE');
        }

        return self::getSitePath();
    }

    /**
     * Get Joomla administrator path.
     *
     * @return string Administrator path
     */
    public static function getAdministratorPath(): string
    {
        if (\defined('JPATH_ADMINISTRATOR')) {
            return (string) \constant('JPATH_ADMINISTRATOR');
        }

        return self::getSitePath() . '/administrator';
    }

    /**
     * Get libraries path.
     *
     * @return string Libraries path
     */
    public static function getLibrariesPath(): string
    {
        if (\defined('JPATH_LIBRARIES')) {
            return (string) \constant('JPATH_LIBRARIES');
        }

        return self::getSitePath() . '/libraries';
    }

    /**
     * Get templates path.
     *
     * @return string Templates path
     */
    public static function getTemplatesPath(): string
    {
        if (\defined('JPATH_TEMPLATES')) {
            return (string) \constant('JPATH_TEMPLATES');
        }

        return self::getSitePath() . '/templates';
    }

    /**
     * Get templates path (alias for JPATH_THEMES).
     *
     * @return string Templates path
     */
    public static function getThemesPath(): string
    {
        return self::getTemplatesPath();
    }

    /**
     * Get plugins path.
     *
     * @return string Plugins path
     */
    public static function getPluginsPath(): string
    {
        if (\defined('JPATH_PLUGINS')) {
            return (string) \constant('JPATH_PLUGINS');
        }

        return self::getSitePath() . '/plugins';
    }

    /**
     * Get modules path.
     *
     * @return string Modules path
     */
    public static function getModulesPath(): string
    {
        if (\defined('JPATH_MODULES')) {
            return (string) \constant('JPATH_MODULES');
        }

        return self::getSitePath() . '/modules';
    }

    /**
     * Get media path.
     *
     * @return string Media path
     */
    public static function getMediaPath(): string
    {
        if (\defined('JPATH_MEDIA')) {
            return (string) \constant('JPATH_MEDIA');
        }

        return self::getSitePath() . '/media';
    }

    /**
     * Get cache path.
     *
     * @return string Cache path
     */
    public static function getCachePath(): string
    {
        try {
            $app = Factory::getApplication();

            /** @var \Joomla\Registry\Registry|null $config */
            $config = method_exists($app, 'getConfig') ? $app->getConfig() : null;
            $cachePath = $config?->get('cache_path');

            if (!empty($cachePath)) {
                return (string) $cachePath;
            }
        } catch (\Throwable $e) {
            // Continue to next fallback
        }

        return self::getSitePath() . '/cache';
    }

    /**
     * Get log path.
     *
     * @return string Log path
     */
    public static function getLogPath(): string
    {
        try {
            $app = Factory::getApplication();

            /** @var \Joomla\Registry\Registry|null $config */
            $config = method_exists($app, 'getConfig') ? $app->getConfig() : null;
            $logPath = $config?->get('log_path');

            if (!empty($logPath)) {
                return (string) $logPath;
            }
        } catch (\Throwable $e) {
            // Continue to next fallback
        }

        return self::getSitePath() . '/logs';
    }
}
