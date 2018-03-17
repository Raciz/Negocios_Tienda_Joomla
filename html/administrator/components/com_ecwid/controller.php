<?php
/**
 * @author     Ecwid, Inc http://www.ecwid.com
 * @copyright  (C) 2009 - 2014 Ecwid, Inc.
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

jimport('joomla.application.component.controller');
JLoader::register(
	'EcwidProductAPI',
	JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR .
	'helpers' . DIRECTORY_SEPARATOR .
	'ecwid_product_api.php'
);
JLoader::register(
    'EcwidCommon',
    JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR .
    'helpers' . DIRECTORY_SEPARATOR .
    'common.php'
);

JLoader::register(
	'Ecwid',
	JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR .
	'helpers' . DIRECTORY_SEPARATOR .
	'ecwid.php'
);


/**
 * ecwid Controller
 *
 * @package    Joomla
 * @subpackage ecwid
 */
include_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/legacy_class.php');

class EcwidController extends EcwidLegacyJController
{
	protected $default_view = 'default';

	public function hide_vote_message() {
		EcwidCommon::setParam('hideVoteMessage', true);
		die('success');
	}
}

