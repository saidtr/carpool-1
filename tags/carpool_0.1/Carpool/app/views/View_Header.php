<?php

class View_Header {

	public static function render($header, $subHeader = null) {
		$html = "<div id='header'>\n";
		$needSpacing = ($header != null && $subHeader != null);
		
		if ($header != null) {
			$html .= "<h1";
			if ($needSpacing === true) {
				$html .= " class='withBottomMargin'";
			}
			$html .= ">$header</h1>\n";
		}
		if ($subHeader !== null) {
			$html .= "<h3>$subHeader</h3>\n";
		}
		$html .= "</div>\n";
		$msg = GlobalMessage::getGlobalMessage();
		if ($msg) {
			$clazz = 'roundcorners';
			$clazz .= ($msg['type'] === GlobalMessage::ERROR) ? ' error' : '';
			$html .= "<p id='globalMessage' class='$clazz' style='display: block'><span id='messageBody'>" . $msg['msg'] . '</span>';
			GlobalMessage::clear();
		} else {
			$html .= "<p id='globalMessage'><span id='messageBody'></span>";
		}
		$html .= "<span id='closeMessage'>" . _('Close') . "</span></p>";
		return $html;
	}

}