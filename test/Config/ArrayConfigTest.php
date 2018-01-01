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

use BlueEvent\Event\Config\ArrayConfig;
use PHPUnit\Framework\TestCase;

class ArrayConfigTest extends TestCase
{
    public function testLoadConfig()
    {
        $arrayConfig = new ArrayConfig;
        $config = $arrayConfig->readConfig(
            $this->getEventFileConfigPath()
        );

        $this->assertEquals(include $this->getEventFileConfigPath(), $config);
    }

    /**
     * config data for test from file
     *
     * @return string
     */
    public function getEventFileConfigPath()
    {
        return __DIR__ . '/testConfig/config.php';
    }
}
