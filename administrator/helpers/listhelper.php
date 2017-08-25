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

/**
 * Jecs Listhelper.
 *
 * @since  1.6
 */
abstract class JHtmlListhelper
{
	static function toggle($value = 0, $view, $field, $i)
	{
		$states = array(
			0 => array('icon-remove', JText::_('Toggle'), 'inactive btn-danger'),
			1 => array('icon-checkmark', JText::_('Toggle'), 'active btn-success')
		);

		$state  = \Joomla\Utilities\ArrayHelper::getValue($states, (int) $value, $states[0]);
		$text   = '<span aria-hidden="true" class="' . $state[0] . '"></span>';
		$html   = '<a href="#" class="btn btn-micro ' . $state[2] . '"';
		$html  .= 'onclick="return toggleField(\'cb'.$i.'\',\'' . $view . '.toggle\',\'' . $field . '\')" title="' . JText::_($state[1]) . '">' . $text . '</a>';

		return $html;
	}
}
