<?php
namespace Games\Util\Iter;

function filter(iterable $iterable, callable $predicate, int $flag = 0): array {
    return array_values(array_filter(toArray($iterable), $predicate, $flag));
}

function getOneMaybe(iterable $iterable, callable $predicate) {
    $items = filter($iterable, $predicate);
    $itemsNumber = count($items);
    if ($itemsNumber > 1) throw \LogicException("Must be zero or none items, given: $itemsNumber");
    return $items[0] ?? null;
}

function any(iterable $iterable, callable $predicate): bool {
    foreach ($iterable as $item) {
        if ($predicate($item)) {
            return true;
        }
    }
    return false;
}

function flat(iterable $iterable): array {
    $items = [];
    foreach ($iterable as $arr) {
        $items = array_merge($items, $arr);
    }
    return $items;
}

function randomValue(iterable $iterable) {
    checkCountable($iterable);
    if (empty($iterable)) throw new \UnderflowException('Collection is empty');
    return array_values(toArray($iterable))[mt_rand(0, count($iterable) - 1)];
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

function isCountable($value): bool {
    return is_array($value) || $value instanceof \Countable;
}

function checkCountable($value): void {
    if (!isCountable($value)) throw new \InvalidArgumentException('Given argument must be instance of Countable or array');
}
