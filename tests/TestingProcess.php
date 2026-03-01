<?php

$arg1 = $argv[1];
$arg2 = $argv[2];
$event = $argv[3];

file_put_contents(
    __DIR__ . "/log/$arg1.txt",
    "arg1: $arg1\narg2: $arg2\neventData: " . print_r($event, true)
);
