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
 * coupon model.
 *
 * @since  1.6
 */
class ProductattributeskuModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_MYMUSE_PRODUCTATTRIBUTE_SKU';

	/**
	 * The type alias for this content type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = 'com_mymuse.productattributesku';

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
		$form = $this->loadForm('com_mymuse.productattributesku', 'productattributesku', array('control' => 'jform', 'load_data' => $loadData));

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
		$data = $app->getUserState('com_mymuse.edit.productattributesku.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('productattributesku.id') == 0)
			{
				$filters     = (array) $app->getUserState('com_mymuse.productattributesku.filter');
			}
		}

		$this->preprocessData('com_mymuse.productattributesku', $data);

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

	public function getParent()
	{
		
		$app 		= Factory::getApplication();
		$input 		= $app->input;
		$parentid 	= $input->get('parentid', 0);

		if(!$parentid){
			$app = Factory::getApplication();
			$parentid = $app->getUserStateFromRequest("com_mymuse.parentid", 'parentid');
		}
		if(!$parentid){
			$this->app->enqueueMessage(Text::_('COM_MYMUSE_COULD_NOT_FIND_PARENT'), 'error');
			$this->setRedirect( 'index.php?option=com_mymuse' );
		}

		$db = Factory::getDBO();
		$query = "SELECT * from #__mymuse_product WHERE id=$parentid";
		$db->setQuery($query);
		$parent = $db->loadObject();
	
		return $parent;
	}
	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__mymuse_product_attribute_sku');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
	}





}
