<?php

class NullLogger {
    
    function doLog($level, $msg) {}
    public function logException(Exception $e) {}

}
    