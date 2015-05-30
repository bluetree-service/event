<?php
/**
 * test Event Dispatcher class
 *
 * @package     ClassEvent
 * @subpackage  Test
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 */
namespace Test;

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
     * test event initialize
     */
    public function testEventInstanceCreation()
    {
        EventDispatcher::init();
        $this->assertNotFalse(EventDispatcher::isInitialized());

        EventDispatcher::init([
            'instance_name' => 'new_instance'
        ]);
        $this->assertNotFalse(EventDispatcher::isInitialized('new_instance'));

        $this->assertEquals(
            [],
            EventDispatcher::getEventConfiguration()
        );

        //test with error
        /*EventDispatcher::init([
            'instance_name' => 'error_instance',
            'event_manager' => 'None\Exist\Namespace'
        ]);*/
    }

    /**
     * test read configuration
     *
     * @todo check other data types
     */
    public function testSetEventDispatcherConfiguration()
    {
        EventDispatcher::init();
        EventDispatcher::setEventConfiguration($this->getEventConfig());

        $this->assertEquals(
            $this->getEventConfig()['configuration'],
            EventDispatcher::getEventConfiguration()
        );
    }

    /**
     * test that event is called correctly
     * 
     * @todo test with filesystem mocking
     */
    public function testTriggerEvent()
    {
        EventDispatcher::init();
        EventDispatcher::setEventConfiguration([
            'configuration' => [
                'test_event' => [
                    'object'    => 'ClassEvent\Event\BaseEvent',
                    'listeners' => [
                        'Test\EventDispatcherTest::trigger',
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
            'configuration' => [
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
}
