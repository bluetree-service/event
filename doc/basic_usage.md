# Basic usage
To use events EventDispatcher class instance must be created. 

## Create EventDispatcher Object

```php
$EventDispatcher = new EventDispatcher([], [
    'test_event_code' => [
        'object'    => 'ClassEvent\Event\BaseEvent',
        'listeners' => [
            'callable string or anonymous function',
        ]
    ]
]);
```