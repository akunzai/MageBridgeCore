<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Magento\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Yireo_MageBridge_Helper_Event;

require_once __DIR__ . '/MageStubs.php';

$magebridgeEventHelper = dirname(__DIR__, 4) . '/magento/app/code/community/Yireo/MageBridge/Helper/Event.php';

if (!class_exists('Yireo_MageBridge_Helper_Event', false)) {
    require_once $magebridgeEventHelper;
}

#[CoversClass(Yireo_MageBridge_Helper_Event::class)]
final class EventHelperTest extends TestCase
{
    public function testCamelCaseEventNamePrefixesMage(): void
    {
        $this->assertSame(
            'mageCustomerSaveAfter',
            Yireo_MageBridge_Helper_Event::camelCaseEventName('customer_save_after')
        );
        $this->assertSame('mageCheckout', Yireo_MageBridge_Helper_Event::camelCaseEventName('checkout'));
    }

    public function testWithoutEmptyAssocDropsEmptyLikePhpEmpty(): void
    {
        $cleaned = Yireo_MageBridge_Helper_Event::withoutEmptyAssoc([
            'sku' => 'WGT-001',
            'qty' => 0,
            'name' => '',
            'type' => 'simple',
        ]);

        $this->assertSame(['sku' => 'WGT-001', 'type' => 'simple'], $cleaned);
    }
}
