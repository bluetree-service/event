# Event Object
Event object store current event statement and allow to exchange information
between listeners.
 
## Features
EventObject allow to disable execute all listeners called
after executing `stopPropagation` method. All event listeners called after
propagation stop will log event status `propagation_stop`.  
Event Object store number of executions of `__construct` so we have information
how many time EventObject was created.

## Available methods

* **getLaunchCount** - return number of EventObject creation
* **isPropagationStopped** - return `true` if propagation is stopped
* **stopPropagation** - allow to stop propagation
