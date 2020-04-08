<?php
namespace Games\Card;

use Games\Players;
use MyCLabs\Enum\Enum;
use function Games\Util\Iter\filter;
use function Games\Util\Iter\any;

class CardPlayers extends Players {
    public function havingCard(Card $card): ?CardPlayer {
        $players = filter($this, fn(CardPlayer $player) => $player->hasCard($card));
        assert(count($players) <= 1);
        return $players[0] ?? null;
    }

    public function haveCards(): bool {
        return any($this, fn(CardPlayer $player) => $player->hasCards());
    }
    
    public function sendTeam(Team $team, Enum $message, $payload): void {
        $teamPlayers = filter($this, fn(CardPlayer $player) => $player->team()->eq($team));
        self::sendTo($teamPlayers, $message, $payload);
    }

    protected function playerClass(): string { return CardPlayer::class; }
}
