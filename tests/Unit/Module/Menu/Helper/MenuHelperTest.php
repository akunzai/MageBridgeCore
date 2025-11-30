<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Module\Menu\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for MenuHelper.
 *
 * Since MenuHelper has Joomla dependencies in most methods,
 * we test the pure logic methods using testable implementations.
 */
final class MenuHelperTest extends TestCase
{
    /**
     * Test setRoot returns children when root_id is null.
     */
    public function testSetRootReturnsChildrenWhenRootIdIsNull(): void
    {
        $tree = [
            'category_id' => 1,
            'children' => [
                ['category_id' => 2, 'name' => 'Child 1'],
                ['category_id' => 3, 'name' => 'Child 2'],
            ],
        ];

        $result = TestableMenuHelper::setRoot($tree, null);

        $this->assertCount(2, $result);
        $this->assertSame('Child 1', $result[0]['name']);
        $this->assertSame('Child 2', $result[1]['name']);
    }

    /**
     * Test setRoot returns children when root_id is zero.
     */
    public function testSetRootReturnsChildrenWhenRootIdIsZero(): void
    {
        $tree = [
            'category_id' => 1,
            'children' => [
                ['category_id' => 2, 'name' => 'Child 1'],
            ],
        ];

        $result = TestableMenuHelper::setRoot($tree, 0);

        $this->assertCount(1, $result);
    }

    /**
     * Test setRoot returns children of matching root category.
     */
    public function testSetRootReturnsChildrenOfMatchingCategory(): void
    {
        $tree = [
            'category_id' => 1,
            'children' => [
                ['category_id' => 2, 'name' => 'Child 1'],
                ['category_id' => 3, 'name' => 'Child 2'],
            ],
        ];

        $result = TestableMenuHelper::setRoot($tree, 1);

        $this->assertCount(2, $result);
    }

    /**
     * Test setRoot finds nested root category.
     */
    public function testSetRootFindsNestedRootCategory(): void
    {
        $tree = [
            'category_id' => 1,
            'children' => [
                [
                    'category_id' => 2,
                    'name' => 'Parent',
                    'children' => [
                        ['category_id' => 4, 'name' => 'Nested Child 1'],
                        ['category_id' => 5, 'name' => 'Nested Child 2'],
                    ],
                ],
                ['category_id' => 3, 'name' => 'Other'],
            ],
        ];

        $result = TestableMenuHelper::setRoot($tree, 2);

        $this->assertCount(2, $result);
        $this->assertSame('Nested Child 1', $result[0]['name']);
        $this->assertSame('Nested Child 2', $result[1]['name']);
    }

