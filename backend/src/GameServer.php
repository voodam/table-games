<?php
namespace Games;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Games\Util\Logging;
use Games\RecvMsg;
use MyCLabs\Enum\Enum;
use Games\Exception\WrongTurnException;
use Games\Util\Func\id;

abstract class GameServer implements MessageComponentInterface, MsgObservableInterface {
    use MsgObservable;
    use Logging;

    protected Players $players;
    protected int $needPlayersNumber;
    protected array $initialTeamScore;
    private array $payloadPreparers = [];

    abstract protected function startGame();
    
    protected function defaultPlayerName(): string {
        return 'Игрок ' . (count($this->players) + 1);
    }

    public function __construct(int $needPlayersNumber, array $initialTeamScore = []) {
        $this->needPlayersNumber = $needPlayersNumber;
        $this->players = $this->createPlayers($needPlayersNumber);
        $this->initialTeamScore = $initialTeamScore;
        $this->attachObserver($this, RecvMsg::CONNECT());
    }
    
    public function players(): Players { return $this->players; }
    public function needPlayersNumber(): int { return $this->needPlayersNumber; }

    public function connect(?string $name, $_, ConnectionInterface $conn) {
        $this->log('connect');
        $playersNumber = count($this->players);
        if ($this->players->contains($conn)) throw new \LogicException("Connection for player {$this->players->get($conn)} was added already");
        assert($playersNumber <= $this->needPlayersNumber);

        if ($playersNumber === $this->needPlayersNumber) {
            $this->log('too many players');
            $conn->close();
            return;
        }

        $player = $this->players->create($conn, $name ?? $this->defaultPlayerName());
        
        if (count($this->players) < $this->needPlayersNumber) {
            $player->send(SendMsg::WAIT_PLAYERS());
        } else {
            $this->log('start game');
            $this->players->sendAll(SendMsg::START_GAME());
            $this->startGame();
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->log('on close');
        if (!$this->players->contains($conn)) return;
        $this->log('abort game');
        foreach ($this->players as $player) {
            $player->closeConn();
        }
        $this->players->clear();
    }

    public function onMessage(ConnectionInterface $conn, $json) {
        $player = $this->players->maybeGet($conn);
        $this->log("msg from '$player': $json");
        $msg = json_decode($json, true);
        $type = $msg['type'] ?? null;
        if ($type === null) throw new \InvalidArgumentException("message $json has no type");

        $observers = $this->getObserversRec($type);
        if (empty($observers)) {
            $this->error("no observers for message: $type");
            return;
        }
        
        $preparer = $this->payloadPreparers[$type] ?? id::class;
        foreach ($observers as $observer) {
            $payload = $preparer($msg['payload'] ?? null);
            $observer->$type($payload, $player, $conn);
        }
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) { 
        if ($e instanceof WrongTurnException) {
            $this->players->get($conn)->send(SendMsg::WRONG_TURN(), $e->getMessage());
        } else {
            $this->error("error: $e");
        }
    }
    
    protected function createPlayers(int $maxPlayers): Players { return new Players($maxPlayers); }
    protected function preparePayload(Enum $message, callable $preparer) { $this->payloadPreparers[$message->getValue()] = $preparer; }
    public function onOpen(ConnectionInterface $conn) {}
}
