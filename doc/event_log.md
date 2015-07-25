# Event Log
To have better control about event triggering and executing listeners we can use
log system implemented into event dispatcher. Event dispatcher has ability to log
all events or only specified one.  
All logs by default are stored in specified log file given in configuration.  
To do that you can use `log_events` config given into `__constructor` or special
method `enableEventLog`. That allow lo log events, by default log all events.

## Log all events
Basic and simplest method is to log all events listeners. To do that you can use
`log_all_events` config given into `__constructor` or special method `logAllEvents`.  
That parameter by default is set to `true` so if log event is enabled, their will
be logged all events.

## Log specified event
To log only specified events you must first disable log all events by set `log_all_events`
to `false` or use method `logAllEvents` with `false` attribute.  
Next use `logEvent` to specify which events will be log as array of events to log.

```php
$instance->logEvent(['event_1', 'event_2']);
```

## Log message example
Default log has specified format, that contains such information as:

1. event key name
2. Date ant time of execution
3. Current launched listener
4. Status of listener execution

### Log message example

```
EVENT: test_event - 14:58:13 - 25-07-2015
Listener: ClassEvent\Test\EventDispatcherTest::trigger -> ok
-----------------------------------------------------------
```

### Execution status
All event listener execution return status for log file.

* **ok** _EVENT_STATUS_OK_ - listener execution was successful
* **error** EVENT_STATUS_ERROR - listener throw some exception (stored in error list)
* **propagation_stop** EVENT_STATUS_BREAK - listener was not executed because propagation was stopped

## Extending Log class
Format of log message can be changed by creating own Log class and inject
new instance to event dispatcher. New class should implements `LogInterface` and
have one public method `makeLog` that get array of parameters to log event.

## Log events methods

* **enableEventLog** - turn on log event
* **disableEventLog** - turn off log events
* **isLogEnabled** - return status of log events (true | false)
* **logEvent** - Allow to log specified events
* **logAllEvents** - turn on or off log all events by boolean value (must be off to log specified events)
* **isLogAllEventsEnabled** - return status of log all events (true | false)
* **getAllEventsToLog** - return list of events to log
