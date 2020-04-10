<?php
require __DIR__ . '/vendor/autoload.php';
use Games\Test\Unit\GoatTrumpTest;

foreach ([
    new GoatTrumpTest()
] as $test) {
    $test->run();
}
