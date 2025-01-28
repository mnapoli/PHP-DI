<?php

declare(strict_types=1);

namespace DI\Definition;

interface Castable {
    public function asInt(): static;
    public function asFloat(): static;
    public function asBool(): static;

    public function getCast(): string;
}
