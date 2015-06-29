<?php
/**
 * test Event Dispatcher class
 *
 * @package     ClassEvent
 * @subpackage  Test
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 */
namespace ClassEvent\Test;

use ClassEvent\Event\EventDispatcher;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * store information that even was triggered
     *
     * @var bool
     */
    public static $eventTriggered = 0;

    /**
     * test that dispatcher trigger exception if try to call method before initialization
     */
    public function testCallMethodIfDispatcherNotInitialized()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Event Dispatcher must be initialized.'
        );

        EventDispatcher::getErrors();
    }

    /**
     * test event initialize
     */
    public function testEventInstanceCreation()
    {
        $this->assertFalse(EventDispatcher::isInitialized());
        EventDispatcher::init();
        $this->assertNotFalse(EventDispatcher::isInitialized());

        EventDispatcher::init([
            'options' => ['instance_name' => 'instance_1']
        ]);
        $this->assertNotFalse(EventDispatcher::isInitialized('instance_1'));

        $this->assertEquals(
            [],
            EventDispatcher::getEventConfiguration()
        );

        EventDispatcher::init([
            'options' => [
                'instance_name' => 'instance_2',
                'event_manager' => new \ClassEvent\Event\Base\EventManager
            ]
        ]);

        $this->assertEquals(
            [],
            EventDispatcher::getEventConfiguration()
        );
    }

    /**
     * initialize dispatcher with error
     */
    public function testGetNoneExistingInstance()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Instance: not_exists don\'t exists'
        );

        EventDispatcher::getEventConfiguration('not_exists');
    }

    /**
     * test that dispatcher throw exception if event manager is incorrect
     */
    public function testEventInitializeWithError()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Incorrect event manager instance.'
        );

        EventDispatcher::init([
            'options' => [
                'instance_name' => 'error_instance_1',
                'event_manager' => new IncorrectEventManager
            ]
        ]);
    }

    /**
     * test that dispatcher throw exception if event manager is incorrect
     */
    public function testEventInitializeWithErrorSecond()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Incorrect event manager instance.'
        );

        EventDispatcher::init([
            'options' => [
                'instance_name' => 'error_instance_2',
                'event_manager' => 'ClassEvent\Test\IncorrectEventManager'
            ]
        ]);
    }

    /**
     * test read configuration
     */
    public function testSetEventDispatcherConfiguration()
    {
        EventDispatcher::init();
        EventDispatcher::setEventConfiguration($this->getEventConfig());

        $this->assertEquals(
            $this->getEventConfig()['events'],
            EventDispatcher::getEventConfiguration()
        );
    }

    /**
     * test that event is called correctly
     */
    public function testTriggerEvent()
    {
        EventDispatcher::init();
        EventDispatcher::setEventConfiguration([
            'events' => [
                'test_event' => [
                    'object'    => 'ClassEvent\Event\BaseEvent',
                    'listeners' => [
                        'ClassEvent\Test\EventDispatcherTest::trigger',
                        function ($attr, $event) {
                            self::$eventTriggered += $attr['value'];
                        }
                    ]
                ],
            ],
        ]);

        EventDispatcher::triggerEvent('test_event', ['value' => 2]);

        $this->assertEquals(3, self::$eventTriggered);
    }

    public function testTriggerWithStopPropagation()
    {

    }

    public function testAddListenerAndTriggerEvent()
    {

    }

    /**
     * test getting all called events
     */
    public function testGettingAllCreatedEvents()
    {
        $events = EventDispatcher::getCalledEvents();

        $this->assertArrayHasKey('test_event', $events);
    }

    /**
     * test dispatcher error handling
     * 
     * @todo create error and clear
     */
    public function testErrorHandling()
    {
        $this->assertEquals([], EventDispatcher::getErrors());
        $this->assertFalse(EventDispatcher::hasErrors());
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
            'options' => [
                'type'      => 'array',
                'from_file' => false,
            ]
        ];
    }

    /**
     * method to test event triggering
     */
    public static function trigger()
    {
        self::$eventTriggered++;
    }
}
