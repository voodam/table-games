<?php
namespace Games\Card;

use Games\Players;
use MyCLabs\Enum\Enum;
use function Games\Util\Iter\filter;
use function Games\Util\Iter\getOneMaybe;
use function Games\Util\Iter\any;

class CardPlayers extends Players {
    public function havingCard(Card $card): ?CardPlayer {
        return getOneMaybe($this, fn(CardPlayer $player) => $player->hasCard($card));
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
        return $teams;
    }
    
    public function getOtherTeams(object $playerOrTeam): array {
        $team = CardPlayer::getTeam($playerOrTeam);
        return filter($this->teams(), fn(Team $t) => !$team->eq($t));
    }
    
    public function getOtherTeam(object $playerOrTeam): Team {
        $otherTeams = $this->getOtherTeams($playerOrTeam);
        $teamsNumber = count($otherTeams);
        if ($teamsNumber > 1) throw \LogicException("Supposed to be one other team, $teamsNumber given");
        return $otherTeams[0];
    }

    protected function playerClass(): string { return CardPlayer::class; }
}
