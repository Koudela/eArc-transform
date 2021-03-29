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

namespace eArc\Transform\helper;

use Closure;
use eArc\Transform\interfaces\ConfigurationRowInterface;

class ConfigurationRow implements ConfigurationRowInterface
{
    public function __construct(
        protected int|string $sourcePropertyName,
        protected int|string|null $targetPropertyName = null,
        protected bool|array|Closure $transform = true,
        protected bool|array|Closure $reverseTransform = false
    ){}

    public function getSourcePropertyName(): int|string
    {
        return $this->sourcePropertyName;
    }

    public function getTargetPropertyName(): int|string
    {
        return $this->targetPropertyName ?? $this->sourcePropertyName;
    }

    public function getTransform(): bool|callable
    {
        return $this->transform;
    }

    public function getReverseTransform(): bool|callable
    {
        return $this->reverseTransform;
    }
}
