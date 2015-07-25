# ClassEvent

### Included libraries
* **ClassEvent\Base\Event** - Abstract class to store event statement
* **ClassEvent\Base\EventDispatcher** - Main event class, allow to manage events and listeners
* **ClassEvent\BaseEvent** - Simple event object to store event statement
* **ClassEvent\Log\Log** - Allow to save trigger event information into log file

## Documentation

### Basic usage
[Basic usage](https://github.com/chajr/class-event/doc/basic_usage.md)

### Event Configuration
[Load Event Configuration](https://github.com/chajr/class-event/doc/configuration.md)

### Event Object
[Store Event statement in Event Object](https://github.com/chajr/class-event/doc/event_object.md)

### Event Log
[Log each or specified event trigger](https://github.com/chajr/class-event/doc/event_log.md)

### Errors
[Event listeners errors](https://github.com/chajr/class-event/doc/errors.md)

## Install via Composer
To use _ClassEvent_ you can just download package and place it in your code. But recommended
way to use _ClassEvent_ is install it via Composer. To include _ClassEvent_
libraries paste into `composer.json`:

```json
{
    "require": {
        "chajr/class-event": "version_number"
    }
}
```

## Project description

### Used conventions

* **Namespaces** - each library use namespaces (base is _ClassEvent_)
* **PSR-4** - [PSR-4](http://www.php-fig.org/psr/psr-4/) coding standard
* **Composer** - [Composer](https://getcomposer.org/) usage to load/update libraries

### Requirements

* PHP 5.4 or higher



## Change log
All release version changes:  
[Change log](https://github.com/chajr/class-event/doc/changelog.md "Change log")

## License
This bundle is released under the Apache license.  
[Apache license](https://github.com/chajr/class-event/LICENSE "Apache license")

