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

defined('_JEXEC') or die('Restricted access');

// Get Component parameters

if (!defined('ECWID_SCRIPT')) {
	define('ECWID_SCRIPT', 1);
}

$app = JFactory::getApplication();
$params = $app->getParams();

include_once (JPATH_SITE . '/components/com_ecwid/helpers/ecwid_catalog.php');

$options = array(
	'store_id'                        => $params->get('storeID', 1003),
	'list_of_views'                   => array('list', 'grid', 'table'),
	'ecwid_pb_categoriesperrow'       => $params->get('categoriesPerRow', 3),
	'ecwid_pb_productspercolumn_grid' => $params->get('gridColumns', 3),
	'ecwid_pb_productsperrow_grid'    => $params->get('gridRows', 20),
	'ecwid_pb_productsperpage_list'   => $params->get('list', 60),
	'ecwid_pb_productsperpage_table'  => $params->get('table', 60),
	'ecwid_pb_defaultview'            => $params->get('categoryView', 'grid'),
	'ecwid_pb_searchview'             => $params->get('searchView', 'list'),
	'ecwid_mobile_catalog_link'       => '',
	'ecwid_default_category_id'       => $params->get('defaultCategory'),
	'ecwid_is_secure_page'            => '',
	'display_categories'			  => $params->get('displayCategories', 1),
	'display_search'			      => $params->get('displaySearch', 1),
	'enable_chameleon'				  => $params->get('enableChameleon', 1),
    'use_seo_links'                   => $params->get('useSeoLinks', 1) && JFactory::getConfig()->get('sef') && !JFactory::getConfig()->get('sef_suffix'),
	'with_microdata'                  => $params->get('withMicrodata', 1)

);

?>
<div id="ecwid_jwrapper">
	<?php
	echo show_ecwid($options);
	?>
</div>
