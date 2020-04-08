<?php
namespace Games\Card;

use Games\Util\Cmp;
use Games\Card\Rank;
use Games\Card\Suit;
use function Games\Util\Translate\t;

class Card implements \JsonSerializable {
    private Rank $rank;
    private Suit $suit;

    public static function fromDict(array $dict): self {
        return new self(new Rank($dict['rank']), new Suit($dict['suit']));
    }
    
    public static function fromPair(array $pair): self {
        [$rank, $suit] = $pair;
        return new self(new Rank($rank), new Suit($suit));
    }
    
    public static function getSuit(object $cardOrSuit): Suit {
        assert($cardOrSuit instanceof self || $cardOrSuit instanceof Suit);
        return $cardOrSuit instanceof self ? $cardOrSuit->suit() : $cardOrSuit;
    }

    public function __construct(Rank $rank, Suit $suit) {
        $this->rank = $rank;
        $this->suit = $suit;
    }

    public function compare(self $other, callable $rankCmpOrder = null): int {
        if (!$this->haveSameSuit($other)) return Cmp::MORE;
        return $this->rank->compare($other->rank(), $rankCmpOrder);
    }

    public function compareTrump(self $other, Suit $trump, callable $rankCmpOrder = null): int {
        if ($this->haveSameSuit($trump) && !$other->haveSameSuit($trump)) return Cmp::MORE;
        if ($other->haveSameSuit($trump) && !$this->haveSameSuit($trump)) return Cmp::LESS;
        return $this->compare($other, $rankCmpOrder);
    }

    public function haveSameSuit(object $cardOrSuit): bool {
        $suit = self::getSuit($cardOrSuit);
        return $this->suit->equals($suit);
    }

    public function jsonSerialize() { return [$this->rank, $this->suit]; }
    public function translate(): string { return t($this->rank) . ' ' . t($this->suit); }
    public function __toString(): string { return $this->rank . ' of ' . $this->suit; }
    public function rank(): Rank { return $this->rank; }
    public function suit(): Suit { return $this->suit; }
}
