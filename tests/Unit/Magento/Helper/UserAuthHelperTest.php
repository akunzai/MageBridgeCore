<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Magento\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Yireo_MageBridge_Helper_UserAuth;

require_once __DIR__ . '/MageStubs.php';

$magebridgeUserAuthHelper = dirname(__DIR__, 4) . '/magento/app/code/community/Yireo/MageBridge/Helper/UserAuth.php';

if (!class_exists('Yireo_MageBridge_Helper_UserAuth', false)) {
    require_once $magebridgeUserAuthHelper;
}

#[CoversClass(Yireo_MageBridge_Helper_UserAuth::class)]
final class UserAuthHelperTest extends TestCase
{
    public function testIsAdminApplicationIsStrictAdmin(): void
    {
        $this->assertTrue(Yireo_MageBridge_Helper_UserAuth::isAdminApplication('admin'));
        $this->assertFalse(Yireo_MageBridge_Helper_UserAuth::isAdminApplication('site'));
        $this->assertFalse(Yireo_MageBridge_Helper_UserAuth::isAdminApplication('frontend'));
        $this->assertFalse(Yireo_MageBridge_Helper_UserAuth::isAdminApplication(null));
    }

    public function testShouldTryJoomlaAuthOnlyAfterMagentoFails(): void
    {
        $this->assertTrue(Yireo_MageBridge_Helper_UserAuth::shouldTryJoomlaAuth(false, true));
        $this->assertFalse(Yireo_MageBridge_Helper_UserAuth::shouldTryJoomlaAuth(true, true));
        $this->assertFalse(Yireo_MageBridge_Helper_UserAuth::shouldTryJoomlaAuth(false, false));
    }

    public function testJoomlaAuthSucceededRequiresNonEmptyArray(): void
    {
        $this->assertTrue(Yireo_MageBridge_Helper_UserAuth::joomlaAuthSucceeded(['email' => 'a@b.c']));
        $this->assertFalse(Yireo_MageBridge_Helper_UserAuth::joomlaAuthSucceeded([]));
        $this->assertFalse(Yireo_MageBridge_Helper_UserAuth::joomlaAuthSucceeded(false));
        $this->assertFalse(Yireo_MageBridge_Helper_UserAuth::joomlaAuthSucceeded(null));
    }

    public function testCustomerEmailForResultPrefersApiEmail(): void
    {
        $this->assertSame(
            'shopper@example.com',
            Yireo_MageBridge_Helper_UserAuth::customerEmailForResult(['email' => 'shopper@example.com'], 'user')
        );
        $this->assertSame(
            'user',
            Yireo_MageBridge_Helper_UserAuth::customerEmailForResult(['name' => 'Shopper'], 'user')
        );
    }

    public function testShouldCreateCustomerMatchesBangGreaterThanPrecedence(): void
    {
        $this->assertTrue(Yireo_MageBridge_Helper_UserAuth::shouldCreateCustomer(0));
        $this->assertTrue(Yireo_MageBridge_Helper_UserAuth::shouldCreateCustomer(null));
        $this->assertFalse(Yireo_MageBridge_Helper_UserAuth::shouldCreateCustomer(5));
    }
}
