<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;

class CardSendMsg extends Enum {
    // Should be sent in trick
    private const PLAYER_PUTS_CARD = 'playerPutsCard'; // required
    private const TRICK_WINNER_IS = 'trickWinnerIs';
    
    // Should be sent in partie
    private const DEAL = 'deal'; // required
    private const ASK_TRUMP = 'askTrump'; // required
    private const TRUMP_IS = 'trumpIs'; // required
    private const YOUR_PARTIE_SCORE = 'yourPartieScore';
    private const PLAYER_DETERMS_TRUMP = 'playerDetermsTrump';
}
