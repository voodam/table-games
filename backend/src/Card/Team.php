<?php
namespace Games\Card;

use Games\Color;

class Team implements \JsonSerializable {
    private Color $color;

    public function __construct(Color $color) {
        $this->color = $color;
    }
    
    public function name(): string { return $this->color->getValue(); }
    public function eq(self $other): bool { return $this->name() === $other->name(); }
    public function jsonSerialize() { return $this->name(); }
    public function __toString(): string { return $this->name(); }
}
