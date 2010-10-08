<?php

class MailHelper {
    
    private static function buildAuthenticationUrl($page, $contact) {
        assert ($page === 'optout' || $page === 'auth'); 
        return Utils::buildLocalUrl("$page.php", array('c' => $contact['Id'], 'i' => $contact['Identifier']));
    }
    
    public static function render($script, $params = null, $contact = null) {
        $layout = new ViewRenderer(APP_PATH . '/layouts/mail.php');
        $body = new ViewRenderer($script);
        $body->assign($params);
        if ($contact !== null) {
            $layout->optoutUrl = self::buildAuthenticationUrl('optout', $contact);   
            $body->contact = $contact;
        }
        $layout->body = $body;
        return $layout->doRenderToString();
    }
    
}