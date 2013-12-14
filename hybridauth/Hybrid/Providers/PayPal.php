<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | https://github.com/hybridauth/hybridauth
* (c) 2009-2013 HybridAuth authors  | hybridauth.sourceforge.net/licenses.html
*/

/**
 * Hybrid_Providers_PayPal provider adapter based on OAuth2 protocol
 * added by gerasim13 | https://github.com/gerasim13
 */

class Hybrid_Providers_PayPal extends Hybrid_Provider_Model
{
	public $scope = array('openid', 'profile', 'address', 'email', 'phone');
	/**
	* IDp wrappers initializer 
	*/
	function initialize() 
	{
		if (! $this->config["keys"]["id"] || ! $this->config["keys"]["secret"])
		{
			throw new Exception("Your application id and secret are required in order to connect to {$this->providerId}.", 4);
		}
		if (!class_exists('PayPalApiException', false))
		{
			require_once Hybrid_Auth::$config["path_libraries"] . "PayPal/PayPal.php";
		}
		// $this->setUserUnconnected();
		// $this->clearTokens();

		$this->api = new PayPal(
			array(
				'appId'  => $this->config["keys"]["id"],
				'secret' => $this->config["keys"]["secret"],
				'mode'   => $this->config["sandbox"] ? 'sandbox' : 'live',
				'scope'  => $this->scope
			)
		);
		// Refresh token
		if($this->token("access_token") && $this->token("refresh_token") && $this->token("expires_in"))
		{
			if ($this->token("expires_in") < time())
			{
				$token = $this->api->refreshTokenInfo($this->token("refresh_token"), $this->scope);
				$this->saveToken($token);
			}
			$this->api->setAccessToken($this->token("access_token"));
		}
	}

	/**
	* Begin login step
	*/
	function loginBegin()
	{
		$parameters = array("endpoint" => $this->endpoint, "scope" => $this->scope);
		$optionals  = array("endpoint", "scope");

		foreach ($optionals as $parameter)
		{
			if( isset($this->config[$parameter]) && !empty($this->config[$parameter]) )
			{
				$parameters[$parameter] = $this->config[$parameter];
			}
		}

		$url = $this->api->getLoginUrl($parameters);
		Hybrid_Auth::redirect($url);
	}

	/**
	* Complete authorization process
	*/
	function loginFinish()
	{
		$token = $this->api->getTokenInfo();
		$this->saveToken($token);
		// Set user as logged in
		$this->setUserConnected();
	}

	/**
	* Load the user profile from the IDp api client
	*/
	function getUserProfile()
	{
		$user = $this->api->getUserInfo();
		# Store the user profile.
		$this->user->profile->displayName   = $this->get_value($user->getName(), "");
		$this->user->profile->firstName     = $this->get_value($user->getGivenName(), "");
		$this->user->profile->lastName      = $this->get_value($user->getFamilyName(), "");
		$this->user->profile->photoURL      = $this->get_value($user->getPicture(), "");
		$this->user->profile->gender        = $this->get_value($user->getGender(), "");
		$this->user->profile->email         = $this->get_value($user->getEmail(), "");
		$this->user->profile->emailVerified = $this->get_value($user->getEmailVerified(), "");

		if (!is_null($user->getUserId()))
		{
			$profileURL = $user->getUserId();
			$urlPath    = explode("/", $profileURL);
			$identifier = end($urlPath);
			$this->user->profile->identifier = hexdec($identifier);
			$this->user->profile->profileURL = $profileURL;
		}

		if (!is_null($user->getBirthday()))
		{
			list($birthday_month, $birthday_day, $birthday_year) = explode("/", $user->getBirthday());
			$this->user->profile->birthDay   = (int)$birthday_day;
			$this->user->profile->birthMonth = (int)$birthday_month;
			$this->user->profile->birthYear  = (int)$birthday_year;
		}

		return $this->user->profile;
	}

	private function saveToken($token)
	{
		foreach ($token->toArray() as $key => $value)
		{
			$this->token($key, $value);
		}
	}

	private function get_value($value1, $value2)
	{
		return !is_null($value1) ? $value1 : $value2;
	}
}