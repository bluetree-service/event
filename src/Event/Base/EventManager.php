<?php

namespace ClassEvent\Event\Base;

class EventManager implements EventDispatcherInterface
{
    protected $_configuration = [];

    protected $_events = [];

    protected $_errors;

    protected $_hasErrors = false;

    protected $_errorList = [];

    protected $_logEvents = false;

    protected $_eventsToLog = [];

    public function __construct($configurationPath)
    {
        $this->configuration = $this->readEventConfiguration($configurationPath);
    }

    public function getEventObject($eventName)
    {
        try {
            if (!array_key_exists($eventName, $this->_configuration)) {
                throw new \InvalidArgumentException('Event is not defined.');
            }

            if (!array_key_exists($eventName, $this->_events)) {
                $namespace                  = $this->_configuration[$eventName]['object'];
                $this->_events[$eventName]  = new $namespace;
            }
        } catch (\Exception $e) {
            $this->addError($e);
            return false;
        }

        return $this->_events[$eventName];
    }

    public function setEventConfiguration($configuration)
    {
        if (is_string($configuration)) {
            $this->_configuration = array_merge_recursive(
                $this->_configuration,
                $this->readEventConfiguration($configuration)
            );
        } elseif (is_array($configuration)) {
            $this->_configuration = array_merge_recursive($this->_configuration, $configuration);
        }

        return $this;
    }

    public function getEventListeners($name)
    {
        
    }

    public function callFunction($listener, array $data, EventInterface $event)
    {
        
    }

    public function readEventConfiguration($configuration)
    {
        return null;
    }

    public function logEvent()
    {
        
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
