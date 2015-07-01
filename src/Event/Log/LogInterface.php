<?php

namespace ClassEvent\Event\Log;

interface LogInterface
{
    /**
     * create log message
     *
     * @param array $params
     * @return mixed
     */
    public function makeLog(array $params);
}
