<?php
namespace Games\Card;

use Games\Util\Cmp;
use function Games\Util\Iter\filter;

class Deck {
    private array $cards;

    public static function new32(): self {
        $cards = filter(self::get52Cards(), fn(Card $card) => $card->rank()->compare(Rank::_6()) === Cmp::MORE);
        return new self($cards);
    }

    public static function new36(): self {
        $cards = filter(self::get52Cards(), fn(Card $card) => $card->rank()->compare(Rank::_5()) === Cmp::MORE);
        return new self($cards);
    }

    public static function new52(): self {
        return new self(self::get52Cards());
    }

    public function __construct(array $cards) {
        $this->cards = $cards;
    }
    
    public function shuffle(): void {
        shuffle($this->cards);
    }

    public function deal(iterable $players): void {
        $cardsNumber = count($this->cards);
        $playersNumber = count($players);
        if ($cardsNumber === 0) throw new CardException('No cards in deck');
        if ($cardsNumber % $playersNumber !== 0) throw new CardException("Deck can not be shared equally between $playersNumber players");

        $handNumber = $cardsNumber / $playersNumber;
        foreach ($players as $player) {
            $hand = array_splice($this->cards, 0, $handNumber);
            $player->deal($hand);
        }
        assert(count($this->cards) === 0);
    }

    private static function get52Cards(): array {
        static $cards = [];

        if (empty($cards)) {
            foreach (Rank::keys() as $h) {
                foreach (Suit::keys() as $suit) {
                    $cards[] = new Card(Rank::$h(), Suit::$suit());
                }
            }
        }

        return $cards;
    }
}
