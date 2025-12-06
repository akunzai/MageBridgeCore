<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Administrator\Model;

use Exception;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use MageBridge\Component\MageBridge\Administrator\Table\Config;
use MageBridge\Component\MageBridge\Site\Model\Config\Defaults as SiteConfigDefaults;
use MageBridge\Component\MageBridge\Site\Model\Config\Value as SiteConfigValue;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use Joomla\CMS\Form\Form;
use Yireo\Helper\Helper;
use Yireo\Model\CommonModel;

defined('_JEXEC') or die;

/**
 * Bridge configuration class.
 */
class ConfigModel extends CommonModel
{
    /**
     * MageBridge configuration values.
     *
     * @var array<string, mixed>
     */
    protected array $bridgeConfig = [];

    /**
     * Array of default values.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $defaults = null;

    /**
     * Constructor.
     *
     * @return array
     */
    public function __construct()
    {
        $this->defaults = (new SiteConfigDefaults())->getDefaults();
        $this->option = 'com_magebridge';

        parent::__construct();

        $this->setConfig('form_name', 'config');
    }

    /**
     * Method to fetch the data.
     *
     * @return ConfigModel
     */
    public static function getSingleton()
    {
        static $instance;

        if ($instance === null) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Method to set data.
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Method to get data.
     *
     * @return array
     */
    public function getData()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->data)) {
            $db = $this->getDatabase();
            $query = $db->getQuery(true);
            $query->select($db->quoteName(['id', 'name', 'value']));
            $query->from($db->quoteName('#__magebridge_config', 'c'));

            $db->setQuery($query);
            $rows = $db->loadObjectList();

            // Remove duplicates: keep only the first occurrence of each name (lowest ID)
            $seen = [];
            $this->data = [];
            foreach ($rows as $row) {
                if (!isset($seen[$row->name])) {
                    $seen[$row->name] = true;
                    $this->data[] = $row;
                }
            }
        }

        return $this->data;
    }

    /**
     * Get the form with properly formatted data for binding.
     *
     * ConfigModel stores data as array of objects with 'name' and 'value' properties,
     * but the form expects data in ['config' => ['field_name' => 'value']] format.
     */
    public function getForm(array|object|null $data = null, bool $loadData = true): Form|false
    {
        $form = $this->loadForm();

        if (!$form instanceof Form) {
            return false;
        }

        if ($loadData) {
            // Get raw data from database
            $rawData = $this->getData();

            // Convert array of objects to key-value pairs
            $configData = [];
            if (!empty($rawData)) {
                foreach ($rawData as $row) {
                    if (isset($row->name, $row->value)) {
                        $configData[$row->name] = $row->value;
                    }
                }
            }

            // Bind data in the format expected by the form (fields are under 'config' group)
            $form->bind(['config' => $configData]);
        }

        return $form;
    }

    /**
     * Method to get the defaults.
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @return array
     */
    private function loadDefaultConfig()
    {
        foreach ($this->getDefaults() as $name => $value) {
            $this->bridgeConfig[$name] = (new SiteConfigValue(['name' => $name, 'value' => $value]))->toArray();
        }

        return $this->bridgeConfig;
    }

    /**
     * @return array
     */
    private function pushDataIntoConfig($data)
    {
        foreach ($this->bridgeConfig as $name => $c) {
            if (!empty($data)) {
                foreach ($data as $d) {
                    if ($d->name == $c['name']) {
                        $d->isOriginal  = ($this->bridgeConfig[$name]['value'] == $d->value) ? 1 : 0;
                        $d->description = $c['description'];

                        $this->bridgeConfig[$name] = (new SiteConfigValue((array) $d))->toArray();
                        break;
                    }
                }
            }
        }

        return $this->bridgeConfig;
    }

    private function overrideConfig()
    {
        $this->bridgeConfig['method'] = (new SiteConfigValue(['value' => 'post']))->toArray();

        // Determine the right update format
        if ($this->bridgeConfig['update_format']['value'] == '') {
            $component = ComponentHelper::getComponent('com_magebridge');
            $params    = Helper::toRegistry($component->getParams());
            $value     = $params->get('update_format', 'tar.gz');

            $this->bridgeConfig['update_format'] = (new SiteConfigValue(['value' => $value]))->toArray();
        }

        // Disable widgets if needed
        if ($this->input->getInt('widgets', 1) == 0) {
            $this->bridgeConfig['api_widgets'] = (new SiteConfigValue(['value' => 0]))->toArray();
        }

        // Overload a certain values when the Magento Admin Panel needs to be loaded
        if ($this->app->isClient('administrator') && $this->input->getCmd('option') == 'com_magebridge' && $this->input->getCmd('view') == 'root') {
            //$this->bridgeConfig['debug'] = (new SiteConfigValue(['value' => 0]))->toArray();
            $this->bridgeConfig['disable_js_all'] = (new SiteConfigValue(['value' => 1]))->toArray();
            $this->bridgeConfig['disable_js_mootools'] = (new SiteConfigValue(['value' => 1]))->toArray();
        }
        $protocol = $this->bridgeConfig['protocol']['value']; // http or https
        $port = $protocol == 'http' ? 80 : 443;
        if (isset($this->bridgeConfig['port']) && !empty($this->bridgeConfig['port']['value'])) {
            $port = abs(intval($this->bridgeConfig['port']['value']));
        }

        // Return the port-number
        if (!isset($this->bridgeConfig['port'])) {
            $this->bridgeConfig['port'] = (new SiteConfigValue(['value' => $port]))->toArray();
        }

        // Return the URL
        if (!isset($this->bridgeConfig['url'])) {
            $url = '';
            $host = $this->bridgeConfig['host']['value'];
            if (!empty($host)) {
                $url = $protocol . '://' . $host;
                if ($protocol == 'http' && $port != 80 || $protocol == 'https' && $port != 443) {
                    $url .= ':' . $port;
                }
                $url = $url . '/';
                $basedir = $this->bridgeConfig['basedir']['value'];
                if (!empty($basedir)) {
                    $url .= $basedir . '/';
                }
            }

            $this->bridgeConfig['url'] = (new SiteConfigValue(['value' => $url]))->toArray();
        }

        return $this->bridgeConfig;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        if (empty($this->bridgeConfig)) {
            $this->loadDefaultConfig();
            $this->pushDataIntoConfig($this->getData());
            $this->overrideConfig();
        }

        return $this->bridgeConfig;
    }

    public function setConfigValue($name, $value)
    {
        $this->bridgeConfig[$name]['value'] = $value;
    }

    /**
     * Static method to get data.
     *
     * @param string $element
     * @param mixed $overloadValue
     *
     * @return mixed
     */
    public static function load($element = null, $overloadValue = null)
    {
        static $config = null;
        $configModel = self::getSingleton();

        if (empty($config)) {
            $config = $configModel->getConfiguration();
        }

        // Allow overriding values
        if (!empty($element) && isset($config[$element]) && $overloadValue !== null) {
            $configModel->setConfigValue($element, $overloadValue);

            return $overloadValue;
        }

        // Return any other element
        if ($element != null && isset($config[$element])) {
            if (!isset($config[$element]['value'])) {
                //print_r($config[$element]);
            }

            return $config[$element]['value'];
        }

        // Return no value
        if (!empty($element)) {
            return null;
        }

        // Return the configuration itself
        return $config;
    }

    /**
     * Method to check a specific configuration-element.
     *
     * @param string $element
     * @param string $value
     *
     * @return string|null
     */
    public static function check($element, $value = null)
    {
        // Reset an empty value to its original value
        if (empty($value)) {
            $value = self::load($element);
        }

        // Check for settings that should not be kept empty
        $nonempty = ['host', 'website', 'api_user', 'api_key'];
        if (self::allEmpty() == false && in_array($element, $nonempty) && empty($value)) {
            return sprintf('Setting "%s" is empty - Please configure it below', Text::_($element));
        }

        // Check host
        if ($element == 'host') {
            if (preg_match('/([^a-zA-Z0-9\.\-\_\:]+)/', $value) == true) {
                return Text::_('Hostname contains illegal characters. Note that a hostname is not an URL, but only a fully qualified domainname.');
            }

            if (gethostbyname($value) == $value && !preg_match('/([0-9\.]+)/', $value)) {
                return sprintf('DNS lookup of hostname %s failed', $value);
            }

            if (self::load('api_widgets') == true) {
                $bridge = BridgeModel::getInstance();
                $data   = $bridge->build();

                if (empty($data)) {
                    $url = $bridge->getMagentoBridgeUrl();

                    return sprintf('Unable to open a connection to <a href="%s" target="_new">%s</a>', $url, $url);
                }
            }
        }

        // Check API widgets
        if ($element == 'api_widgets' && $value != 1) {
            return Text::_('API widgets are disabled');
        }

        // Check offline
        if ($element == 'offline' && $value == 1) {
            return Text::_('Bridge is disabled through settings');
        }

        // Check website
        if ($element == 'website' && !empty($value)) {
            if (is_numeric($value) == false) {
                return sprintf('Website ID needs to be a numeric value. Current value is "%s"', $value);
            }
        }

        // Check basedir
        if ($element !== 'basedir') {
            return null;
        }

        if (empty($value)) {
            return null;
        }

        if (preg_match('/([a-zA-Z0-9\.\-\_]+)/', $value) == false) {
            return Text::_('Basedir contains illegal characters');
        }

        $root         = UrlHelper::getRootItem();
        $joomla_host  = Uri::getInstance()
            ->toString(['host']);
        $magento_host = self::load('host');

        // Check whether the Magento basedir conflicts with the MageBridge alias
        if (!empty($root) && !empty($root->route) && $root->route == $value && $joomla_host == $magento_host) {
            return Text::_('Magento basedir is same as MageBridge alias, which is not possible');
        }

        return null;
    }

    /**
     * Helper method to detect whether the whole configuration is empty.
     *
     * @return bool
     */
    public static function allEmpty()
    {
        static $allEmpty = null;

        if (empty($allEmpty)) {
            $allEmpty = true;
            $config   = self::load();
            foreach ($config as $c) {
                if ($c['core'] == 0) {
                    $allEmpty = false;
                    break;
                }
            }
        }

        return $allEmpty;
    }

    /**
     * Method to store the configuration in the database.
     *
     * @param array $post
     *
     * @throws Exception
     *
     * @return bool
     */
    public function store($post)
    {
        // If the custom list is empty, set another value
        if (isset($post['disable_js_custom']) && isset($post['disable_js_all'])) {
            if ($post['disable_js_all'] == 2 && empty($post['disable_js_custom'])) {
                $post['disable_js_all'] = 0;
            }

            if ($post['disable_js_all'] == 3 && empty($post['disable_js_custom'])) {
                $post['disable_js_all'] = 1;
            }
        }

        // Convert "disable_css_mage" array into comma-separated string
        if (isset($post['disable_css_mage']) && is_array($post['disable_css_mage'])) {
            if (empty($post['disable_css_mage'][0])) {
                array_shift($post['disable_css_mage']);
            }

            if (empty($post['disable_css_mage'])) {
                $post['disable_css_mage'] = '';
            } else {
                $post['disable_css_mage'] = implode(',', $post['disable_css_mage']);
            }
        }

        // Convert "disable_js_mage" array into comma-separated string
        if (isset($post['disable_js_mage']) && is_array($post['disable_js_mage'])) {
            if (empty($post['disable_js_mage'][0])) {
                array_shift($post['disable_js_mage']);
            }

            if (empty($post['disable_js_mage'])) {
                $post['disable_js_mage'] = '';
            } else {
                $post['disable_js_mage'] = implode(',', $post['disable_js_mage']);
            }
        }

        // Clean the basedir
        if (!empty($post['basedir'])) {
            $post['basedir'] = preg_replace('/^\//', '', $post['basedir']);
            $post['basedir'] = preg_replace('/\/$/', '', $post['basedir']);
        }

        // Check whether the URL-table contains entries
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__magebridge_urls'));
        $query->where($db->quoteName('published') . ' = 1');
        $rows = $db->loadObjectList();

        if (!empty($rows)) {
            $post['load_urls'] = 1;
        } else {
            $post['load_urls'] = 0;
        }

        // Check whether the stores-table contains entries
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__magebridge_stores'));
        $query->where($db->quoteName('published') . ' = 1');
        $rows = $db->loadObjectList();

        if (!empty($rows)) {
            $post['load_stores'] = 1;
        } else {
            $post['load_stores'] = 0;
        }

        // Load the existing configuration
        $config = self::load();

        // Overload each existing value with the posted value (if it exists)
        foreach ($config as $name => $c) {
            if (isset($post[$name]) && isset($config[$name])) {
                $config[$name]['value'] = $post[$name];
            }
        }

        // Detect changes in API-settings and if so, dump and clean the cache
        $detect_values  = ['host', 'port', 'basedir', 'api_user', 'api_password'];
        $changeDetected = false;

        foreach ($detect_values as $d) {
            if (isset($post[$d]) && isset($config[$d]) && $post[$d] != $config[$d]) {
                $changeDetected = true;
            }
        }

        // Clean the cache if changes are detected
        if ($changeDetected) {
            $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
            $cache = $cacheControllerFactory->createCacheController('output', ['defaultgroup' => 'com_magebridge.admin']);
            $cache->clean();
        }

        // Store the values row-by-row
        foreach ($config as $name => $data) {
            if (!isset($data['name']) || empty($data['name']) || is_null($data['value'])) {
                continue;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);

            // Delete all existing records with this name to prevent duplicates
            $query = $database->getQuery(true);
            $query->delete($database->quoteName('#__magebridge_config'));
            $query->where($database->quoteName('name') . ' = ' . $database->quote($data['name']));
            $database->setQuery($query);
            $database->execute();

            // Insert new record (without id to force INSERT)
            $table = new Config($database);
            $dataWithoutId = $data;
            unset($dataWithoutId['id']); // Remove id to ensure INSERT

            if (!$table->bind($dataWithoutId)) {
                throw new Exception('Unable to bind configuration to component');
            }

            if (!$table->store()) {
                throw new Exception('Unable to store configuration to component');
            }
        }

        return true;
    }

    /**
     * Method to store a single value in the database.
     *
     * @param string $name
     * @param mixed $value
     *
     * @throws Exception
     *
     * @return bool
     */
    public function saveValue($name, $value)
    {
        $data = [
            'name'  => $name,
            'value' => $value,
        ];

        $config = self::load();

        if (isset($config[$name])) {
            $data['id'] = $config[$name]['id'];
        }

        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $table    = new Config($database);

        if (!$table->bind($data)) {
            throw new Exception('Unable to bind configuration to component');
        }

        if (!$table->store()) {
            throw new Exception('Unable to store configuration to component');
        }

        return true;
    }
}
