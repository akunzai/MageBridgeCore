<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Model;

use MageBridge\Component\MageBridge\Site\Model\Config\Rules;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Rules::class)]
final class ConfigCheckTest extends TestCase
{
    public function testRequiredElementsAreHostWebsiteAndApiCredentials(): void
    {
        $this->assertSame(
            ['host', 'website', 'api_user', 'api_key'],
            Rules::requiredElements()
        );
    }

    #[DataProvider('requiredEmptyProvider')]
    public function testRequiredSettingIsEmptyWhenConfigIsPopulated(
        string $element,
        mixed $value,
        bool $configAllEmpty,
        bool $expected
    ): void {
        $this->assertSame(
            $expected,
            Rules::requiredSettingIsEmpty($element, $value, $configAllEmpty)
        );
    }

    /**
     * @return array<string, array{string, mixed, bool, bool}>
     */
    public static function requiredEmptyProvider(): array
    {
        return [
            'empty host after first save' => ['host', '', false, true],
            'null api key after first save' => ['api_key', null, false, true],
            'zero treated empty' => ['website', 0, false, true],
            'string zero treated empty' => ['api_user', '0', false, true],
            'host present' => ['host', 'store.example.com', false, false],
            'empty host while wizard still blank' => ['host', '', true, false],
            'non-required field' => ['offline', '', false, false],
        ];
    }

    #[DataProvider('illegalHostnameProvider')]
    public function testHostnameHasIllegalCharacters(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, Rules::hostnameHasIllegalCharacters($value));
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function illegalHostnameProvider(): array
    {
        return [
            'fqdn' => ['store.example.com', false],
            'local' => ['store.dev.local', false],
            'ipv4' => ['192.168.1.10', false],
            'port' => ['store.example.com:8080', false],
            'url' => ['https://store.example.com', true],
            'path' => ['store.example.com/mage', true],
            'space' => ['store example.com', true],
            'null skipped' => [null, false],
        ];
    }

    public function testHostnameLooksLikeIpAcceptsOnlyRealIpAddresses(): void
    {
        $this->assertTrue(Rules::hostnameLooksLikeIp('192.168.1.10'));
        $this->assertTrue(Rules::hostnameLooksLikeIp('::1'));
        $this->assertFalse(Rules::hostnameLooksLikeIp('store.example.com'));
        $this->assertFalse(Rules::hostnameLooksLikeIp('localhost'));
        $this->assertFalse(Rules::hostnameLooksLikeIp('store'));
        $this->assertFalse(Rules::hostnameLooksLikeIp(null));
    }

    public function testApiWidgetsAreDisabledUnlessExactlyOne(): void
    {
        $this->assertFalse(Rules::apiWidgetsAreDisabled(1));
        $this->assertFalse(Rules::apiWidgetsAreDisabled('1'));
        $this->assertTrue(Rules::apiWidgetsAreDisabled(0));
        $this->assertTrue(Rules::apiWidgetsAreDisabled('0'));
    }

    public function testBridgeIsOfflineWhenValueIsOne(): void
    {
        $this->assertTrue(Rules::bridgeIsOffline(1));
        $this->assertTrue(Rules::bridgeIsOffline('1'));
        $this->assertFalse(Rules::bridgeIsOffline(0));
        $this->assertFalse(Rules::bridgeIsOffline('0'));
    }

    public function testWebsiteIdIsNonNumericRejectsLabels(): void
    {
        $this->assertFalse(Rules::websiteIdIsNonNumeric(1));
        $this->assertFalse(Rules::websiteIdIsNonNumeric('2'));
        $this->assertFalse(Rules::websiteIdIsNonNumeric(''));
        $this->assertFalse(Rules::websiteIdIsNonNumeric(null));
        $this->assertTrue(Rules::websiteIdIsNonNumeric('base'));
        $this->assertTrue(Rules::websiteIdIsNonNumeric('main website'));
    }

    public function testBasedirHasIllegalCharactersOnlyWhenNoSafeTokenExists(): void
    {
        $this->assertFalse(Rules::basedirHasIllegalCharacters('shop'));
        $this->assertFalse(Rules::basedirHasIllegalCharacters('shop/foo'));
        $this->assertTrue(Rules::basedirHasIllegalCharacters('/'));
        $this->assertTrue(Rules::basedirHasIllegalCharacters('///'));
    }

    public function testBasedirCollidesWithRootAliasOnlyWhenHostsMatch(): void
    {
        $this->assertTrue(
            Rules::basedirCollidesWithRootAlias('shop', 'shop', 'www.example.com', 'www.example.com')
        );
        $this->assertFalse(
            Rules::basedirCollidesWithRootAlias('shop', 'shop', 'www.example.com', 'store.example.com')
        );
        $this->assertFalse(
            Rules::basedirCollidesWithRootAlias('shop', 'catalog', 'www.example.com', 'www.example.com')
        );
        $this->assertFalse(
            Rules::basedirCollidesWithRootAlias('shop', null, 'www.example.com', 'www.example.com')
        );
        $this->assertFalse(
            Rules::basedirCollidesWithRootAlias('shop', '', 'www.example.com', 'www.example.com')
        );
    }

    public function testOmitBlankSecretsDropsEmptyPasswordFields(): void
    {
        $post = Rules::omitBlankSecrets([
            'host' => 'store.example.com',
            'api_key' => '',
            'http_password' => '',
            'encryption_key' => '',
            'http_user' => 'bridge',
        ]);

        $this->assertSame('store.example.com', $post['host']);
        $this->assertSame('bridge', $post['http_user']);
        $this->assertArrayNotHasKey('api_key', $post);
        $this->assertArrayNotHasKey('http_password', $post);
        $this->assertArrayNotHasKey('encryption_key', $post);
    }

    public function testOmitBlankSecretsKeepsPostedSecrets(): void
    {
        $post = Rules::omitBlankSecrets([
            'api_key' => 'new-secret',
            'http_password' => '0',
            'encryption_key' => 'abc',
        ]);

        $this->assertSame('new-secret', $post['api_key']);
        $this->assertSame('0', $post['http_password']);
        $this->assertSame('abc', $post['encryption_key']);
    }

    public function testFlattenPostedConfigLiftsJformThenLegacy(): void
    {
        $post = Rules::flattenPostedConfig([
            'jform' => ['config' => ['host' => 'jform.host', 'api_key' => '']],
            'config' => ['port' => '443'],
            'option' => 'com_magebridge',
        ]);

        $this->assertSame('jform.host', $post['host']);
        $this->assertSame('443', $post['port']);
        $this->assertSame('', $post['api_key']);
        $this->assertSame('com_magebridge', $post['option']);
        $this->assertArrayNotHasKey('jform', $post);
        $this->assertArrayNotHasKey('config', $post);

        $this->assertArrayNotHasKey('api_key', Rules::omitBlankSecrets($post));
    }

    public function testFormValuesLetsStoredRowsOverrideDefaults(): void
    {
        $values = Rules::formValues(
            [
                'filter_content' => '1',
                'offline_message' => 'The webshop is currently not available. Please come back again later.',
                'http_user' => '',
            ],
            [
                'http_user' => 'bridge',
            ]
        );

        $this->assertSame('1', $values['filter_content']);
        $this->assertSame(
            'The webshop is currently not available. Please come back again later.',
            $values['offline_message']
        );
        $this->assertSame('bridge', $values['http_user']);
    }
}
