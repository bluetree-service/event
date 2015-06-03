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
     *
     * @todo check other data types
     * @todo read config from file
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

    public function testGettingAllCreatedEvents()
    {

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
     * method to test event triggering
     */
    public static function trigger()
    {
        self::$eventTriggered++;
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
