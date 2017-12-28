<?php

namespace BlueEvent\Event\Config;

use Zend\Config\Reader\Xml;

class XmlConfig implements ConfigReader
{
    /**
     * @param string $path
     * @return array
     */
    public function readConfig($path)
    {
        $reader = new Xml;
        return $reader->fromFile($path);
    }
}
