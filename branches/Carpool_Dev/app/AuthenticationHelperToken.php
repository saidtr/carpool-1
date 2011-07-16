<?php

class AuthenticationHelperToken implements IAuthenticationHelper {

    function init() {
        return true;
    }

    function authenticate($params) {
        assert(isset($params['user']) && isset($params['pass']));

        $contactId = $params['user'];
        $token     = $params['pass'];

        $contact = DatabaseHelper::getInstance()->getContactByIdentifier($contactId, $identifier);
        if ($contact) {
            info(__METHOD__ . ': Contact ' . $contact['Id'] . ' succesfully authenticated');
            return array('Id' => $contact['Id'], 'Role' => $contact['Role']);
        } else {
            warn(__METHOD__ . ': Authentication failed for contact "' . $contactId . '" and token "' . $identifier . '"');
            return false;
        }
    }
    
}