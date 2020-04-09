<?php
namespace Games\Card\Goat;

use Games\MsgObservableInterface;
use Games\MsgObservable;
use Games\SendMsg;
use Games\Card\Card;
use Games\Card\Team;
use Games\Card\Partie;
use Games\Card\CardRecvMsg;
use Games\Exception\GameEndException;
use Games\Util\Logging;
use Games\Card\CardSendMsg;
use function Games\Util\Iter\filter;

class Goat implements MsgObservableInterface {
    use MsgObservable;
    use Logging;
    
    private GoatPlayers $players;
    private \SplObjectStorage $score; // Team -> int
    private Partie $partie;

    public function __construct(GoatPlayers $players) {
        $this->players = $players;
        $this->score = new \SplObjectStorage;
        $this->changeScore(fn() => 0);
    }

    public function start() {
        $this->arrangePlayersToTeams();
        $this->newPartie();
    }

    public function putCard(Card $card, GoatPlayer $player) {
        if ($this->winner()) throw new GameEndException('Game was ended: we have a winner');
        assert(!$this->partie->ended());
        
        $this->partie->putCard($player, $card);
        if (!$this->partie->ended()) {
            return;
        }
        
        $this->countScore();
        $winner = $this->winner();
        if ($winner) {
            $this->players->sendWinner($winner);
            $this->restart();
        } else {
            $this->newPartie();
        }
    }

    private function arrangePlayersToTeams() {
        $teams = $this->teams();
        assert(count($teams) === 2);
        $playerTeams = array_merge($teams, $teams);
        $i = 0;
        foreach ($this->players as $player) {
            $player->joinTeam($playerTeams[$i++]);
        }
    }
    
    private function restart(): void {
        $this->changeScore(function(int $oldScore, Team $team) {
            return $team->eq($this->winner()) ? $oldScore - 12 : 0;
        });
        $this->newPartie();
    }

    private function newPartie(): void {
        $this->detachObserver($this->partie ?? null, CardRecvMsg::DETERM_TRUMP());
        $this->partie = new GoatPartie($this->players);
        $this->attachObserver($this->partie, CardRecvMsg::DETERM_TRUMP());
        $this->partie->deal();
    }

    private function winner(): ?Team {
        $winners = filter($this->teams(), fn($team) => $this->score[$team] >= 12);
        assert(count($winners) <= 1);
        return $winners[0] ?? null;
    }

    private function countScore(): void {
        assert ($this->winner() === null);
        
        $this->changeScore(function(int $oldScore, Team $team) {
            [$score, $partieScore] = $this->partie->score($team);
            $this->players->sendTeam($team, CardSendMsg::YOUR_PARTIE_SCORE(), $partieScore);
            return $score + $oldScore;
        });
    }
    
    private function changeScore(callable $scoreCalc): void {
        $newScorePayload = [];
        foreach ($this->teams() as $team) {
            $this->score[$team] = $scoreCalc($this->score[$team] ?? null, $team);
            $newScorePayload[(string)$team] = $this->score[$team];
        }
        $this->players->sendAll(SendMsg::GAME_SCORE(), $newScorePayload);
    }

    private ?array $teams;

    private function teams(): array {
        if (!isset($this->teams)) {
            $this->teams = [new Team('Команда 1'), new Team('Команда 2')];
        }
        return $this->teams;
    }
}
