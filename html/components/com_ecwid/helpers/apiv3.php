<?php
/**
 * @author     Ecwid, Inc http://www.ecwid.com
 * @copyright  (C) 2009 - 2018 Ecwid, Inc.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Contributors:
 * @author     Rick Blalock
 * @license    GNU/GPL
 * and
 * @author     RocketTheme http://www.rockettheme.com
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class EcwidApiV3
{
	static protected $instance = null;

	protected $is_paid_account = null;

	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new EcwidApiV3();
		}

		return self::$instance;
	}
	
	public function setToken($value)
	{
		Ecwid::setParam('apiv3token', $value);	
	}
	
	public function getToken()
	{
		return Ecwid::getParam('apiv3token');
	}
	
	public function hasScope($scope)
	{
		$scope_string = Ecwid::getParam('apiv3scope');
		
		return strpos($scope_string, $scope) !== false;
	}
	
	public function setScope($scope)
	{
		Ecwid::setParam('apiv3scope', $scope);
	}
	
	public function getClientId()
	{
		return 'bmWzQL83eEQBrPkd';
	}

	public function getClientSecret()
	{
		return 'X37DpDfXQFYmvhJHjG74HXPfWBBTTZzM';
	}	
	
	public function isPaidAccount()
	{
		if (!is_null($this->is_paid_account)) {
			return $this->is_paid_account;
		}
		
		$http = JHttpFactory::getHttp();
		
		$response = $http->get(
			'https://app.ecwid.com/api/v3/' . Ecwid::getParam('storeID') . '/profile?token=' . $this->getToken()	
		);
		
		if ($response->code == 200) {
			$profile = @json_decode($response->body);

			$this->is_paid_account = $profile
				&& property_exists( $profile, 'account')
				&& property_exists( $profile->account, 'availableFeatures' )
				&& is_array( $profile->account->availableFeatures )
				&& in_array(
					'PREMIUM', $profile->account->availableFeatures
				);
		}
		
		return $this->is_paid_account;
	}


	public function isAvailable()
	{
		return (bool)$this->getToken();
	}
	
}