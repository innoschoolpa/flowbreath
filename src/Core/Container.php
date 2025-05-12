<?php

namespace App\Core;

class Container
{
    private static $instances = [];

    public static function get($class)
    {
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = self::createInstance($class);
        }
        return self::$instances[$class];
    }

    private static function createInstance($class)
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $class();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            if (!$type || $type->isBuiltin()) {
                throw new \Exception("Cannot resolve parameter {$parameter->getName()} in {$class}");
            }

            $dependencyClass = $type->getName();
            $dependencies[] = self::get($dependencyClass);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    public static function register($class, $instance)
    {
        self::$instances[$class] = $instance;
    }

    public static function clear()
    {
        self::$instances = [];
    }
} 