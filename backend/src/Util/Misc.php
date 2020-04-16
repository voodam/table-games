<?php
namespace Games\Util\Misc;

function getArg(int $index, $default = null) {
    global $argv;
    return isset($argv[$index]) && !empty($argv[$index]) ? $argv[$index] : $default;
}
