<?php

class Logger {
    
    const LOG_DEBUG = 0;
    const LOG_INFO  = 1;
    const LOG_WARN  = 2;
    const LOG_ERR   = 3;
    const LOG_NONE  = 4;
	
	private $_writer;
	private $_logLevel;
	
	private static function translateLogLevel($level) {
	    switch ($level) {
	        case self::LOG_DEBUG : return "Dbg";
	        case self::LOG_INFO  : return "Inf";
	        case self::LOG_WARN  : return "Wrn";
	        case self::LOG_ERR   : return "Err";
	        default: return "";
	    }
	}
		
	public function logException(Exception $e) {
	    $this->doLog(self::LOG_ERR, 'Error in ' . $e->getFile() . ' line ' . $e->getLine() . ': ' . $e->getMessage());
	}
	
	public function doLog($level, $str) {
		if ($this->_writer && $level >= $this->_logLevel) {
		    flock($this->_writer, LOCK_EX);		      	            
		    $line = '[ ' . self::translateLogLevel($level) . ' ] ' . $_SERVER['REMOTE_ADDR'] . ' ' . date('d/m/y H:i:s') . ' [ ' . Utils::getRunningScript() . ' ] ' . preg_replace("/[\n\r]/", "", $str);
			fwrite($this->_writer, $line . PHP_EOL);
			flock($this->_writer, LOCK_UN);
		}
	}
	
	public function __construct($logFile, $logLevel = self::LOG_WARN) {
	    $this->_logLevel = $logLevel;
	    if ($this->_logLevel < self::LOG_NONE) {
		    $this->_writer = @fopen($logFile, 'a');
		    if (!$this->_writer) {
		        throw new Exception('Could not create logger');
		    }
	    }		
	}
	
	public function __destruct() {
		if ($this->_writer) {
			fclose($this->_writer);
		}
	}
	
}