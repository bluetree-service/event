# Basic usage
To use events EventDispatcher class instance must be created and give some event
configuration in constructor or special `setEventConfiguration` method. That configuration
must have event code (used for event trigger) and list of listeners that will executed
on each event trigger.

```php
$eventDispatcher = new EventDispatcher;
$eventDispatcher->setEventConfiguration([
    'event_code' => [
        'object'    => 'ClassEvent\Event\BaseEvent',
        'listeners' => [
            function ($attr, $event) {
                echo 'Listener was executed';
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
Each listener get two parameters. First is array of additional parameters that was
given in `triggerEvent` method. Second is instance of Event object, that was given
in `object` key on event configuration or `ClassEvent\Event\BaseEvent` when that
object was not specified.  

```php
$eventDispatcher->triggerEvent(
    'event_code',
    [
        'parameter_one' => 'value a',
        'parameter_two' => 'value b',
    ]
);
```
