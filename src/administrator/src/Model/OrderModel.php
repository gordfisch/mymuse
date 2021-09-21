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
 * Order model.
 *
 * @since  1.6
 */
class OrderModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_MYMUSE_ORDER';

	/**
	 * The type alias for this content type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = 'com_mymuse.order';

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
		$form = $this->loadForm('com_mymuse.order', 'order', array('control' => 'jform', 'load_data' => $loadData));

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
		$data = $app->getUserState('com_mymuse.edit.order.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('order.id') == 0)
			{
				$filters     = (array) $app->getUserState('com_mymuse.orders.filter');
			}
		}

		$this->preprocessData('com_mymuse.order', $data);

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
					->from($db->quoteName('#__mymuse_order'));

				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}

	}

	/**
	 * Method to get a single order and its items, payments and shipping
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		$input = Factory::getApplication()->input;
		$params = MyMuseHelper::getParams();
		$this->setState('order.id',$input->get('id'));
		$db = FActory::getDBO();
		
		if ($item = parent::getItem($pk)) {
			
			// get user details
			$item->user = Factory::getUser($item->user_id);
			$profile_key = $params->get('my_profile_key', 'mymuse');

			//if we are using no_reg
			if($params->get('my_registration') == "no_reg" || $item->user->username == "buyer"){
				$fields = MyMuseHelper::getNoRegFields();
				$registry = new JRegistry;
				$registry->loadString($item->notes);
				$item->notes = $registry->toArray();
			
				foreach($fields as $field){
					if($registry->get($field)){
						$item->user->profile[$field] = $item->notes[$field];
						//echo $field." ".$registry->get($field)."<br />";
					}else{
						$item->user->profile[$field] = '';
					}
				}
				if(isset($item->user->profile['first_name'])){
					$item->user->name = $item->user->profile['first_name']." ".@$item->user->profile['last_name'];
				}
				if($item->user->profile['email']){
					$item->user->email = $item->user->profile['email'];
				}
				if($item->user->profile['name']){
					$item->user->name = $item->user->profile['name'];
				}
			
				$notes = '';
				foreach($item->notes as $key => $val){
					$notes .= $key.'='.$val."\n";
				}
				$item->notes = $notes;
			
			}else{
				// Load the profile data from the database.
				$query = 'SELECT profile_key, profile_value FROM #__user_profiles' .
						' WHERE user_id = '.(int) $item->user_id .
						' AND profile_key LIKE \''.$profile_key.'.%\'' .
						' ORDER BY ordering';
				$db->setQuery($query);
				$results = $db->loadRowList();
					
				// Check for a database error.
				if ($db->getErrorNum()) {
					$this->setError($db->getErrorMsg());
					return false;
				}
				// Merge the profile data.
				$item->user->profile = array();
				foreach ($results as $v) {
					$k = str_replace("$profile_key.", '', $v[0]);
					$item->user->profile[$k] = trim(json_decode($v[1], true),'"');
				}
			}

			// Lets load the order items
			$query = "SELECT * FROM #__mymuse_order_item WHERE order_id=".$item->id;
			$db->setQuery( $query );
			$item->items = $db->loadObjectList();
			
			for($i=0; $i<count($item->items); $i++){
				$item->items[$i]->parent_name = '';
				$item->items[$i]->category_name = '';
				$query = "SELECT c.title, p.catid, p.parentid 
						
						FROM #__mymuse_product as p LEFT JOIN #__categories as c ON c.id=p.catid
						WHERE p.id='".$item->items[$i]->product_id."'";
				$db->setQuery( $query );
				$res = $db->loadObject();
				$item->items[$i]->category_name = $res->title;
				$item->items[$i]->parentid = $res->parentid;
				if($item->items[$i]->parentid > 0){
					$query = "SELECT title from #__mymuse_product
							WHERE id = '".$item->items[$i]->parentid."'";
					$db->setQuery( $query );
					$item->items[$i]->parent_name = $db->loadResult();
				}
			}
		
			$item->order_total = 0.00;
			$downloadable = 0;

			$item->downloadlink= '';
			for($i = 0; $i < count($item->items); $i++){

				$item->items[$i]->subtotal = sprintf("%.2f", $item->items[$i]->product_item_price * $item->items[$i]->product_quantity);
				$item->order_total += $item->items[$i]->subtotal;

				if($item->items[$i]->file_name != '' && ($item->items[$i]->product_in_stock > -1 || $item->items[$i]->product_in_stock == '')){
					$downloadable++;
				}
			}
  			

  			if($downloadable){
  				if($params->get('my_registration') == "no_reg" || $item->user->username == "buyer"){
  					$item->downloadlink = JURI::root()."index.php?option=com_mymuse&task=accdownloads&id=".$item->order_number;
  				}else{
  					$item->downloadlink = JURI::root()."index.php?option=com_mymuse&task=downloads&id=".$item->order_number;
  				}
  				if($params->get('top_menu_item','')){
  					$item->downloadlink .= "&Itemid=".$params->get('top_menu_item');
  				}
  				//load any downloads
  				$query = "SELECT * FROM #__mymuse_downloads
  				WHERE order_id=".$item->id;
  				
  				$db = JFActory::getDBO();
  				$db->setQuery( $query );
  				$item->downloads = $db->loadObjectList();
  			}
  			
  		
			// get taxes and total
			$item->tax_array = array();
			$item->tax_total = 0;
			$item->paid_to_date =  0;
			 
			$q = "SELECT * FROM #__mymuse_tax_rate WHERE state=1 ORDER BY ordering";
			$db->setQuery($q);
			$tax_rates = $db->loadObjectList();
			foreach($tax_rates as $rate){
				$name = trim($rate->tax_name);
				//$regex = TAX_REG_EX;
				$name = preg_replace("/['-\/\s\\\]/","_",$name);
				if($item->$name > 0.00){
					$item->tax_array[$name] = $item->$name;
					$item->tax_total += $item->tax_array[$name];
				}
			}

			$item->order_total	= $item->order_subtotal
			+ $item->order_shipping + $item->tax_total;
			if($item->order_total < 0){
				$item->order_total = 0;
			}
			$item->order_total = sprintf("%.2f", $item->order_total);
			
			// get payment history
			$q = "SELECT * from #__mymuse_order_payment
        			WHERE order_id='".$item->id."'
        			ORDER BY date";
			$db->setQuery($q);
			$item->order_payments = $db->loadObjectList();
			if($item->order_payments){
				foreach($item->order_payments as $payment){
					$item->paid_to_date += $payment->amountin - $payment->amountout;
				}
			}
			 
			// get shipping history
			$q = "SELECT * from #__mymuse_order_shipping
        			WHERE order_id='".$item->id."'
        			ORDER BY created";
			$db->setQuery($q);
			$item->order_shipments = $db->loadObjectList();
        			
			
			
			
			if(isset($item->user->profile['shopper_group'])){
				$query = "SELECT * from #__mymuse_shopper_group WHERE id=".(int)$item->user->profile['shopper_group'];
				$db->setQuery($query);
				if($shopper_group = $db->loadObject()){
					$item->user->shopper_group_name = $shopper_group->shopper_group_name;
					$item->user->shopper_group_discount = $shopper_group->discount;
				}
			}else{
				$item->user->shopper_group_name = "default";
				$item->user->shopper_group_discount = 0;
			}
		}

		return $item;
	}
	
	
	public function getLists()
	{
		$params = MyMuseHelper::getParams();
		//currencies
        $query = "SELECT currency_code as value, CONCAT(symbol,': ',currency_name) as text from #__mymuse_currency ORDER BY currency_code ASC";
        $this->_db->setQuery($query);
        $options = $this->_db->loadObjectList();
        array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('MYMUSE_CURRENCY').' -', 'value', 'text'));
	    $value = $params->get('my_currency');
        $lists['currencies'] = JHTML::_('select.genericlist',  $options, 'currency', 'class="inputbox"', 'value', 'text', $value, JText::_( 'MYMUSE_CURRENCY' ));   

		//payment plugins
        JPluginHelper::importPlugin('mymuse');
		$query = "SELECT element as value, name as text
		FROM #__extensions where folder='mymuse' and enabled='1' and element LIKE '%payment%'";
		$this->_db->setQuery($query);
        $options = $this->_db->loadObjectList();
        for($i=0; $i< count($options); $i++){
        	$options[$i]->text = JText::_($options[$i]->text);
        }
        array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('MYMUSE_PLUGIN').' -', 'value', 'text'));
	    $value = '';
        $lists['plugins'] = JHTML::_('select.genericlist',  $options, 'payment_plugin', 'class="inputbox"', 'value', 'text', $value, JText::_( 'MYMUSE_PLUGIN' ));   
	    
		return $lists;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable($table)
	{
		
		jimport('joomla.filter.output');
		$input = JFactory::getApplication()->input;
		$input->post->getArray();
		$params = MyMuseHelper::getParams();
		$MyMuseHelper = new MyMuseHelper;


		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__mymuse_order');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
		$form = $input->post->getVal('jform',array());

		// payments
        if(isset($form['payment_date']) && $form['payment_date']){
        	$payment['order_id'] 			= $form['id'];
        	$payment['date'] 				= $form['payment_date'];
        	$payment['plugin'] 				= $input->get('payment_plugin');
        	$payment['institution'] 		= $input->get('payment_institution');
        	$payment['amountin'] 			= $input->get('payment_amountin');
        	$payment['currency'] 			= $input->get('currency');
        	$payment['rate'] 				= $input->get('payment_rate');
        	$payment['fees'] 				= $input->get('payment_fees');
        	$payment['transaction_id'] 		= $input->get('payment_transaction_id');
        	$payment['transaction_status'] 	= $input->get('payment_transaction_status');
        	$payment['description'] 		= $input->get('payment_description');

        	
        	if(!$MyMuseHelper->logPayment($payment)){
        		$this->setError($MyMuseHelper->getError());
        		return false;
        	}
        	
        }

    }

}
