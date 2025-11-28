<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Module\Switcher\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for SwitcherHelper.
 *
 * Only tests pure logic that doesn't depend on Joomla APIs.
 */
final class SwitcherHelperTest extends TestCase
{
    /**
     * Test register returns API call for storeviews hierarchy.
     */
    public function testRegisterReturnsApiCall(): void
    {
        $result = TestableSwitcherHelper::register();

        $this->assertSame('api', $result[0][0]);
        $this->assertSame('magebridge_storeviews.hierarchy', $result[0][1]);
    }

    /**
     * Test register returns exactly one item.
     */
    public function testRegisterReturnsOneItem(): void
    {
        $result = TestableSwitcherHelper::register();

        $this->assertCount(1, $result);
    }

    /**
     * Test build returns null when stores is empty.
     */
    public function testBuildReturnsNullWhenStoresEmpty(): void
    {
        $result = TestableSwitcherHelper::build([], null);

        $this->assertNull($result);
    }

    /**
     * Test build returns null when stores is not array.
     */
    public function testBuildReturnsNullWhenStoresNotArray(): void
    {
        // The actual helper checks for empty() and is_array()
        $result = TestableSwitcherHelper::buildWithEmptyCheck(null);

        $this->assertNull($result);
    }

    /**
     * Test build returns specific store when store_id matches.
     */
    public function testBuildReturnsSpecificStoreWhenIdMatches(): void
    {
        $stores = [
            ['value' => 'store1', 'label' => 'Store 1'],
            ['value' => 'store2', 'label' => 'Store 2'],
        ];
        $params = new TestableSwitcherParams(['store_id' => 'store2']);

        $result = TestableSwitcherHelper::build($stores, $params);

        $this->assertCount(1, $result);
        $this->assertSame('store2', $result[0]['value']);
    }

    /**
     * Test build returns all stores when store_id not found.
     */
    public function testBuildReturnsAllStoresWhenIdNotFound(): void
    {
        $stores = [
            ['value' => 'store1', 'label' => 'Store 1'],
            ['value' => 'store2', 'label' => 'Store 2'],
        ];
        $params = new TestableSwitcherParams(['store_id' => 'nonexistent']);

        $result = TestableSwitcherHelper::build($stores, $params);

        $this->assertCount(2, $result);
    }

    /**
     * Test build returns all stores when store_id is null.
     */
    public function testBuildReturnsAllStoresWhenIdNull(): void
    {
        $stores = [
            ['value' => 'store1', 'label' => 'Store 1'],
            ['value' => 'store2', 'label' => 'Store 2'],
        ];
        $params = new TestableSwitcherParams([]);

        $result = TestableSwitcherHelper::build($stores, $params);

        $this->assertCount(2, $result);
    }

    /**
     * Test getRootItemIdByLanguage returns matching ID.
     */
    public function testGetRootItemIdByLanguageReturnsMatchingId(): void
    {
        $associations = [
            'en-GB' => 101,
            'nl-NL' => 102,
            'de-DE' => 103,
        ];

        $result = TestableSwitcherHelper::getRootItemIdByLanguage('nl-NL', $associations, 100);

        $this->assertSame(102, $result);
    }

    /**
     * Test getRootItemIdByLanguage handles underscore format.
     */
    public function testGetRootItemIdByLanguageHandlesUnderscoreFormat(): void
    {
        $associations = [
            'en-GB' => 101,
            'nl-NL' => 102,
        ];

        $result = TestableSwitcherHelper::getRootItemIdByLanguage('nl_NL', $associations, 100);

        $this->assertSame(102, $result);
    }

    /**
     * Test getRootItemIdByLanguage returns current ID when not found.
     */
    public function testGetRootItemIdByLanguageReturnsCurrentIdWhenNotFound(): void
    {
        $associations = [
            'en-GB' => 101,
        ];

        $result = TestableSwitcherHelper::getRootItemIdByLanguage('fr-FR', $associations, 100);

        $this->assertSame(100, $result);
    }

    /**
     * Test getRootItemIdByLanguage returns current ID when associations empty.
     */
    public function testGetRootItemIdByLanguageReturnsCurrentIdWhenEmpty(): void
    {
        $associations = [];

        $result = TestableSwitcherHelper::getRootItemIdByLanguage('en-GB', $associations, 100);

        $this->assertSame(100, $result);
    }

    /**
     * Test getFullSelect creates options with group prefix.
     */
    public function testGetFullSelectOptionsWithGroupPrefix(): void
    {
        $stores = [
            [
                'value' => 'group1',
                'label' => 'Group 1',
                'website' => 'base',
                'childs' => [
                    ['value' => 'store1', 'label' => 'Store 1'],
                ],
            ],
        ];

        $options = TestableSwitcherHelper::buildOptions($stores, 'base', false);

        // With single group, no prefix
        $this->assertStringNotContainsString('-- ', $options[0]['label']);
    }

