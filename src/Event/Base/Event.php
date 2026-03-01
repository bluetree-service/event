<?php

/**
 * Event Object Class
 *
 * @package     BlueEvent
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 */

declare(strict_types=1);

namespace BlueEvent\Event\Base;

use BlueEvent\Event\Base\Interfaces\EventInterface;

abstract class Event implements EventInterface
{
    /**
     * @var array
     */
    protected array $exchanger = [];

    /**
     * store information how many times event object was called
     *
     * @var int
     */
    protected static int $launchCount = 0;

    /**
     * store information that event propagation is stopped or not
     *
     * @var bool
     */
    protected bool $propagationStopped = false;

    /**
     * @var array
     */
    protected array $eventParameters = [];

    /**
     * @var string
     */
    protected string $eventName = '';

    /**
     * create event instance
     *
     * @param string $eventName
     * @param array $parameters
     */
    public function __construct(string $eventName, array $parameters)
    {
        $this->eventName = $eventName;
        $this->eventParameters = $parameters;

        self::$launchCount++;
    }

    /**
     * return number how many times event was called
     *
     * @return int
     */
    public static function getLaunchCount(): int
    {
        return self::$launchCount;
    }

    /**
     * @return void
     */
    public static function resetLaunchCount(): void
    {
        self::$launchCount = 0;
    }

    /**
     * return information that event propagation is stopped or not
     *
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * allow to stop event propagation
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * @return string
     */
    public function getEventCode(): string
    {
        return $this->eventName;
    }

    /**
     * @return array
     */
    public function getEventParameters(): array
    {
        return $this->eventParameters;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setExchanger(string $name, mixed $value): void
    {
        $this->exchanger[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getExchanger(string $name): mixed
    {
        return $this->exchanger[$name] ?? null;
    }
}
