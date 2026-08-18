<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Magento\Helper;

use MageBridge\Tests\Unit\Helper\EncryptionVectors;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo_MageBridge_Helper_Encryption;

require_once __DIR__ . '/MageStubs.php';

#[CoversClass(Yireo_MageBridge_Helper_Encryption::class)]
final class EncryptionHelperTest extends TestCase
{
    private Yireo_MageBridge_Helper_Encryption $helper;

    protected function setUp(): void
    {
        parent::setUp();
        \Mage::reset();
        \Mage::$storeConfig['magebridge/joomla/encryption_key'] = EncryptionVectors::KEY;
        \Mage::$storeConfig['magebridge/joomla/encryption'] = 1;
        \Mage::$protocol = 'http';
        $this->helper = new Yireo_MageBridge_Helper_Encryption();
    }

    protected function tearDown(): void
    {
        \Mage::reset();
        parent::tearDown();
    }

    public function testBase64EncodeReturnsUrlSafeString(): void
    {
        $encoded = Yireo_MageBridge_Helper_Encryption::base64_encode('Hello World!');

        $this->assertStringNotContainsString('+', $encoded);
        $this->assertStringNotContainsString('/', $encoded);
        $this->assertStringNotContainsString('=', $encoded);
        $this->assertNotEmpty($encoded);
    }

    #[DataProvider('base64RoundTripProvider')]
    public function testBase64RoundTrip(string $input): void
    {
        $encoded = Yireo_MageBridge_Helper_Encryption::base64_encode($input);
        $decoded = Yireo_MageBridge_Helper_Encryption::base64_decode($encoded);

        $this->assertSame($input, $decoded);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function base64RoundTripProvider(): array
    {
        return [
            'simple string' => ['Hello World'],
            'special characters' => ['!@#$%^&*()_+-=[]{}|;:\'",.<>?/`~'],
            'unicode characters' => ['你好世界 こんにちは'],
            'json data' => ['{"key":"value","number":123}'],
        ];
    }

    public function testGetSaltedKeyMatchesIndependentMd5Vector(): void
    {
        $this->assertSame(
            EncryptionVectors::SALTED_FRONTEND,
            $this->helper->getSaltedKey(EncryptionVectors::SALT_MATERIAL)
        );
    }

    public function testEncryptReturnsNullForBlankInput(): void
    {
        $this->assertNull($this->helper->encrypt(''));
        $this->assertNull($this->helper->encrypt('   '));
    }

    public function testEncryptReturnsPlaintextWhenEncryptionDisabled(): void
    {
        \Mage::$storeConfig['magebridge/joomla/encryption'] = 0;

        $this->assertSame(EncryptionVectors::PLAINTEXT, $this->helper->encrypt(EncryptionVectors::PLAINTEXT));
    }

    public function testEncryptReturnsPlaintextWhenProtocolIsHttps(): void
    {
        \Mage::$protocol = 'https';

        $this->assertSame(EncryptionVectors::PLAINTEXT, $this->helper->encrypt(EncryptionVectors::PLAINTEXT));
    }

    public function testEncryptReturnsPlaintextWhenKeyIsEmpty(): void
    {
        \Mage::$storeConfig['magebridge/joomla/encryption_key'] = '';

        $this->assertSame(EncryptionVectors::PLAINTEXT, $this->helper->encrypt(EncryptionVectors::PLAINTEXT));
    }

    public function testEncryptProducesPayloadWithSeparatorAndUrlSafeBase64(): void
    {
        $payload = $this->helper->encrypt(EncryptionVectors::PLAINTEXT);
        $this->assertIsString($payload);
        $parts = explode('|=|', $payload);

        $this->assertCount(2, $parts);
        $this->assertStringNotContainsString('+', $parts[0] . $parts[1]);
        $this->assertStringNotContainsString('/', $parts[0] . $parts[1]);
        $this->assertStringNotContainsString('=', $parts[0] . $parts[1]);
    }

    public function testDecryptReturnsNullForEmptyInput(): void
    {
        $this->assertNull($this->helper->decrypt(''));
        $this->assertNull($this->helper->decrypt(null));
    }

    public function testDecryptDoesNotTrimWhitespaceOnlyInput(): void
    {
        $this->assertSame('   ', $this->helper->decrypt('   '));
    }

    public function testDecryptReturnsPlaintextWhenPayloadHasNoSeparator(): void
    {
        $this->assertSame('not-encrypted', $this->helper->decrypt('not-encrypted'));
    }

    public function testDecryptRestoresIndependentCiphertextVector(): void
    {
        $this->assertSame(
            EncryptionVectors::PLAINTEXT,
            $this->helper->decrypt(EncryptionVectors::CIPHERTEXT)
        );
    }

    public function testEncryptDecryptRoundTrip(): void
    {
        $cipher = $this->helper->encrypt(EncryptionVectors::PLAINTEXT);

        $this->assertSame(EncryptionVectors::PLAINTEXT, $this->helper->decrypt($cipher));
    }

    public function testDecryptReturnsOriginalPayloadWhenKeyDoesNotMatch(): void
    {
        \Mage::$storeConfig['magebridge/joomla/encryption_key'] = 'wrong-key-wrong-key-wrong-key!!!!';

        $this->assertSame(
            EncryptionVectors::CIPHERTEXT,
            $this->helper->decrypt(EncryptionVectors::CIPHERTEXT)
        );
    }
}
