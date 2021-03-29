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

use eArc\Transform\helper\ConfigurationRow;
use eArc\Transform\interfaces\ConfigurationInterface;
use eArc\Transform\interfaces\ConfigurationRowInterface;

class Configuration implements ConfigurationInterface
{
    protected array $config = [];

    public function __construct(ConfigurationInterface|array $config = [])
    {
        foreach ($config as $conf) {
            if ($conf instanceof ConfigurationRowInterface) {
                $this->config['_'.$conf->getSourcePropertyName()] = $conf;
            } else {
                $this->add(
                    $conf[0],
                    array_key_exists(1, $conf) ? $conf[1] : null,
                    array_key_exists(2, $conf) ? $conf[2] : true,
                    array_key_exists(3, $conf) ? $conf[3] : false
                );
            }
        }
    }

    public function add(int|string $sourcePropertyName, int|string|null $targetPropertyName = null, bool|callable $transform = true, bool|callable $reverseTransform = false): static
    {
        $this->config['_'.$sourcePropertyName] = new ConfigurationRow(
            $sourcePropertyName,
            $targetPropertyName,
            $transform,
            $reverseTransform
        );

        return $this;
    }

    public function getIterator(): iterable
    {
        foreach ($this->config as $config) {
            yield $config;
        }
    }
}
