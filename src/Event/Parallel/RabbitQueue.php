<?php

/**
 * @author Michał Adamiak <michal.adamiak@orba.co>
 * @copyright Copyright (C) 2026 Orba Sp. z o.o. (http://orba.pl)
 */

declare(strict_types=1);

namespace BlueEvent\Event\Parallel;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitQueue
{
    private AMQPStreamConnection $connection;
    private \PhpAmqpLib\Channel\AMQPChannel $channel;

    /**
     * @throws \Exception
     */
    public function __construct(
        readonly string $host,
        readonly int $port,
        readonly string $username,
        readonly string $password,
        readonly string $vhost,
        private readonly string $exchange,
        private readonly string $exchangeType,
        private readonly string $queue
    ) {
        $this->connection = new AMQPStreamConnection($host, $port, $username, $password, $vhost);
        $this->channel = $this->connection->channel();

        $this->channel->exchange_declare($this->exchange, $this->exchangeType, false, true, false);
        $this->channel->queue_declare($this->queue, false, true, false, false);
        $this->channel->queue_bind($this->queue, $this->exchange);
    }

    /**
     * @throws \JsonException
     */
    public function publish(string $routingKey, array $payload): void
    {
        $body = \json_encode($payload, JSON_THROW_ON_ERROR);

        $message = new AMQPMessage($body, [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);

        $this->channel->basic_publish($message, $this->exchange, $routingKey);
    }

    /**
     * @throws \Exception
     */
    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        $this->close();
    }
}