    /**
     * Test setRoot finds deeply nested root category.
     */
    public function testSetRootFindsDeeplyNestedRootCategory(): void
    {
        $tree = [
            'category_id' => 1,
            'children' => [
                [
                    'category_id' => 2,
                    'children' => [
                        [
                            'category_id' => 3,
                            'children' => [
                                ['category_id' => 5, 'name' => 'Deep Child'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = TestableMenuHelper::setRoot($tree, 3);

        $this->assertCount(1, $result);
        $this->assertSame('Deep Child', $result[0]['name']);
    }

    /**
     * Test setRoot returns empty array when root not found.
     */
    public function testSetRootReturnsEmptyArrayWhenRootNotFound(): void
    {
        $tree = [
            'category_id' => 1,
            'children' => [
                ['category_id' => 2, 'name' => 'Child'],
            ],
        ];

        $result = TestableMenuHelper::setRoot($tree, 999);

        $this->assertEmpty($result);
    }

    /**
     * Test setRoot returns empty array when tree is null.
     */
    public function testSetRootReturnsEmptyArrayWhenTreeIsNull(): void
    {
        $result = TestableMenuHelper::setRoot(null, 1);

        $this->assertEmpty($result);
    }

    /**
     * Test setRoot returns empty array when tree has no children.
     */
    public function testSetRootReturnsEmptyArrayWhenNoChildren(): void
    {
        $tree = [
            'category_id' => 1,
        ];

        $result = TestableMenuHelper::setRoot($tree, null);

        $this->assertEmpty($result);
    }

    /**
     * Test setRoot handles empty children array.
     */
    public function testSetRootHandlesEmptyChildrenArray(): void
    {
        $tree = [
            'category_id' => 1,
            'children' => [],
        ];

        $result = TestableMenuHelper::setRoot($tree, null);

        $this->assertEmpty($result);
    }

    /**
     * Test getArguments returns correct structure with all parameters.
     */
    public function testGetArgumentsReturnsCorrectStructure(): void
    {
        $params = new TestableParams([
            'count' => 5,
            'levels' => 3,
            'startlevel' => 2,
            'include_product_count' => 1,
        ]);

        $result = TestableMenuHelper::getArguments($params);

        $this->assertIsArray($result);
        $this->assertSame(5, $result['count']);
        $this->assertSame(3, $result['levels']);
        $this->assertSame(2, $result['startlevel']);
        $this->assertSame(1, $result['include_product_count']);
    }

    /**
     * Test getArguments uses defaults when params are not set.
     */
    public function testGetArgumentsUsesDefaults(): void
    {
        $params = new TestableParams([]);

        $result = TestableMenuHelper::getArguments($params);

        $this->assertIsArray($result);
        $this->assertSame(0, $result['count']);
        $this->assertSame(1, $result['levels']);
        $this->assertSame(1, $result['startlevel']);
        $this->assertArrayNotHasKey('include_product_count', $result);
    }

    /**
     * Test getArguments excludes product count when not enabled.
     */
    public function testGetArgumentsExcludesProductCountWhenNotEnabled(): void
    {
        $params = new TestableParams([
            'include_product_count' => 0,
        ]);

        $result = TestableMenuHelper::getArguments($params);

        $this->assertArrayNotHasKey('include_product_count', $result);
    }

    /**
     * Test getCssClass returns category classes.
     */
    public function testGetCssClassReturnsCategoryClasses(): void
    {
        $params = new TestableParams([]);
        $item = [
            'entity_id' => 5,
            'url_key' => 'electronics',
        ];

        $result = TestableMenuHelper::getCssClass($params, $item, 1, 0, []);

        $this->assertStringContainsString('category-5', $result);
        $this->assertStringContainsString('category-electronics', $result);
    }

    /**
     * Test getCssClass returns parent class when has children.
     */
    public function testGetCssClassReturnsParentClassWhenHasChildren(): void
    {
        $params = new TestableParams([]);
        $item = [
            'entity_id' => 5,
            'children_count' => 3,
        ];

        $result = TestableMenuHelper::getCssClass($params, $item, 1, 0, []);

        $this->assertStringContainsString('parent', $result);
    }

    /**
     * Test getCssClass returns level class when enabled.
     */
    public function testGetCssClassReturnsLevelClassWhenEnabled(): void
    {
        $params = new TestableParams(['css_level' => 1]);
        $item = ['entity_id' => 5];

        $result = TestableMenuHelper::getCssClass($params, $item, 3, 0, []);

        $this->assertStringContainsString('level3', $result);
    }

    /**
     * Test getCssClass returns first/last classes when enabled.
     */
    public function testGetCssClassReturnsFirstLastClassesWhenEnabled(): void
    {
        $params = new TestableParams(['css_firstlast' => 1]);
        $item = ['entity_id' => 5];
        $tree = ['a', 'b', 'c'];

        // First item
        $resultFirst = TestableMenuHelper::getCssClass($params, $item, 1, 0, $tree);
        $this->assertStringContainsString('first', $resultFirst);

        // Last item
        $resultLast = TestableMenuHelper::getCssClass($params, $item, 1, 3, $tree);
        $this->assertStringContainsString('last', $resultLast);
    }

    /**
     * Test getCssClass returns even/odd classes when enabled.
     */
    public function testGetCssClassReturnsEvenOddClassesWhenEnabled(): void
    {
        $params = new TestableParams(['css_evenodd' => 1]);
        $item = ['entity_id' => 5];

        $resultEven = TestableMenuHelper::getCssClass($params, $item, 1, 0, []);
        $this->assertStringContainsString('even', $resultEven);

        $resultOdd = TestableMenuHelper::getCssClass($params, $item, 1, 1, []);
        $this->assertStringContainsString('odd', $resultOdd);
    }

    /**
     * Test getCssClass removes duplicate classes.
     */
    public function testGetCssClassRemovesDuplicates(): void
    {
        $params = new TestableParams([]);
        $item = ['entity_id' => 5];

        $result = TestableMenuHelper::getCssClass($params, $item, 1, 0, []);

        // Should not have duplicate 'category-5'
        $this->assertSame(1, substr_count($result, 'category-5'));
    }
}

/**
 * Testable implementation of MenuHelper without Joomla dependencies.
 */
class TestableMenuHelper
{
    private static int $currentCategoryId = 0;

    /** @var array<int> */
    private static array $currentCategoryPath = [];

    /**
     * Set the current category ID for testing.
     */
    public static function setCurrentCategoryId(int $id): void
    {
        self::$currentCategoryId = $id;
    }

    /**
     * Set the current category path for testing.
     *
     * @param array<int> $path
     */
    public static function setCurrentCategoryPath(array $path): void
    {
        self::$currentCategoryPath = $path;
    }

    /**
     * Get the current category ID.
     */
    public static function getCurrentCategoryId(): int
    {
        return self::$currentCategoryId;
    }

    /**
     * Get the current category path.
     *
     * @return array<int>
     */
    public static function getCurrentCategoryPath(): array
    {
        return self::$currentCategoryPath;
    }

    /**
     * Helper-method to return a specified root-category from a tree.
     *
     * @param array<string, mixed>|null $tree
     *
     * @return array<mixed>
     */
    public static function setRoot(?array $tree = null, ?int $root_id = null): array
    {
        // If no root-category is configured, just return all children
        if (!$root_id > 0) {
            return $tree['children'] ?? [];
        }

        // If the current level contains the configured root-category, return it's children
        if (isset($tree['category_id']) && $tree['category_id'] == $root_id) {
            return $tree['children'] ?? [];
        }

        // Loop through the children to find the configured root-category
        if (isset($tree['children']) && is_array($tree['children']) && count($tree['children']) > 0) {
            foreach ($tree['children'] as $item) {
                $subtree = self::setRoot($item, $root_id);
                if (!empty($subtree)) {
                    return $subtree;
                }
            }
        }

        return [];
    }

    /**
     * Method to get the API-arguments based upon the module parameters.
     *
     * @return array<string, mixed>|null
     */
    public static function getArguments(TestableParams $params): ?array
    {
        $arguments = [
            'count' => (int) $params->get('count', 0),
            'levels' => (int) $params->get('levels', 1),
            'startlevel' => (int) $params->get('startlevel', 1),
        ];

        if ($params->get('include_product_count') == 1) {
            $arguments['include_product_count'] = 1;
        }

        return $arguments;
    }

    /**
     * Helper-method to return a CSS-class string.
     *
     * @param array<string, mixed> $item
     * @param array<mixed> $tree
     */
    public static function getCssClass(TestableParams $params, array $item, int $level, int $counter, array $tree): string
    {
        $current_category_id = self::getCurrentCategoryId();
        $current_category_path = self::getCurrentCategoryPath();

        $class = [];

        if (isset($item['entity_id'])) {
            if ($item['entity_id'] == $current_category_id) {
                $class[] = 'current';
                $class[] = 'active';
            } elseif (in_array($item['entity_id'], $current_category_path)) {
                $class[] = 'active';
            }

            $class[] = 'category-' . $item['entity_id'];
            $class[] = 'category-' . ($item['url_key'] ?? '');
        }

        if (isset($item['children_count']) && $item['children_count'] > 0) {
            $class[] = 'parent';
        }

        if ($params->get('css_level', 0) == 1) {
            $class[] = 'level' . $level;
        }

        if ($params->get('css_firstlast', 0) == 1) {
            if ($counter == 0) {
                $class[] = 'first';
            }

            if ($counter == count($tree)) {
                $class[] = 'last';
            }
        }

        if ($params->get('css_evenodd', 0) == 1) {
            if ($counter % 2 == 0) {
                $class[] = 'even';
            }

            if ($counter % 2 == 1) {
                $class[] = 'odd';
            }
        }

        $class = array_unique($class);

        return implode(' ', $class);
    }
}

/**
 * Testable params class mimicking Joomla Registry.
 */
class TestableParams
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

    /**
     * Get a parameter value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
