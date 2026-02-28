<?php

namespace BlueEvent\Event\Parallel;

use BlueEvent\Event\Base\Interfaces\EventInterface;

interface RabbitListenerInterface
{
    /**
     * @param EventInterface $event
     * @return void
     */
    public function trigger(EventInterface $event): void;

    /**
     * @param RabbitQueue $rabbit
     * @return void
     */
    public function setRabbitConnection(RabbitQueue $rabbit): void;
}
