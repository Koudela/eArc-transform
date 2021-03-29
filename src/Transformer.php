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

namespace eArc\Transform;

use eArc\Transform\exceptions\PropertyMissingException;
use eArc\Transform\interfaces\ConfigurationInterface;
use eArc\Transform\interfaces\ConfigurationRowInterface;
use ReflectionClass;

class Transformer
{
    protected ConfigurationInterface $config;

    public function __construct(ConfigurationInterface $transformerConfiguration)
    {
        $this->config = $transformerConfiguration;
    }

    public function transform(array|object $source, array|object &$target, bool $strict = true): void
    {
        $this->processTransformation($source, $target, $strict, false);
    }

    public function reverseTransform(array|object $target, array|object &$source, bool $strict = true): void
    {
        $this->processTransformation($target, $source, $strict, true);
    }

    protected function processTransformation(array|object $source, array|object &$target, bool $strict, bool $isReverse): void
    {
        if (is_array($source)) {
            if (is_array($target)) {
                $this->transformArrayToArray($source, $target, $strict, $isReverse);
            } else {
                $this->transformArrayToObject($source, $target, $strict, $isReverse);
            }
        } else {
            if (is_array($target)) {
                $this->transformObjectToArray($source, $target, $strict, $isReverse);
            } else {
                $this->transformObjectToObject($source, $target, $strict, $isReverse);
            }
        }
    }

    protected function transformArrayToArray(array $source, array &$target, bool $strict, bool $isReverse): void
    {
        /** @var ConfigurationRowInterface $config */
        foreach ($this->config as $config) {
            if ($transform = $isReverse ? $config->getReverseTransform() : $config->getTransform()) {
                $sourcePropertyName = $isReverse ? $config->getTargetPropertyName() : $config->getSourcePropertyName();
                if (!array_key_exists($sourcePropertyName, $source)) {
                    if ($strict) {
                        throw new PropertyMissingException();
                    }

                    continue;
                }
                $targetPropertyName = $isReverse ? $config->getSourcePropertyName() : $config->getTargetPropertyName();

                $target[$targetPropertyName] =
                    true === $transform ?
                        $source[$sourcePropertyName] :
                        $transform($source[$sourcePropertyName], $target[$targetPropertyName]);
            }
        }
    }

    protected function transformObjectToArray(object $source, array &$target, bool $strict, bool $isReverse = false): void
    {
        $sourceReflection = new ReflectionClass($source);

        /** @var ConfigurationRowInterface $config */
        foreach ($this->config as $config) {
            if ($transform = $isReverse ? $config->getReverseTransform() : $config->getTransform()) {
                $sourcePropertyName = $isReverse ? $config->getTargetPropertyName() : $config->getSourcePropertyName();
                try {
                    $targetPropertyName = $isReverse ? $config->getSourcePropertyName() : $config->getTargetPropertyName();
                    $target[$targetPropertyName] =
                        true === $transform ?
                            static::getValue($sourceReflection, $sourcePropertyName) :
                            $transform(static::getValue($sourceReflection, $sourcePropertyName), $target[$targetPropertyName]);
                } catch (PropertyMissingException $exception) {
                    if ($strict) {
                        throw $exception;
                    }
                }
            }
        }
    }

    protected function transformArrayToObject(array $source, object $target, bool $strict, bool $isReverse): void
    {
        $targetReflection = new ReflectionClass($target);

        /** @var ConfigurationRowInterface $config */
        foreach ($this->config as $config) {
            if ($transform = $isReverse ? $config->getReverseTransform() : $config->getTransform()) {
                $sourcePropertyName = $isReverse ? $config->getTargetPropertyName() : $config->getSourcePropertyName();
                if (!array_key_exists($sourcePropertyName, $source)) {
                    if ($strict) {
                        throw new PropertyMissingException();
                    }

                    continue;
                }
                try {
                    $targetPropertyName = $isReverse ? $config->getSourcePropertyName() : $config->getTargetPropertyName();
                    $value = true === $transform ?
                        $source[$sourcePropertyName] :
                        $transform($source[$sourcePropertyName], static::getValue($targetReflection, $targetPropertyName));
                    static::setValue($targetReflection, $targetPropertyName, $target, $value);
                } catch (PropertyMissingException $exception) {
                    if ($strict) {
                        throw $exception;
                    }
                }
            }
        }
    }

    protected function transformObjectToObject(object $source, object $target, bool $strict, bool $isReverse): void
    {
        $targetReflection = new ReflectionClass($target);
        $sourceReflection = new ReflectionClass($source);

        /** @var ConfigurationRowInterface $config */
        foreach ($this->config as $config) {
            if ($transform = $isReverse ? $config->getReverseTransform() : $config->getTransform()) {
                $sourcePropertyName = $isReverse ? $config->getTargetPropertyName() : $config->getSourcePropertyName();
                try {
                    $targetPropertyName = $isReverse ? $config->getSourcePropertyName() : $config->getTargetPropertyName();
                    $value = true === $transform ?
                        static::getValue($sourceReflection, $sourcePropertyName) :
                        $transform(static::getValue($sourceReflection, $sourcePropertyName), static::getValue($targetReflection, $targetPropertyName));
                    static::setValue($targetReflection, $targetPropertyName, $target, $value);
                } catch (PropertyMissingException $exception) {
                    if ($strict) {
                        throw $exception;
                    }
                }
            }
        }
    }

    protected static function getValue(ReflectionClass $sourceReflection, string $propertyName): mixed
    {
        while (!$sourceReflection->hasProperty($propertyName)) {
            if (!$sourceReflection = $sourceReflection->getParentClass()) {
                throw new PropertyMissingException();
            }
        }

        $property = $sourceReflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getDefaultValue();
    }

    protected static function setValue(ReflectionClass $targetReflection, string $propertyName, object $target, $value): void
    {
        while (!$targetReflection->hasProperty($propertyName)) {
            if (!$targetReflection = $targetReflection->getParentClass()) {
                throw new PropertyMissingException();
            }
        }

        $property = $targetReflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($target, $value);
    }
}
