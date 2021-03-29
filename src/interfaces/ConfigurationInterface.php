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

namespace eArc\Transform\interfaces;

use IteratorAggregate;

/**
 * Interface of a transformer configuration. A transformer configuration tells the transformer how to map values between
 * two data instances (objects or arrays).
 */
interface ConfigurationInterface extends IteratorAggregate
{
    /**
     * Adds an transformation mapping to the transformer configuration.
     *
     * @param int|string $sourcePropertyName The property name of the source object or the key of the source array.
     * @param int|string|null $targetPropertyName The property name of the target object or the key of the target array.
     * If null $sourcePropertyName is used
     * @param bool|callable $transform The callable is applied on the source property value and the result is assigned
     * to the target property on calling transform. If true the identity mapping is used instead of a callable. If false
     * the mapping is excluded.
     * @param bool|callable $reverseTransform The callable is applied on the target property value and the result is
     * assigned to the source property on calling reverse transform. If true the identity mapping is used instead of a
     * callable. If false the mapping is excluded.
     *
     * @return static
     */
    public function add(int|string $sourcePropertyName, int|string|null $targetPropertyName = null, bool|callable $transform = true, bool|callable $reverseTransform = false): static;
}
