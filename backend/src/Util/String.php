<?php
namespace Games\Util\String;

function fixedExplode(string $delimiter, string $string, int $limit = PHP_INT_MAX): array {
    if (empty($string)) return [];
    return explode($delimiter, $string, $limit);
}
