<?php
namespace Games\Card;

use Games\Util\Logging;
use function Games\Util\Iter\any;

abstract class Partie {
    use Logging;
    
    protected CardPlayer $trumpPlayer;
    protected CardPlayer $eldest;
    protected Trump $trump;
    private array $tricks = [];
    protected CardPlayers $players;
    private \SplObjectStorage $cardScoreCache;

    abstract public function next(): self;
    abstract protected function calculateGameScore(int $cardsScore, Team $team): int;
    abstract protected function trumpPlayer(): CardPlayer;
    protected function createTrick(): Trick { return new Trick($this->players, $this->trump); }

    public function __construct(CardPlayers $players, CardPlayer $eldest) {
        $this->players = $players;
        $this->eldest = $eldest;
        $this->log("Partie eldest is '$this->eldest'");
        $this->cardScoreCache = new \SplObjectStorage;
    }
    
    public function deal(): void {
        $deck = Deck::new32();
        $deck->shuffle();
        $deck->deal($this->players);
        $this->trumpPlayer = $this->trumpPlayer();
        $this->log("Partie trump player is '$this->trumpPlayer'");
        assert(isset($this->trumpPlayer));
        $this->trumpPlayer->send(CardSendMsg::ASK_TRUMP(), $this->trumpPlayer);
        $this->players->sendAbout($this->trumpPlayer, CardSendMsg::PLAYER_DETERMS_TRUMP());
    }
    
    public function determineTrump(Trump $trump) { 
        $this->trump = $trump;
        $this->players->sendAll(CardSendMsg::TRUMP_IS(), $this->trump);
        $this->newTrick($this->eldest);
    }

    public function putCard(CardPlayer $player, Card $card): void {
        if (!isset($this->trump)) throw new CardException('Can not make turn while trump is not set');
        if ($this->ended()) throw new CardException('Party is over');
        assert(!$this->curTrick()->ended());
    
        $this->curTrick()->putCard($player, $card);
        if ($this->curTrick()->ended()) {
            $this->getTrick();
        } else {
            $this->players->sendNext($this->players->getNext($player));
        }
    }

    public function gameScore(Team $team): int {
        if (!$this->ended()) throw new CardException('Party is not over');
        return $this->calculateGameScore($this->cardsScore($team), $team);
    }
    
    
    public function cardsScore(Team $team): int {
        if (!$this->ended()) throw new CardException('Party is not over');
        
        if (!isset($this->cardScoreCache[$team])) {
            $score = array_reduce($this->tricks, fn(int $allScore, Trick $trick) => $trick->winner()->hasTeam($team) 
                ? $allScore + $trick->calculateScore() 
                : $allScore, 0);
            
            $this->cardScoreCache[$team] = $score;
        }
        return $this->cardScoreCache[$team];
    }
    
    public function ended(): bool {
        return !$this->players->haveCards();
    }
    
    protected function gotAnyTrick(Team $team): bool {
        if (!$this->ended()) throw new CardException('Party is not over');
        return any($this->tricks, fn(Trick $trick) => $trick->winner()->hasTeam($team));
    }

    private function getTrick(): void {
        $winner = $this->curTrick()->winner();
        $trickScore = $this->curTrick()->calculateScore();
        $this->players->sendAll(CardSendMsg::TRICK_WINNER_IS(), [$winner, $trickScore]);
        
        if (!$this->ended()) {
            $this->newTrick($winner);
        }
    }

    private function newTrick(CardPlayer $eldest): void {
        assert(empty($this->tricks) || $this->curTrick()->ended());
        array_unshift($this->tricks, $this->createTrick());
        $this->players->sendNext($eldest);
    }
    
    private function curTrick(): Trick { 
        if (empty($this->tricks)) throw new \LogicException('First trick was not created');
        return $this->tricks[0];
    }
}
