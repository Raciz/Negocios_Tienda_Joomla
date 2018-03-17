<!-- Ecwid Shopping Cart extension v3.3 -->
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

if (!defined('ECWID_SCRIPT')) define('ECWID_SCRIPT',1);

require_once JPATH_SITE
        . DIRECTORY_SEPARATOR . 'components'
        . DIRECTORY_SEPARATOR . 'com_ecwid'
        . DIRECTORY_SEPARATOR . 'helpers'
        . DIRECTORY_SEPARATOR . 'common.php';

        echo EcwidCommon::getProductBrowserJS();
?>

<div id="ecwid_minicart_module_wrapper">
	<script type="text/javascript"> xMinicart("style="); </script>
</div>
<!-- END Ecwid Shopping Cart extension v3.3 -->
