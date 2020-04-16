<?php
namespace Games\Test\Unit;

use Games\Test\Unit\Doubles\TrickStub;

class GoatPartieTest {
    public function run() {
        foreach ([
            [0, false, false, 4],
            [0, false, true, 2],
            [1, false, false, 2],
            [29, false, false, 2],
            [30, false, false, 1],
            [59, false, false, 1],
            [60, false, false, 0],
            [0, false, false, 4],
            [, false, true, 4],
            [1, false, false, 4],
            [29, false, false, 4],
            [30, false, false, 2],
            [59, false, false, 2]
        ] as [$cardsScore, $trumpPlayerInTeam, $gotAnyTricks, $assertGameScore]) {
            
        }
    }
    
    private function createTricksToConditions(int $cardsScore, bool $trumpPlayerInTeam, bool $gotAnyTricks): array {
        $tricks = [];
        for ($i = 0; $i < 8; $i++) {
            $tricks[] = new TrickStub($winner, $cardsScore);
            if ($cardsScore) $cardsScore = 0;
        }
        assert($cardsScore === 0);
    }
}
