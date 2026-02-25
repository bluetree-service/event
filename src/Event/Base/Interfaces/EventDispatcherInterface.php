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
    public function logEvent(array $events): self;
    public function getErrors(): array;
    public function hasErrors(): bool;
    public function clearErrors(): self;
    public function getAllEventsToLog(): array;
    public function logAllEvents(bool $log): self;
    public function getConfiguration(?string $option): mixed;
    public function setEventLog(bool $logEvents): self;
    public function setEventConfiguration(array $events): self;
    public function getEventConfiguration(): array;
    public function addEventListener(string $eventName, array $listeners): self;
}
