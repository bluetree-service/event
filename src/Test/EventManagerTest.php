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
            $this->getEventConfig()['configuration'],
            $instance->getEventConfiguration()
        );
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

        $this->assertEquals(
            $this->getEventConfig()['configuration'],
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
}
