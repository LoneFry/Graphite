<?php
/**
 * Role - Role AR class
 * File : /^/models/Role.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * Role class - for managing site roles/responsibilities
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Record.php
 */
class Role extends Record {
    protected static $table = 'Roles';
    protected static $pkey  = 'role_id';
    protected static $query = '';
    protected static $vars  = array(
        'role_id'      => array('type' => 'i', 'min' => 1, 'guard' => true),
        'label'        => array('type' => 's', 'strict' => true, 'min' => 3, 'max' => 255),
        'description'  => array('type' => 's', 'strict' => true, 'min' => 3, 'max' => 255),
        'creator_id'   => array('type' => 'i', 'strict' => true, 'def' => 0, 'min' => 1),
        'disabled'     => array('type' => 'b', 'def' => 0),
        'dateModified' => array('type' => 'ts', 'min' => 0),
        'dateCreated'  => array('type' => 'ts', 'min' => 0),
    );
    protected static $joiners = array(
        'Login' => 'Roles_Logins',
    );

    /**
     * called by Record::insert() BEFORE running INSERT query
     *
     * @return void
     */
    public function oninsert() {
        $this->__set('dateCreated', NOW);
        if ($this->__get('creator_id') < 1) {
            $this->__set('creator_id', G::$S->Login->login_id);
        }
    }

    /**
     * called by Record::update() BEFORE running UPDATE query
     *
     * @return void
     */
    public function onupdate() {
        $this->__set('dateModified', NOW);
    }

    /**
     * Get the Role's Creator
     *
     * @return string the loginname of the creator of the Role
     */
    public function getCreator() {
        if ($this->__get('creator_id') > 0) {
            $creator = new Login($this->__get('creator_id'));
            $creator->load();
            return $creator->loginname;
        }
        return '';
    }

    /**
     * Get the Role's Members
     *
     * @param string $detail Which field to return from the Logins
     *
     * @return array|bool Array of login_id:detail key:value pairs
     */
    public function getMembers($detail = 'grantor_id') {
        if ($detail == 'loginname') {
            $query = "SELECT l.`login_id`, l.`loginname` "
                ."FROM `".Login::getTable()."` l, `".static::getTable('Login')."` rl "
                ."WHERE l.`login_id` = rl.`login_id` AND rl.`role_id` = ".$this->__get('role_id')
                ." ORDER BY l.`loginname`"
            ;
        } else {
            $query = "SELECT rl.`login_id`, rl.`grantor_id` "
                ."FROM `".static::getTable('Login')."` rl "
                ."WHERE rl.`role_id` = ".$this->__get('role_id').''
            ;
        }

        if (false === $result = G::$m->query($query)) {
            return false;
        }
        if (0 == $result->num_rows) {
            $result->close();
            return array();
        }
        $a = array();
        while ($row = $result->fetch_array()) {
            $a[$row[0]] = $row[1];
        }
        $result->close();

        return $a;
    }

    /**
     * Grant Role to specified Login
     *
     * @param int $login_id The login to grant to
     *
     * @return bool true on success, false on failure
     */
    public function grant($login_id) {
        if (!is_numeric($login_id)) {
            return false;
        }
        $grantor = G::$S->Login?G::$S->Login->login_id:0;
        $query = "INSERT INTO `".static::getTable('Login')."` (`role_id`,`login_id`,`grantor_id`,`dateCreated`) "
            ."VALUES (".$this->__get('role_id').",".$login_id.",".$grantor.",".NOW.")";
        if (G::$M->query($query)) {
            return true;
        }
        return false;
    }

    /**
     * Revoke Role from specified Login
     *
     * @param int $login_id The login to revoke from
     *
     * @return bool true on success, false on failure
     */
    public function revoke($login_id) {
        if (!is_numeric($login_id)) {
            return false;
        }
        $query = "DELETE FROM `".static::getTable('Login')."` "
            ."WHERE `role_id` = ".$this->__get('role_id')." AND `login_id` = ".$login_id;
        if (G::$M->query($query)) {
            return true;
        }
        return false;
    }
}
Role::prime();
