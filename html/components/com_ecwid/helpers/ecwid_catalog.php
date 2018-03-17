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

include_once "ecwid_product_api.php";
include_once "EcwidCatalog.php";

function _get_ecwid_catalog_params_from_escaped_fragment()
{
	$fragment = $_GET['_escaped_fragment_'];
	$type = '';
	$id = '';


	$matches = array();
	if (preg_match('!/~/(product|category)/.*id=([\d+]*)!', $fragment, $matches)) {
		$type = $matches[1];
		$id = $matches[2];
	} else if (preg_match('!.*/(p|c)/([\d+]*)!', $fragment, $matches)) {
		$type = $matches[1] == 'p' ? 'product' : 'category';
		$id = $matches[2];
	}

	return array(
		'type' => $type,
		'id' => $id
	);
}

function _get_ecwid_catalog_params_from_seo_urls()
{
	$pattern = '!.*-(p|c)([0-9]*)!';
	$current_url = $_SERVER['REQUEST_URI'];

	$matches = array();
	if ( !preg_match( $pattern, $current_url, $matches ) ) {
		return array();
	}

	$modes = array(
		'p' => 'product',
		'c' => 'category'
	);

	return array( 'type' => $modes[$matches[1]], 'id' => $matches[2] );
}

function show_ecwid($params) {
	$store_id = $params['store_id'];

	if (empty($store_id)) {
	  $store_id = '1003'; //demo mode
	}
		
	$list_of_views = $params['list_of_views'];

	$use_seo_links = isset($params['use_seo_links']) && $params['use_seo_links'];
	
	$c = new EcwidCatalog($store_id, EcwidController::buildEcwidUrl(), $params['with_microdata']);

    if (is_array($list_of_views))    
    	foreach ($list_of_views as $k=>$v) {
    		if (!in_array($v, array('list','grid','table'))) unset($list_of_views[$k]);
	}
	
	if ((!is_array($list_of_views)) || empty($list_of_views)) {
		$list_of_views = array('list','grid','table');
	}

	$ecwid_pb_categoriesperrow = $params['ecwid_pb_categoriesperrow'];
	if (empty($ecwid_pb_categoriesperrow)) {
		$ecwid_pb_categoriesperrow = 3;
	}
	$ecwid_pb_productspercolumn_grid = $params['ecwid_pb_productspercolumn_grid'];
	if (empty($ecwid_pb_productspercolumn_grid)) {
		$ecwid_pb_productspercolumn_grid = 3;
	}
	$ecwid_pb_productsperrow_grid = $params['ecwid_pb_productsperrow_grid'];
	if (empty($ecwid_pb_productsperrow_grid)) {
		$ecwid_pb_productsperrow_grid = 3;
	}
	$ecwid_pb_productsperpage_list = $params['ecwid_pb_productsperpage_list'];
	if (empty($ecwid_pb_productsperpage_list)) {
		$ecwid_pb_productsperpage_list = 10;
	}
	$ecwid_pb_productsperpage_table = $params['ecwid_pb_productsperpage_table'];
	if (empty($ecwid_pb_productsperpage_table)) {
		$ecwid_pb_productsperpage_table = 20;
	}
	$ecwid_pb_defaultview = $params['ecwid_pb_defaultview'];
	if (empty($ecwid_pb_defaultview) || !in_array($ecwid_pb_defaultview, $list_of_views)) {
		$ecwid_pb_defaultview = 'grid';
	}
	$ecwid_pb_searchview = $params['ecwid_pb_searchview'];
	if (empty($ecwid_pb_searchview) || !in_array($ecwid_pb_searchview, $list_of_views)) {
		$ecwid_pb_searchview = 'list';
	}

	$ecwid_com = "app.ecwid.com";

	$ecwid_default_category_id = intval($params['ecwid_default_category_id']);

 	$ecwid_mobile_catalog_link = $params['ecwid_mobile_catalog_link'];
	if (empty($ecwid_mobile_catalog_link)) {
		$ecwid_mobile_catalog_link = "//$ecwid_com/jsp/$store_id/catalog";
	}

    $ajaxIndexingContent = '';

    $cache = JFactory::getCache();
    $cache->setCaching(1);
    $cache->setLifeTime(360);
    $api_enabled = $cache->call('ecwid_is_api_enabled', $store_id);

    $integration_code = '';

	$document = JFactory::getDocument();

	if (method_exists($document, 'addCustomTag')) {
		$script = EcwidCommon::getScriptURL();
		$document->addCustomTag('<link rel="preload" href="' . $script . '" as="script">');
	}

	if ($api_enabled) {

		if (property_exists($document, '_links')) {
			foreach ($document->_links as $key => $link) {
				if ($link['relation'] == 'canonical') {
					unset($document->_links[$key]);
				}
			}
		}

        $catalog_params = false;

		$api = new EcwidProductApi($store_id);

		$do_escaped_fragment = isset($_GET['_escaped_fragment_']);

        if ($do_escaped_fragment) {

			$profile = $api->get_profile();

			if ($profile['closed']) {
				JResponse::setHeader('Status', '503 Service Temporarily Unavailable', true);
				return;
			}

			$catalog_params = _get_ecwid_catalog_params_from_escaped_fragment();
		} else if ($use_seo_links) {
			$catalog_params = _get_ecwid_catalog_params_from_seo_urls();
		}

		$found = false;
		$title = '';
		$description = '';

		if ( isset( $catalog_params['type'] ) && isset( $catalog_params['id'] ) && $catalog_params['id'] ) {
			$type = $catalog_params['type'];
			$id = $catalog_params['id'];

			$hash = '';
			if ($type == 'product') {
				$ajaxIndexingContent = $c->get_product($id);
				
				if ($do_escaped_fragment || $use_seo_links) {
					$product = $api->get_product($id);

					if ($product) {
						$found = true;

						$title = $product['name'];
						$description = $product['description'];

						$hash = substr($product['url'], strpos($product['url'], '#'));
					}
				}
			} elseif ($type == 'category') {

				$ajaxIndexingContent = $c->get_category($id);

				if ($do_escaped_fragment  || $use_seo_links) {
					$cat = $api->get_category($id);

					if ($cat) {
						$found = true;

						$ecwid_default_category_id = $id;

						$title = $cat['name'];
						$description = $cat['description'];

						$hash = substr($cat['url'], strpos($cat['url'], '#'));
					}
				}
			}

			if ($hash && $do_escaped_fragment) {
				$integration_code = '<script type="text/javascript"> if (!document.location.hash) document.location.hash = "' . $hash . '";</script>';

				if (method_exists($document, 'addHeadLink')) {
					$document->addHeadLink(EcwidController::buildEcwidUrl($hash), 'canonical', 'rel', '');
				}
			}
		} else {
			$found = true; // We are in the store root
			$ajaxIndexingContent = $c->get_category($ecwid_default_category_id);

			if ($do_escaped_fragment || $use_seo_links) {
				$category = $api->get_category($ecwid_default_category_id);
				$title = $category['name'];
				$description = $category['description'];
			}
		}

		if ($do_escaped_fragment || $use_seo_links) {
			if ($title) {
				$document->setTitle($title . ' | ' . $document->getTitle());
			}

			if ($description) {
				$description = strip_tags($description);
				$description = html_entity_decode($description, ENT_NOQUOTES, 'UTF-8');

				$description = preg_replace('![\p{Z}\n\s]{1,}!u', ' ', $description);
				$description = trim($description, " \t\xA0\n\r"); // Space, tab, non-breaking space, newline, carriage return
				$description = mb_substr($description, 0, 160);
				$description = htmlspecialchars($description, ENT_COMPAT, 'UTF-8');

				$document->setDescription($description);
			}
		}

		if (!$found) {
			JResponse::setHeader('Status', '404 Not Found', true);
		}
	}
	if ($api_enabled && !$use_seo_links && method_exists($document, 'addCustomTag')) {
		$document->addCustomTag('<meta name="fragment" content="!" />');
	}

	if (empty($ecwid_default_category_id)) {
		$ecwid_default_category_str = '';
	} else {
		$ecwid_default_category_str = ',"defaultCategoryId='. $ecwid_default_category_id .'"';
	}

    $ecwid_element_id = "ecwid-inline-catalog";
    if (!empty($params['ecwid_element_id'])) {
        $ecwid_element_id = $params['ecwid_element_id'];
    }

	$additional_widgets = '';
	if ($params['display_search']) {
		$additional_widgets .= '<div class="ecwid-product-browser-search"><script type="text/javascript"> xSearch(); </script></div>';
	}
	if ($params['display_categories']) {
		$additional_widgets .= '<script type="text/javascript"> xCategoriesV2(); </script>';
	}

	$scripts = <<<HTML
<script type="text/javascript">
window.ec.config.enable_canonical_urls = true;
</script>
HTML;


	$app = JFactory::getApplication();

	$menu = $app->getMenu()->getActive()->link;
	$current_menu_item_url = JRoute::_($menu);
	if ($use_seo_links) {

		$scripts .= <<<HTML
<script type="text/javascript">

window.ec.config.storefrontUrls = {
    cleanUrls: true
};
window.ec.config.baseUrl = '$current_menu_item_url';
</script>
HTML;

	}



       $integration_code .= <<<EOT
<!-- Ecwid Shopping Cart extension v3.3 -->
<script type="text/javascript">
window.ec = window.ec || Object();
window.ec.config = window.ec.config || Object();
</script>
$scripts
$additional_widgets
<div id="$ecwid_element_id">$ajaxIndexingContent
</div>
<div>
<script type="text/javascript">
xProductBrowser("categoriesPerRow=$ecwid_pb_categoriesperrow","views=grid($ecwid_pb_productsperrow_grid,$ecwid_pb_productspercolumn_grid) list($ecwid_pb_productsperpage_list) table($ecwid_pb_productsperpage_table)","categoryView=$ecwid_pb_defaultview","searchView=$ecwid_pb_searchview","style="$ecwid_default_category_str,"id=$ecwid_element_id");</script>
</div>
<!-- END Ecwid Shopping Cart extension v3.3 -->
EOT;

	return $integration_code;
}

function ecwid_is_api_enabled($ecwid_store_id) {
	$ecwid_store_id = intval($ecwid_store_id);
	$api = new EcwidProductApi($ecwid_store_id);
  return $api->is_api_enabled();
}

?>
