<?php
namespace DI\Test\IntegrationTest\Definitions\AutowireDefinition;

class VariadicMethodInjection
{
    public $values;

    public function set(...$values)
    {
        $this->values = $values;
    }
}