<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * transform component
 *
 * @package earc/cast
 * @link https://github.com/Koudela/eArc-transform/
 * @copyright Copyright (c) 2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\Transform\Util;

use eArc\Transform\Configuration;
use eArc\Transform\Transformer;
use ReflectionClass;
use ReflectionException;

class ConfigurationFactory
{
    protected const IS_NOT_OBJECT = '!object';

    protected array $configurations = [];

    public function registerConfiguration(string|null $sourceClass, string|null $targetClass, Configuration $configuration): static
    {
        $this->configurations[$sourceClass ?? static::IS_NOT_OBJECT][$targetClass ?? static::IS_NOT_OBJECT] = $configuration;

        return $this;
    }

    public function unregisterConfiguration(string|null $sourceClass, string|null $targetClass): static
    {
        unset($this->configurations[$sourceClass ?? static::IS_NOT_OBJECT][$targetClass ?? static::IS_NOT_OBJECT]);

        return $this;
    }

    public function buildCanonicalConfig(object|array $source, object|array $target): Configuration
    {
        return new Configuration($this->getCanonicalConfig($source, $target));
    }

    public function getCanonicalConfig(object|array $source, object|array $target): array
    {
        $properties = [];

        $targetReflection = is_object($target) ? new ReflectionClass($target) : null;

        if (is_array($source)) {
            foreach (array_keys($source) as $propertyName) {
                try {
                    $properties[] = $this->getCanonicalConfigRow(
                        $propertyName,
                        $source[$propertyName],
                        $this->getPropertyValue($targetReflection, $target, $propertyName)
                    );
                } catch (ReflectionException $exception) {
                    unset($exception);
                }
            }

            return $properties;
        }

        $source = new ReflectionClass($source);

        do {
            foreach ($source->getProperties() as $property) {
                $property->setAccessible(true);
                try {
                    $properties[] = $this->getCanonicalConfigRow(
                        $property->getName(),
                        $property->getDefaultValue(),
                        $this->getPropertyValue($targetReflection, $target, $property->getName())
                    );
                } catch (ReflectionException $exception) {
                    unset($exception);
                }
            }
        } while ($source = $source->getParentClass());

        return $properties;
    }

    /**
     * @param ReflectionClass|null $reflection
     * @param object|array $data
     * @param string $propertyName
     * @return mixed
     *
     * @throws ReflectionException
     */
    protected function getPropertyValue(ReflectionClass|null $reflection, object|array $data, string $propertyName): mixed
    {
        if (is_null($reflection)) {
            return $data[$propertyName];
        }

        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($data);
    }

    protected function getCanonicalConfigRow(string $propertyName, $sourcePropertyValue, $targetPropertyValue): array
    {
        if (!is_object($sourcePropertyValue) && !is_array($sourcePropertyValue)
            || !is_object($targetPropertyValue) && !is_array($targetPropertyValue)
            || !is_object($sourcePropertyValue) && !is_object($targetPropertyValue)
        ) {
            return [$propertyName];
        }

        $sourceClass = get_class($sourcePropertyValue) ?: static::IS_NOT_OBJECT;
        $targetClass = get_class($targetPropertyValue) ?: static::IS_NOT_OBJECT;

        $transformer = isset($this->configurations[$sourceClass][$targetClass]) ? new Transformer($this->configurations[$sourceClass][$targetClass]) : null;

        return [
            $propertyName,
            null,
            is_null($transformer) ? null : function($source, $target) use ($transformer) {
                $transformer->transform($source, $target);

                return $target;
            },
            is_null($transformer) ? null : function($target, $source) use ($transformer) {
                $transformer->reverseTransform($target, $source);

                return $target;
            }
        ];
    }
}
