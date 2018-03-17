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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>

<div id="j-main-container" class="wrap span8">

<form class="pure-form ecwid-settings general-settings"
		  id="adminForm"
		  method="POST"
		  action="<?php echo JRoute::_('index.php?option=com_ecwid'); ?>"
		>
		<input type="hidden" name="task" value="default.saveGeneral" />
		<?php echo JHtml::_('form.token'); ?>
		<fieldset>

			<div class="greeting-box">

				<div class="image-container">
					<img class="greeting-image" src="<?php echo $this->baseurl; ?>/components/com_ecwid/assets/images/store_inprogress.png" width="142" />
				</div>

				<div class="messages-container">
					<div class="main-message">

						<?php echo JText::_('COM_ECWID_INITIAL_THANKS_FOR_CHOOSING_ECWID'); ?>
					</div>
					<div class="secondary-message">
						<?php echo JText::_('COM_ECWID_INITIAL_LETS_GET_STARTED'); ?>
					</div>
				</div>

			</div>
			<hr />

			<ol>
				<li>
					<h4><?php echo JText::_('COM_ECWID_INITIAL_REGISTER_AT_ECWID'); ?></h4>
					<div>
						<?php echo JText::_('COM_ECWID_INITIAL_REGISTER_AT_ECWID_NOTE'); ?>
					</div>
					<div class="ecwid-account-buttons">
						<a class="pure-button pure-button-secondary" target="_blank" href="<?php echo $this->getRegisterLink(); ?>">
							<?php echo JText::_('COM_ECWID_INITIAL_CREATE_NEW_ACCOUNT'); ?>
						</a>
						<a class="pure-button pure-button-secondary" target="_blank" href="https://my.ecwid.com/cp/?source=joomla#t1=&t2=Dashboard">
							<?php echo JText::_('COM_ECWID_INITIAL_SIGN_IN'); ?>
						</a>
					</div>
					<div class="note">
						<?php echo JText::_('COM_ECWID_INITIAL_SIGN_UP_NOTE'); ?>
					</div>
				</li>
				<li>
					<h4><?php echo JText::_('COM_ECWID_INITIAL_FIND_STORE_ID'); ?></h4>
					<div>
						<?php echo JText::_('COM_ECWID_INITIAL_FIND_STORE_ID_NOTE'); ?>
					</div>
				</li>
				<li>
					<h4>
						<?php echo JText::_('COM_ECWID_INITIAL_ENTER_STORE_ID'); ?>
					</h4>
					<div><label for="ecwid_store_id"><?php echo JText::_('COM_ECWID_INITIAL_STORE_ID_LABEL'); ?>:</label></div>
					<div class="pure-control-group store-id">
						<?php $this->renderElement('storeID'); ?>
						<button type="submit" class="pure-button pure-button-primary">
							<?php echo JText::_('COM_ECWID_INITIAL_SAVE_STORE_ID'); ?>
						</button>
					</div>

				</li>
			</ol>
			<hr />
            <p class="credits"><?php echo JText::_('COM_ECWID_THANKS_FOR_CONTRIBUTION'); ?></p>
			<p class="help"><?php echo JText::_('COM_ECWID_VISIT_HELP_CENTER'); ?></p>

		</fieldset>
	</form>
</div>
