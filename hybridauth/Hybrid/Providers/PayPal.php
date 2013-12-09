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

define('AUTHORIZATION_ENDPOINT', 'https://identity.x.com/xidentity/resources/authorize');
define('ACCESS_TOKEN_ENDPOINT',  'https://identity.x.com/xidentity/oauthtokenservice');
define('PROFILE_ENDPOINT',       'https://identity.x.com/xidentity/resources/profile/me');

class Hybrid_Providers_PayPal extends Hybrid_Provider_Model_OAuth2
{
	/**
	* IDp wrappers initializer 
	*/
	function initialize() 
	{
		parent::initialize();
		// Provider api end-points
		$this->api->authorize_url  = "https://api.sandbox.paypal.com/v1/authorize";
		$this->api->token_url      = ACCESS_TOKEN_ENDPOINT;
	}

	/**
	* Complete authorization process
	*/
	function loginFinish()
	{

	}

	/**
	* Load the user profile from the IDp api client
	*/
	function getUserProfile()
	{}
}