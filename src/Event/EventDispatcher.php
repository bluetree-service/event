<?php

namespace ClassEvent\Event;

use ClassEvent\Event\Base\Interfaces\EventManagerInterface;
use ClassEvent\Event\Base\Interfaces\EventInterface;
use ClassEvent\Event\Base\EventManager;

class EventDispatcher
{
    protected static $_initialized = false;
    protected static $_dispatcherInstance = [];

    /**
     * initialize event dispatch instance
     *
     * @param string $configurationPath
     * @param string|null $configType
     * @param string $instanceName
     * @param string|EventManagerInterface $eventManager
     */
    public static function init(
        $configurationPath,
        $configType = null,
        $instanceName = 'default',
        $eventManager = 'default'
    ) {
        if (array_key_exists($instanceName, self::$_dispatcherInstance)) {
            return;
        }

        if ($eventManager === 'default') {
            self::$_dispatcherInstance[$instanceName] = new EventManager($configurationPath, $configType);
        } elseif ($eventManager instanceof EventManagerInterface) {
            self::$_dispatcherInstance[$instanceName] = $eventManager;
        } else {
            throw new \RuntimeException('Undefined event dispatcher instance.');
        }

        self::$_initialized = true;
    }

    /**
     * trigger new event with automatic call all subscribed listeners
     *
     * @param string $name
     * @param array $data
     * @param string $instanceName
     */
    public static function triggerEvent($name, $data = [], $instanceName = 'default')
    {
        self::_initException();
        $listeners = self::getInstance($instanceName)->getEventListeners($name);
        /** @var EventInterface $event */
        $event = self::getInstance($instanceName)->getEventObject($name);

        if (!$event) {
            throw new \UnexpectedValueException(
                self::getInstance($instanceName)->getErrors()
            );
        }

        foreach ($listeners as $listener) {
            if ($event->isPropagationStopped()) {
                break;
            }

            try {
                self::getInstance($instanceName)->callFunction($listener, $data, $event);
            } catch (\Exception $e) {
                self::getInstance($instanceName)->addError($e);
            }
        }
    }

    public static function addEventListener()
    {
        self::_initException();
    }

    public static function getCalledEvents()
    {
        
    }

    /**
     * allow to add event configuration after initialize event dispatcher
     *
     * @param array $config
     * @param string $type
     * @param string $instanceName
     */
    public static function setEventConfiguration(array $config, $type, $instanceName = 'default')
    {
        self::_initException();
        self::getInstance($instanceName)->setEventConfiguration($config, $type);
    }

    public static function getErrors()
    {
        self::_initException();
    }

    public static function hasErrors()
    {
        self::_initException();
    }

    public static function clearErrors()
    {
        self::_initException();
    }
    
    protected static function _initException()
    {
        if (!self::$_initialized) {
            throw new \RuntimeException('Event Dispatcher must be initialized.');
        }
    }

    /**
     * return event dispatcher instance object
     *
     * @param string $instanceName
     * @return null|EventManagerInterface
     */
    public static function getInstance($instanceName = 'default')
    {
        if (!array_key_exists($instanceName, self::$_dispatcherInstance)) {
            return null;
        }

        return self::$_dispatcherInstance[$instanceName];
    }
}
