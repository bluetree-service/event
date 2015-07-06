<?php

namespace ClassEvent\Event\Base\Interfaces;

interface EventDispatcherInterface
{
    public function __construct(array $options = [], $events = []);
    public function getEventObject($eventName);
    public function triggerEvent($name, $data = []);
    public function readEventConfiguration($configuration, $type);
    public function logEvent();
    public function getErrors();
    public function hasErrors();
    public function clearErrors();
    public function addError(\Exception $e);
    public function setEventConfiguration(array $config);
    public function getEventConfiguration();
    public function getAllEvents();
    public function addEventListener($eventName, array $listeners);
}
