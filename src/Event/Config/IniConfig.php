<?php

declare(strict_types=1);

namespace BlueEvent\Event\Config;

use Laminas\Config\Reader\Ini;

class IniConfig implements ConfigReader
{
    /**
     * @param string $path
     * @return array
     */
    public function readConfig(string $path): array
    {
        $reader = new Ini();
        return $reader->fromFile($path);
    }
}
