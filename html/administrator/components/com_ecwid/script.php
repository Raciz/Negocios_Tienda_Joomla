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
/**
 * Script file of Ecwid component.
 */
class com_ecwidInstallerScript
{
	public function update($parent)
	{
		JLoader::register(
			'EcwidCommon',
			JPATH_SITE . DIRECTORY_SEPARATOR .
			'components' . DIRECTORY_SEPARATOR .
			'com_ecwid' . DIRECTORY_SEPARATOR .
			'helpers' . DIRECTORY_SEPARATOR .
			'common.php'
		);

		EcwidCommon::setParam('enableChameleon', 0);
		EcwidCommon::setParam('apiV3Token', '');
	}
}