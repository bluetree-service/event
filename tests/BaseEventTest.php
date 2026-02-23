<?php

/**
 * test Event Object class
 *
 * @package     BlueEvent
 * @subpackage  Test
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 */
namespace BlueEventTest;

use BlueEvent\Event\Base\EventDispatcher;
use PHPUnit\Framework\TestCase;

class BaseEventTest extends TestCase
{
    /**
     * store information that even was triggered
     *
     * @var bool
     */
    public static $eventTriggered = 0;

    /**
     * test return of event object launch count correct value
     */
    public function testEventLaunchCount()
    {
        $instance = new EventDispatcher;
        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    'BlueEvent\Test\BaseEventTest::trigger',
                ]
            ],
        ]);

        /** use static method to avoid launch increment and store in EventDispatcher instance */
        $this->assertEquals(0, \BlueEvent\Event\BaseEvent::getLaunchCount());

        $instance->triggerEvent('test_event');
        $instance->triggerEvent('test_event');

        $this->assertEquals(2, \BlueEvent\Event\BaseEvent::getLaunchCount());
    }

    /**
     * test all methods in base event object
     */
    public function testEventMethods()
    {
        $info = [];

        $instance = new EventDispatcher;
        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    function ($event) use (&$info) {
                        /** @var $event \BlueEvent\Event\BaseEvent */
                        $info[] = $event->getEventCode();
                        $info[] = $event->getEventParameters();
                        $info[] = $event->isPropagationStopped();
                        $info[] = $event->getLaunchCount();
                    },
                ]
            ],
        ]);

        $instance->triggerEvent('test_event', [1, 2]);

        $this->assertEquals(
            [
                'test_event',
                [1, 2],
                false,
                3,
            ],
            $info
        );
    }

    public function testExchangeDateBetweenListeners()
    {
        $info = false;

        $instance = new EventDispatcher;
        $instance->setEventConfiguration([
            'test_event' => [
                'object'    => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    function ($event) {
                        $event->something = true;
                    },
                    function ($event) use (&$info) {
                        $info = $event->something;
                    },
                ]
            ],
        ]);

        $instance->triggerEvent('test_event');

        $this->assertTrue($info);
    }

    /**
     * method to test event triggering
     */
    public static function trigger()
    {
        self::$eventTriggered++;
    }
}
