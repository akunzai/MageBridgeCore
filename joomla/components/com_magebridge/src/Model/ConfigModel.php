<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use MageBridge\Component\MageBridge\Administrator\Table\Config;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Model\BridgeModel;
use MageBridge\Component\MageBridge\Site\Model\Config\Defaults;
use MageBridge\Component\MageBridge\Site\Model\Config\Value;
use Yireo\Helper\Helper;
use Yireo\Model\AbstractModel;
use RuntimeException;

use function sprintf;

final class ConfigModel extends AbstractModel
{
    /**
     * @var array<int, object>|null
     */
    protected ?array $data = null;

    /**
     * @var array<string, array{id: ?int, name: ?string, value: mixed, core: int, description: string}>
     */
    protected $config = [];

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $defaults = null;

    public function __construct()
    {
        $this->defaults = (new Defaults())->getDefaults();

        parent::__construct();
    }

    public static function getSingleton(): self
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * @param array<int, object> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return array<int, object>
     */
    public function getData(): array
    {
        if ($this->data === null) {
            $db = $this->getDatabase();
            $query = $db->getQuery(true);
            $query->select($db->quoteName(['id', 'name', 'value']))
                ->from($db->quoteName('#__magebridge_config', 'c'));

            $db->setQuery($query);
            $result = $db->loadObjectList();

            $this->data = is_array($result) ? $result : [];
        }

        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        return $this->defaults ?? [];
    }

    /**
     * @return array<string, array{id: ?int, name: ?string, value: mixed, core: int, description: string}>
     */
    private function loadDefaultConfig(): array
    {
        foreach ($this->getDefaults() as $name => $value) {
            $this->config[$name] = (new Value(['name' => $name, 'value' => $value]))->toArray();
        }

        return $this->config;
    }

    /**
     * @param array<int, object> $data
     *
     * @return array<string, array{id: ?int, name: ?string, value: mixed, core: int, description: string}>
     */
    private function pushDataIntoConfig(array $data): array
    {
        foreach ($this->config as $name => $configValue) {
            foreach ($data as $row) {
                if (!is_object($row)) {
                    continue;
                }

                if (!property_exists($row, 'name') || $row->name !== $configValue['name']) {
                    continue;
                }

                $row->isOriginal  = ($this->config[$name]['value'] ?? null) == ($row->value ?? null) ? 1 : 0;
                $row->description = $configValue['description'] ?? '';

                $this->config[$name] = (new Value((array) $row))->toArray();
                break;
            }
        }

        return $this->config;
    }

    /**
     * @return array<string, array{id: ?int, name: ?string, value: mixed, core: int, description: string}>
     */
    private function overrideConfig(): array
    {
        $this->config['method'] = (new Value(['value' => 'post']))->toArray();

        if (($this->config['update_format']['value'] ?? '') === '') {
            $component = ComponentHelper::getComponent('com_magebridge');
            $params    = Helper::toRegistry($component->getParams());
            $value     = $params->get('update_format', 'tar.gz');

            $this->config['update_format'] = (new Value(['value' => $value]))->toArray();
        }

        if ($this->input->getInt('widgets', 1) === 0) {
            $this->config['api_widgets'] = (new Value(['value' => 0]))->toArray();
        }

        if ($this->app->isClient('administrator')
            && $this->input->getCmd('option') === 'com_magebridge'
            && $this->input->getCmd('view') === 'root') {
            $this->config['disable_js_all']      = (new Value(['value' => 1]))->toArray();
            $this->config['disable_js_mootools'] = (new Value(['value' => 1]))->toArray();
        }

        $protocol = (string) ($this->config['protocol']['value'] ?? 'http');
        $port     = $protocol === 'http' ? 80 : 443;

        if (!empty($this->config['port']['value'] ?? null)) {
            $port = abs((int) $this->config['port']['value']);
        }

        if (!isset($this->config['port'])) {
            $this->config['port'] = (new Value(['value' => $port]))->toArray();
        }

        if (!isset($this->config['url'])) {
            $url  = '';
            $host = (string) ($this->config['host']['value'] ?? '');

            if ($host !== '') {
                $url = $protocol . '://' . $host;

                if (($protocol === 'http' && $port !== 80) || ($protocol === 'https' && $port !== 443)) {
                    $url .= ':' . $port;
                }

                $url .= '/';

                $basedir = (string) ($this->config['basedir']['value'] ?? '');

                if ($basedir !== '') {
                    $url .= $basedir . '/';
                }
            }

            $this->config['url'] = (new Value(['value' => $url]))->toArray();
        }

        return $this->config;
    }

