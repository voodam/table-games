<?php
namespace Games\Test\Unit\Doubles;

use Games\Card\TrickInterface;
use Games\Card\CardPlayer;
use Games\Card\Card;

class TrickStub implements TrickInterface {
    private CardPlayer $winner;
    private int $score;
    private int $numTurns;
    private int $wasTurns = 0;
    
    public function __construct(CardPlayer $winner, int $score, int $numTurns = 4) {
        $this->winner = $winner;
        $this->score = $score;
        $this->numTurns = $numTurns;
    }
    
    public function putCard(CardPlayer $player, Card $card): void {
        $this->wasTurns++;
    }
    
    public function ended(): bool {
        return $this->wasTurns >= $this->numTurns;
    }
    
    public function winner(): CardPlayer { return $this->winner; }
    public function calculateScore(): int { return $this->score; }
}
