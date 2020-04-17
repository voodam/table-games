<?php
namespace Games;

use MyCLabs\Enum\Enum;

class SendMsg extends Enum {
    private const WAIT_PLAYERS ='waitPlayers';
    private const START_GAME = 'startGame';
    private const YOUR_TEAM = 'yourTeam';
    private const WRONG_TURN = 'wrongTurn';
    
    // Should be sent in card partie or game
    private const YOUR_TURN = 'yourTurn'; // required
    private const TURN_OF = 'turnOf'; // required
    
    // Should be sent in game
    private const WINNER_IS = 'winnerIs';
    private const GAME_SCORE = 'gameScore';
}
