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

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');
include_once(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/legacy_class.php');

/**
 * HTML View class for the ecwid component
 */
class EcwidViewEcwid extends EcwidLegacyJView {
	function display($tpl = null) {

        $this->_prepareDocument();

        parent::display($tpl);
    }

    function _prepareDocument() 
    {
        $app        = JFactory::getApplication();
        $params     = $app->getParams();

        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_ecwid/assets/frontend.css');

        if ($params->get('menu-meta_description'))
        {
            $this->document->setDescription($params->get('menu-meta_description'));
        }   
        
        if ($params->get('menu-meta_keywords'))
        {
            $this->document->setMetadata('keywords', $params->get('menu-meta_keywords'));
        }   
        
        if ($params->get('robots'))
        {
            $this->document->setMetadata('robots', $params->get('robots'));
        }   
    }
}
?>
