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

if (!class_exists('EcwidLegacyJView', false)) {
	$jversion = new JVersion();
	if (version_compare($jversion->getShortVersion(), '2.5.5', '>')) {
		class EcwidLegacyJView extends JViewLegacy
		{
		}

		class EcwidLegacyJController extends JControllerLegacy
		{
		}

		class EcwidLegacyJModel extends JModelLegacy
		{
		}
	} else {
		jimport('joomla.application.component.view');
		jimport('joomla.application.component.controller');
		jimport('joomla.application.component.model');
		class EcwidLegacyJView extends JView
		{
		}

		class EcwidLegacyJController extends JController
		{
		}

		class EcwidLegacyJModel extends JModel
		{
		}
	}
}
