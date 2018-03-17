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

class EcwidSSO
{
	static protected $instance = null;

	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new EcwidSSO();
		}

		return self::$instance;
	}

	public function isEnabled()
	{
		if (Ecwid::getParam('ssoKey') != '') {
			return true;
		}

		if (!$this->isAvailable()) {
			return false;
		}

		if (
			Ecwid::getParam('ssoEnabled')
			&& Ecwid::getApiV3()->getToken() != ''
			&& Ecwid::getApiV3()->hasScope('create_customers')
		) {
			return true;
		}

		return false;
	}

	public function isAvailable()
	{
		return Ecwid::isPaidAccount() && Ecwid::getApiV3()->isAvailable();
	}

	public function getSsoCode($params)
	{
		if (!$this->isEnabled()) return;
		
		$useV1 = false;
		$useV3 = Ecwid::getApiV3()->hasScope('create_customers') && Ecwid::getApiV3()->isPaidAccount();

		if (!$useV3) {
			$useV1 = $params->get('ssoKey') && EcwidCommon::isPaidAccount($params->get('storeID'));
		}
		
		if (!$useV1 && !$useV3) {
			return "";
		}

		$user = JFactory::getUser();

		$sso_profile = '';
		if ($user->get('id')) {

			$user_data = array(
				'userId' => $user->get('id'),
				'profile' => array(
					'email' => $user->get('email'),
					'billingPerson' => array(
						'name' => $user->get('name')
					)
				)
			);

			if ($useV3) {
				$key = Ecwid::getApiV3()->getClientSecret();
				$user_data['appClientId'] = Ecwid::getApiV3()->getClientId();
			} else {
				$key = Ecwid::getParam('ssoKey');
				$user_data['appId'] = "wp_" . $params->get('storeID');
			}


			$user_data_encoded = base64_encode(json_encode($user_data));
			
			$time = time();
			$hmac = hash_hmac('sha1', "$user_data_encoded $time", $key);

			$sso_profile = "$user_data_encoded $hmac $time";
		}

		$pb_url = EcwidCommon::getProductBrowserURL();

		$template = JRoute::_('index.php?option=com_users&view=login&return=ECWIDBACKURL', false);
		$signin_url = str_replace('BACKURL', base64_encode($pb_url), $template);
		$sign_in_out_urls = <<<JS


var ecwidSignInUrlTemplate = '$template';

if (typeof window.btoa != 'undefined') {
    window.Ecwid.OnPageLoaded.add(function() {
        var url = ecwidSignInUrlTemplate.replace('ECWIDBACKURL', btoa(location.href));
        window.Ecwid.setSignInUrls({
            signInUrl: url,
            signOutUrl: url
        });
    });
}

window.Ecwid.OnAPILoaded.add(function() {
    window.Ecwid.setSignInUrls({
        signInUrl: '$signin_url',
        signOutUrl: '$signin_url'
    });
});

JS;

		return <<<JS

<script type="text/javascript">
var ecwid_sso_profile="$sso_profile";
$sign_in_out_urls
</script>

JS;
	}
}
