<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html 
*/

/**
 * HybridAuth storage manager
 */
class Hybrid_Storage 
{
	// ------------------------------------------------------------------------------

	private static $sessionSaveHandler = NULL;

	public static function setSaveHandler($handler)
	{
		self::$sessionSaveHandler = $handler;
	}

	public static function saveHandler()
	{
		return self::$sessionSaveHandler;
	}

	// ------------------------------------------------------------------------------

	function __construct()
	{
		if (!is_null(self::$sessionSaveHandler))
		{
			session_set_save_handler(self::$sessionSaveHandler, true);
		}

		if (!session_id())
		{
			if(!session_start())
			{
				throw new Exception("Hybridauth requires the use of 'session_start()' at the start of your script, which appears to be disabled.", 1);
			}
		}
		// Read data from session
		$this->_config = $this->read_config();
		$this->_store  = $this->read_store();
		// Add session id and lib version in config
		$this->config("php_session_id", session_id());
		$this->config("version", Hybrid_Auth::$version);
	}

	function __destruct()
	{
		$this->write_config();
		$this->write_store();
		unset($this->_config);
		unset($this->_store);
		self::$sessionSaveHandler = NULL;
	}

	// ------------------------------------------------------------------------------

	private $_config = NULL;
	private $_store  = NULL;

	private function write_config()
	{
		//self::$sessionSaveHandler->HA_CONFIG = $this->_config;
		$_SESSION["HA::CONFIG"] = $this->_config;
		//error_log('session data: ' . serialize($_SESSION));
	}

	private function read_config()
	{
		//return isset(self::$sessionSaveHandler->HA_CONFIG) ? self::$sessionSaveHandler->HA_CONFIG : array();
		return isset($_SESSION["HA::CONFIG"]) ? $_SESSION["HA::CONFIG"] : array();
	}

	private function write_store()
	{
		//self::$sessionSaveHandler->HA_STORE = $this->_store;
		$_SESSION["HA::STORE"] = $this->_store;
		//error_log('session data: ' . serialize($_SESSION));
	}

	private function read_store()
	{
		//return isset(self::$sessionSaveHandler->HA_STORE) ? self::$sessionSaveHandler->HA_STORE : array();
		return isset($_SESSION["HA::STORE"]) ? $_SESSION["HA::STORE"] : array();
	}

	// ------------------------------------------------------------------------------

	public function config($key, $value = null) 
	{
		$key = strtolower($key);

		if (!is_null($value))
		{
			$this->_config[$key] = $value;
			$this->write_config();
		}
		elseif (isset($this->_config[$key]))
		{
			return $this->_config[$key];
		}

		return NULL;
	}

	public function get($key) 
	{
		$key = strtolower($key);

		if (isset($this->_store[$key]))
		{
			return $this->_store[$key];
		}

		return NULL; 
	}

	public function set( $key, $value )
	{
		$key = strtolower($key);

		$this->_store[$key] = $value;
		$this->write_store();
	}

	public function clear()
	{
		$this->_store = array();
		$this->write_store();
	} 

	public function delete($key)
	{
		$key = strtolower($key);

		if (isset($this->_store[$key]))
		{
			unset($this->_store[$key]);
			$this->write_store();
		}
	}

	public function deleteMatch($key)
	{
		$key = strtolower($key);

		if (count($this->_store))
		{
			foreach($this->_store as $k => $v )
			{
				if(strstr($k, $key))
				{
					unset($this->_store[$k]); 
				}
			}
			$this->write_store();
		}
	}

	public function getSessionData()
	{
		if (isset($this->_store))
		{
			return $this->_store;
		}
		return NULL; 
	}

	public function restoreSessionData( $sessiondata = NULL )
	{
		$this->_store = unserialize($sessiondata);
		$this->write_store();
	} 
}
