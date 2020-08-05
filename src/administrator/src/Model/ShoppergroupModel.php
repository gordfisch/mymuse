<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;

/**
 * Shoppergroup model.
 *
 * @since  1.6
 */
class ShoppergroupModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_MYMUSE_SHOPPERGROUP';

	/**
	 * The type alias for this content type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = 'com_mymuse.shoppergroup';

	/**
	 * Batch copy/move command. If set to false, the batch copy/move command is not supported
	 *
	 * @var  string
	 */
	protected $batch_copymove = false;

	/**
	 * Allowed batch commands
	 *
	 * @var  array
	 */
	protected $batch_commands = array(
		'language_id' => 'batchLanguage'
	);



	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form. [optional]
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not. [optional]
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_mymuse.shoppergroup', 'shoppergroup', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_mymuse.edit.shoppergroup.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('shoppergroup.id') == 0)
			{
				$filters     = (array) $app->getUserState('com_mymuse.shoppergroups.filter');
			}
		}
		if(isset($data->usergroups_id)){
			$db = Factory::getDBO();
			$query = "SELECT title from #__usergroups WHERE id = ".$data->usergroups_id;
			//echo $query;
			$db->setQuery($query);
			$title = $db->loadResult();
			$data->title = $title;
		}
		

		$this->preprocessData('com_mymuse.shoppergroup', $data);

		return $data;
	}


	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   Table  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since   1.6
	 */
	protected function getReorderConditions($table)
	{
		return [
			$this->_db->quoteName('published') . ' >= 0',
		];
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   Table  $table  A Table object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{



	}



}
