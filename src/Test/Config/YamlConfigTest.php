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

use BlueEvent\Event\Config\YamlConfig;
use PHPUnit\Framework\TestCase;

class YamlConfigTest extends TestCase
{
    public function testLoadConfig()
    {
        $arrayConfig = new YamlConfig;
        $config = $arrayConfig->readConfig(
            __DIR__ . '/testConfig/config.yaml'
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
