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
use ClassEvent\Event\Base\EventManager;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * store information that even was triggered
     *
     * @var bool
     */
    public static $eventTriggered = false;

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
    }

    /**
     * test read configuration
     *
     * @todo check other data types
     */
    public function testSetEventManagerConfiguration()
    {
        $eventManager = new EventManager;
        $eventManager->setEventConfiguration($this->getEventConfig());

        $this->assertEquals($this->getEventConfig()['configuration'], $eventManager->getEventConfiguration());
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
                    ]
                ]
            ],
        ]);
        EventDispatcher::triggerEvent('test_event');

        $this->assertTrue(self::$eventTriggered);
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
        self::$eventTriggered = true;
    }
}
