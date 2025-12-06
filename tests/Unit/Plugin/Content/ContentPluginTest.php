<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Plugin\Content;

use PHPUnit\Framework\TestCase;

/**
 * Tests for ContentPlugin.
 *
 * Since ContentPlugin depends on Joomla content system,
 * we test pure logic using testable implementations.
 */
final class ContentPluginTest extends TestCase
{
    /**
     * Test subscribed events returns correct events.
     */
    public function testGetSubscribedEventsReturnsCorrectEvents(): void
    {
        $events = TestableContentPlugin::getSubscribedEvents();

        $this->assertArrayHasKey('onContentPrepare', $events);
        $this->assertSame('onContentPrepare', $events['onContentPrepare']);
    }

    /**
     * Test Magento CMS tag detection with valid tags.
     */
    public function testHasMagentoCmsTagsDetectsValidTags(): void
    {
        $plugin = new TestableContentPlugin();

        $this->assertTrue($plugin->hasMagentoCmsTags('{{store url="home"}}'));
        $this->assertTrue($plugin->hasMagentoCmsTags('{{block type="core/template" template="test.phtml"}}'));
        $this->assertTrue($plugin->hasMagentoCmsTags('{{widget type="cms/widget_page_link"}}'));
        $this->assertTrue($plugin->hasMagentoCmsTags('Some text {{skin url="images/test.jpg"}} more text'));
        $this->assertTrue($plugin->hasMagentoCmsTags('{{media url="test.jpg"}}'));
    }

    /**
     * Test Magento CMS tag detection returns false for no tags.
     */
    public function testHasMagentoCmsTagsReturnsFalseForNoTags(): void
    {
        $plugin = new TestableContentPlugin();

        $this->assertFalse($plugin->hasMagentoCmsTags('Regular text without tags'));
        $this->assertFalse($plugin->hasMagentoCmsTags(''));
        $this->assertFalse($plugin->hasMagentoCmsTags('Text with {single braces}'));
        $this->assertFalse($plugin->hasMagentoCmsTags('Text with { { spaced braces } }'));
    }

    /**
     * Test Magento CMS tag detection with empty content in tags.
     */
    public function testHasMagentoCmsTagsWithEmptyContent(): void
    {
        $plugin = new TestableContentPlugin();

        // Empty tag content should still match the pattern
        $this->assertTrue($plugin->hasMagentoCmsTags('{{a}}'));
    }

    /**
     * Test Magento CMS tag detection does not match incomplete tags.
     */
    public function testHasMagentoCmsTagsDoesNotMatchIncompleteTags(): void
    {
        $plugin = new TestableContentPlugin();

        $this->assertFalse($plugin->hasMagentoCmsTags('{{incomplete tag'));
        $this->assertFalse($plugin->hasMagentoCmsTags('incomplete tag}}'));
        $this->assertFalse($plugin->hasMagentoCmsTags('{single brace}'));
    }

    /**
     * Test generating content cache key.
     */
    public function testGenerateCacheKey(): void
    {
        $plugin = new TestableContentPlugin();

        $row = ['text' => 'Some content', 'id' => 1];
        $option = 'com_content';

        $key = $plugin->generateCacheKey($row, $option);

        $this->assertNotEmpty($key);
        $this->assertStringContainsString(':com_content', $key);
    }

    /**
     * Test cache keys are different for different content.
     */
    public function testCacheKeysAreDifferentForDifferentContent(): void
    {
        $plugin = new TestableContentPlugin();

        $row1 = ['text' => 'Content A', 'id' => 1];
        $row2 = ['text' => 'Content B', 'id' => 2];

        $key1 = $plugin->generateCacheKey($row1, 'com_content');
        $key2 = $plugin->generateCacheKey($row2, 'com_content');

        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test cache keys are different for same content but different options.
     */
    public function testCacheKeysAreDifferentForDifferentOptions(): void
    {
        $plugin = new TestableContentPlugin();

        $row = ['text' => 'Same content', 'id' => 1];

        $key1 = $plugin->generateCacheKey($row, 'com_content');
        $key2 = $plugin->generateCacheKey($row, 'com_magebridge');

        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test plugin is not enabled when BridgeModel class doesn't exist.
     */
    public function testIsEnabledReturnsFalseWhenBridgeModelNotExists(): void
    {
        $plugin = new TestableContentPlugin();
        $plugin->setBridgeModelExists(false);

        $this->assertFalse($plugin->isEnabled());
    }

    /**
     * Test plugin is not enabled when bridge is offline.
     */
    public function testIsEnabledReturnsFalseWhenBridgeOffline(): void
    {
        $plugin = new TestableContentPlugin();
        $plugin->setBridgeModelExists(true);
        $plugin->setBridgeOffline(true);

        $this->assertFalse($plugin->isEnabled());
    }

    /**
     * Test plugin is not enabled on API pages.
     */
    public function testIsEnabledReturnsFalseOnApiPage(): void
    {
        $plugin = new TestableContentPlugin();
        $plugin->setBridgeModelExists(true);
        $plugin->setBridgeOffline(false);
        $plugin->setIsApiPage(true);

        $this->assertFalse($plugin->isEnabled());
    }

    /**
     * Test plugin is enabled when all conditions are met.
     */
    public function testIsEnabledReturnsTrueWhenAllConditionsMet(): void
    {
        $plugin = new TestableContentPlugin();
        $plugin->setBridgeModelExists(true);
        $plugin->setBridgeOffline(false);
        $plugin->setIsApiPage(false);

        $this->assertTrue($plugin->isEnabled());
    }
}

/**
 * Testable implementation of ContentPlugin without Joomla dependencies.
 */
class TestableContentPlugin
{
    private bool $bridgeModelExists = true;
    private bool $bridgeOffline = false;
    private bool $isApiPage = false;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepare' => 'onContentPrepare',
        ];
    }

    /**
     * Check if text contains Magento CMS tags.
     */
    public function hasMagentoCmsTags(string $text): bool
    {
        return preg_match('/{{([^}]+)}}/', $text) === 1;
    }

    /**
     * Generate a cache key for content.
     *
     * @param array<string, mixed> $row
     */
    public function generateCacheKey(array $row, string $option): string
    {
        return md5(var_export($row, true)) . ':' . $option;
    }

    /**
     * Set whether BridgeModel class exists.
     */
    public function setBridgeModelExists(bool $exists): void
    {
        $this->bridgeModelExists = $exists;
    }

    /**
     * Set whether bridge is offline.
     */
    public function setBridgeOffline(bool $offline): void
    {
        $this->bridgeOffline = $offline;
    }

    /**
     * Set whether this is an API page.
     */
    public function setIsApiPage(bool $isApi): void
    {
        $this->isApiPage = $isApi;
    }

    /**
     * Check if the plugin is enabled.
     */
    public function isEnabled(): bool
    {
        if (!$this->bridgeModelExists) {
            return false;
        }

        if ($this->bridgeOffline) {
            return false;
        }

        if ($this->isApiPage) {
            return false;
        }

        return true;
    }
}
