<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Jecs
 * @author     Massimo Di Primimio <m.diprimio@outlook.com>
 * @copyright  2017 Massimo Di Primimio
 * @license    GNU General Public License versione 2 o successiva; vedi LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Jecs', JPATH_COMPONENT);
JLoader::register('JecsController', JPATH_COMPONENT . '/controller.php');


// Execute the task.
$controller = JControllerLegacy::getInstance('Jecs');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
