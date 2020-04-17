<?php
namespace Games\Card;

use Games\Team;

interface PartieInterface {
    function determineTrump(Trump $trump);
    function deal(): void;
    function putCard(CardPlayer $player, Card $card): void;
    function gameScore(Team $team): int;
    function cardsScore(Team $team): int;
    function next(): self;
    function ended(): bool;
}
