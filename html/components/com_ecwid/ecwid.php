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

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the base controller
require_once JPATH_COMPONENT . '/controller.php';

JLoader::register(
	'EcwidCommon',
	JPATH_SITE . DIRECTORY_SEPARATOR .
	'components' . DIRECTORY_SEPARATOR .
	'com_ecwid' . DIRECTORY_SEPARATOR .
	'helpers' . DIRECTORY_SEPARATOR .
	'common.php'
);

// Initialize the controller
$controller = new EcwidController();
$controller->execute( null );

// Redirect if set by the controller
$controller->redirect();
?>
