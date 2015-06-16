<?php
/**
 * test Event Manager class
 *
 * @package     ClassEvent
 * @subpackage  Test
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 */
namespace Test;

use ClassEvent\Event\Base\Interfaces\EventInterface;
use ClassEvent\Event\Base\EventManager;

class EventManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * store information that even was triggered
     *
     * @var bool
     */
    public static $eventTriggered = 0;

    /**
     * test event initialize
     */
    public function testEventCreation()
    {
        $instance = new EventManager;
        $this->assertTrue($instance instanceof EventManager);
        $this->assertFalse($instance->hasErrors());

        $instance = new EventManager($this->getEventConfig());
        $this->assertEquals(
            $this->getEventConfig()['events'],
            $instance->getEventConfiguration()
        );
    }

    /**
     * test read configuration
     */
    public function testSetEventManagerConfiguration()
    {
        $eventManager = new EventManager;
        $eventManager->setEventConfiguration($this->getEventConfig());

        $this->assertEquals(
            $this->getEventConfig()['events'],
            $eventManager->getEventConfiguration()
        );

        $eventManager->setEventConfiguration([
            'events' => [
                'test_event_code' => [
                    'listeners' => [
                        'newListener'
                    ]
                ]
            ]
        ]);

        $config                                     = $this->getEventConfig()['events'];
        $config['test_event_code']['listeners'][]   = 'newListener';
        $this->assertEquals(
            $config,
            $eventManager->getEventConfiguration()
        );

        $eventManager = new EventManager;
        $eventManager->setEventConfiguration($this->getEventFileConfig('array'));
        $this->assertEquals(
            $this->getEventConfig()['events'],
            $eventManager->getEventConfiguration()
        );

        $eventManager = new EventManager;
        $eventManager->setEventConfiguration($this->getEventFileConfig('json'));
        $this->assertEquals(
            $this->getEventConfig()['events'],
            $eventManager->getEventConfiguration()
        );

        $eventManager = new EventManager;
        $eventManager->setEventConfiguration($this->getEventFileConfig('ini'));
        $this->assertEquals(
            $this->getEventConfig()['events'],
            $eventManager->getEventConfiguration()
        );
    }

    /**
     * test that event is called correctly
     *
     * @todo test with filesystem mocking
     */
    public function testTriggerEvent()
    {
        $instance = new EventManager;
        $instance->setEventConfiguration([
            'events' => [
                'test_event' => [
                    'object'    => 'ClassEvent\Event\BaseEvent',
                    'listeners' => [
                        'Test\EventManagerTest::trigger',
                        function ($attr, $event) {
                            self::$eventTriggered += $attr['value'];
                        }
                    ]
                ],
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
        $instance = new EventManager;
        $instance->setEventConfiguration([
            'events' => [
                'test_event' => [
                    'object'    => 'ClassEvent\Event\BaseEvent',
                    'listeners' => [
                        'Test\EventManagerTest::triggerStop',
                        function ($attr, $event) {
                            self::$eventTriggered += $attr['value'];
                        }
                    ]
                ],
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
        $instance = new EventManager;
        $instance->setEventConfiguration([
            'events' => [
                'test_event' => [
                    'object'    => 'ClassEvent\Event\BaseEvent',
                    'listeners' => [
                        'Test\EventManagerTest::trigger'
                    ]
                ],
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
        $instance = new EventManager;
        $instance->setEventConfiguration([
            'events' => [
                'test_event' => [
                    'object'    => 'ClassEvent\Event\BaseEvent',
                    'listeners' => [
                        'Test\EventManagerTest::triggerError',
                    ]
                ],
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
        $instance = new EventManager;
        $instance->setEventConfiguration([
            'events' => [
                'test_event' => [
                    'object'    => 'ClassEvent\Event\BaseEvent',
                    'listeners' => [
                        'Test\EventManagerTest::triggerError',
                    ]
                ],
            ],
        ]);

        $instance->triggerEvent('test_event');
        $instance->triggerEvent('test_event');

        $this->assertEquals(6, $instance->getEventObject('test_event')->getLaunchCount());
    }

    /**
     * test get all called events objects
     */
    public function testGettingAllCreatedEvents()
    {
        $instance = new EventManager;

        $this->assertEmpty($instance->getAllEvents());

        $instance->setEventConfiguration([
            'events' => [
                'test_event' => [
                    'object'    => 'ClassEvent\Event\BaseEvent',
                    'listeners' => [
                        'Test\EventManagerTest::triggerError',
                    ]
                ],
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
        $instance = new EventManager;

        $this->assertFalse($instance->isLogEnabled());
        $instance->disableEventLog();
        $this->assertFalse($instance->isLogEnabled());
        $instance->enableEventLog();
        $this->assertTrue($instance->isLogEnabled());
    }

    /**
     * test try to add event listener fo none existing event in configuration
     */
    public function testAddEventListenerForNoneExistingEvent()
    {
        $instance = new EventManager;

        $instance->addEventListener('test_event', []);

        $instance->triggerEvent('test_event');

        $this->assertNotEmpty($instance->getAllEvents());
        $this->assertCount(1, $instance->getAllEvents());
    }

    public function testErrorHandling()
    {

    }

    /**
     * config data for test
     *
     * @return array
     */
    public function getEventConfig()
    {
        return [
            'events' => [
                'test_event_code' => [
                    'object'    => 'ClassEvent\Event\BaseEvent',
                    'listeners' => [
                        'ClassOne::method',
                        'ClassSecond::method',
                        'someFunction',
                    ]
                ]
            ],
            'type'      => 'array',
            'from_file' => false,
        ];
    }

    /**
     * config data for test from file
     *
     * @param string $type
     * @return array
     */
    public function getEventFileConfig($type)
    {
        $extension = $type;
        if ($type === 'array') {
            $extension = 'php';
        }

        return [
            'events'    => dirname(__FILE__) . '/testConfig/config.' . $extension,
            'from_file' => true,
            'type'      => $type,
        ];
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
}
