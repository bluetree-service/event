<?php

namespace BlueEvent\Event\Base\Interfaces;

interface EventDispatcherInterface
{
    public function __construct(array $options = [], $events = []);
    public function getEventObject($eventName);
    public function triggerEvent($name, array $data = []);
    public function readEventConfiguration($configuration, $type);
    public function logEvent();
    public function getErrors();
    public function hasErrors();
    public function clearErrors();
    public function getAllEventsToLog();
    public function isLogAllEventsEnabled();
    public function logAllEvents();
    public function isLogEnabled();
    public function disableEventLog();
    public function enableEventLog();
    public function setEventConfiguration(array $config);
    public function getEventConfiguration();
    public function getAllEvents();
    public function addEventListener($eventName, array $listeners);
}
