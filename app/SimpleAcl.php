<?php

/**
 * The Simple ACL class provides a simple data structure that holds "roles"
 * and "resources". Both roles and resources can be of any type, but the usual
 * usage would be integer roles and string resources.
 * 
 * Supports fast answer for queries of type "is role X allowed to resource Y"
 * The roles can be hierarchic, meaning - we can define roles that inherits all
 * the priveleges of other roles.
 * 
 * Usage:
 * 1. Define relevant roles
 * 2. Add rules of type: role -> allowed resources 
 * 3. Query the ACL
 * 
 * This class does not do any actual filtering, only providing convenient data
 * structure for managing access
 *
 */
class SimpleAcl {

    // Array containing list of available resources, each resource points on
    // all roles allowed access
    private $_acl = null;
    
    // Array containing list of all available roles, each role points on all 
    // inherited roles
    private $_roles = null;
    
    // Cache role hierarchy, to save the tree traversal each time
    private $_rolesHierarchyCache = null;

    public function __construct() {
        $this->_acl = array();
        $this->_roles = array();
        $this->_rolesHierarchyCache = array();
    }
    
    public function isAllowed($role, $resource) {
        // debug(__METHOD__ . "($role, $resource)");

        // We should only have defined roles; resource may not exist
        assert('array_key_exists($role, $this->_roles)');

        $res = (isset($this->_acl[$resource]) && in_array($role, $this->_acl[$resource]));
        
        info(__METHOD__ . ": Access to resource $resource for role $role is " . (($res) ? 'allowed' : 'blocked'));
        return $res;
    }

    public function getAllowedRoles($resource) {
        if (isset($this->_acl[$resource])) {
            return $this->_acl[$resource];
        }
        return false;
    }

    public function addRole($role, $rolesIncluded = null) {
        // debug(__METHOD__ . "($role, " . json_encode($rolesIncluded) . ")");

        assert('!isset($this->_roles[$role])');

        // Each role in the ACL holds an array of all roles that inherit from
        // this role.  
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
        // debug(__METHOD__ . "($role, " . json_encode($resource) . ")");
        
        assert('isset($this->_roles[$role])');
        
        // Each resource holds an array of roles allowed to access it
        if (is_array($resource)) {
            foreach ($resource as $r) {
                if (!isset($this->_acl[$r])) {
                    $this->_acl[$r] = $this->getAllRolesInherited($role);
                } else {
                    $this->_acl[$r] = array_merge($this->_acl[$r], $this->getAllRolesInherited($role));
                }
            }
        } else {
            if (!isset($this->_acl[$resource])) {
                $this->_acl[$resource] = $this->getAllRolesInherited($role);
            } else {
                $this->_acl[$resource] = array_merge($this->_acl[$resource], $this->getAllRolesInherited($role));
            }
        }
    }

    // Iterate over the role hierarchy and returns a list of all roles 
    // that inherit from this role (including this one).
    private function getAllRolesInherited($role) {
        if (!isset($this->_rolesHierarchyCache[$role])) {
            $res = array();
            $rolesIncluded = $this->_roles[$role];
            while (!empty($rolesIncluded)) {
                $cur = array_pop($rolesIncluded);
                $res []= $cur;
                foreach ($this->_roles[$cur] as $inc) {
                    if (in_array($inc, $res) === false) {
                        array_push($rolesIncluded, $inc);
                    }
                }
            }
            $this->_rolesHierarchyCache[$role] = $res;
            return $res;
        }
        return $this->_rolesHierarchyCache[$role];
    }
    
    public function setRoles($roles) {
        assert('(is_array($roles) === true) && !empty($roles)');
        
        $this->_roles = $roles;
    }
    
    public function setAcl($acl) {
        assert('(is_array($acl) === true) && !empty($acl)');
        assert('$this->_roles !== null');
        
        $this->_acl = $acl;
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

