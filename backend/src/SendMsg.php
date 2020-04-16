<?php
namespace Games;

use MyCLabs\Enum\Enum;

class SendMsg extends Enum {
    private const YOUR_TEAM = 'yourTeam';
    private const START_GAME = 'startGame';
    private const YOUR_TURN = 'yourTurn';
    private const WAIT_PLAYERS ='waitPlayers';
    private const TURN_OF = 'turnOf';
    private const WRONG_TURN = 'wrongTurn';
    private const WINNER_IS = 'winnerIs';
    private const GAME_SCORE = 'gameScore';
}
