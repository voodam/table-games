<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;

class CardRecvMsg extends Enum {
    private const DETERMINE_TRUMP = 'determineTrump';
    private const PUT_CARD = 'putCard';
}
