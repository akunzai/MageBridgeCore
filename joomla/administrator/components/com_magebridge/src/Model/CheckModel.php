<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Version;
use MageBridge\Component\MageBridge\Administrator\Helper\Update;
use MageBridge\Component\MageBridge\Administrator\Model\ConfigModel;
use MageBridge\Component\MageBridge\Administrator\Model\StoresModel;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Register;
use Yireo\Model\CommonModel;

class CheckModel extends CommonModel
{
    public const CHECK_OK      = 'ok';
    public const CHECK_WARNING = 'warning';
    public const CHECK_ERROR   = 'error';

    private array $checks = [];

    public function addResult(string $group, string $check, string $status, string $description = ''): void
    {
        if (empty($this->checks[$group])) {
            $this->checks[$group] = [];
        }

        $this->checks[$group][] = [
            'text'        => Text::_($check),
            'status'      => $status,
            'description' => $description,
        ];
    }

    public function getChecks(bool $installer = false): array
    {
        $this->doSystemChecks($installer);

        if (!$installer) {
            $this->doExtensionChecks();
            $this->doBridgeChecks();
            $this->doPluginChecks();
            $this->doConfigChecks();
        }

        return $this->checks;
    }

    public function doConfigChecks(): void
    {
        $group  = 'config';
        $config = ConfigModel::load();

        foreach ($config as $entry) {
            $result = ConfigModel::check($entry['name'], $entry['value']);

            if (!empty($result)) {
                $this->addResult($group, $entry['name'], self::CHECK_WARNING, $result);
            } else {
                $this->addResult($group, $entry['name'], self::CHECK_OK, $entry['description']);
            }
        }
    }

    public function doBridgeChecks(): void
    {
        $register = Register::getInstance();
        $bridge   = BridgeModel::getInstance();

        $versionId = $register->add('version');

        $bridge->build();
        $versionMagento = $register->getDataById($versionId);
        $versionJoomla  = Update::getComponentVersion();

        if (empty($versionMagento)) {
            $this->addResult('bridge', 'Bridge version', self::CHECK_WARNING, Text::_('COM_MAGEBRIDGE_CHECK_BRIDGE_NO_VERSION'));
        } else {
            $status = version_compare($versionMagento, $versionJoomla, '=') ? self::CHECK_OK : self::CHECK_ERROR;
            $this->addResult('bridge', 'Bridge version', $status, sprintf(Text::_('COM_MAGEBRIDGE_CHECK_BRIDGE_VERSION'), $versionMagento, $versionJoomla));
        }

        $status = (int) ConfigModel::load('modify_url') === 1 ? self::CHECK_OK : self::CHECK_WARNING;
        $this->addResult('bridge', 'Modify URLs', $status, Text::_('COM_MAGEBRIDGE_CHECK_BRIDGE_MODIFY_URL'));

        $status = (int) ConfigModel::load('disable_js_mootools') === 1 ? self::CHECK_OK : self::CHECK_WARNING;
        $this->addResult('bridge', 'Disable MooTools', $status, Text::_('COM_MAGEBRIDGE_CHECK_BRIDGE_DISABLE_MOOTOOLS'));

        $status = (int) ConfigModel::load('link_to_magento') === 0 ? self::CHECK_OK : self::CHECK_WARNING;
        $this->addResult('bridge', 'Link to Magento', $status, Text::_('COM_MAGEBRIDGE_CHECK_BRIDGE_LINK_TO_MAGENTO'));

        $status = $this->checkStoreRelations();
        $this->addResult('bridge', 'Store Relations', $status, Text::_('COM_MAGEBRIDGE_CHECK_BRIDGE_STORE_RELATIONS'));
    }

    private function getBytesFromValue(string $value): int
    {
        $value = trim($value);
        $last  = strtolower($value[strlen($value) - 1]);
        $result = (int) $value;

        switch ($last) {
            case 'g':
                $result *= 1024;
                // no break
            case 'm':
                $result *= 1024;
                // no break
            case 'k':
                $result *= 1024;
        }

        return $result;
    }