    /**
     * @return array<string, array{id: ?int, name: ?string, value: mixed, core: int, description: string}>
     */
    public function getConfiguration(): array
    {
        if ($this->config === []) {
            $this->loadDefaultConfig();
            $this->pushDataIntoConfig($this->getData());
            $this->overrideConfig();
        }

        return $this->config;
    }

    public function setConfigValue(string $name, mixed $value): void
    {
        if (!isset($this->config[$name])) {
            return;
        }

        $this->config[$name]['value'] = $value;
    }

    public static function load(?string $element = null, mixed $overloadValue = null): mixed
    {
        static $config = null;

        $configModel = self::getSingleton();

        if ($config === null) {
            $config = $configModel->getConfiguration();
        }

        if ($element !== null && isset($config[$element]) && $overloadValue !== null) {
            $configModel->setConfigValue($element, $overloadValue);

            return $overloadValue;
        }

        if ($element !== null && isset($config[$element])) {
            return $config[$element]['value'] ?? null;
        }

        if ($element !== null) {
            return null;
        }

        return $config;
    }

    public static function check(string $element, mixed $value = null): ?string
    {
        if ($value === null || $value === '') {
            $value = self::load($element);
        }

        $nonempty = ['host', 'website', 'api_user', 'api_key'];

        if (self::allEmpty() === false && in_array($element, $nonempty, true) && empty($value)) {
            return sprintf(Text::_('Setting "%s" is empty - Please configure it below'), Text::_($element));
        }

        if ($element === 'host') {
            if ($value !== null && preg_match('/([^a-zA-Z0-9.\-_:]+)/', (string) $value) === 1) {
                return Text::_('Hostname contains illegal characters. Note that a hostname is not an URL, but only a fully qualified domainname.');
            }

            if ($value !== null && gethostbyname((string) $value) === $value && preg_match('/([0-9.]+)/', (string) $value) === 0) {
                return sprintf(Text::_('DNS lookup of hostname %s failed'), (string) $value);
            }

            if (self::load('api_widgets')) {
                $bridge = BridgeModel::getInstance();
                $data   = $bridge->build();

                if (empty($data)) {
                    $url = $bridge->getMagentoBridgeUrl();

                    return sprintf(Text::_('Unable to open a connection to <a href="%s" target="_new">%s</a>'), $url, $url);
                }
            }
        }

        if ($element === 'api_widgets' && (int) $value !== 1) {
            return Text::_('API widgets are disabled');
        }

        if ($element === 'offline' && (int) $value === 1) {
            return Text::_('Bridge is disabled through settings');
        }

        if ($element === 'website' && $value !== null && $value !== '') {
            if (!is_numeric($value)) {
                return sprintf(Text::_('Website ID needs to be a numeric value. Current value is "%s"'), (string) $value);
            }
        }

        if ($element !== 'basedir') {
            return null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (preg_match('/([a-zA-Z0-9.\-_]+)/', (string) $value) === 0) {
            return Text::_('Basedir contains illegal characters');
        }

        $root         = UrlHelper::getRootItem();
        $joomlaHost   = Uri::getInstance()->toString(['host']);
        $magentoHost  = (string) self::load('host');

        if (!empty($root) && !empty($root->route) && $root->route === $value && $joomlaHost === $magentoHost) {
            return Text::_('Magento basedir is same as MageBridge alias, which is not possible');
        }

        return null;
    }

    public static function allEmpty(): bool
    {
        static $allEmpty = null;

        if ($allEmpty === null) {
            $allEmpty = true;
            $config   = self::load();

            if (is_array($config)) {
                foreach ($config as $entry) {
                    if (($entry['core'] ?? 0) === 0) {
                        $allEmpty = false;
                        break;
                    }
                }
            }
        }

        return $allEmpty;
    }

    /**
     * @param array<string, mixed> $post
     */
    public function store(array $post): bool
    {
        if (isset($post['disable_js_custom'], $post['disable_js_all'])) {
            if ((int) $post['disable_js_all'] === 2 && empty($post['disable_js_custom'])) {
                $post['disable_js_all'] = 0;
            }

            if ((int) $post['disable_js_all'] === 3 && empty($post['disable_js_custom'])) {
                $post['disable_js_all'] = 1;
            }
        }

        if (isset($post['disable_css_mage']) && is_array($post['disable_css_mage'])) {
            if (empty($post['disable_css_mage'][0])) {
                array_shift($post['disable_css_mage']);
            }

            $post['disable_css_mage'] = empty($post['disable_css_mage'])
                ? ''
                : implode(',', $post['disable_css_mage']);
        }

        if (isset($post['disable_js_mage']) && is_array($post['disable_js_mage'])) {
            if (empty($post['disable_js_mage'][0])) {
                array_shift($post['disable_js_mage']);
            }

            $post['disable_js_mage'] = empty($post['disable_js_mage'])
                ? ''
                : implode(',', $post['disable_js_mage']);
        }

        if (!empty($post['basedir'])) {
            $post['basedir'] = preg_replace('/^\//', '', (string) $post['basedir']);
            $post['basedir'] = preg_replace('/\/$/', '', (string) $post['basedir']);
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__magebridge_urls'))
            ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $post['load_urls'] = empty($rows) ? 0 : 1;

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__magebridge_stores'))
            ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $post['load_stores'] = empty($rows) ? 0 : 1;

        $config = self::load();

        if (is_array($config)) {
            foreach ($config as $name => &$entry) {
                if (isset($post[$name]) && isset($entry['value'])) {
                    $entry['value'] = $post[$name];
                }
            }
            unset($entry);
        }

        $detectValues   = ['host', 'port', 'basedir', 'api_user', 'api_password'];
        $changeDetected = false;

        foreach ($detectValues as $key) {
            if (isset($post[$key], $config[$key]['value']) && $post[$key] != $config[$key]['value']) {
                $changeDetected = true;
            }
        }

        if ($changeDetected) {
            $cacheControllerFactory = Factory::getContainer()->get(CacheControllerFactoryInterface::class);
            $cache = $cacheControllerFactory->createCacheController('output', ['defaultgroup' => 'com_magebridge.admin']);
            $cache->clean();
        }

        foreach ($config as $name => $data) {
            if (!isset($data['name']) || $data['name'] === '' || $data['value'] === null) {
                continue;
            }

            $database = Factory::getContainer()->get(DatabaseInterface::class);
            $table    = new Config($database);

            if (!$table->bind($data)) {
                throw new RuntimeException('Unable to bind configuration to component');
            }

            if (!$table->store()) {
                throw new RuntimeException('Unable to store configuration to component');
            }
        }

        return true;
    }

    public function saveValue(string $name, mixed $value): bool
    {
        $data = [
            'name'  => $name,
            'value' => $value,
        ];

        $config = self::load();

        if (isset($config[$name]['id'])) {
            $data['id'] = $config[$name]['id'];
        }

        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $table    = new Config($database);

        if (!$table->bind($data)) {
            throw new RuntimeException('Unable to bind configuration to component');
        }

        if (!$table->store()) {
            throw new RuntimeException('Unable to store configuration to component');
        }

        return true;
    }
}
