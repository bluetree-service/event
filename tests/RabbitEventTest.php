<?php

/**
 * @author Michał Adamiak <michal.adamiak@orba.co>
 * @copyright Copyright (C) 2026 Orba Sp. z o.o. (http://orba.pl)
 */

declare(strict_types=1);

namespace BlueEventTest;

use BlueEvent\Event\Base\EventDispatcher;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use BlueEvent\Event\Parallel\RabbitListener;

class RabbitEventTest extends TestCase
{
    /**
     * @throws \JsonException
     * @throws \Exception
     */
    public function testCreateEventDispatcherForRabbit(): void
    {
        $instance = $this->newObject();

        $instance->setEventConfiguration([
            'test_event' => [
                'object' => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    new RabbitListener()
                ]
            ],
            'test_event2' => [
                'object' => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    new RabbitListener()
                ]
            ],
        ]);

        $this->assertEquals(0, \BlueEvent\Event\BaseEvent::getLaunchCount());

        $instance->triggerEvent('test_event');

        $this->assertEmpty($instance->getErrors());
        $this->assertEquals(1, \BlueEvent\Event\BaseEvent::getLaunchCount());


        $message = $this->getMessage();
        $this->assertJson($message);
        $data = \json_decode($message, true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals('test_event', $data['eventName']);
        $this->assertEquals(1, $data['count']);

        $instance->triggerEvent('test_event2');

        $message = $this->getMessage(true);
        $this->assertJson($message);
        $data = \json_decode($message, true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals('test_event2', $data['eventName']);
        $this->assertEquals(2, $data['count']);
        $instance->closeRabbitConnection();
    }

    /**
     * @throws \ReflectionException
     */
    protected function newObject(): EventDispatcher
    {
        \BlueEvent\Event\BaseEvent::resetLaunchCount();

        return new EventDispatcher([
            'rabbitmq' => [
                'enabled' => true,
                'host' => 'rabbitmq82',
            ]
        ]);
    }

    /**
     * @throws \Exception
     */
    protected function getMessage(): string
    {
        $exchange = 'event_dispatcher';
        $exchangeType = 'direct';
        $queue = 'event_dispatcher_queue';

        $connection = new AMQPStreamConnection('rabbitmq82', 5672, 'guest', 'guest', '/');
        $channel = $connection->channel();

        $channel->exchange_declare($exchange, $exchangeType, false, true, false);
        $channel->queue_declare($queue, false, true, false, false);
        $channel->queue_bind($queue, $exchange);

        $res = $channel->basic_get($queue, '', true);
        $channel->basic_ack($res->get('delivery_tag'));

//        $channel->basic_consume($queue, '', false, true, false, false, function ($message) {
//            dump($message->body);
//        });

//        $channel->wait();

        $channel->close();
        $connection->close();

        return $res->getBody();
    }
}
