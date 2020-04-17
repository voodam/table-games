<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;

class CardRecvMsg extends Enum {
    // Should be recieved in partie
    private const DETERMINE_TRUMP = 'determineTrump';
    
    // Should be recieved in game
    private const PUT_CARD = 'putCard'; // required
}
