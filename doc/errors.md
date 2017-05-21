# Errors
Their always is possibility that something goes wrong and some of the listeners
crash whole system. To avoid that situation _EventDispatcher_ execute all listeners
in `try/catch` block and store all information about exceptions.  
That solution allow to continue system work event if some listeners throw exception
and if that necessary check _EventDispatcher_ error status and make some special
actions when listener throw exception.  
To handle that feature _EventDispatcher_ provide three methods:

* **getErrors** - return all listeners exception objects as array
* **hasErrors** - return `true` if some of listeners throw exception
* **clearErrors** - clear error list and set has errors to false

Each exception log listener execution with `error` status

## Error list
Example of listener exception list:

```php
array (
  0 => 
  array (
    'message' => 'Test error',
    'line' => 532,
    'file' => '/projects/event/src/Test/EventDispatcherTest.php',
    'trace' => '#0 [internal function]: BlueEvent\\Test\\EventDispatcherTest::triggerError(Array, Object(BlueEvent\\Event\\BaseEvent))
#1 /home/chajr/projects/class-event/src/Event/Base/EventDispatcher.php(203): call_user_func_array(\'BlueEvent\\\\Test...\', Array)
#2 /home/chajr/projects/class-event/src/Event/Base/EventDispatcher.php(154): BlueEvent\\Event\\Base\\EventDispatcher->_callFunction(\'BlueEvent\\\\Test...\', Array, Object(BlueEvent\\Event\\BaseEvent))
#3 /home/chajr/projects/class-event/src/Test/EventDispatcherTest.php(234): BlueEvent\\Event\\Base\\EventDispatcher->triggerEvent(\'test_event\')
#4 [internal function]: BlueEvent\\Test\\EventDispatcherTest->testTriggerEventWithError()
#5 phar:///usr/local/bin/phpunit/phpunit/Framework/TestCase.php(846): ReflectionMethod->invokeArgs(Object(BlueEvent\\Test\\EventDispatcherTest), Array)
#6 phar:///usr/local/bin/phpunit/phpunit/Framework/TestCase.php(731): PHPUnit_Framework_TestCase->runTest()
#7 phar:///usr/local/bin/phpunit/phpunit/Framework/TestResult.php(609): PHPUnit_Framework_TestCase->runBare()
#8 phar:///usr/local/bin/phpunit/phpunit/Framework/TestCase.php(687): PHPUnit_Framework_TestResult->run(Object(BlueEvent\\Test\\EventDispatcherTest))
#9 phar:///usr/local/bin/phpunit/phpunit/Framework/TestSuite.php(716): PHPUnit_Framework_TestCase->run(Object(PHPUnit_Framework_TestResult))
#10 phar:///usr/local/bin/phpunit/phpunit/Framework/TestSuite.php(716): PHPUnit_Framework_TestSuite->run(Object(PHPUnit_Framework_TestResult))
#11 phar:///usr/local/bin/phpunit/phpunit/Framework/TestSuite.php(716): PHPUnit_Framework_TestSuite->run(Object(PHPUnit_Framework_TestResult))
#12 phar:///usr/local/bin/phpunit/phpunit/TextUI/TestRunner.php(388): PHPUnit_Framework_TestSuite->run(Object(PHPUnit_Framework_TestResult))
#13 phar:///usr/local/bin/phpunit/phpunit/TextUI/Command.php(151): PHPUnit_TextUI_TestRunner->doRun(Object(PHPUnit_Framework_TestSuite), Array)
#14 phar:///usr/local/bin/phpunit/phpunit/TextUI/Command.php(103): PHPUnit_TextUI_Command->run(Array, true)
#15 /usr/local/bin/phpunit(620): PHPUnit_TextUI_Command::main()
#16 {main}',
  ),
)
```
