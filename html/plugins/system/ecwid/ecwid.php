<?php
/**
 * @author     Ecwid, Inc http://www.ecwid.com
 * @copyright  (C) 2009 - 2017 Ecwid, Inc.
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

jimport('joomla.plugin.plugin');


class plgSystemEcwid extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access    protected
	 *
	 * @param    object    $subject The object to observe
	 * @param     array    $config  An array that holds the plugin configuration
	 *
	 * @since    1.0
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

        JLoader::register(
            'EcwidCommon',
            JPATH_SITE . DIRECTORY_SEPARATOR .
            'components' . DIRECTORY_SEPARATOR .
            'com_ecwid' . DIRECTORY_SEPARATOR .
            'helpers' . DIRECTORY_SEPARATOR .
            'common.php'
        );

		JLoader::register(
			'Ecwid',
			JPATH_SITE . DIRECTORY_SEPARATOR .
			'components' . DIRECTORY_SEPARATOR .
			'com_ecwid' . DIRECTORY_SEPARATOR .
			'helpers' . DIRECTORY_SEPARATOR .
			'ecwid.php'
		);
	}

    /**
     * onBeforeRender handler
     *
     * Adds the prefetch metas
     *
     * @access  public
     * @return null
     */
    function onBeforeRender()
    {
        $doc = JFactory::getDocument();

        if ($doc->getType() == 'html' && JFactory::getApplication()->isSite()) {
			$doc->addCustomTag('<meta http-equiv="x-dns-prefetch-control" content="on">');
			$doc->addCustomTag('<link rel="dns-prefetch" href="//images-cdn.ecwid.com/">');
            $doc->addCustomTag('<link rel="dns-prefetch" href="//images.ecwid.com/">');
            $doc->addCustomTag('<link rel="dns-prefetch" href="//app.ecwid.com/">');
			$doc->addCustomTag('<link rel="dns-prefetch" href="//ecwid-static-ru.r.worldssl.net">');
			$doc->addCustomTag('<link rel="dns-prefetch" href="//ecwid-images-ru.r.worldssl.net">');
/*
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$id = $menu->getActive()->id;
			$params = JComponentHelper::getParams('com_ecwid');
			$lastPbMenuItemId = $params->get('lastPbMenuItemIdForPrefetch');

			if ($id && $lastPbMenuItemId && $id != $lastPbMenuItemId) {
				$link = JRoute::_($menu->getItem($lastPbMenuItemId)->link);
				$doc->addCustomTag('<link rel="prefetch" href="' . $link . '">');
				$doc->addCustomTag('<link rel="prerender" href="' . $link . '">');
			}
*/
		}

        if (JFactory::getApplication()->isAdmin()) {

            $input = JFactory::getApplication()->input;

            if ('com_menus' == $input->getCmd('option') && 'item' == $input->getCmd('view')) {
                $doc->addScript('components/com_ecwid/assets/menu-item.js');
                $doc->addStylesheet('components/com_ecwid/assets/css/menu-item.css');
            }

            $is_landing = $input->getCmd('option') == 'com_cpanel';
            $is_ecwid = $input->getCmd('option') == 'com_ecwid';

            if (!$is_landing && !$is_ecwid) return;

            $params = JComponentHelper::getParams('com_ecwid');

            $installDate = $params->get('ecwidInstallDate');
            if (empty($installDate)) {
                EcwidCommon::setParam('ecwidInstallDate', time());
            }

            if (!$params->get('hideVoteMessage') && time() - $params->get('ecwidInstallDate') > 60*60*24*30 && EcwidCommon::isPaidAccount()) {

                $doc->addStylesheet('components/com_ecwid/assets/css/messages.css');
                $doc->addScript('components/com_ecwid/assets/messages.js');

                JPlugin::loadLanguage('com_ecwid', JPATH_ADMINISTRATOR);
                $message = JText::_('COM_ECWID_VOTE_MESSAGE');
                $buttonText = JText::_('COM_ECWID_VOTE_BUTTON');
                $hideText = JText::_('COM_ECWID_VOTE_HIDE');
                $url = 'http://extensions.joomla.org/write-review/review/add?extension_id=1017';
                $messageHtml = <<<HTML
            <div id="ecwid-vote-message" style="display:inline-block;width:100%">
                <div id="ecwid-vote-text">$message</div>
                <a class="btn btn-primary" id="ecwid-vote-button" target="_blank" href="$url">$buttonText</a>
                <a href="javascript:void(0);" id="ecwid-vote-hide">$hideText</a>
            </div>
HTML;
                JFactory::getApplication()->enqueueMessage($messageHtml, 'message');
            }

        }
    }

    function onAfterRender()
    {

        $doc = JFactory::getDocument();
        $app = JFactory::getApplication();

        if ($app->isSite() && $doc->getType() == 'html') {

			$script_url = EcwidCommon::getScriptURL();
			$escript  = PHP_EOL . '<script data-cfasync="false" type="text/javascript" src="' . $script_url . '"></script>';

			$eparams = $app->getParams();
			if ($eparams->get('storeID', null) == null) {
				$eparams = JComponentHelper::getParams('com_ecwid');
			}
			$sso = Ecwid::getSso()->getSSOCode($eparams);

			$body = method_exists($app, 'getBody') ? $app->getBody() : JResponse::getBody();
			// split up the body after the body tag
			$matches = preg_split('/(<body.*?>)/i', $body, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

			if (defined('ECWID_SCRIPT')) {
                $body = $matches[0] . $matches[1] . $escript . $sso . $matches[2];
            } else {
                $body = $matches[0] . $matches[1] . $sso . $matches[2];
            }

            if (method_exists($app, 'setBody')) {
                $app->setBody($body);
            } else {
                JResponse::setBody($body);
            }
        }
    }

    protected function getSSOCode($params)
    {
    }
}
