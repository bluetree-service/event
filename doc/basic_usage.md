# Basic usage
To use events EventDispatcher class instance must be created and give some event
configuration in constructor or special `setEventConfiguration` method. That configuration
must have event code (used for event trigger) and list of listeners that will executed
on each event trigger.

```php
$eventDispatcher = new EventDispatcher;
$eventDispatcher->setEventConfiguration([
    'event_code' => [
        'object'    => 'BlueEvent\Event\BaseEvent',
        'listeners' => [
            function ($event) {
                echo 'Listener was executed for event: ' . $event->getEventCode();
            },
        ]
    ]
]);
```

After that we can launch event trigger method to execute listeners.

```php
$eventDispatcher->triggerEvent('event_code');
```

After executing each `triggerEvent` method we her message _Listener was executed_.

### Listener
Listener is everything that is acceptable by `call_user_func` function. So listener
can be namespace and method separated by _::_, anonymous function, or array where
firs parameter is object and second method to execute.  
All listeners on list are executed after trigger event in order that is the same
as the listeners on the list.  
Each listener get only one parameter that is Event object instance that was set up in event configuration key.
All objects must be compatible with `BlueEvent\Event\Base\Interfaces` or just implement `BlueEvent\Event\Base\Event`.
If object was not specified, by default will be used `BlueEvent\Event\BaseEvent`.

```php
$eventDispatcher->triggerEvent(
    'event_code',
    [
        'parameter_one' => 'value a',
        'parameter_two' => 'value b',
    ]
);
```
### Add listener by method
Their is also possibility to set listeners via special method. To do that use
`addEventListener` method that take two attributes. First is event code and
second list of listeners.  
If EventObject don't exists for given event code than default EventObject will be
used.
