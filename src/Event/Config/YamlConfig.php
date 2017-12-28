<?php

namespace BlueEvent\Event\Config;

use Zend\Config\Reader\Yaml;

class YamlConfig implements ConfigReader
{
    /**
     * @param string $path
     * @return array
     */
    public function readConfig($path)
    {
        $reader = new Yaml(['Spyc','YAMLLoadString']);
        return $reader->fromFile($path);
    }
}
