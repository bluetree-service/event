<?php
return [
    'test_event_code' => [
        'object'    => 'BlueEvent\Event\BaseEvent',
        'listeners' => [
            'ClassOne::method',
            'ClassSecond::method',
            'someFunction',
        ]
    ]
];