    public function doSystemChecks(bool $installer = false): void
    {
        $config         = ConfigModel::load();
        /** @var CMSApplication */
        $app = Factory::getApplication();
        $joomlaConfig   = $app->getConfig();
        $serverSoftware = $_SERVER['software'] ?? null;

        $status = version_compare(PHP_VERSION, '8.1.0', '>=') ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('compatibility', 'PHP version', $status, sprintf(Text::_('COM_MAGEBRIDGE_CHECK_PHP_VERSION'), '8.1.0'));

        $memoryLimit = $this->getBytesFromValue((string) ini_get('memory_limit'));
        $status      = ($memoryLimit >= (32 * 1024 * 1024) || (int) ini_get('memory_limit') === -1) ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('compatibility', 'PHP memory', $status, sprintf(Text::_('COM_MAGEBRIDGE_CHECK_PHP_MEMORY'), '32Mb', ini_get('memory_limit')));

        $jversion = new Version();
        $status   = version_compare($jversion->getShortVersion(), '3.0.0', '>=') ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('compatibility', 'Joomla! version', $status, sprintf(Text::_('COM_MAGEBRIDGE_CHECK_JOOMLA_VERSION'), '3.0.0'));

        $status = function_exists('simplexml_load_string') ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('compatibility', 'SimpleXML', $status, Text::_('COM_MAGEBRIDGE_CHECK_SIMPLEXML'));

        $status = in_array('ssl', stream_get_transports(), true) ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('compatibility', 'OpenSSL', $status, Text::_('COM_MAGEBRIDGE_CHECK_OPENSSL'));

        $status = function_exists('json_decode') ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('compatibility', 'JSON', $status, Text::_('COM_MAGEBRIDGE_CHECK_JSON'));

        $status = function_exists('curl_init') ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('compatibility', 'CURL', $status, Text::_('COM_MAGEBRIDGE_CHECK_CURL'));

        if (stristr((string) $serverSoftware, 'apache')) {
            if (function_exists('apache_get_modules')) {
                $modules = apache_get_modules();
                $status  = (is_array($modules) && in_array('mod_rewrite', $modules, true)) ? self::CHECK_OK : self::CHECK_WARNING; // @phpstan-ignore-line
                $this->addResult('compatibility', 'Apache mod_rewrite', $status, Text::_('COM_MAGEBRIDGE_CHECK_APACHE_REWRITE'));
            } else {
                $this->addResult('compatibility', 'Apache mod_rewrite', self::CHECK_WARNING, Text::_('COM_MAGEBRIDGE_CHECK_APACHE_REWRITE_UNKNOWN'));
            }
        }

        if (function_exists('apache_get_modules') === false && function_exists('is_writeable')) {
            $this->addResult('compatibility', 'File permissions', is_writable(JPATH_SITE) ? self::CHECK_OK : self::CHECK_WARNING, Text::_('COM_MAGEBRIDGE_CHECK_FILE_PERMISSIONS'));
        }
    }

    public function doExtensionChecks(): void
    {
        if (file_exists(JPATH_SITE . '/plugins/system/rokmoduleorder.php')) {
            $this->addResult('extension', 'RokModuleOrder', self::CHECK_ERROR, Text::_('COM_MAGEBRIDGE_CHECK_ROKMODULEORDER'));
        }

        if (file_exists(JPATH_SITE . '/plugins/system/rsform.php')) {
            $this->addResult('extension', 'RSForm', self::CHECK_ERROR, Text::_('COM_MAGEBRIDGE_CHECK_RSFORM'));
        }

        if (file_exists(JPATH_SITE . '/components/com_acesef/acesef.php')) {
            $this->addResult('extension', 'AceSEF', self::CHECK_ERROR, Text::_('COM_MAGEBRIDGE_CHECK_ACESEF'));
        }

        if (file_exists(JPATH_SITE . '/components/com_sh404sef/sh404sef.php')) {
            $this->addResult('extension', 'sh404SEF', self::CHECK_ERROR, Text::_('COM_MAGEBRIDGE_CHECK_SH404SEF'));
        }

        if (file_exists(JPATH_ADMINISTRATOR . '/components/com_sef/controller.php')) {
            $this->addResult('extension', 'JoomSEF', self::CHECK_WARNING, Text::_('COM_MAGEBRIDGE_CHECK_JOOMSEF'));
        }

        if (file_exists(JPATH_SITE . '/components/com_rsfirewall/rsfirewall.php')) {
            $this->addResult('extension', 'RSFirewall', self::CHECK_WARNING, Text::_('COM_MAGEBRIDGE_CHECK_RSFIREWALL'));
        }
    }

    public function doPluginChecks(): void
    {
        $group = 'plugins';

        $plugins = ['system', 'user', 'authentication', 'magebridge', 'magebridgeproduct', 'magebridgestore'];

        foreach ($plugins as $folder) {
            $plugin = PluginHelper::getPlugin($folder, 'magebridge');
            $status = !empty($plugin) ? self::CHECK_OK : self::CHECK_WARNING;
            $this->addResult($group, ucfirst($folder) . ' plugin', $status, Text::_('COM_MAGEBRIDGE_CHECK_PLUGIN_' . strtoupper($folder)));
        }
    }

    public function checkStoreRelations(): string
    {
        $model = new StoresModel();
        $rows = $model->getData();

        if (empty($rows)) {
            return self::CHECK_WARNING;
        }

        $relations = [];

        foreach ($rows as $row) {
            $lookup = $row['menuitem'] . '-' . $row['website'] . '-' . $row['group'] . '-' . $row['store'] . '-' . $row['customer_group'];

            if (in_array($lookup, $relations, true)) {
                return self::CHECK_ERROR;
            }

            $relations[] = $lookup;
        }

        return self::CHECK_OK;
    }
}
