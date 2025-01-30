<?php

declare(strict_types=1);

namespace DI\Test\UnitTest\Definition\Source;

use DI\Definition\Source\DefinitionGlob;
use DI\Definition\ValueDefinition;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \DI\Definition\Source\DefinitionGlob
 */
class DefinitionGlobTest extends TestCase
{
    /**
     * @test
     */
    public function should_load_definitions_from_glob()
    {
        $pattern = __DIR__ . '/*/definitions.php';
        $source = new DefinitionGlob($pattern);

        $class = new ReflectionClass(DefinitionGlob::class);
        $property = $class->getProperty('sourceChain');
        $property->setAccessible(true);
        $sourceChain = $property->getValue($source);
        // sources are not initialized (and files are not read) before getting definitions
        $this->assertNull($sourceChain);

        $definitions = $source->getDefinitions();
        $this->assertCount(2, $definitions);

        /** @var ValueDefinition $definition */
        $definition = $definitions['foo'];
        $this->assertInstanceOf(ValueDefinition::class, $definition);
        $this->assertEquals('bar', $definition->getValue());
        $this->assertIsString($definition->getValue());
    }

    /**
     * @test
     */
    public function empty_definitions_for_pattern_not_matching_any_files()
    {
        $pattern = __DIR__ . '/*/no-definitions-here.php';
        $source = new DefinitionGlob($pattern);

        $definitions = $source->getDefinitions();
        $this->assertCount(0, $definitions);
    }
}
