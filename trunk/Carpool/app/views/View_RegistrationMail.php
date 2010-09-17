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
                '<h1>' . printf(_('Thanks, %s'), htmlspecialchars($contact['Name'])) . '!</h1>' .
                '<div id="content">' .
                '<p>' . _('You sucssfully joined the carpool.') . '</p>' .
				'<p>' . printf(_('You can always update or delete your account by browsing to %s'), '<a href="' . htmlspecialchars($authUrl) . '">' . htmlspecialchars($authUrl) . '</a>') . '.</p>' .
        		'<p>' . _('Unless you ask for it, you will never get any more emails from this site.') . '</p>' .
                '<p>' . _('Thanks') . ',<br/>' . printf('The %s team', _(getConfiguration('app.name'))) .
				'</div>' .
                '</body>' .
                '</html>';        
        return $html;      
    }
    
    
}