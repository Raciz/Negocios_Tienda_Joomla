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

jimport('joomla.application.component.modelform');

/**
 * @package        Joomla
 * @subpackage     Config
 */
class EcwidModelDefault extends JModelForm
{

	/**
	 * Get the params for the configuration variables
	 */
	function getParams($component = "com_ecwid")
	{
		static $instance;

		$params = new JRegistry();
		$table  = JTable::getInstance('extension');
		$result = $table->find(array('element' => $component));
		$table->load($result);
		$params->loadString($table->params);
		return $params;
	}


	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_ecwid.default.default.data', array());

		if (empty($data)) {
			$data = $this->getParams();
		}

		return $data;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param    array      $data        Data for the form.
	 * @param    boolean    $loadData    True if the form is to load its own data (default case), false if not.
	 *
	 * @return    mixed    A JForm object on success, false on failure
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_ecwid.default', 'default', array('control'  => 'jform',
		                                                               'load_data' => $loadData
		                                                          ));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param    array      $data        Data for the form.
	 * @param    boolean    $loadData    True if the form is to load its own data (default case), false if not.
	 *
	 * @return    mixed    A JForm object on success, false on failure
	 * @since    1.6
	 */
	public function getAppearanceForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_ecwid.appearance', 'appearance', array('control'  => 'jform',
																	  'load_data' => $loadData
		));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param    array      $data        Data for the form.
	 * @param    boolean    $loadData    True if the form is to load its own data (default case), false if not.
	 *
	 * @return    mixed    A JForm object on success, false on failure
	 * @since    1.6
	 */
	public function getAdvancedForm($data = array(), $loadData = true)
	{
		// Get the form.
		static $form = null;

		if (!is_null($form)) {
			return $form;
		}

		$form = $this->loadForm('com_ecwid.advanced', 'advanced', array('control'  => 'jform',
																			'load_data' => $loadData
		));
		if (empty($form)) {
			return false;
		}

		$api = new EcwidProductApi($this->getParams()->get('storeID'));

		if (EcwidCommon::isPaidAccount($this->getParams()->get('storeID'))) {
			$xml = '<field name="defaultCategory" type="list" label="COM_ECWID_ADVANCED_DEFAULT_CATEGORY_ID_LABEL" required="false" labelclass="control-label">';
			$xml .= '<option value="">COM_ECWID_ADVANCED_DEFAULT_CATEGORY_ROOT</option>';

			$cats = $api->get_all_categories();
			foreach($cats as $cat) {
				$xml .= '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['name'], ENT_NOQUOTES, 'UTF-8') . '</option>';
			}

			$xml .= '</field>';
			$list = new \SimpleXMLElement($xml);

			$form->setField($list);
		} else {
			$xml = '<field name="source" type="text" label="Source" required="false" labelclass="control-label" />';
			$text = new \SimpleXMLElement($xml);
			$form->setField($text);
		}

        return $form;
	}
	/**
	 * Method to save the form data.
	 *
	 * @param    array    The form data.
	 *
	 * @return    boolean    True on success.
	 */
	public function save($data)
	{
		// Initialise variables;
		$params = $this->getParams();

		$to_trim = array('ssoKey', 'categoriesPerRow', 'gridColumns', 'gridRows', 'list', 'table');

		foreach($data as $name => $value) {
			if (in_array($name, $to_trim)) {
				$data[$name] = trim($value);
			}
		}

        $params->loadArray($data);

		$params->set("storeID", trim($params->get("storeID")));

		$table  = JTable::getInstance('extension');
		$result = $table->find(array('element' => 'com_ecwid'));
		$table->load($result);

		$table->params = $params->__toString();

		$table->store();

		// Clean the cache.
		$cache = JFactory::getCache('_system');
		$cache->clean();

		return true;
	}

}
?>
