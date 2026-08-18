<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Helper;

/**
 * Independent encrypt/decrypt fixtures shared by the Joomla and Magento helpers.
 *
 * Ciphertext was produced with openssl AES-256-CBC, IV 0123456789abcdef,
 * then the MageBridge URL-safe base64 alphabet — not by calling either helper.
 */
final class EncryptionVectors
{
    public const KEY = 'magebridge-test-key-32bytes!!!!!';

    public const PLAINTEXT = 'user@example.com';

    public const SALT_MATERIAL = 'frontend';

    public const SALTED_FRONTEND = '094e6cddacf64179d18fa9461406b941';

    public const CIPHERTEXT = 'eHJYQUhaYXFlelh5aTF0NjhOblVrdG1XT1pwZytRUS8xakdrdVVYRkhkQT0,|=|MDEyMzQ1Njc4OWFiY2RlZg,,';
}
