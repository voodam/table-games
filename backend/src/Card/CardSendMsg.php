<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;

class CardSendMsg extends Enum {
    private const YOUR_TEAM = 'yourTeam';
    private const DEAL = 'deal';
    private const PLAYER_PUTS_CARD = 'playerPutsTrump';
    private const ASK_TRUMP = 'askTrump';
    private const PLAYER_ASKS_TRUMP = 'playerAsksTrump';
    private const TRUMP_IS = 'trumpIs';
    private const YOUR_PARTIE_SCORE = 'yourPartieScore';
}
