# BlueEvent

[![Latest Stable Version](https://poser.pugx.org/bluetree-service/event/v/stable.svg)](https://packagist.org/packages/bluetree-service/event)
[![Total Downloads](https://poser.pugx.org/bluetree-service/event/downloads.svg)](https://packagist.org/packages/bluetree-service/event)
[![License](https://poser.pugx.org/bluetree-service/event/license.svg)](https://packagist.org/packages/bluetree-service/event)
[![Dependency Status](https://www.versioneye.com/user/projects/5926da93368b08001772764b/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5926da93368b08001772764b)
[![Documentation Status](https://readthedocs.org/projects/event/badge/?version=latest)](https://readthedocs.org/projects/event/?badge=latest)

##### Builds
| Travis | Scrutinizer |
|:---:|:---:|
| [![Build Status](https://travis-ci.org/bluetree-service/event.svg)](https://travis-ci.org/bluetree-service/event) | [![Build Status](https://scrutinizer-ci.com/g/bluetree-service/event/badges/build.png?b=master)](https://scrutinizer-ci.com/g/bluetree-service/event/build-status/master) |

##### Coverage
| Coveralls | Scrutinizer |
|:---:|:---:|
| [![Coverage Status](https://coveralls.io/repos/github/bluetree-service/event/badge.svg?branch=master)](https://coveralls.io/github/bluetree-service/event?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/bluetree-service/event/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bluetree-service/event/?branch=master) |

##### Quality
| Code Climate | Scrutinizer | Sensio Labs |
|:---:|:---:|:---:|
| [![Code Climate](https://codeclimate.com/github/bluetree-service/event/badges/gpa.svg)](https://codeclimate.com/github/bluetree-service/event) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bluetree-service/event/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bluetree-service/event/?branch=master) | [![SensioLabsInsight](https://insight.sensiolabs.com/projects/1487084a-8f8d-4d4e-962d-59f036a67096/mini.png)](https://insight.sensiolabs.com/projects/1487084a-8f8d-4d4e-962d-59f036a67096) |
|  | [![Code Intelligence Status](https://scrutinizer-ci.com/g/bluetree-service/event/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence) |  |


Simple PHP event handling mechanism

### Included classes
* **BlueEvent\Base\Event** - Abstract class to store event statement
* **BlueEvent\Base\EventDispatcher** - Main event class, allow to manage events and listeners
* **BlueEvent\BaseEvent** - Simple event object to store event statement
* **BlueEvent\Event\Base\Interfaces\EventDispatcherInterface** - Use to create different objects that can be used by all libraries required event library
* **BlueEvent\Event\Base\Interfaces\EventInterface** - Use to create Event objects

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
[Event listeners errors](https://github.com/bluetree-service/event/doc/errors.md)

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

* PHP 5.6 or higher


## Change log
All release version changes:  
[Change log](https://github.com/bluetree-service/event/doc/changelog.md "Change log")

## License
This bundle is released under the Apache license.  
[Apache license](https://github.com/bluetree-service/event/LICENSE "Apache license")

## Travis Information
[Travis CI Build Info](https://travis-ci.org/bluetree-service/event)
