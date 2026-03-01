<?php

/**
 * @author Michał Adamiak <michal.adamiak@orba.co>
 * @copyright Copyright (C) 2026 Orba Sp. z o.o. (http://orba.pl)
 */

declare(strict_types=1);

namespace BlueEvent\Event\Parallel;

use BlueEvent\Event\Base\Interfaces\EventInterface;
use React\ChildProcess\Process;
use React\EventLoop\Loop;

class ReactListener implements ReactListenerInterface
{
    /**
     * @param string $command
     */
    public function __construct(private readonly string $command) {}

    /**
     * @param EventInterface $event
     * @return void
     */
    public function trigger(EventInterface $event): void
    {
        $data = [
            'eventName' => $event->getEventCode(),
            'parameters' => $event->getEventParameters(),
            'count' => $event->getLaunchCount(),
        ];

        $loop = Loop::get();

        $process = new Process($this->command . ' ' . escapeshellarg(serialize($data)));
        $process->start($loop);

        $loop?->run();
    }
}
