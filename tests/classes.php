<?php

$src = [
    'exceptions//InvalidTransportException.php',
    'GelfTarget.php',
];

$generator = classGenerator($src);

foreach ($generator as $class) {
    require_once dirname(__DIR__) . '/src/' . $class;
}

function classGenerator($classes) {
    foreach ($classes as $class) {
        yield $class;
    }
}