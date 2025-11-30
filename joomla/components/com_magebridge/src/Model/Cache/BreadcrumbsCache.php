<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Cache;

defined('_JEXEC') or die;

final class BreadcrumbsCache extends Cache
{
    public function __construct(?string $request = null, ?int $cacheTime = null)
    {
        parent::__construct('breadcrumbs', $request, $cacheTime);
    }

    public function store(mixed $data): bool
    {
        return parent::store(serialize($data));
    }

    public function load(): mixed
    {
        $data = parent::load();

        if ($data === null) {
            return null;
        }

        return unserialize($data, ['allowed_classes' => true]);
    }
}
