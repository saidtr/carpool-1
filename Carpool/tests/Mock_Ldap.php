<?php

// Dummy implementation of the LDAP functions

function ldap_connect($domain, $port = 389) {
    return true;
}

function ldap_error($con) {
    return "NOT IMPLEMENTED";
}

function ldap_errno($con) {
    return 48;
}

function ldap_err2str($errCode) {
    return "NOT IMPLEMENTED";
}

// This function treats credentials where username == password and not empty as valid
function ldap_bind($con, $user, $password) {
    $ldapDomainName = getConfiguration('auth.ldap.domain.name');
    if ($ldapDomainName) {
        $password = $ldapDomainName . '\\' . $password;
    }
    return (
        !Utils::isEmptyString($user) &&
        !Utils::isEmptyString($password) &&
        ($user === $password));
}

function ldap_unbind($con) {
    return true;
}
