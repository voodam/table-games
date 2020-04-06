<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;

class CardSendMsg extends Enum {
    private const YOUR_TEAM = 'yourTeam';
    private const DEAL = 'deal';
    private const HE_PUTS_CARD = 'hePutsTrump';
    private const ASK_TRUMP = 'askTrump';
    private const HE_ASKS_TRUMP = 'heAsksTrump';
    private const TRUMP_IS = 'trumpIs';
}
