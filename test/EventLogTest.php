<?php
namespace BlueEventTest;

use PHPUnit\Framework\TestCase;
use BlueEvent\Event\Base\EventLog;
use BlueEvent\Event\Base\EventDispatcher;

class EventLogTest extends TestCase
{
    /**
     * store generated log file path
     *
     * @var string
     */
    protected $logPath;

    /**
     * test that log file was created correctly
     */
    public function testEventLog()
    {
        $instance = new EventLog([
            'log_object' => false,
            'log_all_events' => false,
            'log_events' => true,
            'log_config' => [
                'log_path' => $this->logPath,
                'level' => 'debug',
            ],
        ]);

        $instance->logEvents[] = 'some_name';

        $this->assertFileNotExists($this->logPath . EventDispatcherTest::EVENT_LOG_NAME);
        $instance->makeLogEvent('some_name', 'some_listener', EventDispatcher::EVENT_STATUS_OK);
        $this->assertFileExists($this->logPath . EventDispatcherTest::EVENT_LOG_NAME);
    }


    /**
     * test that log file was created correctly
     */
    public function testEventLogWithAllEvents()
    {
        $instance = new EventLog([
            'log_object' => false,
            'log_all_events' => true,
            'log_events' => true,
            'log_config' => [
                'log_path' => $this->logPath,
                'level' => 'debug',
            ],
        ]);

        $instance->logEvents[] = 'some_name';
        $instance->logEvents[] = 'some_name_2';

        $this->assertFileNotExists($this->logPath . EventDispatcherTest::EVENT_LOG_NAME);
        $instance->makeLogEvent('some_name', 'some_listener', EventDispatcher::EVENT_STATUS_OK);
        $instance->makeLogEvent(
            'some_name_2',
            function () {
            },
            EventDispatcher::EVENT_STATUS_OK
        );
        $this->assertFileExists($this->logPath . EventDispatcherTest::EVENT_LOG_NAME);
    }

    public function testEventLogWithGivenLogObject()
    {
        $instance = new EventLog([
            'log_object' => false,
            'log_all_events' => false,
            'log_events' => true,
            'log_config' => [
                'log_path' => $this->logPath,
                'level' => 'debug',
                'storage' => \SimpleLog\Storage\File::class,
            ],
        ]);

        $instance->logEvents[] = 'some_name';

        $this->assertFileNotExists($this->logPath . EventDispatcherTest::EVENT_LOG_NAME);
        $instance->makeLogEvent('some_name', ['class', 'method'], EventDispatcher::EVENT_STATUS_OK);
        $this->assertFileExists($this->logPath . EventDispatcherTest::EVENT_LOG_NAME);
    }

    /**
     * actions launched before test starts
     */
    protected function setUp()
    {
        $this->logPath = __DIR__ . '/log';

        $this->clearLog();
    }

    protected function clearLog()
    {
        $logFile = $this->logPath . EventDispatcherTest::EVENT_LOG_NAME;

        if (file_exists($logFile)) {
            unlink($logFile);
        }
    }

    /**
     * actions launched after test was finished
     */
    protected function tearDown()
    {
        $this->clearLog();
    }
}
