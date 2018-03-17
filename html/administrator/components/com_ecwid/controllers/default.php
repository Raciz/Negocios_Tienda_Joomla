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

jimport('joomla.application.component.controllerform');

class EcwidControllerDefault extends JControllerForm
{
	public function __construct($config = array())
	{
		parent::__construct($config);
		// Apply, Save & New, and Save As copy should be standard on forms.
		$this->registerTask('apply', 'save');
	}

	public function resetStoreID()
	{
		$data = $this->getModel()->getParams();
		
		$this->getModel()->save(array('storeID' => false));

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option, false));
	}
	
	public function setWithMicrodata()
	{
		$data = $this->getModel()->getParams();
		
		$this->getModel()->save(array('withMicrodata' => @$_GET['value']));

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option, false));
	}

	public function saveAppearance()
	{
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');
		$data = array_merge(
			array(
				'displayCategories' => 0,
				'displaySearch' => 0,
				'enableChameleon' => 0
			),
			$data
		);

		$result = $this->saveForm($this->getModel()->getAppearanceForm(), $data);
		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option. '&layout=appearance', false));

		return $result;
	}

	public function saveGeneral()
	{
		$result = $this->saveForm($this->getModel()->getForm());
		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&saved', false));

		return $result;
	}

	public function saveAdvanced()
	{
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');
		
		$checkboxes = array(
			'useSeoLinks' => 0,
			'ssoEnabled' => 0
		);
		
		$data = array_merge(
			$checkboxes,
			$data
		);
		$result = $this->saveForm($this->getModel()->getAdvancedForm(), $data);
		// Redirect to the list screen.
		
		if (!$data['ssoEnabled']) {
			Ecwid::setParam('ssoKey', '');
		}
		
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&layout=advanced', false));

		return $result;
	}

	public function saveForm($form, $data = null)
	{
		// Check for request forgeries.
		if (class_exists('JSession') && method_exists('JSession', 'checkToken')) {
            JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        } elseif (class_exists('JRequest') && method_exists('JRequest', 'checkToken')) { // for joomla 1.7
            JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        }

		// Initialise variables.
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();
		$model = $this->getModel();

		if (is_null($data)) {
			$data = JFactory::getApplication()->input->get('jform', array(), 'array');
		}
		$context = "$this->option.edit.$this->context";

		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.

		if (!$form) {
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Test if the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false) {
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option, false));

			return false;
		}

        // Attempt to save the data.
		if (!$model->save($validData)) {
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option, false));

			return false;
		}

		$this->setMessage(JText::_('COM_ECWID_SAVE_SUCCESS'));

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $validData);

		return true;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param    string    $key    The name of the primary key of the URL variable.
	 *
	 * @return    Boolean    True if access level checks pass, false otherwise.
	 * @since    1.6
	 */
	public function cancel($key = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option, false));
		return true;
	}
}