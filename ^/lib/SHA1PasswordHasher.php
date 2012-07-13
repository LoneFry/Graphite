<?php
/** **************************************************************************
 * Project     : Graphite
 *                Simple MVC web-application framework
 * Created By  : LoneFry
 *                dev@lonefry.com
 * License     : CC BY-NC-SA
 *                Creative Commons Attribution-NonCommercial-ShareAlike
 *                http://creativecommons.org/licenses/by-nc-sa/3.0/
 *
 * File        : /^/lib/SHA1PasswordHasher.php
 *                Simple SHA1 based Password Hashing plugin
 ****************************************************************************/

require_once LIB.'/PasswordHasher.php';

class SHA1PasswordHasher implements IPasswordHasher {
	/**
	 * private constructor to prevent instantiation
	 */
	private function __construct() {
	}

	/**
	 * Create a hashword using sha1()
	 *
	 * @param string $password password to hash
	 *
	 * @return string hashed password for storage
	 */
	public static function hash_password($password) {
		return sha1($password);
	}

	/**
	 * Test a password against a recalled SHA1
	 *
	 * @param string $password password to test
	 * @param string $hash     SHA1 from database
	 *
	 * @return bool true if password passes, false if not
	 */
	public static function test_password($password, $hash) {
		return sha1($password) == $hash;
	}

	/**
	 * Test a hash is SHA1
	 *
	 * @param string $hash SHA1 from database
	 *
	 * @return bool true if argument passes as SHA1, false if not
	 */
	public static function is_hash($hash) {
		return preg_match('/[0-9a-f]{40}/i', $hash);
	}
}

