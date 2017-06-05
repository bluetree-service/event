<?php

namespace BlueEvent\Event\Base\Interfaces;

interface EventInterface
{
    public function __construct($eventName, array $parameters);
    public static function getLaunchCount();
    public function isPropagationStopped();
    public function stopPropagation();
    public function getEventCode();
    public function getEventParameters();
}
