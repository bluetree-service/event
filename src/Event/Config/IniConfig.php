<?php

namespace Config;

use Zend\Config\Reader\Ini;

class IniConfig implements ConfigReader
{
    /**
     * @param string $path
     * @return array
     */
    public function readConfig($path)
    {
        $reader = new Ini;
        return $reader->fromFile($path);
    }
}
