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

use ClassEvent\Event\EventFacade;

class EventFacadeTest extends \PHPUnit_Framework_TestCase
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

        EventFacade::getErrors();
    }

    /**
     * test event initialize
     */
    public function testEventInstanceCreation()
    {
        $this->assertFalse(EventFacade::isInitialized());
        EventFacade::init();
        $this->assertNotFalse(EventFacade::isInitialized());

        EventFacade::init([], 'instance_1');
        $this->assertNotFalse(EventFacade::isInitialized('instance_1'));

        $this->assertEquals(
            [],
            EventFacade::getEventConfiguration()
        );

        EventFacade::init(
            [
                'options' => [
                    'event_dispatcher' => new \ClassEvent\Event\Base\EventDispatcher
                ]
            ],
            'instance_2'
        );

        $this->assertEquals(
            [],
            EventFacade::getEventConfiguration()
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

        EventFacade::getEventConfiguration('not_exists');
    }

    /**
     * test that dispatcher throw exception if event dispatcher is incorrect
     */
    public function testEventInitializeWithError()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Incorrect event dispatcher instance.'
        );

        EventFacade::init(
            [
                'options' => [
                    'event_dispatcher' => new IncorrectEventDispatcher
                ]
            ],
            'error_instance_1'
        );
    }

    /**
     * test that dispatcher throw exception if event dispatcher is incorrect
     */
    public function testEventInitializeWithErrorSecond()
    {
        $this->setExpectedException(
            'RuntimeException',
            'Incorrect event dispatcher instance.'
        );

        EventFacade::init(
            [
                'options' => [
                    'event_dispatcher' => 'ClassEvent\Test\IncorrectEventDispatcher'
                ]
            ],
            'error_instance_2'
        );
    }

    /**
     * test read configuration
     */
    public function testSetEventFacadeConfiguration()
    {
        EventFacade::init();
        EventFacade::setEventConfiguration($this->getEventConfig());

        $this->assertEquals(
            $this->getEventConfig()['events'],
            EventFacade::getEventConfiguration()
        );
    }

    /**
     * test that event is called correctly
     */
    public function testTriggerEvent()
    {
        EventFacade::init();
        EventFacade::setEventConfiguration([
            'events' => [
                'test_event' => [
                    'object'    => 'ClassEvent\Event\BaseEvent',
                    'listeners' => [
                        'ClassEvent\Test\EventFacadeTest::trigger',
                        function ($attr, $event) {
                            self::$eventTriggered += $attr['value'];
                        }
                    ]
                ],
            ],
        ]);

        EventFacade::triggerEvent('test_event', ['value' => 2]);

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
        $events = EventFacade::getCalledEvents();

        $this->assertArrayHasKey('test_event', $events);
    }

    /**
     * test dispatcher error handling
     */
    public function testErrorHandling()
    {
        EventFacade::init();
        EventFacade::setEventConfiguration([
            'test_event' => [
                'object'    => 'ClassEvent\Event\BaseEvent',
                'listeners' => [
                    'ClassEvent\Test\EventDispatcherTest::triggerError',
                ]
            ],
        ]);

        $this->assertFalse(EventFacade::hasErrors());
        $this->assertEquals([], EventFacade::getErrors());

        EventFacade::triggerEvent('test_event');

        $this->assertTrue(EventFacade::hasErrors());

        EventFacade::clearErrors();
        $this->assertFalse(EventFacade::hasErrors());
        $this->assertEquals([], EventFacade::getErrors());
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
