<?php
namespace Games\Util\Iter;

function filter(iterable $iterable, callable $predicate, int $flag = 0): array {
    return array_values(array_filter(toArray($iterable), $predicate, $flag));
}

function any(iterable $iterable, callable $predicate): bool {
    foreach (toArray($iterable) as $item) {
        if ($predicate($item)) {
            return true;
        }
    }
    return false;
}

function getFirstKey(iterable $iterable, callable $predicate, $defaultValue = null) {
    foreach ($iterable as $key => $value) {
        if ($predicate($value, $key)) {
            return $key;
        }
    }
    return $defaultValue;
}

function toArray(iterable $iterable): array {
    return $iterable instanceof \Traversable ? iterator_to_array($iterable) : $iterable;
}
