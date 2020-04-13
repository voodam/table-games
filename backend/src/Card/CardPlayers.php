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
    
    public function sendTeam(Team $team, Enum $message, $payload = null): void {
        $teamPlayers = filter($this, fn(CardPlayer $player) => $player->hasTeam($team));
        self::sendTo($teamPlayers, $message, $payload);
    }
    
    public function teams(): array {
        $teams = [];
        foreach ($this as $player) {
            assert($player instanceof CardPlayer);
            foreach ($teams as $team) {
                if ($player->hasTeam($team)) {
                    continue 2;
                }
            }
            
            $teams[] = $player->team();
        }
        assert(count($teams) <= $this->maxTeams);
        return $teams;
    }
    
    public function getOtherTeams(object $playerOrTeam): array {
        $player = CardPlayer::getTeam($playerOrTeam);
        return filter($this->teams(), fn(Team $team) => !$player->hasTeam($team));
    }

    protected function playerClass(): string { return CardPlayer::class; }
}
