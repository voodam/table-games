<?php
namespace Games\Card;

use Games\Players;
use function Games\Util\Iter\getOneMaybe;
use function Games\Util\Iter\any;

class CardPlayers extends Players {
    public function havingCard(Card $card): ?CardPlayer {
        return getOneMaybe($this, fn(CardPlayer $player) => $player->hasCard($card));
    }

    public function haveCards(): bool {
        return any($this, fn(CardPlayer $player) => $player->hasCards());
    }

    protected function playerClass(): string { return CardPlayer::class; }
}
