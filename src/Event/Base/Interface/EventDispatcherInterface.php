<?php

namespace ClassEvent\Event\Base;

interface EventDispatcherInterface
{
    public function __construct($configurationPath);
    public function getEventObject($eventName);
    public function getEventListeners($name);
    public function callFunction($listener, array $data, EventInterface $event);
    public function readEventConfiguration($configuration);
    public function logEvent();
    public function getErrors();
    public function hasErrors();
    public function clearErrors();
    public function addError(\Exception $e);
    public function setEventConfiguration($configuration);
}
