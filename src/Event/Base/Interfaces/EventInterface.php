<?php

/**
 * Event Object Class Interface
 *
 * @package     BlueEvent
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 */

declare(strict_types=1);

namespace BlueEvent\Event\Base\Interfaces;

interface EventInterface
{
    public function __construct(string $eventName, array $parameters);
    public static function getLaunchCount(): int;
    public function isPropagationStopped(): bool;
    public function stopPropagation(): void;
    public function getEventCode(): string;
    public function getEventParameters(): array;
    public function setExchanger(string $name, mixed $value): void;
    public function getExchanger(string $name): mixed;
}
