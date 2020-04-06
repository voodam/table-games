<?php
namespace Games\Util;

trait Logging {

    protected function log(string $msg): void {
        echo $msg . "\n";
    }

}
