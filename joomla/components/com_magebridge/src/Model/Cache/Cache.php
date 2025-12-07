<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Cache;

defined('_JEXEC') or die;

use Joomla\Filesystem\Folder;
use MageBridge\Component\MageBridge\Site\Helper\UrlHelper;
use MageBridge\Component\MageBridge\Site\Helper\PathHelper;
use MageBridge\Component\MageBridge\Site\Model\ConfigModel;
use RuntimeException;

class Cache
{
    private const DEFAULT_CACHE_TIME = 300;

    private string $request;

    private string $cacheFolder;

    private string $cacheName;

    private string $cacheFile;

    private int $cacheTime;

    /**
     * @var string[]
     */
    private array $denyPages = [
        '^checkout',
        '^customer',
        '^persistent',
        '^wishlist',
        '^contacts',
        '^paypal',
    ];

    public function __construct(string $name = '', ?string $request = null, ?int $cacheTime = null)
    {
        $this->request = $request !== null && $request !== ''
            ? $request
            : (string) UrlHelper::getRequest();

        $this->cacheName    = $name . '_' . md5($this->request);
        $this->cacheFolder  = PathHelper::getCachePath() . '/com_magebridge';
        $this->cacheFile    = $this->cacheFolder . '/' . $this->cacheName . '.php';

        $configuredCacheTime = ConfigModel::load('cache_time');
        $configuredCacheTime = is_numeric($configuredCacheTime)
            ? (int) $configuredCacheTime
            : self::DEFAULT_CACHE_TIME;

        $this->cacheTime = $cacheTime ?? $configuredCacheTime;
    }

    public function validate(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (!is_dir($this->cacheFolder)) {
            try {
                Folder::create($this->cacheFolder);
            } catch (RuntimeException $exception) {
                return false;
            }

            if (!is_dir($this->cacheFolder)) {
                return false;
            }
        }

        foreach ($this->denyPages as $deny) {
            $pattern = '/' . str_replace('/', '\/', $deny) . '/';

            if (preg_match($pattern, $this->request) === 1) {
                return false;
            }
        }

        return true;
    }

    public function load(): mixed
    {
        if (!$this->validate()) {
            return null;
        }

        if (!is_file($this->cacheFile)) {
            return null;
        }

        if (filemtime($this->cacheFile) < (time() - $this->cacheTime)) {
            return null;
        }

        $data = file_get_contents($this->cacheFile);

        return $data === false ? null : $data;
    }

    public function store(mixed $data): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $directory = dirname($this->cacheFile);

        if (!is_dir($directory)) {
            try {
                Folder::create($directory);
            } catch (RuntimeException $exception) {
                return false;
            }

            if (!is_dir($directory)) {
                return false;
            }
        }

        if (!is_writable($directory)) {
            return false;
        }

        if (!is_string($data) && !is_scalar($data)) {
            return false;
        }

        $bytes = file_put_contents($this->cacheFile, (string) $data);

        return $bytes !== false;
    }

    public function flush(): bool
    {
        if (!is_file($this->cacheFile)) {
            return false;
        }

        return @unlink($this->cacheFile);
    }

    public function isEnabled(): bool
    {
        return (bool) ConfigModel::load('enable_cache');
    }

}
