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

    public function __construct(Rank $rank, Suit $suit) {
        $this->rank = $rank;
        $this->suit = $suit;
    }

    public function compare(self $other, callable $rankCmpOrder = null): int {
        if (!$this->haveSuitOf($other)) return Cmp::MORE;
        return $this->rank->compare($other->rank(), $rankCmpOrder);
    }

    public function compareTrump(self $other, Trump $trump, callable $rankCmpOrder = null): int {
        $thisIsTrump = $trump->isTrump($this);
        $otherIsTrump = $trump->isTrump($other);
        
        if ($thisIsTrump && !$otherIsTrump) return Cmp::MORE;
        if ($otherIsTrump && !$thisIsTrump) return Cmp::LESS;
        if ($thisIsTrump && $otherIsTrump) return $this->compare($other, [$trump, 'rankCmpOrder']);
        return $this->compare($other, $rankCmpOrder);
    }

    public function haveSuitOf(Card $other, Trump $trump = null): bool {
        $haveSameSuits = $this->haveSuit($other->suit());
        if (!isset($trump)) {
            return $haveSameSuits;
        }
        
        $thisIsTrump = $trump->isTrump($this);
        $otherIsTrump = $trump->isTrump($other);
        
        if (!$thisIsTrump && !$otherIsTrump) return $haveSameSuits;
        return $thisIsTrump && $otherIsTrump;
    }
    
    public function haveSuit(Suit $suit): bool { return $this->suit->equals($suit); }
    public function haveRank(Rank $rank): bool { return $this->rank->equals($rank); }
    public function jsonSerialize() { return [$this->rank, $this->suit]; }
    public function translate(): string { return t($this->rank) . ' ' . t($this->suit); }
    public function __toString(): string { return $this->rank . ' of ' . $this->suit; }
    public function rank(): Rank { return $this->rank; }
    public function suit(): Suit { return $this->suit; }
}
