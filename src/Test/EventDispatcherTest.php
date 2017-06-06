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
    protected $logPath;

    /**
     * actions launched before test starts
     */
    protected function setUp()
    {
        $this->logPath = dirname(__FILE__) . '/log';

        if (file_exists($this->logPath . self::EVENT_LOG_NAME)) {
            unlink($this->logPath . self::EVENT_LOG_NAME);
        }
    }

    /**
     * test event initialize
     *
     * @param array $options
     * @dataProvider configDataProvider
     */
    public function testEventCreation($options)
    {
        $instance = new EventDispatcher;
        $this->assertInstanceOf('BlueEvent\Event\Base\EventDispatcher', $instance);
        $this->assertFalse($instance->hasErrors());

        $instance = new EventDispatcher($options);
        $this->assertEquals($options['events'], $instance->getEventConfiguration());
    }

    /**
     * test read configuration
     *
     * @param array $options
     * @dataProvider configDataProvider
     */
    public function testSetEventDispatcherConfiguration($options)
    {
        $eventDispatcher = new EventDispatcher;
        $eventDispatcher->setEventConfiguration($options['events']);

        $this->assertEquals($options['events'], $eventDispatcher->getEventConfiguration());

        $eventDispatcher->setEventConfiguration([
            'test_event_code' => [
                'listeners' => [
                    'newListener'
                ]
            ]
        ]);

        $options['events']['test_event_code']['listeners'][] = 'newListener';
        $this->assertEquals($options['events'], $eventDispatcher->getEventConfiguration());

        unset($options['events']['test_event_code']['listeners'][3]);

        $eventDispatcher   = new EventDispatcher;
        $eventDispatcher->readEventConfiguration(
            $this->getEventFileConfigPath('array'),
            'array'
        );
        $this->assertEquals($options['events'], $eventDispatcher->getEventConfiguration());

        $eventDispatcher   = new EventDispatcher;
        $eventDispatcher->readEventConfiguration(
            $this->getEventFileConfigPath('json'),
            'json'
        );
        $this->assertEquals($options['events'], $eventDispatcher->getEventConfiguration());

        $eventDispatcher   = new EventDispatcher;
        $eventDispatcher->readEventConfiguration(
            $this->getEventFileConfigPath('ini'),
            'ini'
        );
        $this->assertEquals($options['events'], $eventDispatcher->getEventConfiguration());

        $eventDispatcher = new EventDispatcher(
            [
                'from_file' => $this->getEventFileConfigPath('xml'),
                'type'      => 'xml'
            ]
        );
        $this->assertEquals($options['events'], $eventDispatcher->getEventConfiguration());

        $eventDispatcher = new EventDispatcher(
            [
                'from_file' => $this->getEventFileConfigPath('yaml'),
                'type'      => 'yaml'
            ]
        );
        $this->assertEquals($options['events'], $eventDispatcher->getEventConfiguration());
    }

    /**
     * check for error if configuration file don't exists
     *
     * @expectedException \InvalidArgumentException
     */
    public function testTryToLoadConfigFromMissingFile()
    {
        $eventDispatcher = new EventDispatcher;

        $eventDispatcher->readEventConfiguration(
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
                    function ($event) {
                        /** @var $event \BlueEvent\Event\BaseEvent */
                        self::$eventTriggered += $event->getEventParameters()['value'];
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
                    function ($event) {
                        /** @var $event \BlueEvent\Event\BaseEvent */
                        self::$eventTriggered += $event->getEventParameters()['value'];
                    }
                ]
            ],
        ]);

        $this->assertEquals(3, self::$eventTriggered);

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
                function ($event) {
                    /** @var $event \BlueEvent\Event\BaseEvent */
                    self::$eventTriggered += $event->getEventParameters()['value'];
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

        $instance->triggerEvent('invalid_object_event');
    }

    /**
     * test that event log can be enabled/disabled
     */
    public function testGetConfiguration()
    {
        $instance = new EventDispatcher;

        $this->assertEquals(
            [
                'type' => 'array',
                'log_events' => false,
                'log_all_events' => true,
                'from_file' => false,
                'log_path' => false,
                'log_object' => false,
                'events' => [],
            ],
            $instance->getConfiguration()
        );
    }

    /**
     * test that event log can be enabled/disabled
     */
    public function testEnableAndDisableEventLog()
    {
        $instance = new EventDispatcher;

        $this->assertFalse($instance->getConfiguration('log_events'));
        $instance->disableEventLog();
        $this->assertFalse($instance->getConfiguration('log_events'));
        $instance->enableEventLog();
        $this->assertTrue($instance->getConfiguration('log_events'));

        $instance = new EventDispatcher([
            'log_events' => true
        ]);

        $this->assertTrue($instance->getConfiguration('log_events'));
        $instance->disableEventLog();
        $this->assertFalse($instance->getConfiguration('log_events'));
    }

    /**
     * test that log file was created correctly
     */
    public function testEventLog()
    {
        $instance = new EventDispatcher([
            'log_events'    => true,
            'log_path'      => $this->logPath,
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

        $this->assertFileNotExists($this->logPath . self::EVENT_LOG_NAME);
        $instance->triggerEvent('test_event');
        $this->assertFileExists($this->logPath . self::EVENT_LOG_NAME);
    }

    /**
     * check calling additional log object and listener as array
     */
    public function testEventLogWithExternalObjects()
    {
        $instance = new EventDispatcher([
            'log_all_events'    => true,
            'log_path'          =>  $this->logPath,
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
        $this->assertFileExists($this->logPath);
    }

    /**
     * test log event with direct given all events
     */
    public function testEventLogWithGivenEvents()
    {
        $instance = new EventDispatcher([
            'log_path' =>  $this->logPath
        ]);

        $instance->enableEventLog()->logAllEvents();

        $this->assertTrue($instance->getConfiguration('log_all_events'));

        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEvent\Test\EventDispatcherTest::trigger',
                ]
            ],
        ]);

        $instance->triggerEvent('test_event');
        $this->assertFileExists($this->logPath);
    }


    /**
     * test log event with direct given specified event key
     */
    public function testEventLogWithSpecifiedEvents()
    {
        $instance = new EventDispatcher([
            'log_path'          =>  $this->logPath,
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
        $this->assertFileExists($this->logPath);
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
                'options' => [
                    'type'      => 'array',
                    'from_file' => false,
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
     * @param EventInterface $event
     */
    public static function triggerStop(EventInterface $event)
    {
        $event->stopPropagation();
        self::$eventTriggered++;
    }

    /**
     * test that event is called correctly
     */
    public function testTriggerMultipleEvents()
    {
        $testData = [];
        $instance = new EventDispatcher(['events' => [
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    function ($event) use (&$testData) {
                        /** @var $event \BlueEvent\Event\BaseEvent */
                        $testData['test_event'] = $event->getEventParameters();
                    }
                ]
            ],
            'test_event_other' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    function ($event) use (&$testData) {
                        /** @var $event \BlueEvent\Event\BaseEvent */
                        $testData['test_event_other'] = $event->getEventParameters();
                    }
                ]
            ],
        ]]);

        $instance->triggerEvent('test_event', ['value' => 2]);

        $this->assertArrayHasKey('test_event', $testData);
        $this->assertEquals(
            ['value' => 2],
            $testData['test_event']
        );

        $instance->triggerEvent('test_event_other', ['value' => 5]);

        $this->assertArrayHasKey('test_event_other', $testData);
        $this->assertEquals(
            ['value' => 5],
            $testData['test_event_other']
        );
    }

    /**
     * actions launched after test was finished
     */
    protected function tearDown()
    {
        if (file_exists($this->logPath . self::EVENT_LOG_NAME)) {
            unlink($this->logPath . self::EVENT_LOG_NAME);
        }
    }
}
