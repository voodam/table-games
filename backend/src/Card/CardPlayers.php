<?php
namespace Games\Card;

use Games\Players;
use function Games\Util\Iter\filter;
use function Games\Util\Iter\any;

class CardPlayers extends Players {
    public function havingCard(Card $card): CardPlayer {
        return filter($this, fn(CardPlayer $player) => $player->hasCard($card))[0];
    }

    public function haveCards(): bool {
        return any($this, fn(CardPlayer $player) => $player->hasCards());
    }

    protected function playerClass(): string { return CardPlayer::class; }
}
