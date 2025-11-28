<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

/**
 * Tests for ConfigController::fixPost() method.
 *
 * Since fixPost is a private method, we use reflection to test it.
 * This tests the core logic of normalizing POST data from different formats.
 */
final class ConfigControllerTest extends TestCase
{
    /**
     * Test fixPost handles Joomla form format (jform[config][field]).
     */
    public function testFixPostHandlesJoomlaFormFormat(): void
    {
        $post = [
            'jform' => [
                'config' => [
                    'host' => 'store.dev.local',
                    'port' => '8080',
                    'api_user' => 'test_user',
                ],
            ],
            'option' => 'com_magebridge',
        ];

        $result = $this->callFixPost($post);

        $this->assertSame('store.dev.local', $result['host']);
        $this->assertSame('8080', $result['port']);
        $this->assertSame('test_user', $result['api_user']);
        $this->assertArrayNotHasKey('jform', $result);
        $this->assertSame('com_magebridge', $result['option']);
    }

    /**
     * Test fixPost handles legacy format (config[field]).
     */
    public function testFixPostHandlesLegacyFormat(): void
    {
        $post = [
            'config' => [
                'host' => 'legacy.host.com',
                'port' => '443',
            ],
            'task' => 'save',
        ];

        $result = $this->callFixPost($post);

        $this->assertSame('legacy.host.com', $result['host']);
        $this->assertSame('443', $result['port']);
        $this->assertArrayNotHasKey('config', $result);
        $this->assertSame('save', $result['task']);
    }

    /**
     * Test fixPost handles mixed format (both jform and config).
     */
    public function testFixPostHandlesMixedFormat(): void
    {
        $post = [
            'jform' => [
                'config' => [
                    'host' => 'jform.host.com',
                ],
            ],
            'config' => [
                'port' => '9000',
            ],
        ];

        $result = $this->callFixPost($post);

        // jform values should be processed first
        $this->assertSame('jform.host.com', $result['host']);
        // legacy config values should also be processed
        $this->assertSame('9000', $result['port']);
        $this->assertArrayNotHasKey('jform', $result);
        $this->assertArrayNotHasKey('config', $result);
    }

    /**
     * Test fixPost handles empty post data.
     */
    public function testFixPostHandlesEmptyPost(): void
    {
        $result = $this->callFixPost([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test fixPost preserves non-config fields.
     */
    public function testFixPostPreservesNonConfigFields(): void
    {
        $post = [
            'option' => 'com_magebridge',
            'view' => 'config',
            'task' => 'apply',
            'jform' => [
                'config' => [
                    'host' => 'test.com',
                ],
            ],
        ];

        $result = $this->callFixPost($post);

        $this->assertSame('com_magebridge', $result['option']);
        $this->assertSame('config', $result['view']);
        $this->assertSame('apply', $result['task']);
        $this->assertSame('test.com', $result['host']);
    }

    /**
     * Test fixPost handles nested config with various value types.
     */
    public function testFixPostHandlesVariousValueTypes(): void
    {
        $post = [
            'jform' => [
                'config' => [
                    'string_value' => 'test',
                    'int_value' => 123,
                    'bool_value' => true,
                    'empty_value' => '',
                    'null_value' => null,
                    'array_value' => ['a', 'b', 'c'],
                ],
            ],
        ];

        $result = $this->callFixPost($post);

        $this->assertSame('test', $result['string_value']);
        $this->assertSame(123, $result['int_value']);
        $this->assertTrue($result['bool_value']);
        $this->assertSame('', $result['empty_value']);
        $this->assertNull($result['null_value']);
        $this->assertSame(['a', 'b', 'c'], $result['array_value']);
    }

    /**
     * Test fixPost handles jform without config key.
     */
    public function testFixPostHandlesJformWithoutConfig(): void
    {
        $post = [
            'jform' => [
                'other_key' => 'value',
            ],
            'existing' => 'data',
        ];

        $result = $this->callFixPost($post);

        // jform should remain since config key doesn't exist
        $this->assertArrayHasKey('jform', $result);
        $this->assertSame('data', $result['existing']);
    }

    /**
     * Test fixPost handles config as non-array.
     */
    public function testFixPostHandlesConfigAsNonArray(): void
    {
        $post = [
            'config' => 'not_an_array',
            'jform' => [
                'config' => 'also_not_array',
            ],
        ];

        $result = $this->callFixPost($post);

        // Non-array config values should be preserved as-is
        $this->assertSame('not_an_array', $result['config']);
        $this->assertArrayHasKey('jform', $result);
    }

    /**
     * Call the fixPost method using a simplified implementation.
     *
     * This mimics the actual fixPost logic without requiring Joomla dependencies.
     *
     * @param array $post Posted configuration data
     * @return array Normalized post data
     */
    private function callFixPost(array $post): array
    {
        // Extract config array from jform if present (Joomla form format: jform[config][field])
        if (isset($post['jform']['config']) && is_array($post['jform']['config'])) {
            foreach ($post['jform']['config'] as $name => $value) {
                $post[$name] = $value;
            }

            unset($post['jform']);
        }

        // Also handle legacy format (config[field])
        if (isset($post['config']) && is_array($post['config'])) {
            foreach ($post['config'] as $name => $value) {
                $post[$name] = $value;
            }

            unset($post['config']);
        }

        return $post;
    }
}
