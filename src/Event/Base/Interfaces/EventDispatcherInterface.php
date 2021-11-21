<?php

/**
 * Event Dispatcher Class Interface
 *
 * @package     BlueEvent
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 */

declare(strict_types=1);

namespace BlueEvent\Event\Base\Interfaces;

interface EventDispatcherInterface
{
    public function __construct(array $options = []);
    public function triggerEvent(string $name, array $data = []);
    public function readEventConfiguration(string $configuration, string $type);
    public function logEvent();
    public function getErrors();
    public function hasErrors();
    public function clearErrors();
    public function getAllEventsToLog();
    public function logAllEvents();
    public function getConfiguration();
    public function setEventLog($logEvents);
    public function setEventConfiguration(array $events);
    public function getEventConfiguration();
    public function addEventListener(string $eventName, array $listeners);
}
