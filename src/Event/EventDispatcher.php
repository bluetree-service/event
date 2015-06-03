<?php

namespace ClassEvent\Event;

use ClassEvent\Event\Base\Interfaces\EventManagerInterface;

class EventDispatcher
{
    const DEFAULT_INSTANCE = 'default';

    protected static $_initialized = false;
    protected static $_managerInstance = [];
    protected static $_instanceConfig = [];

    /**
     * store all default options for event dispatcher
     *
     * @var array
     */
    protected static $_defaultOptions = [
        'events'            => [],
        'type'              => 'array',
        'from_file'         => false,
        'log_events'        => false,
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
        $options      = self::_setInstanceConfig($options);
        $instanceName = $options['instance_name'];

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
    public static function triggerEvent($name, $data = [], $instanceName = self::DEFAULT_INSTANCE)
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
    public static function isInitialized($instance = self::DEFAULT_INSTANCE)
    {
        $instanceExists = array_key_exists($instance, self::$_managerInstance);

        return self::$_initialized && $instanceExists;
    }

    public static function addEventListener()
    {
        self::_initException();
    }

    /**
     * return all called events for given instance
     *
     * @param string $instance
     * @return mixed
     */
    public static function getCalledEvents($instance = self::DEFAULT_INSTANCE)
    {
        /** @var EventManagerInterface $instanceObject */
        $instanceObject = self::$_managerInstance[$instance];
        return $instanceObject->getAllEvents();
    }

    /**
     * allow to add event configuration after initialize event dispatcher
     *
     * @param array $config
     */
    public static function setEventConfiguration(array $config)
    {
        self::_initException();
        $config = self::_setInstanceConfig($config);
        self::_getInstance($config['instance_name'])->setEventConfiguration($config);
    }

    /**
     * return set up configuration for given instance
     *
     * @param string $instanceName
     * @return array
     */
    public static function getEventConfiguration($instanceName = self::DEFAULT_INSTANCE)
    {
        /** @var EventManagerInterface $instanceObject */
        $instanceObject = self::$_managerInstance[$instanceName];
        return $instanceObject->getEventConfiguration();
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

    /**
     * allow to set configuration for given instance
     *
     * @param array $config
     * @return array
     */
    protected static function _setInstanceConfig(array $config)
    {
        $instanceName = self::DEFAULT_INSTANCE;

        if (array_key_exists('instance_name', $config)) {
            $instanceName = $config['instance_name'];
        }

        if (array_key_exists($instanceName, self::$_instanceConfig)) {
            self::$_instanceConfig[$instanceName] = array_replace_recursive(
                self::$_instanceConfig[$instanceName],
                $config
            );
        } else {
            self::$_instanceConfig[$instanceName] = array_merge(
                self::$_defaultOptions,
                $config
            );
        }

        return self::$_instanceConfig[$instanceName];
    }

    /**
     * check that event dispatcher was initialized
     *
     * @throws \RuntimeException
     */
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
    protected static function _getInstance($instanceName = self::DEFAULT_INSTANCE)
    {
        if (!array_key_exists($instanceName, self::$_managerInstance)) {
            return null;
        }

        return self::$_managerInstance[$instanceName];
    }
}
