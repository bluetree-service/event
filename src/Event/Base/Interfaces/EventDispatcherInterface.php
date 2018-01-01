<?php
/**
 * Event Dispatcher Class Interface
 *
 * @package     BlueEvent
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 */

namespace BlueEvent\Event\Base\Interfaces;

interface EventDispatcherInterface
{
    public function __construct(array $options = []);
    public function triggerEvent($name, array $data = []);
    public function readEventConfiguration($configuration, $type);
    public function logEvent();
    public function getErrors();
    public function hasErrors();
    public function clearErrors();
    public function getAllEventsToLog();
    public function logAllEvents();
    public function getConfiguration();
    public function setEventLog($logEvents);
    public function setEventConfiguration(array $config);
    public function getEventConfiguration();
    public function addEventListener($eventName, array $listeners);
}
