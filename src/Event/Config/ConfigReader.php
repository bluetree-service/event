<?php

declare(strict_types=1);

namespace BlueEvent\Event\Config;

interface ConfigReader
{
    /**
     * @param string $path
     * @return array
     */
    public function readConfig(string $path): array;
}
