<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model;

defined('_JEXEC') or die;

use MageBridge\Component\MageBridge\Site\Model\Bridge\Meta;
use MageBridge\Component\MageBridge\Site\Model\Cache\BlockCache;
use MageBridge\Component\MageBridge\Site\Model\Cache\BreadcrumbsCache;
use MageBridge\Component\MageBridge\Site\Model\Cache\HeadersCache;
use MageBridge\Component\MageBridge\Site\Model\DebugModel;
use MageBridge\Component\MageBridge\Site\Helper\RegisterHelper;

class Register
{
    public const MAGEBRIDGE_SEGMENT_STATUS_SYNCED = 1;

    private static ?self $instance = null;

    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    private function __construct()
    {
        // Don't call init() here - it will be called after instance is set
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            // Set instance BEFORE calling init() to prevent recursion
            self::$instance = new self();
            self::$instance->init();
        }

        return self::$instance;
    }

    public function init(): void
    {
        RegisterHelper::preload();
    }

    public function add(?string $type = null, ?string $name = null, $arguments = null): ?string
    {
        if (in_array($type, ['filter', 'block', 'api', 'widget'], true)) {
            $id = md5((string) $type . (string) $name . serialize($arguments));
        } else {
            $id = $type;
        }

        $this->data[$id] = [
            'type'      => $type,
            'name'      => $name,
            'arguments' => $arguments,
        ];

        return $id;
    }

    public function getById(?string $id = null)
    {
        if ($id === null) {
            return false;
        }

        return $this->data[$id] ?? false;
    }

    public function getDataById(?string $id = null)
    {
        $segment = $this->getById($id);

        return $segment['data'] ?? null;
    }

    public function get(?string $type = null, ?string $name = null, $arguments = null, ?string $id = null)
    {
        if ($id !== null) {
            DebugModel::getInstance()
                ->warning('Use of fourth argument in method MageBridgeModelRegister::get() is deprecated');
        }

        if (in_array($type, ['filter', 'block', 'api', 'widget'], true)) {
            $id = md5((string) $type . (string) $name . serialize($arguments));
        } else {
            $id = null;
        }

        foreach ($this->data as $index => $segment) {
            $matchesId   = $id !== null && $index === $id;
            $matchesType = $id === null && ($segment['type'] ?? null) === $type;
            $matchesName = $matchesType && ($segment['name'] ?? null) === $name;

            if ($matchesId || ($matchesName) || ($matchesType && $name === null)) {
                return $segment;
            }
        }

        return null;
    }

    public function getData(?string $type = null, ?string $name = null, $arguments = null, ?string $id = null)
    {
        if ($id !== null) {
            DebugModel::getInstance()
                ->warning('Use of fourth argument in method MageBridgeModelRegister::getData() is deprecated');
        }

        $segment = $this->get($type, $name, $arguments, $id);

        return $segment['data'] ?? null;
    }

    public function remove(string $type, string $name): bool
    {
        foreach ($this->data as $index => $segment) {
            if (($segment['type'] ?? null) === $type && ($segment['name'] ?? null) === $name) {
                unset($this->data[$index]);

                return true;
            }
        }

        return false;
    }

    public function clean(): self
    {
        $this->data = [];

        return $this;
    }

    public function getRegister(): array
    {
        return $this->data;
    }

    public function getPendingRegister(): array
    {
        $pending = [];

        foreach ($this->data as $id => $segment) {
            if (($segment['status'] ?? null) === self::MAGEBRIDGE_SEGMENT_STATUS_SYNCED) {
                continue;
            }

            if (!empty($segment['data'])) {
                continue;
            }

            $pending[$id] = $segment;
        }

        if (!empty($pending) && !isset($pending['meta'])) {
            $pending['meta'] = [
                'type'      => 'meta',
                'name'      => null,
                'arguments' => Meta::getInstance()->getRequestData(),
            ];
        }

        return $pending;
    }

    public function merge(array $data): void
    {
        if (empty($this->data)) {
            $this->data = $data;

            return;
        }

        DebugModel::getInstance()
            ->notice('Merging register (' . count($data) . ' segments)');

        foreach ($data as $id => $segment) {
            $segment['status'] = self::MAGEBRIDGE_SEGMENT_STATUS_SYNCED;
            $this->data[$id]   = $segment;

            if (empty($segment['data']) || empty($segment['type'])) {
                continue;
            }

            $cache = null;

            switch ($segment['type']) {
                case 'block':
                    if (
                        isset($segment['meta']['allow_caching'])
                        && (int) $segment['meta']['allow_caching'] === 1
                        && isset($segment['meta']['cache_lifetime'])
                        && (int) $segment['meta']['cache_lifetime'] > 0
                    ) {
                        $cache = new BlockCache($segment['name']);
                    }
                    break;
                case 'headers':
                    $cache = new HeadersCache();
                    break;
                case 'breadcrumbs':
                    $cache = new BreadcrumbsCache();
                    break;
            }

            if ($cache !== null) {
                $cache->store($segment['data']);
            }
        }
    }

    public function loadCache(): void
    {
        foreach ($this->data as $index => $segment) {
            if (!isset($segment['type'])) {
                continue;
            }

            $cache = null;

            switch ($segment['type']) {
                case 'block':
                    if (!empty($segment['name'])) {
                        $cache = new BlockCache($segment['name']);
                    }
                    break;
                case 'headers':
                    $cache = new HeadersCache();
                    break;
                case 'breadcrumbs':
                    $cache = new BreadcrumbsCache();
                    break;
            }

            if ($cache !== null && $cache->validate()) {
                $segmentData = $cache->load();

                if (!empty($segmentData)) {
                    $this->data[$index]['data']  = $segmentData;
                    $this->data[$index]['cache'] = 1;
                }
            }
        }
    }

    public function __toString(): string
    {
        return var_export($this->data, true);
    }
}
