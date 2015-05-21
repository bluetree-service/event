<?php

namespace ClassEvent\Event;

use ClassEvent\Event\Base\Interfaces\EventManagerInterface;
use ClassEvent\Event\Base\EventManager;

class EventDispatcher
{
    protected static $_initialized = false;
    protected static $_managerInstance = [];

    /**
     * initialize event dispatch instance
     *
     * @param string $configurationPath
     * @param string|null $configType
     * @param string $instanceName
     * @param string|EventManagerInterface $eventManager
     */
    public static function init(
        $configurationPath = '',
        $configType = null,
        $instanceName = 'default',
        $eventManager = 'default'
    ) {
        if (array_key_exists($instanceName, self::$_managerInstance)) {
            return;
        }

        if ($eventManager === 'default') {
            self::$_managerInstance[$instanceName] = new EventManager($configurationPath, $configType);
        } elseif ($eventManager instanceof EventManagerInterface) {
            self::$_managerInstance[$instanceName] = $eventManager;
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
        self::_getInstance($instanceName)->triggerEvent($name, $data);
    }

    /**
     * check that event dispatcher was initialized with given instance
     * 
     * @param string $instance
     * @return bool
     */
    public static function isInitialized($instance = 'default')
    {
        $instanceExists = array_key_exists($instance, self::$_managerInstance);

        return self::$_initialized && $instanceExists;
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
        self::_getInstance($instanceName)->setEventConfiguration($config, $type);
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
     * return event manager instance object
     *
     * @param string $instanceName
     * @return null|EventManagerInterface
     */
    protected static function _getInstance($instanceName = 'default')
    {
        if (!array_key_exists($instanceName, self::$_managerInstance)) {
            return null;
        }

        return self::$_managerInstance[$instanceName];
    }
}
