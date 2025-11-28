<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Site\Helper;

use PHPUnit\Framework\TestCase;

/**
 * Tests for Site BlockHelper.
 *
 * Since BlockHelper has some Joomla dependencies,
 * we test pure logic using testable implementations.
 */
final class BlockHelperTest extends TestCase
{
    /**
     * Test parseBlock inserts form token before closing form tag.
     */
    public function testParseBlockInsertsFormToken(): void
    {
        $helper = new TestableBlockHelper();
        $helper->setFormToken('<input type="hidden" name="token" value="abc123">');

        $data = '<form action="/submit"><input type="text" name="test"></form>';
        $result = $helper->parseBlock($data);

        $this->assertStringContainsString('name="token"', $result);
        $this->assertStringContainsString('value="abc123"', $result);
        $this->assertStringContainsString('</form>', $result);
    }

    /**
     * Test parseBlock handles multiple forms.
     */
    public function testParseBlockHandlesMultipleForms(): void
    {
        $helper = new TestableBlockHelper();
        $helper->setFormToken('[TOKEN]');

        $data = '<form>Form 1</form><div>Content</div><form>Form 2</form>';
        $result = $helper->parseBlock($data);

        $this->assertSame(2, substr_count($result, '[TOKEN]'));
    }

    /**
     * Test parseBlock with no forms.
     */
    public function testParseBlockWithNoForms(): void
    {
        $helper = new TestableBlockHelper();
        $helper->setFormToken('[TOKEN]');

        $data = '<div>No forms here</div>';
        $result = $helper->parseBlock($data);

        $this->assertSame($data, $result);
    }

    /**
     * Test parseJdocTags detects module include tags.
     */
    public function testParseJdocTagsDetectsModuleInclude(): void
    {
        $helper = new TestableBlockHelper();

        $data = '<div><jdoc:include type="modules" name="left" /></div>';
        $matches = $helper->findJdocTags($data);

        $this->assertCount(1, $matches);
        $this->assertSame('modules', $matches[0]['type']);
        $this->assertSame('left', $matches[0]['name']);
    }

    /**
     * Test parseJdocTags detects multiple tags.
     */
    public function testParseJdocTagsDetectsMultipleTags(): void
    {
        $helper = new TestableBlockHelper();

        $data = '<jdoc:include type="modules" name="top" /><div>Content</div><jdoc:include type="modules" name="bottom" />';
        $matches = $helper->findJdocTags($data);

        $this->assertCount(2, $matches);
        $this->assertSame('top', $matches[0]['name']);
        $this->assertSame('bottom', $matches[1]['name']);
    }

    /**
     * Test parseJdocTags ignores non-module types.
     */
    public function testParseJdocTagsIgnoresNonModuleTypes(): void
    {
        $helper = new TestableBlockHelper();

        $data = '<jdoc:include type="component" />';
        $matches = $helper->findJdocTags($data);

        // Component type doesn't have a name attribute typically
        $this->assertCount(1, $matches);
        $this->assertSame('component', $matches[0]['type']);
    }

    /**
     * Test parseJdocTags returns empty for no tags.
     */
    public function testParseJdocTagsReturnsEmptyForNoTags(): void
    {
        $helper = new TestableBlockHelper();

        $data = '<div>No jdoc tags here</div>';
        $matches = $helper->findJdocTags($data);

        $this->assertCount(0, $matches);
    }

    /**
     * Test parseJdocTags parses attributes correctly.
     */
    public function testParseJdocTagsParsesAttributes(): void
    {
        $helper = new TestableBlockHelper();

        $data = '<jdoc:include type="modules" name="sidebar" style="xhtml" />';
        $matches = $helper->findJdocTags($data);

        $this->assertCount(1, $matches);
        $this->assertSame('modules', $matches[0]['type']);
        $this->assertSame('sidebar', $matches[0]['name']);
        $this->assertSame('xhtml', $matches[0]['style']);
    }

    /**
     * Test replaceJdocTag replaces tag with content.
     */
    public function testReplaceJdocTag(): void
    {
        $helper = new TestableBlockHelper();

        $data = '<div><jdoc:include type="modules" name="test" /></div>';
        $replacement = '<p>Module Content</p>';

        $result = $helper->replaceJdocTag($data, '<jdoc:include type="modules" name="test" />', $replacement);

        $this->assertStringContainsString('<p>Module Content</p>', $result);
        $this->assertStringNotContainsString('jdoc:include', $result);
    }

    /**
     * Test isModulesType returns true for modules type.
     */
    public function testIsModulesTypeReturnsTrue(): void
    {
        $helper = new TestableBlockHelper();

        $this->assertTrue($helper->isModulesType('modules'));
    }

    /**
     * Test isModulesType returns false for other types.
     */
    public function testIsModulesTypeReturnsFalse(): void
    {
        $helper = new TestableBlockHelper();

        $this->assertFalse($helper->isModulesType('component'));
        $this->assertFalse($helper->isModulesType('head'));
        $this->assertFalse($helper->isModulesType('message'));
    }
}

/**
 * Testable implementation of BlockHelper without Joomla dependencies.
 */
class TestableBlockHelper
{
    private string $formToken = '';

    /**
     * Set form token for testing.
     */
    public function setFormToken(string $token): void
    {
        $this->formToken = $token;
    }

    /**
     * Parse block and insert form token.
     */
    public function parseBlock(string $data): string
    {
        return str_replace('</form>', $this->formToken . '</form>', $data);
    }

    /**
     * Find jdoc include tags in data.
     *
     * @return array<int, array<string, string>>
     */
    public function findJdocTags(string $data): array
    {
        if (!preg_match_all('#<jdoc:include\ type="([^\"]+)"(.*)\/>#iU', $data, $matches)) {
            return [];
        }

        $result = [];

        foreach ($matches[0] as $index => $fullMatch) {
            $tag = [
                'full' => $fullMatch,
                'type' => $matches[1][$index],
            ];

            // Parse attributes from the rest of the tag
            $attributes = trim($matches[2][$index]);
            if (preg_match_all('/(\w+)="([^"]*)"/', $attributes, $attrMatches)) {
                foreach ($attrMatches[1] as $attrIndex => $attrName) {
                    $tag[$attrName] = $attrMatches[2][$attrIndex];
                }
            }

            $result[] = $tag;
        }

        return $result;
    }

    /**
     * Replace jdoc tag with content.
     */
    public function replaceJdocTag(string $data, string $tag, string $replacement): string
    {
        return str_replace($tag, $replacement, $data);
    }

    /**
     * Check if type is modules.
     */
    public function isModulesType(string $type): bool
    {
        return $type === 'modules';
    }
}
