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
use ClassEvent\Event\Base\Interfaces\EventManagerInterface;
use ClassEvent\Event\Base\EventManager;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test event initialize
     */
    public function testEventInstanceCreation()
    {
        EventDispatcher::init('');

        $this->assertNotNull(EventDispatcher::getInstance());
        $this->assertTrue(EventDispatcher::getInstance() instanceof EventManagerInterface);
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

        $this->assertEquals($this->getEventConfig(), $eventManager->getEventConfiguration());
    }

    /**
     * config data for test
     *
     * @return array
     */
    public function getEventConfig()
    {
        return [
            'test_event_code' => [
                'object'    => 'ClassEvent\Event\BaseEvent',
                'listeners' => [
                    'ClassOne::method',
                    'ClassSecond::method',
                    'someFunction',
                ]
            ]
        ];
    }
}
