<?php

namespace Config;

class ArrayConfig implements ConfigReader
{
    /**
     * @param string $path
     * @return mixed
     */
    public function readConfig($path)
    {
        return include $path;
    }
}
