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
 * Interface of a transformer configuration row. A transformer configuration row configures a single property mapping
 * between two data instances (objects or arrays).
 */
interface ConfigurationRowInterface
{
    /**
     * @return int|string The property name of the source object or the key of the source array.
     */
    public function getSourcePropertyName(): int|string;

    /**
     * @return int|string The property name of the target object or the key of the target array.
     */
    public function getTargetPropertyName(): int|string;

    /**
     * @return bool|callable The callable is applied on the source property value and the result is assigned to the
     * target property on calling transform. If true the identity mapping is used instead of a callable. If false the
     * mapping is excluded.
     */
    public function getTransform(): bool|callable;

    /**
     * @return bool|callable The callable is applied on the target property value and the result is assigned to the
     * source property on calling reverse transform. If true the identity mapping is used instead of a callable. If
     * false the mapping is excluded.
     */
    public function getReverseTransform(): bool|callable;
}
