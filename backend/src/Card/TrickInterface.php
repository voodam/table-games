<?php
namespace Games\Card;

interface TrickInterface {
    function putCard(CardPlayer $player, Card $card): void;
    function ended(): bool;
    function calculateScore(): int;
    function winner(): CardPlayer;
}
