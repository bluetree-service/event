# React usage
Using events in React PHP is the same as in basic usage. You need to provide a specific listener for React PHP which is
`BlueEvent\Event\Parallel\ReactListener`. Also, you can create your own listener, just remember that it needs to implement
`BlueEvent\Event\Parallel\ReactListenerInterface`.  
In constructor of ReactListener you need to provide a string that contains code that will execute another PHP script in 
a separate process.  
Event data will pass to that script as a serialized string by CLI argument. It will always be the last one, so you can use
some other arguments before that.

```php
$eventDispatcher = new EventDispatcher;
$eventDispatcher->setEventConfiguration([
    'event_code' => [
        'object'    => 'BlueEvent\Event\BaseEvent',
        'listeners' => [
            new ReactListener("php path/to/your/script.php"),
        ]
    ]
]);
```

# Rabbit MQ usage
To use Rabbit MQ you need to set up a listener that will send a message to Rabbit MQ server. To do that you can use
`BlueEvent\Event\Parallel\RabbitListener` class. It needs to be set up in the same way as ReactListener. The only
difference is that you don't need to provide any argument in the constructor.  
Also you need to enable RabbitMQ connection in `EventDispatcher` constructor by setting specific parameter. Optionally, 
you can provide connection parameters as an array. Configuration is merged with default ones, so you don't need to
provide all parameters, just the ones that you want to change.  
Also you can create your own listener, just remember that it needs to implement `BlueEvent\Event\Parallel\RabbitListenerInterface`.

```php
$eventDispatcher = new EventDispatcher(
    [
        'rabbitmq' => [
            'enabled' => false,
            'host' => 'localhost',
            'port' => 5672,
            'username' => 'guest',
            'password' => 'guest',
            'vhost' => '/',
            'exchange' => 'event_dispatcher',
            'exchange_type' => 'direct',
            'queue' => 'event_dispatcher_queue',
        ],
    ]
);
```

```php
$eventDispatcher = new EventDispatcher;
$eventDispatcher->setEventConfiguration([
    'event_code' => [
        'object'    => 'BlueEvent\Event\BaseEvent',
        'listeners' => [
            new RabbitListener()
        ]
    ]
]);
```
