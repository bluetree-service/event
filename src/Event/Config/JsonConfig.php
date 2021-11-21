<?php

declare(strict_types=1);

namespace BlueEvent\Event\Config;

use Laminas\Config\Reader\Json;

class JsonConfig implements ConfigReader
{
    /**
     * @param string $path
     * @return array
     */
    public function readConfig(string $path): array
    {
        $reader = new Json();
        return $reader->fromFile($path);
    }
}
