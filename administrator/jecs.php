<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Jecs
 * @author     Massimo Di Primimio <m.diprimio@outlook.com>
 * @copyright  2017 Massimo Di Primimio
 * @license    GNU General Public License versione 2 o successiva; vedi LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_jecs'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Jecs', JPATH_COMPONENT_ADMINISTRATOR);
JLoader::register('JecsHelper', JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'jecs.php');

$controller = JControllerLegacy::getInstance('Jecs');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
