# Event Dispatcher configuration

## Basic event dispatcher configuration
Basic configuration can be in _EventDispatcher_ `__construct` as first parameter.

```php
$EventDispatcher = new EventDispatcher([
    'option_key' => 'value'
]);
```

Available options:

1. **type** - File type of event configuration, can be: `array`, `yaml`, `ini`, `xml`, `json`
2. **from_file** - Allow to load configuration from file (`true` | `false` default: `false`)
3. **log_events** - Allow to log events to log file (`true` | `false` default: `false`)
4. **log_all_events** - Allow to log all events to log file, otherwise log only specified one (`true` | `false` default: `true`)
5. **log_path** - Path to log file
6. **log_object** - Namespace of class to handle log, default is: `\SimpleLog\Log` (Must be instance of `\SimpleLog\LogInterface`)

## Configuration via methods
Some of configuration options can be changed by some special methods. There are
three categories of that methods, first to turn on option, seccond to turn off
and last to check that option is on or off.

* **enableEventLog** - Set *log_events* option to `true`
* **disableEventLog** - Set *log_events* option to `false`
* **getConfiguration** - Return `EventDispatcher` configuration or single option if key is given as value
* **logAllEvents** - Set *log_all_events* option to given parameter (will be split into separate methods in future)
* **logEvent** - Allow to log specified events

## Event configuration
To store event listeners for specified event code and object to store event statement
we use event configuration, that can be applied in constructor or specified
method `setEventConfiguration`.  
Both of them accept the same configuration structure, that is an array.  
Event configuration must have event code, as key for another array. Event code
is used to recognize event trigger and store configuration. On next level configurations
has two keys `object` (optional) and `listeners`. First one define namespace
of class that will be used to store event statement. That value is optional, when
it not defined, the will be used `BlueEvent\Event\BaseEvent`.  
Last one `listeners` define all event listeners as array. Listeners are methods
or functions (everything that can be called by `call_user_func`) that will be
launch each time when event code was triggered.  
Event configuration structure example:

```php
$config = [
    'event_code' => [
        'object'    => 'EventStatementClass',
        'listeners' => [
            'Listener::method',
            'AnotherListener::method',
            'someFunction',
        ]
    ],
    'another_event_code' => [
            'listeners' => [
                [new \Listener, 'method'],
                function ($attr, $event) {
                    //listener logic
                },
            ]
        ],
];
```

### Load configuration from file
If event configuration must be loaded from file, there are two possibilities.  
First is set `from_file` to `true`, set event configuration type by `type` and instead
second parameter in constructor paste path to event configuration file, like here:

```php
$EventDispatcher = new EventDispatcher(
    'path/to/file.php',
    [
        'from_file' => true,
        'type'      => 'array'
    ]
);
```

Second possibility is to use method `readEventConfiguration` that will load configuration
from file after create dispatcher instance. First parameter is path to event configuration
and second configuration type

```php
$EventDispatcher->readEventConfiguration(
    'path/to/file.json',
    'json'
);
```

Available configuration types are:

* **array** - load configuration from php file, set as configuration (file must return array)
* **json**
* **yaml**
* **xml** - use xml without attributes
* **ini**

### Check current event configuration
To ger current event configuration just execute `getEventConfiguration` that will
return full loaded event configuration array.
