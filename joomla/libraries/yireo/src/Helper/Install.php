<?php

declare(strict_types=1);

namespace Yireo\Helper;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Yireo\Helper\PathHelper;

/**
 * Yireo Install Helper.
 */
class Install
{
    /**
     * @param array $files
     */
    public static function remove($files = [])
    {
        if (empty($files)) {
            $files = Helper::getData('obsolete_files');
        }

        if (!empty($files)) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    if (is_file($file)) {
                        File::delete($file);
                    }

                    if (is_dir($file)) {
                        Folder::delete($file);
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     *
     * @return bool
     */
    public static function installExtension($url, $label)
    {
        // System variables
        $app = Factory::getApplication();

        // Download the package-file
        $package_file = self::downloadPackage($url);

        // Simple check for the result
        if ($package_file == false) {
            throw new Exception(sprintf(Text::_('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_FILE_EMPTY'), $url));
        }

        // Check if the downloaded file exists
        $tmp_path = $app->get('tmp_path');
        $package_path = $tmp_path . '/' . $package_file;

        if (!is_file($package_path)) {
            throw new Exception(sprintf(Text::_('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_FILE_NOT_EXIST'), $package_path));
        }

        // Check if the file is readable
        if (!is_readable($package_path)) {
            throw new Exception(sprintf(Text::_('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_FILE_NOT_READABLE'), $package_path));
        }

        // Now we assume this is an archive, so let's unpack it
        $package = InstallerHelper::unpack($package_path);

        if ($package == false) {
            throw new Exception(sprintf(Text::_('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_NO_ARCHIVE', 'package')));
        }

        // Call the actual installer to install the package
        $installer = Installer::getInstance();

        if ($installer->install($package['dir']) == false) {
            throw new Exception(sprintf(Text::_('LIB_YIREO_HELPER_INSTALL_EXTENSION_FAIL'), $package['name']));
        }

        // Get the name of downloaded package
        if (!is_file($package['packagefile'])) {
            /** @var CMSApplication $app */
            $app = Factory::getApplication();
            $config = $app->getConfig();
            $package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
        }

        // Clean up the installation
        @InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
        $app->enqueueMessage(sprintf(Text::_('LIB_YIREO_HELPER_INSTALL_EXTENSION_SUCCESS', $label)), 'notice');

        // Clean the Joomla! plugins cache
        $options = ['defaultgroup' => 'com_plugins', 'cachebase' => PathHelper::getAdministratorPath() . '/cache'];
        $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
        $cache = $cacheControllerFactory->createCacheController('callback', $options);
        $cache->clean();

        return true;
    }

    /*
     * Download a specific package using the MageBridge Proxy (CURL-based)
     *
     * @param string $url
     * @param string|null $file
     *
     * @return string|false
     */
    public static function downloadPackage($url, $file = null)
    {
        // System variables
        /** @var CMSApplication $app */
        $app = Factory::getApplication();
        $config = $app->getConfig();

        // Use fopen() instead
        if (ini_get('allow_url_fopen') == 1) {
            return InstallerHelper::downloadPackage($url, $file);
        }

        // Set the target path if not given
        if (empty($file)) {
            $file = $config->get('tmp_path') . '/' . InstallerHelper::getFilenameFromURL($url);
        } else {
            $file = $config->get('tmp_path') . '/' . basename($file);
        }

        // Open the remote server socket for reading
        $ch = curl_init($url);

        /** @phpstan-ignore-next-line */
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FRESH_CONNECT => false,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_BUFFERSIZE => 8192,
            CURLOPT_SSLVERSION => 0,
        ]);

        $data = curl_exec($ch);
        curl_close($ch);

        if (empty($data)) {
            $app->enqueueMessage(Text::_('LIB_YIREO_HELPER_INSTALL_REMOTE_DOWNLOAD_FAILED') . ', ' . curl_error($ch), 'warning');
            return false;
        }

        // Write received data to file
        /** @phpstan-ignore-next-line */
        File::write($file, $data);

        // Return the name of the downloaded package
        return basename($file);
    }

    public static function hasLibraryInstalled($library)
    {
        if (is_dir(PathHelper::getSitePath() . '/libraries/' . $library)) {
            $query = 'SELECT `name` FROM `#__extensions` WHERE `type`="library" AND `element`="' . $library . '"';
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $db->setQuery($query);

            return (bool) $db->loadObject();
        }

        return false;
    }

    public static function hasPluginInstalled($plugin, $group)
    {
        if (file_exists(PathHelper::getSitePath() . '/plugins/' . $group . '/' . $plugin . '/' . $plugin . '.xml')) {
            $query = 'SELECT `name` FROM `#__extensions` WHERE `type`="plugin" AND `element`="' . $plugin . '" AND `folder`="' . $group . '"';
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $db->setQuery($query);

            return (bool) $db->loadObject();
        }

        return false;
    }

    public static function hasPluginEnabled($plugin, $group)
    {
        $query = 'SELECT `enabled` FROM `#__extensions` WHERE `type`="plugin" AND `element`="' . $plugin . '" AND `folder`="' . $group . '"';
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery($query);

        return (bool) $db->loadResult();
    }

    public static function enablePlugin($plugin, $group, $label)
    {
        if (self::hasPluginInstalled($plugin, $group) == false) {
            return false;
        } elseif (self::hasPluginEnabled($plugin, $group) == true) {
            return true;
        }

        $query = 'UPDATE `#__extensions` SET `enabled`="1" WHERE `type`="plugin" AND `element`="' . $plugin . '" AND `folder`="' . $group . '"';
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery($query);
        $app = Factory::getApplication();

        try {
            $db->execute();
            $app->enqueueMessage(Text::_('LIB_YIREO_HELPER_INSTALL_ENABLE_PLUGIN_SUCCESS', $label), 'notice');
        } catch (Exception $e) {
            $app->enqueueMessage(sprintf(Text::_('LIB_YIREO_HELPER_INSTALL_ENABLE_PLUGIN_FAIL', $label)), 'warning');
        }

        // Clean the Joomla! plugins cache
        $options = ['defaultgroup' => 'com_plugins', 'cachebase' => PathHelper::getAdministratorPath() . '/cache'];
        $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
        $cache = $cacheControllerFactory->createCacheController('callback', $options);
        $cache->clean();

        return true;
    }

    public static function autoInstallLibrary($library, $url, $label)
    {
        // If the library is already installed, exit
        if (self::hasLibraryInstalled($library)) {
            return true;
        }

        // Otherwise first, try to install the library
        if (self::installExtension($url, $label) == false) {
            Factory::getApplication()->enqueueMessage(sprintf(Text::_('LIB_YIREO_HELPER_INSTALL_MISSING', $label)), 'warning');
        }
    }

    public static function autoInstallEnablePlugin($plugin, $group, $url, $label)
    {
        // If the plugin is already installed, enable it
        if (self::hasPluginInstalled($plugin, $group)) {
            self::enablePlugin($plugin, $group, $label);

            // Otherwise first, try to install the plugin
        } else {
            if (self::installExtension($url, $label)) {
                self::enablePlugin($plugin, $group, $label);
            } else {
                Factory::getApplication()->enqueueMessage(sprintf(Text::_('LIB_YIREO_HELPER_INSTALL_MISSING', $label)), 'warning');
            }
        }
    }
}
