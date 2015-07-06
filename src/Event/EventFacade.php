<?php

namespace ClassEvent\Event;

use ClassEvent\Event\Base\Interfaces\EventDispatcherInterface;

class EventFacade
{
    /**
     * default instance name
     */
    const DEFAULT_INSTANCE = 'default';

    /**
     * store information that event dispatcher was initialized
     *
     * @var bool
     */
    protected static $_initialized = false;

    /**
     * store all called event dispatcher instances
     *
     * @var array
     */
    protected static $_dispatcherInstance = [];

    /**
     * store all instances default configuration
     *
     * @var array
     */
    protected static $_instanceConfig = [];

    /**
     * store all default options for event dispatcher
     *
     * @var array
     */
    protected static $_defaultOptions = [
        'events'    => [],
        'options'   => [
            'type'              => 'array',
            'from_file'         => false,
            'log_events'        => false,
            'instance_name'     => self::DEFAULT_INSTANCE,
            'event_dispatcher'  => 'ClassEvent\Event\Base\EventDispatcher',
            'log_all_events'    => false,
            'log_path'          => false,
        ]
    ];

    /**
     * initialize event dispatch instance
     *
     * @param array $options
     */
    public static function init(array $options = [])
    {
        $options        = self::_setInstanceConfig($options);
        $instanceName   = $options['options']['instance_name'];
        $message        = 'Incorrect event dispatcher instance.';

        if (array_key_exists($instanceName, self::$_dispatcherInstance)) {
            return;
        }

        switch (true) {
            case is_string($options['options']['event_dispatcher']):
                $reflection = new \ReflectionClass($options['options']['event_dispatcher']);

                if (!$reflection->implementsInterface('ClassEvent\Event\Base\Interfaces\EventDispatcherInterface')) {
                    throw new \RuntimeException($message);
                }

                self::$_dispatcherInstance[$instanceName] = $reflection->newInstanceArgs([
                    $options['options'],
                    $options['events'],
                ]);
                break;

            case $options['options']['event_dispatcher'] instanceof EventDispatcherInterface:
                self::$_dispatcherInstance[$instanceName] = $options['options']['event_dispatcher'];
                break;

            default:
                throw new \RuntimeException($message);
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
        $instanceExists = array_key_exists($instance, self::$_dispatcherInstance);

        return self::$_initialized && $instanceExists;
    }

    public static function addEventListener()
    {
        self::_initException();
    }

    /**
     * return all called events for given instance
     *
     * @param string $instanceName
     * @return mixed
     */
    public static function getCalledEvents($instanceName = self::DEFAULT_INSTANCE)
    {
        /** @var EventDispatcherInterface $instanceObject */
        $instanceObject = self::_getInstance($instanceName);
        return $instanceObject->getAllEvents();
    }

    /**
     * allow to add event configuration after initialize event dispatcher
     *
     * @param array $config
     */
    public static function setEventConfiguration(array $config)
    {
        if (!isset($config['options']['instance_name'])) {
            $config['options']['instance_name'] = self::DEFAULT_INSTANCE;
        }

        self::_initException();
        $config = self::_setInstanceConfig($config);
        self::_getInstance($config['options']['instance_name'])
            ->setEventConfiguration($config['events']);
    }

    /**
     * return set up configuration for given instance
     *
     * @param string $instanceName
     * @return array
     */
    public static function getEventConfiguration($instanceName = self::DEFAULT_INSTANCE)
    {
        /** @var EventDispatcherInterface $instanceObject */
        $instanceObject = self::_getInstance($instanceName);
        return $instanceObject->getEventConfiguration();
    }

    /**
     * return all event dispatcher errors
     *
     * @param string $instanceName
     * @return array
     */
    public static function getErrors($instanceName = self::DEFAULT_INSTANCE)
    {
        self::_initException();
        /** @var EventDispatcherInterface $instanceObject */
        $instanceObject = self::_getInstance($instanceName);
        return $instanceObject->getErrors();
    }

    /**
     * return information that event dispatcher has some errors
     *
     * @param string $instanceName
     * @return bool
     */
    public static function hasErrors($instanceName = self::DEFAULT_INSTANCE)
    {
        self::_initException();
        /** @var EventDispatcherInterface $instanceObject */
        $instanceObject = self::_getInstance($instanceName);
        return $instanceObject->hasErrors();
    }

    /**
     * clear all event dispatcher errors
     *
     * @param string $instanceName
     * @return $this
     */
    public static function clearErrors($instanceName = self::DEFAULT_INSTANCE)
    {
        self::_initException();
        /** @var EventDispatcherInterface $instanceObject */
        $instanceObject = self::_getInstance($instanceName);
        return $instanceObject->clearErrors();
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

        if (isset($config['options']['instance_name'])) {
            $instanceName = $config['options']['instance_name'];
        }

        if (isset(self::$_instanceConfig['options'][$instanceName])) {
            self::$_instanceConfig[$instanceName] = array_replace_recursive(
                self::$_instanceConfig[$instanceName],
                $config
            );
        } else {
            self::$_instanceConfig[$instanceName] = array_replace_recursive(
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
     * return event dispatcher instance object
     *
     * @param string $instanceName
     * @return null|EventDispatcherInterface
     * @throws \RuntimeException
     */
    protected static function _getInstance($instanceName = self::DEFAULT_INSTANCE)
    {
        if (array_key_exists($instanceName, self::$_dispatcherInstance)) {
            return self::$_dispatcherInstance[$instanceName];
        }

        throw new \RuntimeException('Instance: ' . $instanceName . ' don\'t exists');
    }
}
