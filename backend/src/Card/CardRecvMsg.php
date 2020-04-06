<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;

class CardRecvMsg extends Enum {
    private const DETERM_TRUMP = 'determTrump';
    private const PUT_CARD = 'putCard';
}
