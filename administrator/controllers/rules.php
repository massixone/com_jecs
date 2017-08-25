<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Jecs
 * @author     Massimo Di Primimio <m.diprimio@outlook.com>
 * @copyright  2017 Massimo Di Primimio
 * @license    GNU General Public License versione 2 o successiva; vedi LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

use Joomla\Utilities\ArrayHelper;

/**
 * Rules list controller class.
 *
 * @since  1.6
 */
class JecsControllerRules extends JControllerAdmin
{
	/**
	 * Method to clone existing Rules
	 *
	 * @return void
	 */
	public function duplicate()
	{
		// Check for request forgeries
		Jsession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new Exception(JText::_('COM_JECS_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Jtext::_('COM_JECS_ITEMS_SUCCESS_DUPLICATED'));
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_jecs&view=rules');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since    1.6
	 */
	public function getModel($name = 'rule', $prefix = 'JecsModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks   = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}

	/**
	 * Method to toggle fields on a list
	 *
	 * @throws Exception
	 */
	public function toggle()
	{
		// Initialise variables
		$app    = JFactory::getApplication();
		$ids    = $app->input->get('cid', array(), '', 'array');
		$field  = $app->input->get('field');

		if (empty($ids))
		{
			$app->enqueueMessage('warning', JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else
		{
			// Get the model
			$model = $this->getModel('rule');

			foreach ($ids as $pk)
			{
				// Toggle the items
				if (!$model->toggle($pk, $field))
				{
					throw new Exception(500, $model->getError());
				}
			}
		}

	$this->setRedirect(JRoute::_('index.php?option=' . $app->input->get('option') . '&view=' . $app->input->get('view'), false));
	}
}
