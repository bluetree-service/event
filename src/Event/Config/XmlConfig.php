<?php

declare(strict_types=1);

namespace BlueEvent\Event\Config;

use Laminas\Config\Reader\Xml;

class XmlConfig implements ConfigReader
{
    /**
     * @param string $path
     * @return array
     */
    public function readConfig(string $path): array
    {
        return (new Xml())->fromFile($path);
    }
}
