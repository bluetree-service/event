# Event Object
Event object store current event statement and allow to exchange information between listeners. Also event object
store all data given by trigger.

## Features
EventObject allow to disable execute all listeners called
after executing `stopPropagation` method. All event listeners called after
propagation stop will log event status `propagation_stop`.  
Event Object store number of executions of `__construct` so we have information
how many time EventObject was created. That calculation is stored for all event object executions, not only for
given code like stop propagation.

## Available methods

* **getLaunchCount** - return number of EventObject creation
* **isPropagationStopped** - return `true` if propagation is stopped (used only by `EventDispatcher`)
* **stopPropagation** - allow to stop propagation
* **getEventParameters** - get all parameters that was given by `triggerEvent`
* **getEventCode** - return event code that was used by `triggerEvent`
