<?php
namespace Games\Card;

use Games\Team;
use Games\Util\Logging;
use function Games\Util\Iter\any;
use Games\Card\TrickInterface;

abstract class Partie {
    use Logging;
    
    protected CardPlayer $trumpPlayer;
    protected CardPlayer $eldest;
    protected Trump $trump;
    /**
     * @var TrickInterface[]
     */
    private array $tricks = [];
    protected CardPlayers $players;
    private \SplObjectStorage $cardScoreCache; // Team -> int

    abstract protected function calculateGameScore(int $cardsScore, Team $team): int;
    abstract protected function trumpPlayer(): CardPlayer;
    protected function createTrick(): TrickInterface { return new Trick($this->players, $this->trump); }

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
        $this->players->sendAll(CardSendMsg::TRUMP_IS(), ['trump' => $this->trump, 'player' => $this->trumpPlayer]);
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
        $this->checkEnded();
        return $this->calculateGameScore($this->cardsScore($team), $team);
    }
    
    public function cardsScore(Team $team): int {
        $this->checkEnded();
        return $this->cardScoreCache[$team] ??= $this->calculateCardsScore($team);
    }
    
    public function next(): self {
        $this->checkEnded();
        return new static($this->players, $this->players->getNext($this->eldest));
    }
    
    public function ended(): bool {
        return !$this->players->haveCards();
    }
    
    protected function gotAnyTrick(Team $team): bool {
        $this->checkEnded();
        return any($this->tricks, fn(TrickInterface $trick) => $trick->winner()->hasTeam($team));
    }
    
    private function calculateCardsScore(Team $team): int {
        return array_reduce($this->tricks, fn(int $allScore, TrickInterface $trick) => $trick->winner()->hasTeam($team) 
            ? $allScore + $trick->calculateScore() 
            : $allScore, 0);
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
    
    private function curTrick(): TrickInterface { 
        if (empty($this->tricks)) throw new \LogicException('First trick was not created');
        return $this->tricks[0];
    }
    
    private function checkEnded(): void {
        if (!$this->ended()) throw new CardException('Party is not over');
    }
}
