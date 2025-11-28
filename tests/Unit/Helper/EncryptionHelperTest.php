<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Helper;

use MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(EncryptionHelper::class)]
final class EncryptionHelperTest extends TestCase
{
    public function testBase64EncodeReturnsUrlSafeString(): void
    {
        $input = 'Hello World!';
        $encoded = EncryptionHelper::base64_encode($input);

        // Verify URL-safe characters are used
        $this->assertStringNotContainsString('+', $encoded);
        $this->assertStringNotContainsString('/', $encoded);
        $this->assertStringNotContainsString('=', $encoded);

        // Verify it contains the replacement characters or is valid
        $this->assertIsString($encoded);
        $this->assertNotEmpty($encoded);
    }

    public function testBase64DecodeReturnsOriginalString(): void
    {
        $original = 'Hello World!';
        $encoded = EncryptionHelper::base64_encode($original);
        $decoded = EncryptionHelper::base64_decode($encoded);

        $this->assertSame($original, $decoded);
    }

    public function testBase64DecodeWithNullReturnsFalse(): void
    {
        $result = EncryptionHelper::base64_decode(null);
        $this->assertFalse($result);
    }

    public function testBase64DecodeWithNonStringReturnsFalse(): void
    {
        // @phpstan-ignore argument.type
        $result = EncryptionHelper::base64_decode(123);
        $this->assertFalse($result);
    }

    #[DataProvider('base64RoundTripProvider')]
    public function testBase64RoundTrip(string $input): void
    {
        $encoded = EncryptionHelper::base64_encode($input);
        $decoded = EncryptionHelper::base64_decode($encoded);

        $this->assertSame($input, $decoded);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function base64RoundTripProvider(): array
    {
        return [
            'simple string' => ['Hello World'],
            'empty string' => [''],
            'special characters' => ['!@#$%^&*()_+-=[]{}|;:\'",.<>?/`~'],
            'unicode characters' => ['你好世界 こんにちは'],
            'binary-like data' => ["\x00\x01\x02\x03\xff\xfe"],
            'long string' => [str_repeat('a', 1000)],
            'json data' => ['{"key":"value","number":123}'],
            'url with parameters' => ['https://example.com/path?param=value&other=test'],
        ];
    }

    public function testBase64EncodeWithEmptyString(): void
    {
        $encoded = EncryptionHelper::base64_encode('');
        $this->assertSame('', $encoded);
    }

    public function testBase64DecodeWithEmptyString(): void
    {
        $decoded = EncryptionHelper::base64_decode('');
        $this->assertSame('', $decoded);
    }

    public function testBase64EncodingIsReversible(): void
    {
        // Test that the custom encoding is fully reversible
        $testCases = [
            'test+with/special=chars',
            base64_encode('binary data'),
            'normal text',
        ];

        foreach ($testCases as $input) {
            $encoded = EncryptionHelper::base64_encode($input);
            $decoded = EncryptionHelper::base64_decode($encoded);
            $this->assertSame($input, $decoded, "Failed for input: {$input}");
        }
    }
}
