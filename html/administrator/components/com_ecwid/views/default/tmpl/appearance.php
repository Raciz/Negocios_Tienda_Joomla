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

<?php if ($this->submenu): ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->submenu; ?>
    </div>
<?php endif; ?>


<div id="j-main-container" class="wrap span8">

<h2><?php echo JText::_('COM_ECWID_APPEARANCE_SETTINGS'); ?></h2>
<form class="pure-form pure-form-aligned ecwid-settings appearance-settings"
	  id="adminForm"
	  method="POST"
	  action="<?php echo JRoute::_('index.php?option=com_ecwid&layout=appearance'); ?>"
	>
	<input type="hidden" name="task" value="default.saveAppearance" />
<?php echo JHtml::_('form.token'); ?>

<fieldset>

<div class="pure-control-group small-input">
	<div class="input">
		<div>
			<?php $this->renderElement('displaySearch'); ?>
			<?php $this->maybeEnableCheckboxIfDefault('displaySearch'); ?>
        </div>
	</div>
	<div class="label">
		<?php $this->renderLabel('displaySearch'); ?>
	</div>
	<div class="note">
		<?php echo JText::sprintf(
			'COM_ECWID_APPEARANCE_DISPLAY_SEARCH_MODULE_NOTE',
			JRoute::_('index.php?option=com_modules&filter_search=ecwid%20search')
		);
		?>
	</div>
</div>

<div class="pure-control-group small-input">
	<div class="input">
		<div>
			<?php $this->renderElement('displayCategories'); ?>
			<?php $this->maybeEnableCheckboxIfDefault('displayCategories'); ?>
        </div>
	</div>
	<div class="label">
		<?php $this->renderLabel('displayCategories'); ?>
	</div>
	<div class="note">
		<?php echo JText::sprintf(
			'COM_ECWID_APPEARANCE_DISPLAY_CATEGORIES_MODULE_NOTE',
			JRoute::_('index.php?option=com_modules&filter_search=ecwid%20categories')
		);
		?>
	</div>
</div>

<div class="pure-control-group small-input">
	<div class="input">
		<div>
			<?php $this->renderElement('categoriesPerRow'); ?>
		</div>
	</div>
	<div class="label">
		<?php $this->renderLabel('categoriesPerRow'); ?>
	</div>
	<div class="note">
	</div>
</div>

<hr />


<div class="pure-control-group">
	<label class="products-per-page-label"><?php echo JText::_('COM_ECWID_APPEARANCE_PRODUCTS_PER_PAGE'); ?></label>
	<div class="ecwid-pb-view-size grid active" tabindex="1">
		<div class="title"><?php echo JText::_('COM_ECWID_APPEARANCE_GRID_VIEW'); ?></div>
		<div class="main-area">
			<?php $this->embedSvg('grid'); ?>
		</div>
		<div class="right">
			<div class="ruler"></div>
			<?php $this->renderElement('gridColumns'); ?>
		</div>
		<div class="bottom">
			<div class="ruler"></div>
			<?php $this->renderElement('gridRows'); ?>
		</div>
	</div>

	<div class="ecwid-pb-view-size list" tabindex="1">
		<div class="title"><?php echo JText::_('COM_ECWID_APPEARANCE_LIST_VIEW'); ?></div>
		<div class="main-area">
			<?php $this->embedSvg('list'); ?>
		</div>
		<div class="right">
			<div class="ruler"></div>
			<?php $this->renderElement('list'); ?>
		</div>
	</div>


	<div class="ecwid-pb-view-size table" tabindex="1">
		<div class="title"><?php echo JText::_('COM_ECWID_APPEARANCE_TABLE_VIEW'); ?></div>
		<div class="main-area">
			<?php $this->embedSvg('table'); ?>
		</div>
		<div class="right">
			<div class="ruler"></div>
			<?php $this->renderElement('table'); ?>
		</div>
	</div>
	<p class="note pb-note"><?php echo JText::_('COM_ECWID_APPEARANCE_PRODUCTS_PER_PAGE_NOTE'); ?></p>
</div>

<hr />

<div class="pure-control-group">
	<div class="label">
		<?php $this->renderLabel('categoryView'); ?>
	</div>
	<?php $this->renderElement('categoryView'); ?>
</div>

<div class="pure-control-group">
	<div class="label">
		<?php $this->renderLabel('searchView'); ?>
	</div>
	<?php $this->renderElement('searchView'); ?>
</div>

<?php if ($this->showChameleon()): ?>
<hr />

<div class="pure-control-group small-input">
	<div class="input">
		<div>
			<?php $this->renderElement('enableChameleon'); ?>
		</div>
	</div>
	<div class="label">
		<?php $this->renderLabel('enableChameleon'); ?>
	</div>
	<div class="note wide-note">
		<?php echo JText::_('COM_ECWID_APPEARANCE_ENABLE_CHAMELEON_NOTE');
		?>
	</div>
</div>
<?php endif; ?>

<hr />
<p class="help"><?php echo JText::_('COM_ECWID_FIND_MORE_DESIGN_TOOLS'); ?></p>


</fieldset>

<input type="submit" style="visibility: hidden" />

</form>

</div>
