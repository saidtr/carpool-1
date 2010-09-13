<?php

class View_Header {
    
    public static function render($header) {
        $html = "<div id=\"header\"><h1>$header</h1></div>\n";
        $msg = GlobalMessage::getGlobalMessage();
        if ($msg) {
        	$clazz = ($msg['type'] === GlobalMessage::ERROR) ? 'error' : '';
        	$html .= "<p id='globalMessage' class='$clazz' style='display: block'><span id='messageBody'>" . $msg['msg'] . '</span>';
        	GlobalMessage::clear();
        } else {
        	$html .= "<p id='globalMessage'><span id='messageBody'></span>";
        }
        $html .= "<span id='closeMessage'>" . _('Close') . "</span></p>";
        return $html;      
    }
    
    
}