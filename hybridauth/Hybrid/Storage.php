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

	private $_config = array();
	private $_store  = array();

	private function write_config()
	{
		$_SESSION["HA::CONFIG"] = serialize($this->_config);
	}

	private function read_config()
	{
		if (isset($_SESSION["HA::CONFIG"]))
		{
			return unserialize($_SESSION["HA::CONFIG"]);
		}
		return array();
	}

	private function write_store()
	{
		$_SESSION["HA::STORE"] = serialize($this->_store);
	}

	private function read_store()
	{
		if (isset($_SESSION["HA::STORE"]))
		{
			return unserialize($_SESSION["HA::STORE"]);
		}
		return array();
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
