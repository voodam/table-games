<?php
namespace Games\Card;

class Team implements \JsonSerializable {
    private string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }
    
    public function eq(self $other): bool { return $this->name === $other->name(); }
    public function name(): string { return $this->name; }
    public function jsonSerialize() { return $this->name; }
    public function __toString(): string { return $this->name; }
}
