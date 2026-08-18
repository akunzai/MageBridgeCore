<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Helper;

use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;

/**
 * Runs the real EncryptionHelper algorithm with injected config.
 */
final class TestableEncryptionHelper extends EncryptionHelper
{
    /** @var array<string, mixed> */
    public static array $settings = [];

    public static function reset(): void
    {
        self::$settings = [
            'encryption_key' => EncryptionVectors::KEY,
            'encryption' => 1,
            'protocol' => 'http',
        ];
    }

    protected static function loadSetting($element)
    {
        return self::$settings[$element] ?? null;
    }
}
