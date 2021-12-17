<?php
class Session_Native extends Session {

	public function id()
	{
		return session_id();
	}

	protected function _read($id = NULL)
	{
		
		$session_cookie_domain = empty(Cookie::$domain)
		    ? ini_get('session.cookie_domain')
		    : Cookie::$domain;

		session_set_cookie_params(
			$this->_lifetime,
			Cookie::$path,
			$session_cookie_domain,
			Cookie::$secure,
			Cookie::$httponly
		);

		session_cache_limiter(FALSE);

		session_name($this->_name);

		if ($id){
			session_id($id);
		}

        try {
            session_start();
        } catch(Exception $e) {
            $this->_destroy();
            session_start();
        } 

		$this->_data =& $_SESSION;
		return NULL;
	}

	/**
	 * @return  string
	 */
	protected function _regenerate()
	{
		// Regenerate the session id
		session_regenerate_id();

		return session_id();
	}

	/**
	 * @return  bool
	 */
	protected function _write()
	{
		// Write and close the session
		session_write_close();

		return TRUE;
	}

	/**
	 * @return  bool
	 */
	protected function _restart()
	{
		// Fire up a new session
		$status = session_start();

		// Use the $_SESSION global for storing data
		$this->_data =& $_SESSION;

		return $status;
	}

	/**
	 * @return  bool
	 */
	protected function _destroy()
	{
		// Destroy the current session
		session_destroy();

		// Did destruction work?
		$status = ! session_id();

		if ($status)
		{
			// Make sure the session cannot be restarted
			Cookie::delete($this->_name);
		}

		return $status;
	}

}
