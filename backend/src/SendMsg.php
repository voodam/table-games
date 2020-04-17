<?php
namespace Games;

use MyCLabs\Enum\Enum;

class SendMsg extends Enum {
    private const WAIT_PLAYERS ='waitPlayers';
    private const START_GAME = 'startGame';
    private const YOUR_TEAM = 'yourTeam';
    private const WRONG_TURN = 'wrongTurn';
    
    // Should be sent in card partie / other game
    private const YOUR_TURN = 'yourTurn';
    private const TURN_OF = 'turnOf';
    
    // Should be sent in game (optionally)
    private const WINNER_IS = 'winnerIs';
    private const GAME_SCORE = 'gameScore';
}
