<?php

namespace Config;

interface ConfigReader
{
    /**
     * @param string $path
     * @return array
     */
    public function readConfig($path);
}
