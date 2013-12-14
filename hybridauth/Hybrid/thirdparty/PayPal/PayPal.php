<?php

//_________________________________________________________________________________________________

require_once(__DIR__ . '/../../../../../../autoload.php');
use PayPal\Common\PPApiContext;
use PayPal\Auth\OpenId\PPOpenIdSession;
use PayPal\Auth\OpenId\PPOpenIdTokeninfo;
use PayPal\Auth\OpenId\PPOpenIdUserinfo;

//_________________________________________________________________________________________________

class PayPalApiException extends Exception
{

}

//_________________________________________________________________________________________________

abstract class BasePayPal
{
	protected $context;
	protected $appId;
	protected $secret;

	function __construct($config)
	{
		$this->appId   = isset($config['appId'])  ? $config['appId']  : null;
		$this->secret  = isset($config['secret']) ? $config['secret'] : null;
		$this->context = new PPApiContext(array('mode' => $config['mode']));
	}
}

//_________________________________________________________________________________________________

class PayPal extends BasePayPal
{
	private $token;

	public function getLoginUrl($params = array())
	{
		$endpoint = isset($params['endpoint']) ? $params['endpoint'] : null;
		$scope    = isset($params['scope'])    ? $params['scope']    : null;
		return PPOpenIdSession::getAuthorizationUrl($endpoint, $scope , $this->appId, $this->context);
	}

	public function getTokenInfo()
	{
		$code = $_REQUEST['code'];
		if (!isset($code))
		{
			throw new PayPalApiException("Authorization code has not been received");
		}
		// Request token
		$params = array(
			'client_id'     => $this->appId,
			'client_secret' => $this->secret,
			'code'          => $code
		);
		return PPOpenIdTokeninfo::createFromAuthorizationCode($params, $this->context); 
	}

	public function refreshTokenInfo($refreshToken, $scope)
	{
		$params = array(
			'client_id'     => $this->appId,
			'client_secret' => $this->secret,
			'refresh_token' => $refreshToken,
			'scope'         => $scope
		);
		$token = new PPOpenIdTokeninfo();
		return $token->createFromRefreshToken($params, $this->context); 
	}

	public function setAccessToken($token)
	{
		$this->token = $token;
	}

	public function getUserInfo()
	{
		$params = array('access_token' => $this->token);
		return PPOpenIdUserinfo::getUserinfo($params, $this->context);
	}
}

//_________________________________________________________________________________________________

?>