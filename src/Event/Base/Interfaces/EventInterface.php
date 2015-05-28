<?php

namespace ClassEvent\Event\Base\Interfaces;

interface EventInterface
{
    public function __construct();
    public static function getLaunchCount();
    public function isPropagationStopped();
    public function stopPropagation();
}
