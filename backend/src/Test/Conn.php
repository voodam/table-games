<?php
namespace Games\Test;

use Ratchet\ConnectionInterface;

class Conn implements ConnectionInterface {
    public $last = array(
        'send'  => ''
      , 'close' => false
    );

    public $remoteAddress = '127.0.0.1';

    public function send($data) {
        $this->last[__FUNCTION__] = $data;
    }

    public function close() {
        $this->last[__FUNCTION__] = true;
    }
}
