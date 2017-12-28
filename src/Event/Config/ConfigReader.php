<?php

namespace BlueEvent\Event\Config;

interface ConfigReader
{
    /**
     * @param string $path
     * @return array
     */
    public function readConfig($path);
}
