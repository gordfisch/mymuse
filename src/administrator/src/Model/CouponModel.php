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
class CouponModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_MYMUSE_COUPON';

	/**
	 * The type alias for this content type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = 'com_mymuse.coupon';

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
		$form = $this->loadForm('com_mymuse.coupon', 'coupon', array('control' => 'jform', 'load_data' => $loadData));

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
		$data = $app->getUserState('com_mymuse.edit.coupon.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('coupon.id') == 0)
			{
				$filters     = (array) $app->getUserState('com_mymuse.coupons.filter');
			}
		}

		$this->preprocessData('com_mymuse.coupon', $data);

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

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select('MAX(' . $db->quoteName('ordering') . ')')
					->from($db->quoteName('#__mymuse_coupon'));

				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}

	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {

			//Do any procesing on fields here if needed

		}

		return $item;
	}

	/**
     * Method to set the coupon lists
     *
     * @access    public
     * @return    array
     */
	public function getLists()
    {

		$item	= $this->getItem();
		
    	$currencies[] = HTMLHelper::_('select.option', '0', '- '.JText::_('COM_MYMUSE_SELECT_CURRENCY').' -');
    	$query = "SELECT id as value, CONCAT(symbol,' ',currency_name) as text from #__mymuse_currency ORDER BY currency_code ASC";
    	$this->_db->setQuery($query);
    	$currencies = array_merge($currencies, $this->_db->loadObjectList());
    	$lists['currency'] = HTMLHelper::_('select.genericlist',  $currencies, 'jform[currency_id]', 'class="inputbox" size="1" ', 'value', 'text', $item->currency_id);
    		

    	$products[] = HTMLHelper::_('select.option', '0', '- '.JText::_('COM_MYMUSE_SELECT_PRODUCT').' -');
    	$query = "SELECT id as value, CONCAT(title,': ',product_sku) as text from #__mymuse_product
			WHERE parentid='0' ORDER BY title ASC";
    	$this->_db->setQuery($query);
    	$parents = $this->_db->loadObjectList();
    	foreach($parents as $parent){
    		$products[] = $parent;
    		//$products[] = HTMLHelper::_('select.option',  $parent, $parent );
    		$query = "SELECT id as value, CONCAT('&nbsp;-&nbsp;',title,': ',product_sku) as text from #__mymuse_product
				WHERE parentid='".$parent->value."' ORDER BY title ASC";
    		$this->_db->setQuery($query);
    		$children = $this->_db->loadObjectList();
    		foreach($children as $child){
    			$products[] = $child;
    			//$products[] = HTMLHelper::_('select.option',  $child, $child );
    		}
    	}

    	$lists['products'] = HTMLHelper::_('select.genericlist',  $products, 'jform[product_id]', 'class="inputbox" size="1" ', 'value', 'text', $item->product_id);


		return $lists;
    }



}
