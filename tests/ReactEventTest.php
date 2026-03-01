<?php

/**
 * @author Michał Adamiak <michal.adamiak@orba.co>
 * @copyright Copyright (C) 2026 Orba Sp. z o.o. (http://orba.pl)
 */

declare(strict_types=1);


use BlueEvent\Event\Base\EventDispatcher;
use PHPUnit\Framework\TestCase;
use BlueEvent\Event\Parallel\ReactListener;

class ReactEventTest extends TestCase
{
    public function setUp(): void
    {
        $this->clean();
    }

    public function tearDown(): void
    {
        $this->clean();
    }

    protected function clean(): void
    {
        $dir = __DIR__ . '/log';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        foreach (glob($dir . '/*.txt') as $file) {
            unlink($file);
        }
    }

    /**
     * @throws \JsonException
     * @throws \Exception
     */
    public function testCreateEventDispatcherForReact(): void
    {
        $instance = $this->newObject();

        $dir = __DIR__;
        $process1 = "php $dir/TestingProcess.php test_file_1 param2";
        $process2 = "php $dir/TestingProcess.php test_file_2 param4";

        $instance->setEventConfiguration([
            'test_event' => [
                'object' => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    new ReactListener($process1)
                ]
            ],
            'test_event2' => [
                'object' => 'BlueEvent\Event\BaseEvent',
                'listeners' => [
                    new ReactListener($process2)
                ]
            ],
        ]);

        $this->assertEquals(0, \BlueEvent\Event\BaseEvent::getLaunchCount());

        $instance->triggerEvent('test_event');

        $this->assertEmpty($instance->getErrors());
        $this->assertEquals(1, \BlueEvent\Event\BaseEvent::getLaunchCount());
        $this->assertFileExists($dir . '/log/test_file_1.txt');
        $this->assertStringContainsString(
            'arg1: test_file_1
arg2: param2
eventData: a:3:{s:9:"eventName";s:10:"test_event";s:10:"parameters";a:0:{}s:5:"count";i:1;}',
            file_get_contents($dir . '/log/test_file_1.txt')
        );

        $instance->triggerEvent('test_event2');

        $this->assertFileExists($dir . '/log/test_file_2.txt');
        $this->assertStringContainsString(
            'arg1: test_file_2
arg2: param4
eventData: a:3:{s:9:"eventName";s:11:"test_event2";s:10:"parameters";a:0:{}s:5:"count";i:2;}',
            file_get_contents($dir . '/log/test_file_2.txt')
        );
    }

    /**
     */
    protected function newObject(): EventDispatcher
    {
        \BlueEvent\Event\BaseEvent::resetLaunchCount();

        return new EventDispatcher();
    }
}
