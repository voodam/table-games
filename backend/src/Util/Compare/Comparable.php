<?php
namespace Games\Util\Compare;

interface Comparable {
    const MORE = 1;
    const LESS = -1;
    const EQ = 0;
    
    function compare(self $other): int;
}
