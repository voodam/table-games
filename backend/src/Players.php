<?php
namespace Games;

use Ratchet\ConnectionInterface;
use Games\Util\Logging;
use Games\Util\MyObjectStorage;
use MyCLabs\Enum\Enum;

class Players implements \IteratorAggregate, \Countable {
    use Logging;

    private MyObjectStorage $storage; // ConnectionInterface -> Player
    
    public static function sendTo(iterable $players, Enum $message, $payload = null): void {
        if (!is_callable($payload)) {
            $payload = fn() => $payload;
        }
        foreach ($players as $player) {
            $player->send($message, $payload($player));
        }
    }

    public function __construct() {
        $this->storage = new MyObjectStorage;
    }

    public function getIterator(): \Traversable {
        foreach ($this->storage as $conn) {
            yield $this->storage[$conn];
        }
    }

    public function sendNext(Player $player): void {
        $player->send(SendMsg::YOUR_TURN(), $player);
        $this->sendAbout($player, SendMsg::TURN_OF());
    }
    
    public function sendAbout(Player $player, Enum $message): void {
        $this->sendOther($player, $message, $player);
    }
    
    public function sendOther(object $connOrPlayer, Enum $message, $payload = null): void {
        self::sendTo($this->getOther($connOrPlayer), $message, $payload);
    }
    
    public function sendAll(Enum $message, $payload = null): void {
        self::sendTo($this, $message, $payload);
    }
    
    public function sendWinner(\JsonSerializable $winner): void {
        $this->sendAll(SendMsg::WINNER_IS(), $winner);
    }
    
    public function getOther(object $connOrPlayer): array {
        $connection = Player::getConn($connOrPlayer);
        return $this->storage->getOtherInfo($connection);
    }

    public function getNext(Player $prevPlayer): Player {
        $found = false;
        foreach ($this as $player) {
            if ($player === $prevPlayer) {
                $found = true;
                continue;
            }
            if ($found) {
                return $player;
            }
        }
        
        if (!$found) throw new \OutOfBoundsException('Player was not found');
        return $this->getFirst();
    }

    public function create(ConnectionInterface $conn, string $name): Player {
        $class = $this->playerClass();
        $player = new $class($conn, $name);
        $this->set($conn, $player);
        return $player;
    }

    public function set(ConnectionInterface $conn, Player $player): void {
        $this->log("set player: $player");
        if ($this->contains($conn)) throw new \OverflowException('Player exists');
        $this->storage[$conn] = $player;
    }

    public function get(ConnectionInterface $conn): Player {
        if (!$this->contains($conn)) throw new \OutOfBoundsException('Player does not exists');
        return $this->storage[$conn];
    }
    
    public function contains(object $connOrPlayer): bool { 
        return isset($this->storage[Player::getConn($connOrPlayer)]);         
    }
    
    public function getFirst(): Player { return $this->storage->getFirstInfo(); }
    public function maybeGet(ConnectionInterface $conn): ?Player { return $this->storage[$conn] ?? null; }
    public function clear(): void { $this->storage->detachAll(); }
    public function count(): int { return count($this->storage); }
    
    protected function playerClass(): string { return Player::class; }
}
