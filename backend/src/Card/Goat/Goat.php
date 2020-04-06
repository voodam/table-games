<?php
namespace Games\Card\Goat;

use Games\MsgObservableInterface;
use Games\MsgObservable;
use Games\Card\CardPlayers;
use Games\Card\Team;
use Games\Card\Partie;
use Games\Card\CardRecvMsg;
use Games\Card\CardException;
use Games\Util\Loggable;
use function Games\Util\Iter\filter;

class Goat implements MsgObservableInterface {
    use MsgObservable;
    use Loggable;
    
    private CardPlayers $players;
    private \SplObjectStorage $scores; // Team -> int
    private Partie $partie;

    public function __construct(CardPlayers $players) {
        $this->players = $players;
        $this->scores = new \SplObjectStorage;
    }

    public function start() {
        $this->arrangePlayersToTeams();
        $this->newPartie();
    }

    public function putCard(Card $card, CardPlayer $player) {
        if ($this->winner()) throw new CardException('Game was ended');
        assert (!$this->partie->ended());
        
        $this->partie->putCard($player, $card);
        if (!$this->partie->ended()) {
            return;
        }
        
        $this->countScore();
        $winner = $this->winner();
        if ($winner) {
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
        $this->players->sendWinner($winner->name());
        
        foreach ($this->teams() as $team) {
            $newScore = $team->eq($this->winner()) ? $this->scores[$team] - 12 : 0;
            $this->scores[$team] -= $newScore;
        }
        $this->newPartie();
    }

    private function newPartie(): void {
        $this->detachObserver($this->partie, CardRecvMsg::DETERM_TRUMP());
        $this->partie = new GoatPartie($this->players);
        $this->attachObserver($this->partie, CardRecvMsg::DETERM_TRUMP());
    }

    private function winner(): ?Team {
        $winners = filter($this->teams(), fn($team) => $this->scores[$team] >= 12);
        assert($winners <= 1);
        return $winners[0] ?? null;
    }

    private function countScore(): void {
        assert ($this->winner($team) == null);
        foreach ($this->teams() as $team) {
            $this->scores[$team] += $this->partie->score($team);
        }
    }

    private ?array $teams;

    private function teams(): array {
        if (!$this->teams) {
            $this->teams = [new Team('Команда 1'), new Team('Команда 2')];
        }
        return $this->teams;
    }
}
