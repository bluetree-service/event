<?php

/**
 * @author Michał Adamiak <michal.adamiak@orba.co>
 * @copyright Copyright (C) 2026 Orba Sp. z o.o. (http://orba.pl)
 */

declare(strict_types=1);

namespace BlueEvent\Event\Parallel;

use BlueEvent\Event\Base\Interfaces\EventInterface;

class RabbitListener implements RabbitListenerInterface
{
    /**
     * @var RabbitQueue
     */
    protected RabbitQueue $rabbit;

    /**
     * @throws \JsonException
     */
    public function trigger(EventInterface $event): void
    {
        $data = [
            'eventName' => $event->getEventCode(),
            'parameters' => $event->getEventParameters(),
            'count' => $event->getLaunchCount(),
        ];
        $this->rabbit->publish('', $data);
    }

    public function setRabbitConnection(RabbitQueue $rabbit): void
    {
        $this->rabbit = $rabbit;
    }
}
