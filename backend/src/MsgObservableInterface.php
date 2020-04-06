<?php
namespace Games;

interface MsgObservableInterface {
    function getObserversRec(string $message): array;
}
