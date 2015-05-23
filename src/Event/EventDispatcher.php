<?php

namespace ClassEvent\Event;

use ClassEvent\Event\Base\Interfaces\EventManagerInterface;

class EventDispatcher
{
    const DEFAULT_INSTANCE = 'default';

    protected static $_initialized = false;
    protected static $_managerInstance = [];

    /**
     * store all default options for event dispatcher
     *
     * @var array
     */
    protected static $_options = [
        'configuration'     => [],
        'type'              => 'array',
        'from_file'         => false,
        'instance_name'     => self::DEFAULT_INSTANCE,
        'event_manager'     => 'ClassEvent\Event\Base\EventManager'
    ];

    /**
     * initialize event dispatch instance
     *
     * @param array $options
     */
    public static function init(array $options = [])
    {
        $options        = array_merge(self::$_options, $options);
        $instanceName   = $options['instance_name'];

        if (array_key_exists($instanceName, self::$_managerInstance)) {
            return;
        }

        switch (true) {
            case is_string($options['event_manager']):
                $reflection                             = new \ReflectionClass($options['event_manager']);
                self::$_managerInstance[$instanceName]  = $reflection->newInstanceArgs($options);
                break;
            case $options['event_manager'] instanceof EventManagerInterface:
                self::$_managerInstance[$instanceName] = $options['event_manager'];
                break;
            default:
                throw new \RuntimeException('Undefined event dispatcher instance.');
                break;
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
     */
    public static function setEventConfiguration(array $config)
    {
        self::_initException();
        $config = array_merge(self::$_options, $config);
        self::_getInstance($config['instance_name'])->setEventConfiguration($config);
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
