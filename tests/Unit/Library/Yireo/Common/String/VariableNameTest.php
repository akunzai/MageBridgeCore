<?php

declare(strict_types=1);

namespace MageBridge\Tests\Unit\Library\Yireo\Common\String;

use PHPUnit\Framework\TestCase;
use Yireo\Common\String\VariableName;

/**
 * Tests for VariableName string utility class.
 */
final class VariableNameTest extends TestCase
{
    public function testConstructorSetsString(): void
    {
        $variableName = new VariableName('test-string');

        $this->assertSame('test-string', $variableName->toString());
    }

    public function testToStringMagicMethod(): void
    {
        $variableName = new VariableName('hello');

        $this->assertSame('hello', (string) $variableName);
    }

    // Tests for toCamelCase()

    public function testToCamelCaseWithHyphenatedString(): void
    {
        $variableName = new VariableName('my-test-string');

        $result = $variableName->toCamelCase();

        $this->assertSame('MyTestString', $result->toString());
    }

    public function testToCamelCaseWithSingleWord(): void
    {
        $variableName = new VariableName('hello');

        $result = $variableName->toCamelCase();

        $this->assertSame('Hello', $result->toString());
    }

    public function testToCamelCaseWithEmptyString(): void
    {
        $variableName = new VariableName('');

        $result = $variableName->toCamelCase();

        $this->assertSame('', $result->toString());
    }

    public function testToCamelCaseWithMultipleHyphens(): void
    {
        $variableName = new VariableName('a-b-c-d');

        $result = $variableName->toCamelCase();

        $this->assertSame('ABCD', $result->toString());
    }

    public function testToCamelCaseWithoutCapitalize(): void
    {
        $variableName = new VariableName('my-test-string');

        $result = $variableName->toCamelCase(false);

        // Note: The implementation capitalizes all words anyway
        $this->assertSame('MyTestString', $result->toString());
    }

    public function testToCamelCasePreservesAlreadyCamelCase(): void
    {
        $variableName = new VariableName('MyString');

        $result = $variableName->toCamelCase();

        $this->assertSame('MyString', $result->toString());
    }

    public function testToCamelCaseReturnsChainableInstance(): void
    {
        $variableName = new VariableName('test');

        $result = $variableName->toCamelCase();

        $this->assertInstanceOf(VariableName::class, $result);
    }

    // Tests for colonsToClassName()

    public function testColonsToClassNameWithSinglePart(): void
    {
        $variableName = new VariableName('myclass');

        $result = $variableName->colonsToClassName();

        $this->assertSame('Myclass', $result->toString());
    }

    public function testColonsToClassNameWithTwoParts(): void
    {
        $variableName = new VariableName('my-module:my-class');

        $result = $variableName->colonsToClassName();

        $this->assertSame('MyModule\\MyClass', $result->toString());
    }

    public function testColonsToClassNameWithThreeParts(): void
    {
        $variableName = new VariableName('vendor:package:class-name');

        $result = $variableName->colonsToClassName();

        $this->assertSame('Vendor\\Package\\ClassName', $result->toString());
    }

    public function testColonsToClassNameWithEmptyString(): void
    {
        $variableName = new VariableName('');

        $result = $variableName->colonsToClassName();

        $this->assertSame('', $result->toString());
    }

    public function testColonsToClassNameReturnsChainableInstance(): void
    {
        $variableName = new VariableName('test');

        $result = $variableName->colonsToClassName();

        $this->assertInstanceOf(VariableName::class, $result);
    }

    // Tests for chaining

    public function testMethodChaining(): void
    {
        $variableName = new VariableName('my-test');

        $result = $variableName->toCamelCase()->toString();

        $this->assertSame('MyTest', $result);
    }
}
