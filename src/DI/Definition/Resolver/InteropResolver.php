<?php

namespace DI\Definition\Resolver;

use DI\Definition\Definition;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\InteropDefinition;
use Interop\Container\ContainerInterface;
use TheCodingMachine\ServiceProvider\Registry;

/**
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InteropResolver implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DefinitionResolver
     */
    private $definitionResolver;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param DefinitionResolver $definitionResolver Used to resolve previous definitions.
     */
    public function __construct(ContainerInterface $container, DefinitionResolver $definitionResolver, Registry $registry)
    {
        $this->container = $container;
        $this->definitionResolver = $definitionResolver;
        $this->registry = $registry;
    }

    /**
     * @param InteropDefinition $definition
     *
     * {@inheritdoc}
     */
    public function resolve(Definition $definition, array $parameters = [])
    {
        $registryKey = $definition->getRegistryKey();
        $name = $definition->getName();
        $previousDefinition = $definition->getPreviousDefinition();

        $getPrevious = null;
        if ($previousDefinition instanceof Definition) {
            $getPrevious = function () use ($previousDefinition) {
                return $this->definitionResolver->resolve($previousDefinition);
            };
        }

        return $this->registry->createService($registryKey,  $name, $this->container, $getPrevious);
    }

    public function isResolvable(Definition $definition, array $parameters = [])
    {
        return true;
    }
}
