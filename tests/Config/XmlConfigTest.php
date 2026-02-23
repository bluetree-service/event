<?php
/**
 * test Event Object class
 *
 * @package     BlueEvent
 * @subpackage  Test
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 */
namespace BlueEventTest\Config;

use BlueEvent\Event\Config\XmlConfig;
use PHPUnit\Framework\TestCase;

class XmlConfigTest extends TestCase
{
    public function testLoadConfig()
    {
        $arrayConfig = new XmlConfig;
        $config = $arrayConfig->readConfig(
            __DIR__ . '/testConfig/config.xml'
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
