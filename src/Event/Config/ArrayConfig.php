<?php

declare(strict_types=1);

namespace BlueEvent\Event\Config;

class ArrayConfig implements ConfigReader
{
    /**
     * @param string $path
     * @return array
     */
    public function readConfig(string $path): array
    {
        return include $path;
    }
}
