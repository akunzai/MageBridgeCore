<?php

declare(strict_types=1);

namespace MageBridge\Component\MageBridge\Site\Model\Cache;

defined('_JEXEC') or die;

use MageBridge\Component\MageBridge\Site\Model\Bridge\Block;

final class BlockCache extends Cache
{
    /**
     * @var string[]
     */
    private array $allowedBlocks = [
        'content',
    ];

    private string $block;

    public function __construct(string $block = '', ?string $request = null, ?int $cacheTime = null)
    {
        $this->block = $block;

        parent::__construct('block_' . $block, $request, $cacheTime);
    }

    public function validate(): bool
    {
        if (!parent::validate()) {
            return false;
        }

        if (!in_array($this->block, $this->allowedBlocks, true)) {
            return false;
        }

        return true;
    }

    public function store(mixed $data): bool
    {
        $decoded = Block::getInstance()->decode($data);
        $filtered = Block::getInstance()->filterHtml($decoded);

        return parent::store($filtered);
    }
}
