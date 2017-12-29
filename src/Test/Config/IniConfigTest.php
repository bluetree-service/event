<?php
/**
 * test Event Object class
 *
 * @package     BlueEvent
 * @subpackage  Test
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 */
namespace BlueEvent\Test\Config;

use BlueEvent\Event\Config\IniConfig;
use PHPUnit\Framework\TestCase;

class IniConfigTest extends TestCase
{
    public function testLoadConfig()
    {
        $arrayConfig = new IniConfig;
        $config = $arrayConfig->readConfig(
            __DIR__ . '/testConfig/config.ini'
        );

        $this->assertEquals(
            [
                'test_event_code' => [
                    'object'    => 'BlueEvent\Event\BaseEvent',
                    'listeners' => [
                        'ClassOne::method',
                        'ClassSecond::method',
                        'someFunction',
                    ]
                ]
            ],
            $config
        );
    }
}
