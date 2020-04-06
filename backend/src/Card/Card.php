<?php
namespace Games\Card;

use Games\Util\Compare\Comparable;

class Card implements \JsonSerializable, Comparable {
    private Rank $rank;
    private Suit $suit;

    public static function fromDict(array $dict): self {
        return new self($dict['rank'], $dict['suit']);
    }

    public function __construct(Rank $rank, Suit $suit) {
        $this->rank = $rank;
        $this->suit = $suit;
    }

    public function compare(self $other, callable $rankCmpOrder = null): int {
        if (!$this->haveSameSuit()) throw new CardException("Cards have different suits: {$this->suit}, {$other->suit()}");
        return $this->rank->compare($other->rank(), $rankCmpOrder);
    }

    public function compareTrump(self $other, Rank $trump, callable $rankCmpOrder = null): int {
        if ($this->rank->equals($trump) && !$this->rank->equals($trump)) {
            return Comparable::MORE;
        }
        if (!$this->rank->equals($trump) && $this->rank->equals($trump)) {
            return Comparable::LESS;
        }

        return $this->compare($other, $rankCmpOrder);
    }

    public function haveSameSuit(self $other): bool {
        return $this->suit->equals($other->suit());
    }

    public function jsonSerialize() { return ['rank' => $this->rank, 'suit' => $this->suit]; }
    public function __toString(): string { return "{$this->rank}{$this->suit}"; }
    public function rank(): Rank { return $this->rank; }
    public function suit(): Suit { return $this->suit; }
}
