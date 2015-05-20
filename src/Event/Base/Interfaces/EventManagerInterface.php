<?php

namespace ClassEvent\Event\Base\Interfaces;

interface EventManagerInterface
{
    public function __construct($configurationPath);
    public function getEventObject($eventName);
    public function getEventListeners($name);
    public function callFunction($listener, array $data, EventInterface $event);
    public function readEventConfiguration($configuration, $type);
    public function logEvent();
    public function getErrors();
    public function hasErrors();
    public function clearErrors();
    public function addError(\Exception $e);
    public function setEventConfiguration($configuration, $type);
}
