# BlueEvent

### Included libraries
* **BlueEvent\Base\Event** - Abstract class to store event statement
* **BlueEvent\Base\EventDispatcher** - Main event class, allow to manage events and listeners
* **BlueEvent\BaseEvent** - Simple event object to store event statement
* **BlueEvent\Log\Log** - Allow to save trigger event information into log file

## Documentation

### Basic usage
[Basic usage](https://github.com/bluetree-service/event/doc/basic_usage.md)

### Event Configuration
[Load Event Configuration](https://github.com/bluetree-service/event/doc/configuration.md)

### Event Object
[Store Event statement in Event Object](https://github.com/bluetree-service/event/doc/event_object.md)

### Event Log
[Log each or specified event trigger](https://github.com/bluetree-service/event/doc/event_log.md)

### Errors
[Event listeners errors](https://github.com/chajr/bluetree-service/event/doc/errors.md)

## Install via Composer
To use _BlueEvent_ you can just download package and place it in your code. But recommended
way to use _BlueEvent_ is install it via Composer. To include _BlueEvent_
libraries paste into `composer.json`:

```json
{
    "require": {
        "bluetree-service/event": "version_number"
    }
}
```

## Project description

### Used conventions

* **Namespaces** - each library use namespaces (base is _BlueEvent_)
* **PSR-4** - [PSR-4](http://www.php-fig.org/psr/psr-4/) coding standard
* **Composer** - [Composer](https://getcomposer.org/) usage to load/update libraries

### Requirements

* PHP 5.4 or higher



## Change log
All release version changes:  
[Change log](https://github.com/bluetree-service/event/doc/changelog.md "Change log")

## License
This bundle is released under the Apache license.  
[Apache license](https://github.com/bluetree-service/event/LICENSE "Apache license")

