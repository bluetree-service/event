<?php

declare(strict_types=1);

namespace BlueEvent\Event\Config;

use Laminas\Config\Reader\Yaml;

class YamlConfig implements ConfigReader
{
    /**
     * @param string $path
     * @return array
     */
    public function readConfig(string $path): array
    {
        $reader = new Yaml(['Spyc','YAMLLoadString']);
        return $reader->fromFile($path);
    }
}
