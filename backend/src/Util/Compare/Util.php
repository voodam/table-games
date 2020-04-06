<?php
namespace Games\Util\Compare\Util;

function max(Comparable ...$values): Comparable {
    return array_reduce($values, max2::class);
}

function max2(Comparable $first, Comparable $second): Comparable {
    return $first->compare($second) === Comparable::LESS ? $second : $first;
}
