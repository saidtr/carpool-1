<?php

class SimpleAcl {

    private $_acl = null;
    private $_roles = null;

    public function __construct() {
        $this->_acl = array();
        $this->_roles = array();
    }

    public function isAllowed($role, $resource) {
        debug(__METHOD__ . "($role, $resource)");

        // We should only have defined roles; resource may not exist
        assert('array_key_exists($role, $this->_roles)');

        return (isset($this->_acl[$resource]) && in_array($role, $this->_acl[$resource]));
    }

    public function getAllowedRoles($resource) {
        if (isset($this->_acl[$resource])) {
            return $this->_acl[$resource];
        }
        return false;
    }

    public function addRole($role, $rolesIncluded = null) {
        debug(__METHOD__ . "($role, " . json_encode($rolesIncluded) . ")");

        assert('!isset($this->_roles[$role])');

        if (isset($rolesIncluded)) {
            if (is_array($rolesIncluded)) {
                foreach ($rolesIncluded as $r) {
                    $this->_roles[$r] []= $role;
                }
            } else {
                $this->_roles[$rolesIncluded] []= $role;
            }
        }
        $this->_roles[$role] = array($role);
    }

    public function addResource($role, $resource) {
        debug(__METHOD__ . "($role, " . json_encode($resource) . ")");
        if (is_array($resource)) {
            foreach ($resource as $r) {
                $this->_acl[$r] = $this->_roles[$role];
            }
        } else {
            $this->_acl[$resource] = $this->_roles[$role];
        }
    }

    public function dump() {
        if (ENV === ENV_DEVELOPMENT) {
            echo "<hr/>";
            echo "<h1>Roles</h1>";
            var_dump($this->_roles);
            echo "<hr/>";
            echo "<h1>ACL</h1>";
            var_dump($this->_acl);
            echo "<hr/>";
        }
    }


}

