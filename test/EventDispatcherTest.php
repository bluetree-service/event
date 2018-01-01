<?php
/**
 * test Event Dispatcher class
 *
 * @package     BlueEvent
 * @subpackage  Test
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 */
namespace BlueEventTest;

use BlueEvent\Event\Base\Interfaces\EventInterface;
use BlueEvent\Event\Base\EventDispatcher;
use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    /**
     * name of test event log file
     */
    const EVENT_LOG_NAME = '/debug.log';

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
        $this->logPath = __DIR__ . '/log';

        $this->clearLog();
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Incorrect configuration type: incorrect
     */
    public function testTryToLoadConfigWithIncorrectType()
    {
        $eventDispatcher = new EventDispatcher;

        $eventDispatcher->readEventConfiguration(
            $this->getEventFileConfigPath('incorrect'),
            'incorrect'
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
                    'BlueEventTest\EventDispatcherTest::trigger',
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

    public function testTriggerNoneExistingEvent()
    {
        $instance = new EventDispatcher;

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
                    'BlueEventTest\EventDispatcherTest::triggerStop',
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

    public function testAddListenerForNoneExistingKey()
    {
        $instance = new EventDispatcher;

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

        $this->assertEquals(8, self::$eventTriggered);
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
                    'BlueEventTest\EventDispatcherTest::trigger'
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

        $this->assertEquals(11, self::$eventTriggered);
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
                    'BlueEventTest\EventDispatcherTest::triggerError',
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
                'object'    => InvalidEventObject::class,
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
                'log_object' => false,
                'log_config' => [
                    'log_path' => './log',
                    'level' => 'debug',
                    'storage' => \SimpleLog\Storage\File::class,
                ],
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
        $instance->setEventLog(false);
        $this->assertFalse($instance->getConfiguration('log_events'));
        $instance->setEventLog(true);
        $this->assertTrue($instance->getConfiguration('log_events'));

        $instance = new EventDispatcher([
            'log_events' => true
        ]);

        $this->assertTrue($instance->getConfiguration('log_events'));
        $instance->setEventLog(false);
        $this->assertFalse($instance->getConfiguration('log_events'));
    }

    /**
     * test that log file was created correctly
     */
    public function testEventLog()
    {
        $instance = new EventDispatcher([
            'log_events' => true,
            'log_config' => ['log_path' => $this->logPath],
        ]);
        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    [__CLASS__, 'trigger'],
                    'BlueEventTest\EventDispatcherTest::triggerError',
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
            'log_object'        => new \SimpleLog\Log,
        ]);

        $instance->setEventLog(true);

        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    [new self, 'trigger']
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

        $instance->setEventLog(true)->logAllEvents();

        $this->assertTrue($instance->getConfiguration('log_all_events'));

        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEventTest\EventDispatcherTest::trigger',
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

        $instance->setEventLog(true);
        $instance->logEvent(['test_event']);

        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEventTest\EventDispatcherTest::trigger',
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

        return __DIR__ . '/Config/testConfig/config.' . $extension;
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
     *
     * @throws \Exception
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

    protected function clearLog()
    {
        $logFile = $this->logPath . self::EVENT_LOG_NAME;

        if (file_exists($logFile)) {
            unlink($logFile);
        }
    }

    /**
     * actions launched after test was finished
     */
    protected function tearDown()
    {
        $this->clearLog();
    }
}
