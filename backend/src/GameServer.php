<?php
namespace Games;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Games\Util\Logging;
use Games\RecvMsg;
use MyCLabs\Enum\Enum;
use Games\Util\Func\id;

abstract class GameServer implements MessageComponentInterface, MsgObservableInterface {
    use MsgObservable;
    use Logging;

    protected Players $players;
    protected int $numPlayers;
    private array $payloadPreparers = [];

    abstract protected function startGame();
    protected function createPlayers(): Players { return new Players; }

    public function __construct(int $numPlayers) {    
        $this->players = $this->createPlayers();
        $this->numPlayers = $numPlayers;
        $this->attachObserver($this, RecvMsg::CONNECT());
    }

    private function connect(?string $name, $_, ConnectionInterface $conn) {
        $this->log('connect');
        $count = count($this->players);
        if ($this->players->contains($conn)) throw new \LogicException("Connection for player {$this->players->get($conn)} was added already");
        assert($count <= $this->numPlayers);

        if ($count === $this->numPlayers) {
            $this->log('too many players');
            $conn->close();
            return;
        }

        $count++;
        $player = $this->players->create($conn, $name ?? "Игрок $count");

        if ($count < $this->numPlayers) {
            $player->send(SendMsg::WAIT_PLAYERS());
        } else {
            $this->log('start game');
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
        $this->log("message: $json");
        $msg = json_decode($json, true);
        $type = $msg['type'] ?? null;
        if ($type === null) throw new \InvalidArgumentException("message $json has no type");

        $observers = $this->getObserversRec($type);
        if (!$observers) {
            $this->log('unknown message');
            return;
        }
        
        $preparer = $this->payloadPreparers[$type] ?? id::class;
        foreach ($observers as $observer) {
            $payload = $preparer($msg['payload'] ?? null);
            $observer->$type($payload, $this->players->maybeGet($conn), $conn);
        }
    }
    
    protected function preparePayload(Enum $message, callable $preparer) { $this->payloadPreparers[$message->getValue()] = $preparer; }
    public function onError(ConnectionInterface $conn, \Exception $e) { $this->log("error: {$e->getMessage()}"); }
    public function onOpen(ConnectionInterface $conn) {}
}
