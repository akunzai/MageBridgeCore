<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Magento\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Yireo_MageBridge_Helper_JsonrpcPayload;

require_once __DIR__ . '/MageStubs.php';

$magebridgeJsonrpcPayload = dirname(__DIR__, 4) . '/magento/app/code/community/Yireo/MageBridge/Helper/JsonrpcPayload.php';

if (!class_exists('Yireo_MageBridge_Helper_JsonrpcPayload', false)) {
    require_once $magebridgeJsonrpcPayload;
}

#[CoversClass(Yireo_MageBridge_Helper_JsonrpcPayload::class)]
final class JsonrpcPayloadTest extends TestCase
{
    public function testStripMethodPrefixOnlyAtStart(): void
    {
        $this->assertSame('login', Yireo_MageBridge_Helper_JsonrpcPayload::stripMethodPrefix('magebridge.login'));
        $this->assertSame('login', Yireo_MageBridge_Helper_JsonrpcPayload::stripMethodPrefix('login'));
    }

    public function testCanMakeCallRequiresUrlAndAuth(): void
    {
        $this->assertTrue(Yireo_MageBridge_Helper_JsonrpcPayload::canMakeCall('https://www.example.com/jsonrpc', []));
        $this->assertFalse(Yireo_MageBridge_Helper_JsonrpcPayload::canMakeCall('', ['api_user' => 'x']));
        $this->assertFalse(Yireo_MageBridge_Helper_JsonrpcPayload::canMakeCall('https://www.example.com/jsonrpc', false));
    }

    public function testPostBodyAddsAuthAndMethodId(): void
    {
        $post = Yireo_MageBridge_Helper_JsonrpcPayload::postBody('login', ['foo' => 1], ['api_user' => 'bridge']);

        $this->assertSame('login', $post['method']);
        $this->assertSame(1, $post['params']['foo']);
        $this->assertSame(['api_user' => 'bridge'], $post['params']['api_auth']);
        $this->assertSame(md5('login'), $post['id']);
    }

    public function testReplyLooksLikeJsonRequiresLeadingBrace(): void
    {
        $this->assertTrue(Yireo_MageBridge_Helper_JsonrpcPayload::replyLooksLikeJson('{"result":1}'));
        $this->assertFalse(Yireo_MageBridge_Helper_JsonrpcPayload::replyLooksLikeJson(''));
        $this->assertFalse(Yireo_MageBridge_Helper_JsonrpcPayload::replyLooksLikeJson('<html>'));
    }

    public function testNormalizeParamsCoercesNonArray(): void
    {
        $this->assertSame(['a' => 1], Yireo_MageBridge_Helper_JsonrpcPayload::normalizeParams(['a' => 1]));
        $this->assertSame([], Yireo_MageBridge_Helper_JsonrpcPayload::normalizeParams(null));
        $this->assertSame([], Yireo_MageBridge_Helper_JsonrpcPayload::normalizeParams('x'));
    }
}
