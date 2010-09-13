<?php

class View_FeedbackMail {
    
    const REPORT_BUG      = 1;
    const ASK_QUESTION    = 2;
    const REQUEST_FEATURE = 3;
    const CONTACT         = 4;
    const OTHER           = 5;
	
	public static function categoryToString($category) {
		switch ($category) {
			case self::REPORT_BUG      : return _("Report a bug");
			case self::ASK_QUESTION    : return _("Ask a question");
			case self::REQUEST_FEATURE : return _("Request a feature");
			case self::CONTACT         : return _("Contact");
			case self::OTHER           :
			default                    : return _("Other");  
		}
	}
    
    public static function render($category, $content, $email = null) {
        
        $html = '<html>' .
	            '<head><title></title></head>' .
                '<style>' .
                'h1 { font-size: xx-large; } ' .
                '#content p { font-size: large } ' .
                '</style>' .
                '<body>' .
                '<h1>New feedback</h1>' .
                '<div id="content">' .
                '<h2>' . self::categoryToString($category) . '</h2>' .
				'<p>' . nl2br(htmlspecialchars($content)) . '</p>';
        // Possibly put mail
        if (!Utils::isEmptyString($email)) {
        	$html .= '<p>Submitter mail:&nbsp;' . htmlspecialchars($email) . '</p>';
        }
        $html.= '</div>' .
                '</body>' .
                '</html>';        
        return $html;      
    }
    
    
}