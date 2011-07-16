<?php

interface IAuthenticationHelperInteractive extends IAuthenticationHelper {
    
    /**
     * Generates and outputs the form used for this login
     */
    public function putLogonFormFields();
    
    /**
     * Validate the login form
     * 
     * @param $request Contains unescaped request parameters
     * @return True iff all the required data exists and valid
     */
    public function validateForm($request);
    
}