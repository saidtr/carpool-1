<?php

class Utils {
    
    const MAIL_EOL = "\r\n";
        
    static function IsXhrRequest() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'); 
    } 
    
    static function put($val, $default = null) {
    	if (isset($val) && !empty($val)) {
    		echo $val;
    	} else if ($default !== null) {
    		echo $default;
    	}
    }
    
    static function getParam($name, $defaultValue = false) {
    	if (isset($_GET[$name])) {
    		return $_GET[$name];
    	} 
    	return $defaultValue;
    }
    
    static function FormatTime($hour) {
        return sprintf("%02d:00", $hour);
    }
    
    static function getRunningScript() {
        $currentFile = $_SERVER["SCRIPT_NAME"];
        $parts = Explode('/', $currentFile);
        $currentFile = $parts[count($parts) - 1];
        return $currentFile;
    }
    
    static function isEmptyString($str) {
        return empty($str) || trim($str) === '';
    }
    
    static function errorInfoToString($errorInfo) {
    	$res = 'SQL Error code: ' . $errorInfo[0] . ' Driver error code: ' . $errorInfo[1] . ' Info: ' . $errorInfo[2];
    	return $res;
    }
    
    public static function sendMail($to, $toName, $from, $fromName, $subject, $body, $replyTo = null, $replyToName = null) {
	    
	    Logger::info("Send mail to $to as $toName, from $from as $fromName, subject is $subject");
	    
	    $mime_boundary = md5(time());
	    
		if ($replyTo == null || $replyToName == null) {
		    // Default reply to values
	        $replyTo = 'no.reply@checkpoint.com';
	        $replyToName = 'No Reply';
		}
	     
	    $subject_encoded = "=?UTF-8?B?".base64_encode($subject)."?=";

	    // Common Headers
	    $headers = "";
	    $headers .= "From: "."=?UTF-8?B?".base64_encode($fromName)."?= <".$from.">".self::MAIL_EOL;
	    $headers .= "Reply-To: "."=?UTF-8?B?".base64_encode($replyToName)."?= <".$replyTo.">".self::MAIL_EOL;
	    $headers .= "Return-Path: ".$replyToName."<".$replyTo.">".self::MAIL_EOL;    // these two to set reply address
	    $headers .= "Message-ID: <".time()."-".$from.">".self::MAIL_EOL;
	    $headers .= "X-Mailer: PHP v".phpversion().self::MAIL_EOL;          // These two to help avoid spam-filters

	    // Boundry for marking the split & Multitype Headers
	    $headers .= 'MIME-Version: 1.0'.self::MAIL_EOL;
	    $headers .= "Content-Type: multipart/alternative; boundary=\"".$mime_boundary."\"".self::MAIL_EOL;

	    // Text Version
	    $msg = "";
	    $msg .= "--".$mime_boundary.self::MAIL_EOL;
	    $msg .= "Content-Type: text/plain; charset=UTF-8".self::MAIL_EOL;
	    $msg .= "Content-Transfer-Encoding: 8bit".self::MAIL_EOL.self::MAIL_EOL;
	    $msg .= strip_tags(str_replace("<br>", "\n", substr($body, (strpos($body, "<body>")+6)))).self::MAIL_EOL.self::MAIL_EOL;

	    // HTML Version
	    $msg .= "--".$mime_boundary.self::MAIL_EOL;
	    $msg .= "Content-Type: text/html; charset=UTF-8".self::MAIL_EOL;
	    $msg .= "Content-Transfer-Encoding: 8bit".self::MAIL_EOL.self::MAIL_EOL;
	    $msg .= $body.self::MAIL_EOL.self::MAIL_EOL;

	    // Finished
	    $msg .= "--".$mime_boundary."--".self::MAIL_EOL.self::MAIL_EOL;  // finish with two eol's for better security. see Injection.

	    // Send the mail
	    ini_set('sendmail_from', $from);  // the INI lines are to force the From Address to be used !
	    $mail_sent = @mail($to, $subject_encoded, $msg, $headers);

	    ini_restore('sendmail_from');
        
	    if (!$mail_sent) {
            Logger::err("Could not send mail: $subject to $to");	        
	    }
	    return $mail_sent;
	}
	
	/**
	 * Redirects to another page on the same site
	 * 
	 * @param string $page Public page name
	 */
	public static function redirect($page) {
		header('Location: ' . self::buildLocalUrl($page));
	}
	
	public static function buildLocalUrl($page, $params = null) {
		$host = $_SERVER['HTTP_HOST'];
		
		$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		// Ugly hack to get the base directory path even if
		// the running context is from the XHR folder
		// TODO: Better solution with hard-coding the base folder?
		// TODO: Why the hell strlen($uri) < 4?
		if (strlen($uri) >= 4 && substr_compare($uri, "/xhr", -4, 4) == 0) { 
			$uri = substr($uri, 0, -4);
		}
		
		//$uri = PUBLIC_BASE_PATH;
		$res = "http://$host$uri/$page";
		if ($params && !empty($params)) {
		    $res .= '?';
		    foreach ($params as $name => $val) {
		        $res .= $name . '=' . $val . '&';
		    }
		    $res = substr($res, 0, -1);
		}
		return $res;
	}
	
	public static function buildEmail($mail) {
	    if (!strpos($mail, '@')) {
	        return $mail . '@' . DEFAULT_DOMAIN;
	    }
	    return $mail;
	}


}
