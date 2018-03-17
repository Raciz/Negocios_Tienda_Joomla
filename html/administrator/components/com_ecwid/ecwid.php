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
include_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/legacy_class.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR.'/controller.php' );


$controller = EcwidLegacyJController::getInstance('Ecwid');
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
?>