<?php

namespace DI\Definition\Resolver;

use DI\Definition\Definition;
use DI\Definition\Exception\DefinitionException;
use DI\Proxy\ProxyFactory;
use Interop\Container\ContainerInterface;

/**
 * Dispatches to more specific resolvers.
 *
 * Dynamic dispatch pattern.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ResolverDispatcher implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ProxyFactory
     */
    private $proxyFactory;

    private $valueResolver;
    private $arrayResolver;
    private $factoryResolver;
    private $decoratorResolver;
    private $aliasResolver;
    private $objectResolver;
    private $instanceResolver;
    private $envVariableResolver;
    private $stringResolver;
    private $dotNotationResolver;

    public function __construct(ContainerInterface $container, ProxyFactory $proxyFactory)
    {
        $this->container = $container;
        $this->proxyFactory = $proxyFactory;
    }

    /**
     * Resolve a definition to a value.
     *
     * @param Definition $definition Object that defines how the value should be obtained.
     * @param array      $parameters Optional parameters to use to build the entry.
     *
     * @throws DefinitionException If the definition cannot be resolved.
     *
     * @return mixed Value obtained from the definition.
     */
    public function resolve(Definition $definition, array $parameters = [])
    {
        $definitionResolver = $this->getDefinitionResolver($definition);

        return $definitionResolver->resolve($definition, $parameters);
    }

    /**
     * Check if a definition can be resolved.
     *
     * @param Definition $definition Object that defines how the value should be obtained.
     * @param array      $parameters Optional parameters to use to build the entry.
     *
     * @return bool
     */
    public function isResolvable(Definition $definition, array $parameters = [])
    {
        $definitionResolver = $this->getDefinitionResolver($definition);

        return $definitionResolver->isResolvable($definition, $parameters);
    }

    /**
     * Returns a resolver capable of handling the given definition.
     *
     * @param Definition $definition
     *
     * @throws \RuntimeException No definition resolver was found for this type of definition.
     * @return DefinitionResolver
     */
    private function getDefinitionResolver(Definition $definition)
    {
        switch (true) {
            case ($definition instanceof \DI\Definition\ObjectDefinition):
                if (! $this->objectResolver) {
                    $this->objectResolver = new ObjectCreator($this, $this->proxyFactory);
                }
                return $this->objectResolver;
            case ($definition instanceof \DI\Definition\ValueDefinition):
                if (! $this->valueResolver) {
                    $this->valueResolver = new ValueResolver();
                }
                return $this->valueResolver;
            case ($definition instanceof \DI\Definition\AliasDefinition):
                if (! $this->aliasResolver) {
                    $this->aliasResolver = new AliasResolver($this->container);
                }
                return $this->aliasResolver;
            case ($definition instanceof \DI\Definition\DecoratorDefinition):
                if (! $this->decoratorResolver) {
                    $this->decoratorResolver = new DecoratorResolver($this->container, $this);
                }
                return $this->decoratorResolver;
            case ($definition instanceof \DI\Definition\FactoryDefinition):
                if (! $this->factoryResolver) {
                    $this->factoryResolver = new FactoryResolver($this->container);
                }
                return $this->factoryResolver;
            case ($definition instanceof \DI\Definition\ArrayDefinition):
                if (! $this->arrayResolver) {
                    $this->arrayResolver = new ArrayResolver($this);
                }
                return $this->arrayResolver;
            case ($definition instanceof \DI\Definition\EnvironmentVariableDefinition):
                if (! $this->envVariableResolver) {
                    $this->envVariableResolver = new EnvironmentVariableResolver($this);
                }
                return $this->envVariableResolver;
            case ($definition instanceof \DI\Definition\StringDefinition):
                if (! $this->stringResolver) {
                    $this->stringResolver = new StringResolver($this->container);
                }
                return $this->stringResolver;
            case ($definition instanceof \DI\Definition\InstanceDefinition):
                if (! $this->instanceResolver) {
                    $this->instanceResolver = new InstanceInjector($this, $this->proxyFactory);
                }
                return $this->instanceResolver;
            case ($definition instanceof \DI\Definition\DotNotationDefinition):
                if (! $this->dotNotationResolver) {
                    $this->dotNotationResolver = new DotNotationResolver($this->container);
                }
                return $this->dotNotationResolver;
            default:
                throw new \RuntimeException('No definition resolver was configured for definition of type ' . get_class($definition));
        }
    }
}
