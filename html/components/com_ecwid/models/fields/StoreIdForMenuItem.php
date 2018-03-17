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

jimport('joomla.form.formfield');

// The class name must always be the same as the filename (in camel case)
class JFormFieldStoreIdForMenuItem extends JFormFieldText {

    //The field class must know its own type through the variable $type.
    protected $type = 'StoreIdForMenuItem';

        public function getInput() {

            $ver = new JVersion();
            if (!$ver->isCompatible('3')) {
                $doc = JFactory::getDocument();

                $doc->addScript('https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js');
            }

            if (empty($this->value)) {
                $params = JComponentHelper::getParams('com_ecwid');
                $this->value = $params->get('storeID');
            }

            return parent::getInput();
        }
}