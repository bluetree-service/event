<?php

namespace BlueEvent\Event\Base;

use BlueEvent\Event\Base\Interfaces\EventInterface;

abstract class Event implements EventInterface
{
    /**
     * store information how many times event was called
     *
     * @var int
     */
    protected static $launchCount = 0;

    /**
     * store information that event propagation is stopped or not
     *
     * @var bool
     */
    protected $propagationStopped = false;

    /**
     * create event instance
     */
    public function __construct()
    {
        self::$launchCount++;
    }

    /**
     * return number how many times event was called
     *
     * @return int
     */
    public static function getLaunchCount()
    {
        return self::$launchCount;
    }

    /**
     * return information that event propagation is stopped or not
     *
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }

    /**
     * allow to stop event propagation
     *
     * @return $this
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
        return $this;
    }
}
