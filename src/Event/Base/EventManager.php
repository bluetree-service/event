<?php

namespace ClassEvent\Event\Base;

use ClassEvent\Event\Base\Interfaces\EventManagerInterface;
use ClassEvent\Event\Base\Interfaces\EventInterface;
use Zend\Config\Reader;

class EventManager implements EventManagerInterface
{
    /**
     * store all loaded configuration
     *
     * @var array
     */
    protected $_eventsConfig = [];

    /**
     * store all called events
     *
     * @var array
     */
    protected $_events = [];

    /**
     * 
     *
     * @var bool
     */
    protected $_hasErrors = false;

    /**
     * store all errors
     *
     * @var
     */
    protected $_errorList = [];

    /**
     * store default options for event manager
     *
     * @var array
     */
    protected $_options = [
        'events'            => [],
        'type'              => 'array',
        'log_events'        => false,
        'from_file'         => false
    ];

    /**
     * create manage instance
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $config = array_merge($this->_options, $options);

        if ($config['from_file']) {
            $this->_eventsConfig = $this->readEventConfiguration(
                $config['events'],
                $config['type']
            );
        } else {
            $this->_eventsConfig = array_merge(
                $this->_eventsConfig,
                $config['events']
            );
        }
    }

    /**
     * return event object or create it if not exist
     *
     * @param string $eventName
     * @return bool
     */
    public function getEventObject($eventName)
    {
        if (!array_key_exists($eventName, $this->_eventsConfig)) {
            throw new \InvalidArgumentException('Event is not defined.');
        }

        if (!array_key_exists($eventName, $this->_events)) {
            $namespace                  = $this->_eventsConfig[$eventName]['object'];
            $instance                   = new $namespace;

            if (!($instance instanceof EventInterface)) {
                throw new \LogicException('Invalid interface of event object');
            }

            $this->_events[$eventName] = $instance;
        }

        return $this->_events[$eventName];
    }

    /**
     * add event configuration into event manager
     *
     * @param array $config
     * @return $this
     */
    public function setEventConfiguration(array $config)
    {
        $config = array_merge($this->_options, $config);

        if ($config['from_file']) {
            $configuration = $this->readEventConfiguration(
                $config['events'],
                $config['type']
            );
        } else {
            $configuration = $config['events'];
        }

        $this->_eventsConfig = array_merge_recursive(
            $this->_eventsConfig,
            $configuration
        );

        return $this;
    }

    /**
     * trigger new event with automatic call all subscribed listeners
     *
     * @param string $name
     * @param array $data
     * @return $this
     */
    public function triggerEvent($name, $data = [])
    {
        /** @var EventInterface $event */
        $event = $this->getEventObject($name);

        if (!$event) {
            throw new \UnexpectedValueException($this->getErrors());
        }

        foreach ($this->_eventsConfig as $listener) {
            foreach ($listener['listeners'] as $eventListener) {
                if ($event->isPropagationStopped()) {
                    break;
                }

                try {
                    $this->_callFunction($eventListener, $data, $event);
                } catch (\Exception $e) {
                    $this->addError($e);
                }
            }
        }

        return $this;
    }

    /**
     * dynamically add new listener or listeners for given event name
     * listeners are added at end of the list
     *
     * @param string $eventName
     * @param array $listeners
     * @return $this
     */
    public function addEventListener($eventName, array $listeners)
    {
        if (!array_key_exists($eventName, $this->_eventsConfig)) {
            $this->_eventsConfig[$eventName] = [];
        }

        $this->_eventsConfig[$eventName]['listeners'] = array_merge(
            $this->_eventsConfig[$eventName]['listeners'],
            $listeners
        );

        return $this;
    }

    /**
     * allow to call event listeners functions
     *
     * @param string $listener
     * @param array $data
     * @param EventInterface $event
     */
    protected function _callFunction($listener, array $data, EventInterface $event)
    {
        if (is_callable($listener)) {
            call_user_func_array($listener, [$data, $event]);
        }
    }

    /**
     * read configuration from file
     * 
     * @param mixed $configuration
     * @param string|null $type
     * @return array
     */
    public function readEventConfiguration($configuration, $type)
    {
        $config = [];

        if ($type) {
            $config = $this->_configurationStrategy($configuration, $type);
        }

        return $config;
    }

    /**
     * call and read specified configuration
     *
     * @param string $configuration
     * @param string $type
     * @return array
     */
    protected function _configurationStrategy($configuration, $type)
    {
        if (!file_exists($configuration)) {
            throw new \InvalidArgumentException('File ' . $configuration . 'don\'t exists.');
        }

        switch ($type) {
            case 'array':
                $config = include_once($configuration);
                break;
            case 'ini':
                $reader = new Reader\Ini;
                $config = $reader->fromFile($configuration);
                break;
            case 'xml':
                $reader = new Reader\Xml;
                $config = $reader->fromFile($configuration);
                break;
            case 'json':
                $reader = new Reader\Json;
                $config = $reader->fromFile($configuration);
                break;
            case 'yaml':
                $reader = new Reader\Yaml;
                $config = $reader->fromFile($configuration);
                break;
            case 'java':
                $reader = new Reader\JavaProperties;
                $config = $reader->fromFile($configuration);
                break;
            default:
                $config = [];
        }

        return $config;
    }

    /**
     * allow to enable event logging
     *
     * @return $this
     */
    public function enableEventLog()
    {
        $this->_options['log_events'] = true;
        return $this;
    }

    /**
     * allow to disable event logging
     *
     * @return $this
     */
    public function disableEventLog()
    {
        $this->_options['log_events'] = false;
        return $this;
    }

    /**
     * return all currently launched events
     *
     * @return array
     */
    public function getAllEvents()
    {
        return $this->_events;
    }

    /**
     * @todo log all events or single event
     */
    public function logEvent()
    {
        
    }

    /**
     * return current event configuration
     *
     * @return array
     */
    public function getEventConfiguration()
    {
        return $this->_eventsConfig;
    }

    /**
     * return all event manager errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errorList;
    }

    /**
     * return information that event manager has some errors
     *
     * @return bool
     */
    public function hasErrors()
    {
        return $this->_hasErrors;
    }

    /**
     * clear all event manager errors
     *
     * @return $this
     */
    public function clearErrors()
    {
        $this->_errorList = [];
        $this->_hasErrors = false;

        return $this;
    }

    /**
     * add new error to list
     *
     * @param \Exception $exception
     * @return $this
     */
    public function addError(\Exception $exception)
    {
        $this->_errorList[$exception->getCode()] = [
            'message'   => $exception->getMessage(),
            'line'      => $exception->getLine(),
            'file'      => $exception->getFile(),
            'trace'     => $exception->getTraceAsString(),
        ];
        $this->_hasErrors = true;

        return $this;
    }
}
