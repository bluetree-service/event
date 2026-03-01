<?php

declare(strict_types=1);

namespace BlueEvent\Event\Parallel;

use BlueEvent\Event\Base\Interfaces\EventInterface;

interface ReactListenerInterface
{
    /**
     * @param EventInterface $event
     * @return void
     */
    public function trigger(EventInterface $event): void;
}
