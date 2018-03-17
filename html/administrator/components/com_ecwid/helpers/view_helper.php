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

$jversion = new JVersion();

if (version_compare($jversion->getShortVersion(), '3.0', '<')) {
    class EcwidViewHelper {
        static function buildSubmenu($items)
        {
            foreach ($items as $item)
            {
                JSubMenuHelper::addEntry($item['name'], $item['link'], $item['active']);
            }
        }

        static function renderSubmenu()
        {

        }
    }
} else {
    class EcwidViewHelper {
        static public function buildSubmenu($items)
        {
            foreach ($items as $item)
            {
                JHtmlSidebar::addEntry($item['name'], $item['link'], $item['active']);
            }
        }

        static public function renderSubmenu()
        {
            return JHtmlSidebar::render();
        }
    }
}