    /**
     * Test getFullSelect creates options with dash prefix for children in multiple groups.
     */
    public function testGetFullSelectOptionsWithDashPrefixForMultipleGroups(): void
    {
        $stores = [
            [
                'value' => 'group1',
                'label' => 'Group 1',
                'website' => 'base',
                'childs' => [
                    ['value' => 'store1', 'label' => 'Store 1'],
                ],
            ],
            [
                'value' => 'group2',
                'label' => 'Group 2',
                'website' => 'base',
                'childs' => [
                    ['value' => 'store2', 'label' => 'Store 2'],
                ],
            ],
        ];

        $options = TestableSwitcherHelper::buildOptions($stores, 'base', true);

        // With multiple groups, children have prefix
        $childOption = array_filter($options, fn ($o) => $o['value'] === 'v:store1');
        $childOption = array_values($childOption)[0];
        $this->assertStringStartsWith('-- ', $childOption['label']);
    }

    /**
     * Test getFullSelect filters by website.
     */
    public function testGetFullSelectFiltersByWebsite(): void
    {
        $stores = [
            [
                'value' => 'group1',
                'label' => 'Group 1',
                'website' => 'base',
                'childs' => [],
            ],
            [
                'value' => 'group2',
                'label' => 'Group 2',
                'website' => 'other',
                'childs' => [],
            ],
        ];

        $options = TestableSwitcherHelper::buildOptions($stores, 'base', true);

        // Only group1 from 'base' website should be included
        $this->assertCount(1, $options);
    }

    /**
     * Test store value prefix format.
     */
    public function testStoreValuePrefixFormat(): void
    {
        $stores = [
            [
                'value' => 'default',
                'label' => 'Default',
                'website' => 'base',
                'childs' => [
                    ['value' => 'english', 'label' => 'English'],
                ],
            ],
        ];

        $options = TestableSwitcherHelper::buildOptions($stores, 'base', false);

        // Store view values should have v: prefix
        $this->assertSame('v:english', $options[0]['value']);
    }

    /**
     * Test group value prefix format.
     */
    public function testGroupValuePrefixFormat(): void
    {
        $stores = [
            [
                'value' => 'group1',
                'label' => 'Group 1',
                'website' => 'base',
                'childs' => [],
            ],
            [
                'value' => 'group2',
                'label' => 'Group 2',
                'website' => 'base',
                'childs' => [],
            ],
        ];

        $options = TestableSwitcherHelper::buildOptions($stores, 'base', true);

        // Group values should have g: prefix
        $this->assertSame('g:group1', $options[0]['value']);
    }
}

/**
 * Testable implementation of SwitcherHelper logic.
 */
class TestableSwitcherHelper
{
    /**
     * @return array<int, array<int, string>>
     */
    public static function register(): array
    {
        $register = [];
        $register[] = ['api', 'magebridge_storeviews.hierarchy'];

        return $register;
    }

    /**
     * @param array<int, array<string, mixed>> $stores
     * @return array<int, array<string, mixed>>|null
     */
    public static function build(array $stores, ?TestableSwitcherParams $params): ?array
    {
        if (empty($stores)) {
            return null;
        }

        $storeId = $params?->get('store_id');
        foreach ($stores as $store) {
            if ($store['value'] == $storeId) {
                return [$store];
            }
        }

        return $stores;
    }

    /**
     * @param mixed $stores
     */
    public static function buildWithEmptyCheck($stores): ?array
    {
        if (empty($stores) || !is_array($stores)) {
            return null;
        }

        return $stores;
    }

    /**
     * @param array<string, int> $associations
     */
    public static function getRootItemIdByLanguage(string $language, array $associations, int $currentItemId): int
    {
        if (empty($associations)) {
            return $currentItemId;
        }

        foreach ($associations as $rootItemLanguage => $rootItemId) {
            if ($language == $rootItemLanguage) {
                return $rootItemId;
            }

            if ($language == str_replace('-', '_', $rootItemLanguage)) {
                return $rootItemId;
            }
        }

        return $currentItemId;
    }

    /**
     * Build options array from stores.
     *
     * @param array<int, array<string, mixed>> $stores
     * @return array<int, array<string, string>>
     */
    public static function buildOptions(array $stores, string $configWebsite, bool $showGroups): array
    {
        $options = [];

        foreach ($stores as $group) {
            if ($group['website'] != $configWebsite) {
                continue;
            }

            if ($showGroups) {
                $options[] = [
                    'value' => 'g:' . $group['value'],
                    'label' => $group['label'],
                ];
            }

            if (!empty($group['childs'])) {
                foreach ($group['childs'] as $child) {
                    $labelPrefix = ($showGroups) ? '-- ' : '';
                    $options[] = [
                        'value' => 'v:' . $child['value'],
                        'label' => $labelPrefix . $child['label'],
                    ];
                }
            }
        }

        return $options;
    }
}

/**
 * Testable params class.
 */
class TestableSwitcherParams
{
    /** @var array<string, mixed> */
    private array $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
