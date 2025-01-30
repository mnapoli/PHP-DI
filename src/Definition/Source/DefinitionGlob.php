<?php

declare(strict_types=1);

namespace DI\Definition\Source;

/**
 * Reads DI definitions from files matching glob pattern.
 */
class DefinitionGlob implements DefinitionSource
{
    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * Glob pattern to files containing definitions.
     * @var string
     */
    private $pattern;

    /**
     * @var Autowiring
     */
    private $autowiring;

    /**
     * @var SourceChain
     */
    private $sourceChain;

    /**
     * @param string $pattern Glob pattern to files containing definitions
     */
    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function setAutowiring(Autowiring $autowiring)
    {
        $this->autowiring = $autowiring;
    }

    public function getDefinition(string $name, int $startIndex = 0)
    {
        $this->initialize();

        return $this->sourceChain->getDefinition($name, $startIndex);
    }

    public function getDefinitions() : array
    {
        $this->initialize();

        return $this->sourceChain->getDefinitions();
    }

    /**
     * Lazy-loading of the definitions.
     */
    private function initialize()
    {
        if ($this->initialized === true) {
            return;
        }

        $paths = glob($this->pattern, \GLOB_BRACE);
        $sources = array_map(function ($path) {
            return new DefinitionFile($path, $this->autowiring);
        }, $paths);
        $this->sourceChain = new SourceChain($sources);

        $this->initialized = true;
    }
}
