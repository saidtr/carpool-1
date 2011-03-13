<?php

class AuthenticationHelperToken implements IAuthenticationHelper {
    
    function init() {
        return true;
    }
    
    function authenticate($params) {
        assert(isset($params['user']) && isset($params['pass']));
        
        $user = $params['user'];
        $pass = $params['pass'];
        
        return true;
    }
    
}