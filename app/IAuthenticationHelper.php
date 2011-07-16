<?php

interface IAuthenticationHelper {
    
    /**
     * Authenticates a contact by the given parameters.
     * For example: User => joe, Password => blah
     * 
     * @param $params array Array holding the parameters used for authentication
     * @return Contact id if identification succeeded, or false on failure
     * @throws Exception on any internal problem (e.g. database access problem)
     */
    function authenticate($params);
    
}
