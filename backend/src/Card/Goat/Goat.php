<?php
namespace Games\Card\Goat;

use Games\MsgObservableInterface;
use Games\MsgObservable;
use Games\SendMsg;
use Games\Card\Card;
use Games\Team;
use Games\Card\PartieInterface;
use Games\Card\CardRecvMsg;
use Games\Util\Logging;
use Games\Card\CardSendMsg;
use Games\Card\CardPlayers;
use Games\Card\CardPlayer;
use function Games\Util\Iter\getOneMaybe;
use function Games\Util\Translate\t;
use Games\Color;

class Goat implements MsgObservableInterface {
    use MsgObservable;
    use Logging;
    
    private CardPlayers $players;
    private \SplObjectStorage $score; // Team -> int
    private PartieInterface $partie;

    public function __construct(CardPlayers $players, array $initialTeamScore = []) {
        $this->players = $players;
        $this->score = new \SplObjectStorage;
        
        $initialTeamScore = $initialTeamScore ?: [0, 0];
        $numberTeamScore = count($initialTeamScore);
        if ($numberTeamScore !== 2) throw new \LogicException("Two teams in ths game, but given initial score for $numberTeamScore teams");
        $this->changeEachTeamScore(fn($_, $__, $teamIndex) => $initialTeamScore[$teamIndex]);
    }

    public function start() {
        $this->arrangePlayersToTeams();
        $this->newPartie();
    }

    public function putCard(Card $card, CardPlayer $player) {
        if ($this->winner()) throw new \LogicException('Game was ended: we have a winner');
        assert(!$this->partie->ended());
        
        $this->partie->putCard($player, $card);
        if (!$this->partie->ended()) {
            return;
        }
        
        $this->updateScore();
        $winner = $this->winner();
        if ($winner) {
            $this->restart($winner);
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
    
    private function restart(Team $winner): void {
        $this->players->sendWinner($winner);
        $this->changeEachTeamScore(fn(int $oldScore, Team $team) => $team->eq($winner) ? $oldScore - 12 : 0);
        $this->newPartie();
    }

    private function newPartie(): void {
        if (!isset($this->partie)) {
            $this->partie = new GoatPartie($this->players, $this->players->random());
        } else {
            $this->detachObserver($this->partie, CardRecvMsg::DETERMINE_TRUMP());
            $this->partie = $this->partie->next();
        }
        $this->attachObserver($this->partie, CardRecvMsg::DETERMINE_TRUMP());
        $this->partie->deal();
    }

    private function winner(): ?Team {
        return getOneMaybe($this->teams(), fn($team) => $this->score[$team] >= 12);
    }

    private function updateScore(): void {
        assert($this->winner() === null);
        
        $this->changeEachTeamScore(function(int $oldScore, Team $team) {
            $gameScore = $this->partie->gameScore($team);
            $cardsScore = $this->partie->cardsScore($team);
            $this->players->sendTeam($team, CardSendMsg::YOUR_PARTIE_SCORE(), [t($team), $cardsScore]);
            return $gameScore + $oldScore;
        });
    }
    
    private function changeEachTeamScore(callable $scoreCalc): void {
        $newScorePayload = [];
        foreach ($this->teams() as $i => $team) {
            $this->score[$team] = $scoreCalc($this->score[$team] ?? null, $team, $i);
            $newScorePayload[t($team)] = $this->score[$team];
        }
        $this->players->sendAll(SendMsg::GAME_SCORE(), $newScorePayload);
    }

    private ?array $teams;

    private function teams(): array {
        if (!isset($this->teams)) {
            $this->teams = [new Team(Color::BLUE()), new Team(Color::RED())];
        }
        return $this->teams;
    }
}
