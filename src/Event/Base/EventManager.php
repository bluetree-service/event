<?php

namespace ClassEvent\Event\Base;

use ClassEvent\Event\Base\Interfaces\EventManagerInterface;
use ClassEvent\Event\Base\Interfaces\EventInterface;
use Zend\Config\Reader;

class EventManager implements EventManagerInterface
{
    protected $_configuration = [];

    protected $_events = [];

    protected $_errors;

    protected $_hasErrors = false;

    protected $_errorList = [];

    protected $_logEvents = false;

    protected $_eventsToLog = [];

    /**
     * store default options for event manager
     *
     * @var array
     */
    protected $_options = [
        'configuration'     => [],
        'type'              => 'array',
        'from_file'         => false
    ];

    /**
     * create manage instance
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->_options = array_merge($this->_options, $options);

        if ($this->_options['from_file']) {
            $this->_configuration = $this->readEventConfiguration(
                $this->_options['configuration'],
                $this->_options['type']
            );
        } else {
            $this->_configuration = array_merge(
                $this->_configuration,
                $this->_options['configuration']
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
        if (!array_key_exists($eventName, $this->_configuration)) {
            throw new \InvalidArgumentException('Event is not defined.');
        }

        if (!array_key_exists($eventName, $this->_events)) {
            $namespace                  = $this->_configuration[$eventName]['object'];
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
        if ($config['from_file']) {
            $this->_configuration = array_replace_recursive(
                $this->_configuration,
                $this->readEventConfiguration($config['configuration'], $config['type'])
            );
        } else {
            $this->_configuration = array_replace_recursive(
                $this->_configuration,
                $config['configuration']
            );
        }

        return $this;
    }

    /**
     * trigger new event with automatic call all subscribed listeners
     *
     * @param string $name
     * @param array $data
     */
    public function triggerEvent($name, $data = [])
    {
        /** @var EventInterface $event */
        $event = $this->getEventObject($name);

        if (!$event) {
            throw new \UnexpectedValueException($this->getErrors());
        }

        foreach ($this->_configuration as $listener) {
            if ($event->isPropagationStopped()) {
                break;
            }

            foreach ($listener['listeners'] as $eventListener) {
                try {
                    $this->_callFunction($eventListener, $data, $event);
                } catch (\Exception $e) {
                    $this->addError($e);
                }
            }
            
        }
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
     * 
     * @todo other config not only from files
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
                $file   = new \SplFileObject($configuration, "r");
                $config = $file->fread($file->getSize());
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
        return $this->_configuration;
    }

    public function getErrors()
    {
        return $this->_errorList;
    }

    public function hasErrors()
    {
        return $this->_hasErrors;
    }

    public function clearErrors()
    {
        $this->_errorList = [];
        $this->_hasErrors = false;

        return $this;
    }

    public function addError(\Exception $e)
    {
        $this->_errorList[] = $e;
        $this->_hasErrors   = true;

        return $this;
    }
}
