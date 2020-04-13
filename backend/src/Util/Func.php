<?php
namespace Games\Util\Func;

use function Games\Util\Iter\any;

function noop() {}
function id($val) { return $val; }
function _instanceof(string $class): callable { return fn($val) => $val instanceof $class; }
function _new(string $class): callable { return [new \ReflectionClass($class), 'newInstance']; }
function fst(array $arr) { return $arr[0]; }
function compose(callable $f, callable $g): callable { return fn(...$args) => $f($g(...$args)); }
function argsArrayToRest(callable $f): callable { return fn(array $args) => $f(...$args); }

function repeat(int $times, callable $cbk): void { 
    for ($i = 0; $i < $times; $i++) $cbk(); 
}

function getOrReturn($value, array $types, $method) {
    assert(count($types) === 2);
    $types = array_map(fn ($type) => is_callable($type) ? $type : _instanceof($type), $types);
    assert(any($types, fn (callable $type) => $type($value)));
    
    if (is_string($method)) {
        $method = [$value, $method];
    }
    return $types[0]($value) ? $method() : $value;
}
