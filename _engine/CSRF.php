<?php

class CSRF {
	
	/** Session var name
	 * @var string
	 */
	public static $session = '_CSRF';
	
	/** Generate CSRF value for form
	 * @param	string	$form	- Form name as session key
	 * @return	string	- token
	 */
	static function generate($form = NULL) {
		$token = CSRF::token() . CSRF::fingerprint();
		$_SESSION[CSRF::$session . '_' . $form] = $token;
		return $token;
	}
	
	/** Check CSRF value of form
	 * @param	string	$token	- Token
	 * @param	string	$form	- Form name as session key
	 * @return	boolean
	 */
	public static function check($token, $form = NULL) {
		if (isset($_SESSION[CSRF::$session . '_' . $form]) && $_SESSION[CSRF::$session . '_' . $form] == $token) { // token OK
			return (substr($token, -32) == CSRF::fingerprint()); // fingerprint OK?
		}
		return FALSE;
	}
	
	/** Generate token
	 * @param	void
	 * @return  string
	 */
	protected static function token() {
		mt_srand((double) microtime() * 10000);
		$charid = strtoupper(md5(uniqid(rand(), TRUE)));
		return substr($charid, 0, 8) . substr($charid, 8, 4) . substr($charid, 12, 4) . substr($charid, 16, 4) . substr($charid, 20, 12);
	}
	
	/** Returns "digital fingerprint" of user
	 * @param 	void
	 * @return 	string 	- MD5 hashed data
	 */
	protected static function fingerprint() {
		return strtoupper(md5(implode('|', array($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']))));
	}
}
?>