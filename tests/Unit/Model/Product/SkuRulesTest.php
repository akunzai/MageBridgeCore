<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Model\Product;

use MageBridge\Component\MageBridge\Site\Model\Product\SkuRules;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SkuRules::class)]
final class SkuRulesTest extends TestCase
{
    #[DataProvider('matchProvider')]
    public function testMatch(string $sku, string $rule, bool $expected): void
    {
        $this->assertSame($expected, SkuRules::match($sku, $rule));
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function matchProvider(): array
    {
        return [
            'all upper' => ['WGT-001', 'ALL', true],
            'all lower' => ['WGT-001', 'all', true],
            'exact' => ['WGT-001', 'WGT-001', true],
            'trimmed exact' => [' WGT-001 ', 'WGT-001', true],
            'no match' => ['WGT-001', 'GAD-002', false],
            'comma list hit' => ['GAD-002', 'WGT-001,GAD-002,TOL-003', true],
            'comma list miss' => ['XXX', 'WGT-001,GAD-002', false],
            'suffix wildcard' => ['blue-tee', '%tee', true],
            'prefix wildcard' => ['WGT-001', 'WGT%', true],
            'suffix miss' => ['tee-blue', '%tee', false],
            'prefix miss' => ['X-WGT-001', 'WGT%', false],
        ];
    }
}
