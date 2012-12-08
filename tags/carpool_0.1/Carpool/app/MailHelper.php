<?php

class MailHelper {
    
    // TODO: Problematic now; need to fix this and use it over the application
    
    private static function buildAuthenticationUrl($page, $contact) {
        assert ($page === 'optout' || $page === 'auth'); 
        return Utils::buildLocalUrl("$page.php", array('c' => $contact['Id'], 'i' => $contact['Identifier']));
    }
    
    public static function render($script, $params = null, $contact = null) {
        $layout = new ViewRenderer(APP_PATH . '/layouts/mail.php');
        $body = new ViewRenderer($script);
        $body->assign($params);
        if ($contact !== null) {
            // Commented out to remove the opt-out link while it's broken
            // $layout->optoutUrl = self::buildAuthenticationUrl('optout', $contact);   
            $body->contact = $contact;
        }
        $layout->body = $body;
        return $layout->doRenderToString();
    }
    
}