<?php

namespace ClassEvent\Event\Base;

use ClassEvent\Event\Base\Interfaces\EventDispatcherInterface;
use ClassEvent\Event\Base\Interfaces\EventInterface;
use Zend\Config\Reader;
use ClassEvent\Event\Log;

class EventDispatcher implements EventDispatcherInterface
{
    const EVENT_STATUS_OK       = 'ok';
    const EVENT_STATUS_ERROR    = 'error';
    const EVENT_STATUS_BREAK    = 'propagation_stop';

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
     * store logger instance
     *
     * @var Log\LogInterface
     */
    protected $_loggerInstance = null;

    /**
     * store default options for event dispatcher
     *
     * @var array
     */
    protected $_options = [
        'type'              => 'array',
        'log_events'        => false,
        'log_all_events'    => true,
        'from_file'         => false,
        'log_path'          => false,
        'log_object'        => false,
    ];

    /**
     * store all event names to log
     *
     * @var array
     */
    protected $_logEvents = [];

    /**
     * create manage instance
     *
     * @param array $options
     * @param array|string $events
     */
    public function __construct(array $options = [], $events = [])
    {
        $this->_options = array_merge($this->_options, $options);

        if ($this->_options['from_file']) {
            $this->readEventConfiguration(
                $events,
                $this->_options['type']
            );
        } else {
            $this->_eventsConfig = $events;
        }

        unset($this->_options['events']);
    }

    /**
     * return event object or create it if not exist
     *
     * @param string $eventName
     * @return EventInterface
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
     * add event configuration into event dispatcher
     *
     * @param array $events
     * @return $this
     */
    public function setEventConfiguration(array $events)
    {
        $this->_eventsConfig = array_merge_recursive(
            $this->_eventsConfig,
            $events
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

        foreach ($this->_eventsConfig as $listener) {
            foreach ($listener['listeners'] as $eventListener) {
                if ($event->isPropagationStopped()) {
                    $this->_logEvent($name, $eventListener, self::EVENT_STATUS_BREAK);
                    break;
                }

                try {
                    $this->_callFunction($eventListener, $data, $event);
                    $status = self::EVENT_STATUS_OK;
                } catch (\Exception $e) {
                    $this->_addError($e);
                    $status = self::EVENT_STATUS_ERROR;
                }

                $this->_logEvent($name, $eventListener, $status);
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
            $this->_eventsConfig[$eventName] = [
                'object'    => 'ClassEvent\Event\BaseEvent',
                'listeners' => $listeners,
            ];
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
     * @param mixed $path
     * @param string|null $type
     * @return $this
     */
    public function readEventConfiguration($path, $type)
    {
        if ($type) {
            $config = $this->_configurationStrategy($path, $type);
            $this->setEventConfiguration($config);
        }

        return $this;
    }

    /**
     * call and read specified configuration
     *
     * @param string $path
     * @param string $type
     * @return array
     */
    protected function _configurationStrategy($path, $type)
    {
        $config = [];

        if (!file_exists($path)) {
            throw new \InvalidArgumentException('File ' . $path . 'don\'t exists.');
        }

        switch ($type) {
            case 'array':
                $config = include_once($path);
                break;
            case 'ini':
                $reader = new Reader\Ini;
                $config = $reader->fromFile($path);
                break;
            case 'xml':
                $reader = new Reader\Xml;
                $config = $reader->fromFile($path);
                break;
            case 'json':
                $reader = new Reader\Json;
                $config = $reader->fromFile($path);
                break;
            case 'yaml':
                $reader = new Reader\Yaml(['Spyc','YAMLLoadString']);
                $config = $reader->fromFile($path);
                break;
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
     * get information that event log is enabled or disabled
     *
     * @return bool
     */
    public function isLogEnabled()
    {
        return $this->_options['log_events'];
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
     * log given events or all events
     * to log all events use 'all' keyword
     * 
     * @param array $events
     * @return $this
     */
    public function logEvent(array $events = [])
    {
        foreach ($events as $event) {
            if (!in_array($event, $this->_logEvents)) {
                $this->_logEvents[] = $event;
            }
        }

        return $this;
    }

    /**
     * enable or disable log all events
     *
     * @param bool $log
     * @return $this
     */
    public function logAllEvents($log = true)
    {
        $this->_options['log_all_events'] = (bool)$log;
        return $this;
    }

    /**
     * get information that all event log is enabled or disabled
     *
     * @return bool
     */
    public function isLogAllEventsEnabled()
    {
        return $this->_options['log_all_events'];
    }

    /**
     * return list of all events to log
     *
     * @return array
     */
    public function getAllEventsToLog()
    {
        return $this->_logEvents;
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
     * return all event dispatcher errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errorList;
    }

    /**
     * return information that event dispatcher has some errors
     *
     * @return bool
     */
    public function hasErrors()
    {
        return $this->_hasErrors;
    }

    /**
     * clear all event dispatcher errors
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
    public function _addError(\Exception $exception)
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

    /**
     * check that event data can be logged and create log message
     *
     * @param string $name
     * @param mixed $eventListener
     * @param bool $status
     * @return $this
     */
    protected function _logEvent($name, $eventListener, $status)
    {
        if ($this->_options['log_events']
            && ($this->_options['log_all_events']
                || in_array($name, $this->_logEvents)
            )
        ) {
            $this->_createLogObject();
            $data = 'unknown';

            switch (true) {
                case is_string($eventListener):
                    $data = $eventListener;
                    break;
                case $eventListener instanceof \Closure:
                    $data = 'Closure';
                    break;
                case is_array($eventListener):
                    $data = get_class($eventListener[0]) . '::' . $eventListener[1];
                    break;
            }

            $this->_loggerInstance->makeLog([
                'event_name'    => $name,
                'log_path'      => $this->_options['log_path'],
                'listener'      => $data,
                'status'        => $status
            ]);
        }

        return $this;
    }

    /**
     * create log object instance
     *
     * @return $this
     */
    protected function _createLogObject()
    {
        if (!$this->_loggerInstance) {
            if (!$this->_options['log_object']
                && $this->_options['log_object'] instanceof Log\LogInterface
            ) {
                $this->_loggerInstance = $this->_options['log_object'];
            } else {
                $this->_loggerInstance = new Log\Log;
            }
        }

        return $this;
    }
}