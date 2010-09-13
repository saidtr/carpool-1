<?php

class View_RegistrationMail {
    
    public static function render($contact) {
        
        $authUrl = Utils::buildLocalUrl('auth.php', array('c' => $contact['Id'], 'i' => $contact['Identifier']));
        
        $html = '<html>' .
	            '<head><title></title></head>' .
                '<style>' .
                'h1 { font-size: xx-large; } ' .
                '#content p { font-size: large } ' .
                '</style>' .
                '<body>' .
                '<h1>Thanks, ' .  htmlspecialchars($contact['Name']) . '!</h1>' .
                '<div id="content">' .
                '<p>You sucssfully joined the carpool.</p>' .
				'<p>You can always update or delete your account by browsing to <a href="' . htmlspecialchars($authUrl) . '">' . htmlspecialchars($authUrl) . '</a>.</p>' .
        		'<p>Unless you ask for it, you will never get any more emails from this site.</p>' .
                '<p>Thanks,<br/>The ' . constant('APP_NAME') . ' team' .
				'</div>' .
                '</body>' .
                '</html>';        
        return $html;      
    }
    
    
}