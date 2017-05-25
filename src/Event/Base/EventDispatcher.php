<?php

namespace BlueEvent\Event\Base;

use BlueEvent\Event\Base\Interfaces\EventDispatcherInterface;
use BlueEvent\Event\Base\Interfaces\EventInterface;
use Zend\Config\Reader;
use SimpleLog\Log;

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
    protected $eventsConfig = [];

    /**
     * store all called events
     *
     * @var array
     */
    protected $events = [];

    /**
     * @var bool
     */
    protected $hasErrors = false;

    /**
     * store all errors
     *
     * @var
     */
    protected $errorList = [];

    /**
     * store logger instance
     *
     * @var \SimpleLog\LogInterface
     */
    protected $loggerInstance = null;

    /**
     * store default options for event dispatcher
     *
     * @var array
     */
    protected $options = [
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
    protected $logEvents = [];

    /**
     * create manage instance
     *
     * @param array $options
     * @param array|string $events
     */
    public function __construct(array $options = [], $events = [])
    {
        $this->options = array_merge($this->options, $options);

        if ($this->options['from_file']) {
            $this->readEventConfiguration(
                $events,
                $this->options['type']
            );
        } else {
            $this->eventsConfig = $events;
        }

        unset($this->options['events']);
    }

    /**
     * return event object or create it if not exist
     *
     * @param string $eventName
     * @return EventInterface
     */
    public function getEventObject($eventName)
    {
        if (!array_key_exists($eventName, $this->eventsConfig)) {
            throw new \InvalidArgumentException('Event is not defined.');
        }

        if (!array_key_exists($eventName, $this->events)) {
            $namespace = $this->eventsConfig[$eventName]['object'];
            $instance = new $namespace;

            if (!($instance instanceof EventInterface)) {
                throw new \LogicException('Invalid interface of event object');
            }

            $this->events[$eventName] = $instance;
        }

        return $this->events[$eventName];
    }

    /**
     * add event configuration into event dispatcher
     *
     * @param array $events
     * @return $this
     */
    public function setEventConfiguration(array $events)
    {
        $this->eventsConfig = array_merge_recursive(
            $this->eventsConfig,
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
    public function triggerEvent($name, array $data = [])
    {
        /** @var EventInterface $event */
        $event = $this->getEventObject($name);

        foreach ($this->eventsConfig as $listener) {
            foreach ($listener['listeners'] as $eventListener) {
                if ($event->isPropagationStopped()) {
                    $this->makeLogEvent($name, $eventListener, self::EVENT_STATUS_BREAK);
                    break;
                }

                try {
                    $this->callFunction($eventListener, $data, $event);
                    $status = self::EVENT_STATUS_OK;
                } catch (\Exception $e) {
                    $this->addError($e);
                    $status = self::EVENT_STATUS_ERROR;
                }

                $this->makeLogEvent($name, $eventListener, $status);
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
        if (!array_key_exists($eventName, $this->eventsConfig)) {
            $this->eventsConfig[$eventName] = [
                'object' => 'BlueEvent\Event\BaseEvent',
                'listeners' => $listeners,
            ];
        }

        $this->eventsConfig[$eventName]['listeners'] = array_merge(
            $this->eventsConfig[$eventName]['listeners'],
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
    protected function callFunction($listener, array $data, EventInterface $event)
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
            $config = $this->configurationStrategy($path, $type);
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
    protected function configurationStrategy($path, $type)
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
        $this->options['log_events'] = true;
        return $this;
    }

    /**
     * allow to disable event logging
     *
     * @return $this
     */
    public function disableEventLog()
    {
        $this->options['log_events'] = false;
        return $this;
    }

    /**
     * return all currently launched events
     *
     * @return array
     */
    public function getAllEvents()
    {
        return $this->events;
    }

    /**
     * log given events
     *
     * @param array $events
     * @return $this
     */
    public function logEvent(array $events = [])
    {
        foreach ($events as $event) {
            if (!in_array($event, $this->logEvents)) {
                $this->logEvents[] = $event;
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
        $this->options['log_all_events'] = (bool)$log;
        return $this;
    }

    /**
     * get complete object configuration or value of single option
     *
     * @param $option string|null
     * @return mixed
     */
    public function getConfiguration($option = null)
    {
        if (!is_null($option)) {
            return $this->options[$option];
        }

        return $this->options;
    }

    /**
     * return list of all events to log
     *
     * @return array
     */
    public function getAllEventsToLog()
    {
        return $this->logEvents;
    }

    /**
     * return current event configuration
     *
     * @return array
     */
    public function getEventConfiguration()
    {
        return $this->eventsConfig;
    }

    /**
     * return all event dispatcher errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errorList;
    }

    /**
     * return information that event dispatcher has some errors
     *
     * @return bool
     */
    public function hasErrors()
    {
        return $this->hasErrors;
    }

    /**
     * clear all event dispatcher errors
     *
     * @return $this
     */
    public function clearErrors()
    {
        $this->errorList = [];
        $this->hasErrors = false;

        return $this;
    }

    /**
     * add new error to list
     *
     * @param \Exception $exception
     * @return $this
     */
    protected function addError(\Exception $exception)
    {
        $this->errorList[$exception->getCode()] = [
            'message'   => $exception->getMessage(),
            'line'      => $exception->getLine(),
            'file'      => $exception->getFile(),
            'trace'     => $exception->getTraceAsString(),
        ];
        $this->hasErrors = true;

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
    protected function makeLogEvent($name, $eventListener, $status)
    {
        if ($this->options['log_events']
            && ($this->options['log_all_events']
                || in_array($name, $this->logEvents)
            )
        ) {
            $this->createLogObject();

            switch (true) {
                case $eventListener instanceof \Closure:
                    $data = 'Closure';
                    break;
                case is_array($eventListener):
                    $data = get_class($eventListener[0]) . '::' . $eventListener[1];
                    break;
                default:
                    $data = $eventListener;
                    break;
            }

            $this->loggerInstance->makeLog(
                [
                    'event_name' => $name,
                    'listener' => $data,
                    'status' => $status
                ],
                [
                    'log_path' => $this->options['log_path'],
                    'type' => 'events',
                ]
            );
        }

        return $this;
    }

    /**
     * create log object instance
     *
     * @return $this
     */
    protected function createLogObject()
    {
        if (!$this->loggerInstance) {
            if ($this->options['log_object']
                && $this->options['log_object'] instanceof \SimpleLog\LogInterface
            ) {
                $this->loggerInstance = $this->options['log_object'];
            } else {
                $this->loggerInstance = new Log;
            }
        }

        return $this;
    }
}
