<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Yireo_MageBridge_Helper_Encryption;

require_once dirname(__DIR__) . '/Magento/Helper/MageStubs.php';

/**
 * Joomla and Magento must accept each other's AES payload for the same key.
 */
#[CoversClass(\MageBridge\Component\MageBridge\Site\Helper\EncryptionHelper::class)]
#[CoversClass(Yireo_MageBridge_Helper_Encryption::class)]
final class EncryptionInteropTest extends TestCase
{
    private Yireo_MageBridge_Helper_Encryption $magento;

    protected function setUp(): void
    {
        parent::setUp();
        TestableEncryptionHelper::reset();
        \Mage::reset();
        \Mage::$storeConfig['magebridge/joomla/encryption_key'] = EncryptionVectors::KEY;
        \Mage::$storeConfig['magebridge/joomla/encryption'] = 1;
        \Mage::$protocol = 'http';
        $this->magento = new Yireo_MageBridge_Helper_Encryption();
    }

    protected function tearDown(): void
    {
        TestableEncryptionHelper::reset();
        \Mage::reset();
        parent::tearDown();
    }

    public function testMagentoDecryptsWhatJoomlaEncrypts(): void
    {
        $payload = TestableEncryptionHelper::encrypt(EncryptionVectors::PLAINTEXT);

        $this->assertSame(EncryptionVectors::PLAINTEXT, $this->magento->decrypt($payload));
    }

    public function testJoomlaDecryptsWhatMagentoEncrypts(): void
    {
        $payload = $this->magento->encrypt(EncryptionVectors::PLAINTEXT);

        $this->assertSame(EncryptionVectors::PLAINTEXT, TestableEncryptionHelper::decrypt($payload));
    }

    public function testBothSidesDecryptTheSameIndependentVector(): void
    {
        $this->assertSame(
            EncryptionVectors::PLAINTEXT,
            TestableEncryptionHelper::decrypt(EncryptionVectors::CIPHERTEXT)
        );
        $this->assertSame(
            EncryptionVectors::PLAINTEXT,
            $this->magento->decrypt(EncryptionVectors::CIPHERTEXT)
        );
    }

    public function testBothSidesSaltTheSameMaterialToTheSameDigest(): void
    {
        $this->assertSame(
            EncryptionVectors::SALTED_FRONTEND,
            TestableEncryptionHelper::getSaltedKey(EncryptionVectors::SALT_MATERIAL)
        );
        $this->assertSame(
            EncryptionVectors::SALTED_FRONTEND,
            $this->magento->getSaltedKey(EncryptionVectors::SALT_MATERIAL)
        );
    }
}
