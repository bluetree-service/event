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
6. **log_object** - Namespace of class to handle log, default is: `ClassEvent\Event\Log\Log` (Must be instance of `ClassEvent\Event\Log\LogInterface`)

## Configuration via methods
Some of configuration options can be changed by some special methods. There are
three categories of that methods, first to turn on option, seccond to turn off
and last to check that option is on or off.

* **enableEventLog** - Set *log_events* option to `true`
* **disableEventLog** - Set *log_events* option to `false`
* **isLogEnabled** - Return value of *log_events* option
* **logAllEvents** - Set *log_all_events* option to given parameter (will be split into separate methods in future)
* **isLogAllEventsEnabled** - Return value of *log_all_events* option

## Event configuration



getEventConfiguration
