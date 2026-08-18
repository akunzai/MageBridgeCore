<?php

declare(strict_types=1);

/**
 * System-boundary stubs so Magento EncryptionHelper can run without booting OpenMage.
 */
if (!class_exists('Mage_Core_Helper_Abstract', false)) {
    class Mage_Core_Helper_Abstract
    {
    }
}

if (!class_exists('Mage', false)) {
    class Mage
    {
        /** @var array<string, mixed> */
        public static array $storeConfig = [];

        public static ?string $protocol = 'http';

        public static function reset(): void
        {
            self::$storeConfig = [];
            self::$protocol = 'http';
        }

        /**
         * @param string $path
         *
         * @return mixed
         */
        public static function getStoreConfig($path)
        {
            return self::$storeConfig[$path] ?? null;
        }

        /**
         * @param string $name
         */
        public static function getSingleton($name): MageBridgeCoreStub
        {
            return new MageBridgeCoreStub();
        }
    }

    class MageBridgeCoreStub
    {
        /**
         * @param string $key
         */
        public function getMetaData($key): ?string
        {
            return $key === 'protocol' ? Mage::$protocol : null;
        }
    }
}

$magebridgeEncryptionHelper = dirname(__DIR__, 4) . '/magento/app/code/community/Yireo/MageBridge/Helper/Encryption.php';

if (!class_exists('Yireo_MageBridge_Helper_Encryption', false)) {
    require_once $magebridgeEncryptionHelper;
}
