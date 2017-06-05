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
        'type' => 'array',
        'log_events' => false,
        'log_all_events' => true,
        'from_file' => false,
        'log_path' => false,
        'log_object' => false,
        'events' => [],
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
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);

        if ($this->options['from_file']) {
            $this->readEventConfiguration(
                $this->options['from_file'],
                $this->options['type']
            );
        }
    }

    /**
     * return event object or create it if not exist
     *
     * @param string $eventName
     * @param array $data
     * @return EventInterface
     */
    protected function createEventObject($eventName, array $data)
    {
        if (!array_key_exists($eventName, $this->options['events'])) {
            throw new \InvalidArgumentException('Event is not defined.');
        }

        $namespace = $this->options['events'][$eventName]['object'];
        $instance = new $namespace($eventName, $data);

        if (!($instance instanceof EventInterface)) {
            throw new \LogicException('Invalid interface of event object');
        }

        return $instance;
    }

    /**
     * add event configuration into event dispatcher
     *
     * @param array $events
     * @return $this
     */
    public function setEventConfiguration(array $events)
    {
        $this->options['events'] = array_merge_recursive(
            $this->options['events'],
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
        try {
            /** @var EventInterface $event */
            $event = $this->createEventObject($name, $data);
        } catch (\InvalidArgumentException $exception) {
            return $this;
        }

        foreach ($this->options['events'][$name]['listeners'] as $eventListener) {
            if ($event->isPropagationStopped()) {
                $this->makeLogEvent($name, $eventListener, self::EVENT_STATUS_BREAK);
                break;
            }

            try {
                $this->callFunction($eventListener, $event);
                $status = self::EVENT_STATUS_OK;
            } catch (\Exception $e) {
                $this->addError($e);
                $status = self::EVENT_STATUS_ERROR;
            }

            $this->makeLogEvent($name, $eventListener, $status);
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
        if (!array_key_exists($eventName, $this->options['events'])) {
            $this->options['events'][$eventName] = [
                'object' => 'BlueEvent\Event\BaseEvent',
                'listeners' => $listeners,
            ];
        }

        $this->options['events'][$eventName]['listeners'] = array_merge(
            $this->options['events'][$eventName]['listeners'],
            $listeners
        );

        return $this;
    }

    /**
     * allow to call event listeners functions
     *
     * @param string $listener
     * @param EventInterface $event
     */
    protected function callFunction($listener, EventInterface $event)
    {
        if (is_callable($listener)) {
            call_user_func($listener, $event);
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
        return $this->options['events'];
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
