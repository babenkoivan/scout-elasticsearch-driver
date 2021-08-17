<?php

namespace ScoutElastic;

use Illuminate\Support\Str;
use Laravel\Scout\Builder;
use ReflectionClass;

trait ScopesModelHandleable
{
    public function initializeScopesModelHandleable()
    {
        $this->handleScopeMethods();
    }

    private function handleScopeMethods()
    {
        $context = $this;
        $reflectionClass = new ReflectionClass(self::class);

        if ($reflectionClass->hasProperty('scopesElastic')) {
            foreach ($this->scopesElastic as $method) {
                if ($reflectionClass->hasMethod($method)) {
                    $refMethod = $reflectionClass->getMethod($method);
                    $method = Str::of($method)->replaceFirst('scope', '')->camel()->__toString();

                    Builder::macro($method, function (...$args) use ($context, $refMethod) {
                        if (Str::is('scope*', $refMethod->getName())) {
                            $args[] = $this;
                            $args = array_reverse($args);

                            call_user_func_array([$context, $refMethod->getName()], $args);
                        }
                    });
                }
            }
        }
    }
}
