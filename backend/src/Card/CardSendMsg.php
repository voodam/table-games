<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;

class CardSendMsg extends Enum {
    // Should be sent in trick
    private const PLAYER_PUTS_CARD = 'playerPutsCard';
    
    // Should be sent in partie
    private const DEAL = 'deal';
    private const TRICK_WINNER_IS = 'trickWinnerIs';
    private const ASK_TRUMP = 'askTrump'; // optionally
    private const PLAYER_DETERMS_TRUMP = 'playerDetermsTrump'; // optionally
    private const TRUMP_IS = 'trumpIs'; // optionally
    
    // Should be sent in game (optionally)
    private const YOUR_PARTIE_SCORE = 'yourPartieScore';
}
