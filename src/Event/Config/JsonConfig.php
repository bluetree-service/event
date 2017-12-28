<?php

namespace BlueEvent\Event\Config;

use Zend\Config\Reader\Json;

class JsonConfig implements ConfigReader
{
    /**
     * @param string $path
     * @return array
     */
    public function readConfig($path)
    {
        $reader = new Json;
        return $reader->fromFile($path);
    }
}
