<?php
/**
 * test Event Dispatcher class
 *
 * @package     BlueEvent
 * @subpackage  Test
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 */
namespace BlueEvent\Test;

use BlueEvent\Event\Base\Interfaces\EventInterface;
use BlueEvent\Event\Base\EventDispatcher;
use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    /**
     * name of test event log file
     */
    const EVENT_LOG_NAME = '/events.log';

    /**
     * store information that even was triggered
     *
     * @var bool
     */
    public static $eventTriggered = 0;

    /**
     * store generated log file path
     *
     * @var string
     */
    protected $_logPath;

    /**
     * actions launched before test starts
     */
    protected function setUp()
    {
        $this->_logPath = dirname(__FILE__) . '/log';

        if (file_exists($this->_logPath . self::EVENT_LOG_NAME)) {
            unlink($this->_logPath . self::EVENT_LOG_NAME);
        }
    }

    /**
     * test event initialize
     *
     * @param array $events
     * @param array $options
     * @dataProvider configDataProvider
     */
    public function testEventCreation($events, $options)
    {
        $instance = new EventDispatcher;
        $this->assertInstanceOf('BlueEvent\Event\Base\EventDispatcher', $instance);
        $this->assertFalse($instance->hasErrors());

        $instance = new EventDispatcher($options, $events);
        $this->assertEquals($events, $instance->getEventConfiguration());
    }

    /**
     * test read configuration
     *
     * @param array $events
     * @dataProvider configDataProvider
     */
    public function testSetEventDispatcherConfiguration($events)
    {
        $EventDispatcher = new EventDispatcher;
        $EventDispatcher->setEventConfiguration($events);

        $this->assertEquals($events, $EventDispatcher->getEventConfiguration());

        $EventDispatcher->setEventConfiguration([
            'test_event_code' => [
                'listeners' => [
                    'newListener'
                ]
            ]
        ]);

        $events['test_event_code']['listeners'][] = 'newListener';
        $this->assertEquals($events, $EventDispatcher->getEventConfiguration());

        unset($events['test_event_code']['listeners'][3]);

        $EventDispatcher   = new EventDispatcher;
        $EventDispatcher->readEventConfiguration(
            $this->getEventFileConfigPath('array'),
            'array'
        );
        $this->assertEquals($events, $EventDispatcher->getEventConfiguration());

        $EventDispatcher   = new EventDispatcher;
        $EventDispatcher->readEventConfiguration(
            $this->getEventFileConfigPath('json'),
            'json'
        );
        $this->assertEquals($events, $EventDispatcher->getEventConfiguration());

        $EventDispatcher   = new EventDispatcher;
        $EventDispatcher->readEventConfiguration(
            $this->getEventFileConfigPath('ini'),
            'ini'
        );
        $this->assertEquals($events, $EventDispatcher->getEventConfiguration());

        $EventDispatcher = new EventDispatcher(
            [
                'from_file' => true,
                'type'      => 'xml'
            ],
            $this->getEventFileConfigPath('xml')
        );
        $this->assertEquals($events, $EventDispatcher->getEventConfiguration());

        $EventDispatcher = new EventDispatcher(
            [
                'from_file' => true,
                'type'      => 'yaml'
            ],
            $this->getEventFileConfigPath('yaml')
        );
        $this->assertEquals($events, $EventDispatcher->getEventConfiguration());
    }

    /**
     * check for error if configuration file don't exists
     *
     * @expectedException \InvalidArgumentException
     */
    public function testTryToLoadConfigFromMissingFile()
    {
        $EventDispatcher = new EventDispatcher;

        $EventDispatcher->readEventConfiguration(
            $this->getEventFileConfigPath('txt'),
            'txt'
        );
    }

    /**
     * test that event is called correctly
     */
    public function testTriggerEvent()
    {
        $instance = new EventDispatcher;
        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEvent\Test\EventDispatcherTest::trigger',
                    function ($attr, $event) {
                        self::$eventTriggered += $attr['value'];
                    }
                ]
            ],
        ]);

        $instance->triggerEvent('test_event', ['value' => 2]);

        $this->assertEquals(3, self::$eventTriggered);
    }

    /**
     * test trigger event with stop propagation before next listener
     */
    public function testTriggerWithStopPropagation()
    {
        $instance = new EventDispatcher;
        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEvent\Test\EventDispatcherTest::triggerStop',
                    function ($attr, $event) {
                        self::$eventTriggered += $attr['value'];
                    }
                ]
            ],
        ]);

        $instance->triggerEvent('test_event', ['value' => 2]);

        $this->assertEquals(4, self::$eventTriggered);
    }

    /**
     * test dynamically add new listener or listeners for given event name
     */
    public function testAddListenerAndTriggerEvent()
    {
        $instance = new EventDispatcher;
        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEvent\Test\EventDispatcherTest::trigger'
                ]
            ],
        ]);

        $instance->addEventListener(
            'test_event',
            [
                function ($attr, $event) {
                    self::$eventTriggered += $attr['value'];
                }
            ]
        );

        $instance->triggerEvent('test_event', ['value' => 2]);

        $this->assertEquals(7, self::$eventTriggered);
    }

    /**
     * test trigger event with exception
     */
    public function testTriggerEventWithError()
    {
        $instance = new EventDispatcher;
        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEvent\Test\EventDispatcherTest::triggerError',
                ]
            ],
        ]);

        $this->assertFalse($instance->hasErrors());
        $this->assertEquals([], $instance->getErrors());

        $instance->triggerEvent('test_event');

        $this->assertTrue($instance->hasErrors());
        $this->assertEquals(
            'Test error',
            $instance->getErrors()[0]['message']
        );

        $instance->clearErrors();
        $this->assertFalse($instance->hasErrors());
        $this->assertEquals([], $instance->getErrors());
    }

    /**
     * test return of event object launch count correct value
     */
    public function testEventLaunchCount()
    {
        $instance = new EventDispatcher;
        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEvent\Test\EventDispatcherTest::trigger',
                ]
            ],
        ]);

        /** use static method to avoid launch increment and store in EventDispatcher instance */
        $this->assertEquals(4, \BlueEvent\Event\BaseEvent::getLaunchCount());

        $instance->triggerEvent('test_event');
        $instance->triggerEvent('test_event');

        $this->assertEquals(5, $instance->getEventObject('test_event')->getLaunchCount());
        $this->assertEquals(5, \BlueEvent\Event\BaseEvent::getLaunchCount());
    }

    /**
     * test that event dispatcher throw an exception if we try to get none existing event object
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetNoneExistingObject()
    {
        $instance = new EventDispatcher;

        $instance->getEventObject('none_existing_event');
    }

    /**
     * check for error if invalid object was declared as listener
     *
     * @expectedException \LogicException
     */
    public function testGetInvalidEventObject()
    {
        $instance = new EventDispatcher;

        $instance->setEventConfiguration([
            'invalid_object_event' => [
                'object'    => 'BlueEvent\Test\InvalidEventObject',
                'listeners' => []
            ],
        ]);

        $instance->getEventObject('invalid_object_event');
    }

    /**
     * test get all called events objects
     */
    public function testGettingAllCreatedEvents()
    {
        $instance = new EventDispatcher;

        $this->assertEmpty($instance->getAllEvents());

        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEvent\Test\EventDispatcherTest::triggerError',
                ]
            ],
        ]);
        $this->assertEmpty($instance->getAllEvents());

        $instance->triggerEvent('test_event');

        $this->assertNotEmpty($instance->getAllEvents());
        $this->assertCount(1, $instance->getAllEvents());
    }

    /**
     * test that event log can be enabled/disabled
     */
    public function testEnableAndDisableEventLog()
    {
        $instance = new EventDispatcher;

        $this->assertFalse($instance->isLogEnabled());
        $instance->disableEventLog();
        $this->assertFalse($instance->isLogEnabled());
        $instance->enableEventLog();
        $this->assertTrue($instance->isLogEnabled());

        $instance = new EventDispatcher([
            'log_events' => true
        ]);

        $this->assertTrue($instance->isLogEnabled());
        $instance->disableEventLog();
        $this->assertFalse($instance->isLogEnabled());
    }

    /**
     * test try to add event listener fo none existing event in configuration
     */
    public function testAddEventListenerForNoneExistingEvent()
    {
        $instance = new EventDispatcher;

        $instance->addEventListener('test_event', []);

        $instance->triggerEvent('test_event');

        $this->assertNotEmpty($instance->getAllEvents());
        $this->assertCount(1, $instance->getAllEvents());
    }

    /**
     * test that log file was created correctly
     */
    public function testEventLog()
    {
        $instance = new EventDispatcher([
            'log_events'    => true,
            'log_path'      => $this->_logPath,
        ]);
        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEvent\Test\EventDispatcherTest::trigger',
                    'BlueEvent\Test\EventDispatcherTest::triggerError',
                    function () {

                    }
                ]
            ],
        ]);

        $this->assertFileNotExists($this->_logPath . self::EVENT_LOG_NAME);
        $instance->triggerEvent('test_event');
        $this->assertFileExists($this->_logPath . self::EVENT_LOG_NAME);
    }

    /**
     * check calling additional log object and listener as array
     */
    public function testEventLogWithExternalObjects()
    {
        $instance = new EventDispatcher([
            'log_all_events'    => true,
            'log_path'          =>  $this->_logPath,
            'log_object'        => (new \SimpleLog\Log),
        ]);

        $instance->enableEventLog();

        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    [new \BlueEvent\Test\EventDispatcherTest, 'trigger']
                ]
            ],
        ]);

        $instance->triggerEvent('test_event');
        $this->assertFileExists($this->_logPath);
    }

    /**
     * test log event with direct given all events
     */
    public function testEventLogWithGivenEvents()
    {
        $instance = new EventDispatcher([
            'log_path' =>  $this->_logPath
        ]);

        $instance->enableEventLog()->logAllEvents();

        $this->assertTrue($instance->isLogAllEventsEnabled());

        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEvent\Test\EventDispatcherTest::trigger',
                ]
            ],
        ]);

        $instance->triggerEvent('test_event');
        $this->assertFileExists($this->_logPath);
    }


    /**
     * test log event with direct given specified event key
     */
    public function testEventLogWithSpecifiedEvents()
    {
        $instance = new EventDispatcher([
            'log_path'          =>  $this->_logPath,
            'log_all_events'    => false
        ]);

        $instance->enableEventLog();
        $instance->logEvent(['test_event']);

        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEvent\Test\EventDispatcherTest::trigger',
                ]
            ],
        ]);

        $this->assertEquals(
            ['test_event'],
            $instance->getAllEventsToLog()
        );
        $instance->triggerEvent('test_event');
        $this->assertFileExists($this->_logPath);
    }

    /**
     * config data for test
     *
     * @return array
     */
    public function configDataProvider()
    {
        return [
            [
                'events' => [
                    'test_event_code' => [
                        'object'    => 'BlueEvent\Event\BaseEvent',
                        'listeners' => [
                            'ClassOne::method',
                            'ClassSecond::method',
                            'someFunction',
                        ]
                    ]
                ],
                'options' => [
                    'type'      => 'array',
                    'from_file' => false,
                ]
            ]
        ];
    }

    /**
     * config data for test from file
     *
     * @param string $type
     * @return string
     */
    public function getEventFileConfigPath($type)
    {
        $extension = $type;
        if ($type === 'array') {
            $extension = 'php';
        }

        return dirname(__FILE__) . '/testConfig/config.' . $extension;
    }

    /**
     * method to test event triggering
     */
    public static function trigger()
    {
        self::$eventTriggered++;
    }

    /**
     * method to test event triggering
     */
    public static function triggerError()
    {
        throw new \Exception('Test error');
    }

    /**
     * method to test event triggering
     *
     * @param mixed $attr
     * @param EventInterface $event
     */
    public static function triggerStop($attr, EventInterface $event)
    {
        $event->stopPropagation();
        self::$eventTriggered++;
    }

    /**
     * actions launched after test was finished
     */
    protected function tearDown()
    {
        if (file_exists($this->_logPath . self::EVENT_LOG_NAME)) {
            unlink($this->_logPath . self::EVENT_LOG_NAME);
        }
    }
}
