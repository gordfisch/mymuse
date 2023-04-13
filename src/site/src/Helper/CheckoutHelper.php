<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */

namespace Joomla\Component\Mymuse\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Router\Route;
use Joomla\Component\Mymuse\Administrator\Table\OrderTable;
use Joomla\Component\Mymuse\Administrator\Table\OrderitemTable;
use Joomla\Component\Mymuse\Administrator\Table\OrderpaymentTable;
use Joomla\Component\Mymuse\Administrator\Table\OrdershippingTable;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Site\Service\Mymuse;
use Joomla\Registry\Registry;


defined('_JEXEC') or die('Restricted access');


class CheckoutHelper
{
	

	/**
	 * error string
	 *
	 * @var string
	 */
	var $error = '';
	
	/**
	 * db object
	 *
	 * @var object
	 */
	var $_db = null;

	/**
	 * MyMuseCart  object ref
	 *
	 * @var object
	 */
	var $MyMuseCart = null;

	/**
	 * cart
	 *
	 * @var object
	 */
	var $cart = null;

	/**
	 * MyMuseStore object ref
	 *
	 * @var object
	 */
	var $MyMuseStore = null;

	/**
	 * store object ref
	 *
	 * @var object
	 */
	var $store = null;

	/**
	 * currency object ref
	 *
	 * @var object
	 */
	var $currency = null;

	/**
	 * MyMuseShopper object ref
	 *
	 * @var object
	 */
	var $MyMuseShopper = null;

	/**
	 * user
	 *
	 * @var object
	 */
	var $user = null;


	function __construct()
	{
		$this->_db 	= Factory::getDBO();
		$this->MyMuseShopper	=& Mymuse::getObject('Shopper','model');
		$this->MyMuseStore  	=& Mymuse::getObject('Store','model');
		$this->MyMuseCart  		=& Mymuse::getObject('Cart','helper');

		$this->shopper 			= $this->MyMuseShopper->getShopper();
		$this->store 			= $this->MyMuseStore->getStore();
		$this->cart 			= $this->MyMuseCart->cart;
	}

	/**
	 * save
	 * Assemble all order information and add to database
	 *
	 * @return bool
	 */

	public function save( ) {

		$app = Factory::getApplication();
		$jinput = $app->input;
		$params = MyMuseHelper::getParams();

		
		$this->cart_order 	= $this->MyMuseCart->buildOrder(0,1);
		$d 				= $jinput->post->getArray();
		$session 		= Factory::getSession();

		// TODO stop repeat orders on reload
		if($params->get('my_debug')){

			$debug = "CHECKOUT SAVE FUNCTION\n";
			$debug .= "Have a cart ". print_r($this->cart,true) ;
			MyMuseHelper::logMessage( $debug );
		}
		$coupon_id 			= 0;
		$coupon_discount 	= 0;
		$ship_method_id 	= 0;
		$order_shipping     = 0;
		$order_total_tax    = 0;
		$order_status 		= "P";
		if($params->get('my_shop_test')){
			$order_status = "C";
		}
		$downloadable 		= 0;


		// LOOP OVER CART ITEMS TO PROCESS
		$idx = $this->cart["idx"];
		for($i = 0; $i < $idx; $i++) {
			if(@$this->cart[$i]["coupon_id"]){
				$coupon_id = $this->cart[$i]["coupon_id"];
				$d["coupon_id"] = $this->cart[$i]["coupon_id"];
				continue;
			}
			if(!$this->cart[$i]["product_id"]){
				continue;
			}


			if(isset($this->cart[$i]["variation"]) && $this->cart[$i]["variation"] > 0){
				$this->cart[$i]['product'] = $this->MyMuseCart->getProduct($this->cart[$i]["product_id"], $this->cart[$i]["variation"]);
			}else{
				$this->cart[$i]['product'] = $this->MyMuseCart->getProduct($this->cart[$i]["product_id"]);
			}


			$ext = '';

			if(is_object($this->cart[$i]['product']->digital) && $this->cart[$i]['product']->digital->file_name){
				$this->cart[$i]['product']->file_name = $this->cart[$i]['product']->digital->file_name;
				$this->cart[$i]['product']->ext = $this->cart[$i]['product']->digital->file_ext;
				$this->cart[$i]['product']->file_length = $this->cart[$i]['product']->digital->file_length;
				$this->cart[$i]['product']->format = $this->cart[$i]['product']->digital->file_format? strtolower($this->cart[$i]['product']->digital->file_format) : $this->cart[$i]['product']->digital->file_ext;
				$this->cart[$i]['product']->variant = $this->cart[$i]['product']->digital->file_id;
	
			}else{
				
				
				//$this->cart[$i]['product']->ext = pathinfo($this->cart[$i]['product']->file_name, PATHINFO_EXTENSION);
			}

			//format price of a digital download
			if("1" == $params->get('my_price_by_product') && "0" == $this->cart[$i]['product']->product_physical ){
	          $ff = isset($this->cart[$i]['product']->format)? $this->cart[$i]['product']->format: $this->cart[$i]['product']->ext;
	          $this->cart[$i]['product']->price = isset($this->cart[$i]['product']->price[$ff])? $this->cart[$i]['product']->price[$ff] : $this->cart[$i]['product']->price;
	        }



			// SEE IF IT IS AN ALL_FILES and NOT ZIP, ADD ALL FILES TO CART
			if($this->cart[$i]['product']->product_allfiles && !$params->get('my_use_zip')){

				$format = $this->cart[$i]['product']->digital->file_format;
				$query = "SELECT id,track_parentid from `#__mymuse_product` WHERE parentid='".$this->cart[$i]['product']->parentid."'
				AND product_downloadable='1' AND product_allfiles !='1' 
				AND track_parentid > 0
				AND digital LIKE '%file_format\":\"".$format  ."%'
				ORDER BY ordering ";

				$this->_db->setQuery($query);
				$rows = $this->_db->loadObjectList();

				foreach($rows as $row){
					if($this->cart[$i]["product_id"] == $row->id){
						continue;
					}
					$this->cart[$this->cart["idx"]]['product_id'] = $row->track_parentid;
					$this->cart[$this->cart["idx"]]['variation'] = $row->id;
					$this->cart[$this->cart["idx"]]['quantity'] = 1;
					$this->cart[$this->cart["idx"]]['product']= $this->MyMuseCart->getProduct($row->track_parentid, $row->id);

					$digital = $this->cart[$this->cart["idx"]]['product']->digital;
		

					$this->cart[$this->cart["idx"]]['product']->file_name = $digital->file_name;
					$this->cart[$this->cart["idx"]]['product']->ext = $digital->file_ext;
					$this->cart[$this->cart["idx"]]['product']->file_length = $digital->file_length;
	
					
					$this->cart[$this->cart["idx"]]['product']->price = array();
					$this->cart[$this->cart["idx"]]['product']->price['product_price'] = 0;
					$this->cart[$this->cart["idx"]]['product']->format = $format;
					$this->cart["idx"]++;
						
				}
				$this->cart[$i]['product']->file_name = '';

			}elseif($this->cart[$i]['product']->product_allfiles && $params->get('my_use_zip')){
				
			
			
			}

		}


		/**
		 // update stock moved to notify function
		 */
		// TODO: SHOULD EMAIL STORE IF STOCK LESS THAN ZERO
		// TODO: add option for shipping different from billing
		
		if(!@$d["ship_info_id"]){
			$d["ship_info_id"] = $this->shopper->id;
		}


		$order 		= new OrderTable( $this->_db) ;
		$config 	= Factory::getConfig();
		$tzoffset 	= $config->get('config.offset');
		$date 		= Factory::getDate('now', $tzoffset);
		
		$order->user_id 				= $this->shopper->id;
		$order->order_number 			= md5(time().mt_rand());
		$order->store_id 				= $this->store->id;
		$order->shopper_id 				= $this->shopper->id;
		$order->order_subtotal 			= $this->cart_order->order_subtotal;
		$order->ship_info_id 			= $d["ship_info_id"];
		$order->order_shipping 			= @$this->cart_order->order_shipping->cost;
		$order->order_currency 			= $this->store->currency;
		$order->order_status 			= $order_status;
		$order->coupon_id 				= 0;
		$order->coupon_name 			= '';
		$order->coupon_discount 		= 0;
		$order->created 				= $date->toSql(true);
		$order->modified 				= $date->toSql(true);
		$order->discount 				= $this->cart_order->discount;
		$order->shopper_group_discount 	= $this->cart_order->shopper_group_discount;
		$order->notes 					= @$this->cart['notes'];

		//check coupons
		if($params->get('my_use_coupons') && $coupon_id){
			MyMuseHelper::logMessage( "Order::save: coupon");
			$order->coupon_id			= $this->cart_order->coupon->id;
			$order->coupon_name 		= $this->cart_order->coupon->title;
			$order->coupon_discount		= $this->cart_order->coupon->discount;
			// update hit on this coupon
			$query = "UPDATE #__mymuse_coupon SET
			coupon_uses = coupon_uses +1
			WHERE id='".$order->coupon_id."' ";

			$this->_db->setQuery($query);
			$this->_db->execute();
			if($params->get('my_debug')){
				MyMuseHelper::logMessage( "Update coupon: " . $query);
			}
		}
		

		if($this->shopper->username == 'buyer'){
			$fields = MyMuseHelper::getNoRegFields();

			if(isset($order->notes)){
				$notes['notes'] = $order->notes;
			}

			foreach($fields as $field){
				if(isset($this->shopper->profile[$field])){
					$notes[$field] = $this->shopper->profile[$field];
				}elseif(isset($this->shopper->$field)){
					if($field == "name"){
						$notes[$field] = $this->shopper->profile['first_name'].' '.$this->shopper->profile['last_name'];
					}else{
						$notes[$field] = $this->shopper->$field;
					}
				}
			}
			$registry = new Registry;
			$registry->loadArray($notes);
			$order->notes = (string) $registry;
	
		}

		$order->reservation_fee 	= $this->cart_order->reservation_fee;
		$order->non_res_total		= $this->cart_order->non_res_total;
		$order->pay_now				= $this->cart_order->must_pay_now;
		$order->extra 				= @$this->cart['extra'];
		

		//save the order number in the session
		$session->set("order_number",$order->order_number);


		foreach($this->cart_order->tax_array as $name => $amount)  {
			$order->$name = $amount;
		}
		

		$total_tax = $this->cart_order->tax_total;


		// Store the order to the database
		if (!$order->store()) {
			$message = Text::_('COM_MYMUSE_ORDER_COULD_NOT_BE_SAVED').': '.$order->getError();
			Factory::getApplication()->enqueueMessage($message, 'error');
			return false;
		}

		$order->id = $this->_db->insertid();  //$order->getState($this->context . '.id');


		// LOOP OVER CART ITEMS TO STORE TO DB
		$order->idx = 0;
		for($i = 0; $i < $this->cart["idx"]; $i++) {

			if(@$this->cart[$i]["coupon_id"]){
				continue;
			}
			if(!@$this->cart[$i]["product_id"]){
				continue;
			}

			$parentid = $this->cart[$i]['product']->parentid;
	
			$order->items[$i] = new OrderitemTable( $this->_db );
			$order->idx++;
			$order->items[$i]->order_id = $order->id;
			$order->items[$i]->product_id = $this->cart[$i]["product_id"];
			$order->items[$i]->variation_id = isset($this->cart[$i]["variation"])? $this->cart[$i]["variation"] : '';
			$order->items[$i]->product_quantity = $this->cart[$i]["quantity"];
			$order->items[$i]->file_name = '';


			$order->items[$i]->product_sku = $this->cart[$i]['product']->product_sku;
			$order->items[$i]->product_name = $this->cart[$i]['product']->title;
			$order->items[$i]->created = $date->toSql();
			$order->items[$i]->modified = $date->toSql();

			if( 1 == $params->get('my_downloads_enable') &&  isset($this->cart[$i]['product']->file_name) ) {
				$order->items[$i]->file_name = stripslashes($this->cart[$i]['product']->file_name);
				if($params->get('my_download_expire') == "-"){
					$enddate = "0";
				}else{
					$enddate = time() + $params->get('my_download_expire');
				}
				$order->items[$i]->end_date = $enddate;
				$order->items[$i]->downloads = 0;
				if(isset($this->cart[$i]['product']->file_name) && $this->cart[$i]['product']->file_name != ''){
					$downloadable++;
				}
			}


			$order->items[$i]->product_item_price = $this->cart[$i]['product']->price['product_price'];

			$order->items[$i]->product_in_stock = 1;

			if(isset($this->cart[$i]['backordered']) && $this->cart[$i]['backordered']){;
				$order->items[$i]->product_in_stock = -1;
			}



			// Store the item to the database
			try {
				$res = $order->items[$i]->store();

			} catch (Exception $e) {
				$msg = $e->getMessage(); // Returns "Normally you would have other code...
				Factory::getApplication()->enqueueMessage($msg, 'error');
				return false;

			}

			// more fields for printing
			$order->items[$i]->product_sku = $this->cart[$i]['product']->product_sku;
			$order->items[$i]->title = $this->cart[$i]['product']->title;
			if(isset($this->cart[$i]['product']->file_length)){
				$order->items[$i]->file_length = MyMuseHelper::ByteSize($this->cart[$i]['product']->file_length);
			}else{
				$this->cart[$i]['product']->file_length = '';
			}
			
			$order->items[$i]->product_item_subtotal = sprintf("%.2f", $order->items[$i]->product_item_price * $order->items[$i]->product_quantity);
			 
			// Build URLs
			if(isset($this->cart[$i]['catid']) && $this->cart[$i]['catid'] != ''){
				$query = "SELECT * FROM #__categories WHERE id='".$this->cart[$i]['catid']."'";
				$this->_db->setQuery($query);
				if($cat = $this->_db->loadObject()){
					$order->items[$i]->category_name = $cat->title;
				}
				if ($parentid){
					$pid = $parentid;
				}else{
					$pid = $order->items[$i]->product_id;
				}
					
				$order->items[$i]->url = RouteHelper::getProductRoute($pid, $this->cart[$i]['catid']);
				$order->items[$i]->cat_url = RouteHelper::getCategoryRoute($this->cart[$i]['catid']);
			}
		} // end of item insertion

		
		$order->order_total = $order->order_subtotal + $order->order_shipping + $total_tax;
		if($this->cart_order->order_total == 0.00){
			$order->order_total = 0.00;
		}
		 
		// if the total is zero, change the order status to confirmed
		if($order->order_total == 0.00 || $order->order_total < 0.00){
			$order->order_status = "C";
			$query = "UPDATE #__mymuse_order set order_status='C' WHERE id='".$order->id."'";
			$this->_db->setQuery($query);
			if (!$this->_db->execute()) {

				Factory::getApplication()->enqueueMessage(500, $this->_db->stderr() );
				return false;
			}
		}

		//Shipping
		if ($params->get('my_use_shipping') && $this->cart_order->need_shipping
				&& isset($this->cart_order->order_shipping)) {
			$order_shipping = new OrdershippingTable( $this->_db );
			$order_shipping->order_id = $order->id;
			$order_shipping->ship_type = $this->cart_order->order_shipping->ship_type;
			$order_shipping->ship_carrier_code = $this->cart_order->order_shipping->ship_carrier_code;
			$order_shipping->ship_carrier_name = $this->cart_order->order_shipping->ship_carrier_name;
			$order_shipping->ship_method_code = $this->cart_order->order_shipping->ship_method_code;
			$order_shipping->ship_method_name = $this->cart_order->order_shipping->ship_method_name;
			$order_shipping->cost = $this->cart_order->order_shipping->cost;
			$order_shipping->tracking_id = $this->cart_order->order_shipping->tracking_id;
			$order_shipping->ship_handling_fee = isset($this->cart_order->order_shipping->ship_handling_fee)? $this->cart_order->order_shipping->ship_handling_fee: '';
			$order_shipping->ship_handling_type = isset($this->cart_order->order_shipping->ship_handling_type)? $this->cart_order->order_shipping->ship_handling_type : '';
			$order_shipping->ship_handling_charge = isset($this->cart_order->order_shipping->ship_handling_charge)? $this->cart_order->order_shipping->ship_handling_charge : 0.00;
			$order_shipping->shipmentmethod_id = isset($this->cart_order->order_shipping->id)? $this->cart_order->order_shipping->id: 0;
			$order_shipping->order_weight = isset($this->cart_order->order_shipping->order_weight)? $this->cart_order->order_shipping->order_weight : 0;
			$order_shipping->shipment_weight_unit = isset($this->cart_order->order_shipping->shipment_weight_unit)? $this->cart_order->order_shipping->shipment_weight_unit : '';
			$order_shipping->tax_id = isset($this->cart_order->order_shipping->tax_id)? $this->cart_order->order_shipping->tax_id : '';

			$order_shipping->created = $date->toSql();


			if (!$order_shipping->store()) {
				Factory::getApplication()->enqueueMessage(500, $order_shipping->getError() );

				return false;
			}
			$order->order_total = $order->order_total + $order_shipping->cost;
		}

		//add more to the order object for printing
		$order->idx 			= $this->cart["idx"];
		$order->do_html 		= 0;
		$order->show_checkout 	= 0;
		$order->tax_array 		= $this->cart_order->tax_array;
		$order->status_name 	= MyMuseHelper::getStatusName($order->order_status );
		$order->tax_total 		= $total_tax;
		$order->downloadable  	= $downloadable;
		$order->ship_method_id 	= @$this->cart_order->order_shipping->id;
		$order->order_shipping  = $order_shipping;

		 
		// All done with inserting ORDER!!
		// attach the current order to the shopper
		$this->MyMuseShopper->order = $order;
		
		if($params->get('my_debug')){
			$debug = "Order saved:  ".$order->order_number."\n\n";
			MyMuseHelper::logMessage( $debug  );
		}
		
		// SEND EMAIL CONFIRMATION MESSAGES IF STATUS IS CONFIRMED
		// or if payment offline is enabled
		jimport( 'joomla.plugin.helper' );
		 
		if(!$params->get('my_shop_test')){
			$this->MyMuseCart->reset();
		}

		return $order;
	}



	/**
	 * calc_order_subtotal
	 *
	 * @param array $this->cart Cart includes product objects
	 * @return float
	 */
	public function calc_order_subtotal(&$cart) {

		$subtotal = 0.00;
		for($i = 0; $i < $cart["idx"]; $i++) {
			if(@$cart[$i]["coupon_id"]){
				continue;
			}
			if(!@$cart[$i]["product_id"]){
				continue;
			}

			$subtotal += $cart[$i]['product']->price['product_price'] * $cart[$i]['quantity'];
		}
		//$subtotal = sprintf("%.2f", $subtotal);
		return($subtotal);
	}

	/**
	 * calc_order_tax
	 * Did I mention that I hate taxes?
	 *
	 * @param object $order
	 * @return array
	 */
	public function calc_order_tax($order) {
		 
		$params 		= MyMuseHelper::getParams();
		$order_subtotal = $order->order_subtotal;

		$taxes = array();
		
		// No profile?

		if(!isset($this->shopper->profile['country']) && !$params->get('my_add_taxes')){
			return $taxes;
		}
		
		if ($params->get('my_tax_shipping') && isset($order->order_shipping->cost)) {
			$order_taxable = $order->order_subtotal + $order->order_shipping->cost;
		}else{
			$order_taxable = $order->order_subtotal;
		}
	
		//get tax rates
		$q = "SELECT t.*, c.country_name, s.state_name FROM #__mymuse_tax_rate as t
		LEFT JOIN #__mymuse_country as c ON t.country = c.country_3_code
		LEFT JOIN #__mymuse_state as s ON s.id = t.province
		WHERE t.published = 1
		ORDER BY ordering";
		$this->_db->setQuery($q);
		$regex = TAX_REGEX;
		
		if($tax_rates = $this->_db->loadObjectList()){
			//we have rates
		}else{
			//we have no rates
			return $taxes;
		}
		
		// GET USER STATE,COUNTRY, BLOC
		$user_state 	= isset($this->shopper->profile['region'])? $this->shopper->profile['region'] : 'unknown';
		$user_country 	= isset($this->shopper->profile['country'])? $this->shopper->profile['country'] : "unknown";
		$user_bloc 		= '';
		
		if($user_country != 'unknown'){
			$query = "SELECT bloc FROM #__mymuse_country WHERE country_3_code='$user_country'";
			$this->_db->setQuery($query);
			$user_bloc = $this->_db->loadResult();
		}
		
		// GET STORE STATE,COUNTRY, BLOC
		$this->store_state	= $params->get('province');
		$this->store_country	= $params->get('country');
		
		$query = "SELECT * FROM #__mymuse_country WHERE country_2_code='$this->store_country'";
		$this->_db->setQuery($query);
		$this->store_country_res 		= $this->_db->loadObject();
		$this->store_country_3_code 	= $this->store_country_res->country_3_code;
		$this->store_bloc 			= $this->store_country_res->bloc;
		
		$total_physical = 0;
		$total_downloadable = 0;
		foreach($order->items as $item) {
			if($item->product_physical){
				$total_physical += $item->product_item_subtotal;
			}else{
				$total_downloadable += $item->product_item_subtotal;
			}
		}
		
		
		// for European taxes, are shopper and store both in EU? Both in same country?
		// For digital goods, you must now charge VAT based on the buyer's country,
		// break totals up into downloadable and physical
		if(($this->store_bloc == 'EU' && $user_bloc == 'EU') || $params->get('my_add_taxes')){
					
			//do euro tax
			
			
			//how to apply any discount
			if(!$total_physical){ //all downloads
				$total_downloadable = $total_downloadable - $order->discount;
			}
			if(!$total_downloadable){ //all physical
				$total_physical = $total_physical - $order->discount;
			}
			if($total_physical && $total_downloadable){ //it's a mix, take the highest one
				if($total_physical > $total_downloadable){
					$total_physical = $total_physical - $order->discount;
				}else{
					$total_downloadable = $total_downloadable - $order->discount;
				}
			}
			
			//case 1: same country, always charge VAT
			if($this->store_country_3_code == $user_country){
				//same country
				//we will use the regular calculation below
			
			}elseif(isset($this->shopper->profile['vat_number']) && $this->shopper->profile['vat_number'] != ''){
				//different country within union but has VAT Number so no tax
				$taxes['VAT Exempt'] = 0.00;
				return($taxes);
			}else{
				
				$temp_tax = 0;
				foreach($tax_rates as $rate){
					$name = preg_replace("/$regex/","_",$rate->tax_name);
					$taxes[$name] = 0;
					if($rate->country == $user_country){
						//downloadables
						$temp_tax = $total_downloadable * $rate->tax_rate;
						$taxes[$name] += $temp_tax;
						$taxes[$name] = round($taxes[$name],2);
					}elseif($rate->country == $this->store_country_3_code){
						//physical items
						$temp_tax = $total_physical * $rate->tax_rate;
						$taxes[$name] += $temp_tax;
						$taxes[$name] = round($taxes[$name],2);
					}
					if ($taxes[$name] == 0){
						unset($taxes[$name]);
					}
				}
				return $taxes;
			}
		}
		
		//regular calculation
		$temp_tax = 0;
		foreach($tax_rates as $rate){
			$name = preg_replace("/$regex/","_",$rate->tax_name);
			$taxes[$name] = 0;

			// APPLIES TO ALL
			if($rate->tax_applies_to == "C" &&
					($user_country == $rate->country || strtolower($user_country) == strtolower($rate->country_name))){
				$temp_tax = $order_subtotal * $rate->tax_rate;
				$taxes[$name] += $temp_tax;
			}

			// APPLIES TO A STATE/REGION
			
			//first is Californian
			if($rate->tax_applies_to == "S" &&
				($user_state == $rate->province || strtolower($user_state) == strtolower($rate->state_name)) &&
				$rate->state_name == "California" ){
				//only phisical items in California
				$temp_tax = $total_physical * $rate->tax_rate;
				$taxes[$name] += $temp_tax;
				
				// now all other states
			}elseif($rate->tax_applies_to == "S" &&
					($user_state == $rate->province || strtolower($user_state) == strtolower($rate->state_name)) ){
				if($rate->compounded == "1"){
					$order_subtotal += $temp_tax;
				}
				$temp_tax = $order_subtotal * $rate->tax_rate;
				$taxes[$name] += $temp_tax;
			}
		}

		reset($tax_rates);
		foreach($tax_rates as $rate){
			$name = preg_replace("/$regex/","_",$rate->tax_name);
			if ($taxes[$name] == 0){
				unset($taxes[$name]);
			}else{
				$taxes[$name] = round($taxes[$name],2);
			}
		}
		
		return($taxes);
	}

	/**
	 * addTax
	 *
	 * @param float price
	 * @return mixed float on success or false
	 */
	static function addTax($price=0)
	{
		
		if(!$price){
			return false;
		}

		$params = MyMuseHelper::getParams();
		$query = "SELECT country_3_code FROM #__mymuse_country WHERE country_2_code='".$params->get('country')."'";
		$this->_db->setQuery($query);
		$country_3 = $this->_db->loadResult();
		$new_price = $price;
		$taxes = array();
		$query = "SELECT tax_rate FROM #__mymuse_tax_rate WHERE state = '1' 
		AND country='".$country_3."'";

		$this->_db->setQuery($query);
		$regex = TAX_REGEX;

		if($tax_rate = $this->_db->loadResult()){
			$temp_tax = 0;
			
				$temp_tax = $price * $tax_rate;
				$new_price += $temp_tax;
			
		}
		$new_price = round($new_price,2);
		return $new_price;

	}



	public function getOrder($id=0){
		$mainframe 	= Factory::getApplication();
		$params 	= MyMuseHelper::getParams();

		if(!$id){
			$this->error = Text::_('COM_MYMUSE_NO_ORDER_ID');
			return false;
		}

		$downloadable = 0;

		// get the main order
		$query = "SELECT * from #__mymuse_order WHERE id='$id'";
		$this->_db->setQuery($query);
		$order = $this->_db->loadObject();
		$order->user = $this->shopper;

		/*
		if(is_array($order->order_currency)){
			$order->currency_code = $order->order_currency['currency_code'];
		}else{
			$order->currency_code = $order->order_currency;
		}
		*/

		
		$order->shopper_group_name = @$this->shopper->shopper_group_name;
	

		//get the taxes
		$order->tax_array = array();
		$order->tax_total = 0.00;
		$q = "SELECT * FROM #__mymuse_tax_rate WHERE published=1 ORDER BY ordering";
		$this->_db->setQuery($q);
		$tax_rates = $this->_db->loadObjectList();
		$regex = TAX_REGEX;

		foreach($tax_rates as $rate){
			$name = trim($rate->tax_name);
			$name = preg_replace("/$regex/","_",$name);
	
			if(isset($order->$name) && $order->$name > 0.00){
				$order->tax_array[$name] = $order->$name;
				$order->tax_total += $order->$name;
			}
		}


		//build up the items
		$query = "SELECT * from #__mymuse_order_item WHERE order_id=$id ORDER BY id";
		$this->_db->setQuery($query);
		$order->items = $this->_db->loadObjectList();
		$order->subtotal_before_discount = 0.00;

		for($i = 0; $i < count($order->items); $i++){
			if($order->items[$i]->variation_id > 0){
				$order->items[$i]->product = $this->MyMuseCart->getProduct($order->items[$i]->product_id, $order->items[$i]->variation_id);
			}else{
				$order->items[$i]->product = $this->MyMuseCart->getProduct($order->items[$i]->product_id);
			}
			if($order->items[$i]->product){

				$order->items [$i]->title = $order->items [$i]->product->title;
				$order->items [$i]->quantity = $order->items [$i]->product_quantity;
				$order->items [$i]->product_item_subtotal = $order->items [$i]->product_item_price * $order->items [$i]->product_quantity;
				$order->subtotal_before_discount += $order->items [$i]->product_item_subtotal;
				$order->items [$i]->product_physical = $order->items[$i]->product->product_physical;
				if ($order->items [$i]->product->product_allfiles) {
					$parts = explode ( '-', $order->items [$i]->file_name );
					$order->items [$i]->ext = array_pop ( $parts );
				} else {
					$order->items [$i]->ext = pathinfo ( $order->items [$i]->file_name, PATHINFO_EXTENSION );
				}
				$order->items [$i]->attribs = $order->items [$i]->product->attribs;

				if(isset($order->items[$i]->product->digital)){

					if(isset($order->items [$i]->product->digital->file_length)){
						$order->items [$i]->file_length = $order->items [$i]->product->digital->file_length;
					}
					if(isset($order->items [$i]->product->digital->file_time)){
						$order->items [$i]->file_time = $order->items [$i]->product->digital->file_time;
					}
					if(isset($order->items [$i]->product->digital->file_format)){
						$order->items [$i]->format = $order->items [$i]->product->digital->file_format;
					}
				}
				$order->items [$i]->backordered = 0;
				if($order->items [$i]->product_in_stock == -1){
					$order->items [$i]->backordered = 1;
				}

				$catid = 0;
				// Does it have a parent?
				if ($order->items [$i]->product->parentid) {
					$pid = $order->items [$i]->product->parentid;
				} else {
					$pid = $order->items [$i]->id;
				}
				
				$catid = $order->items [$i]->product->catid;
				
				$order->items [$i]->url = RouteHelper::getProductRoute ( $pid, $catid );
				$order->items [$i]->cat_url = RouteHelper::getCategoryRoute ( $catid );

				//bring some items to top level
				$order->items [$i]->category 		= $order->items [$i]->product->category;
				$order->items [$i]->artist 			= $order->items [$i]->product->artist;
				$order->items [$i]->parent_title 	= $order->items [$i]->product->parent_title;
				$order->items [$i]->category_name 	= $order->items [$i]->category->title;
				$order->items [$i]->attributes 		= isset($order->items [$i]->product->attributes)? $order->items [$i]->product->attributes : '';

				if ($params->get ( 'my_downloads_enable' ) == "1") {
					if ($order->items [$i]->file_name != '') {
						$downloadable ++;
					}
				}

			}
		}

		if($downloadable){
			$jinput = Factory::getApplication()->input;
			$Itemid = $jinput->get("Itemid",$params->get('mymuse_default_itemid'));
			
			if($params->get('my_registration') == "no_reg" || $order->user->username == "buyer"){
				$order->downloadlink = Route::_("index.php?option=com_mymuse&view=store&task=accdownloads&id=".$order->order_number);
			}else{
				$order->downloadlink = Route::_("index.php?option=com_mymuse&view=store&task=downloads&id=".$order->order_number);
			}
		}else{
			$order->downloadlink = '';
		}

		//coupon
		if($order->coupon_id){
			$order->coupon = new CMSObject;
			$order->coupon->id = $order->coupon_id;
			$order->coupon->title = $order->coupon_name;
			$order->coupon->discount = $order->coupon_discount;
		}

		//add payments
		$query = "SELECT * from #__mymuse_order_payment WHERE order_id=$id ORDER BY id";
		$this->_db->setQuery($query);
		$order->payments = $this->_db->loadObjectList();

		//add shipments
		$query = "SELECT * from #__mymuse_order_shipping WHERE order_id=$id ORDER BY id";
		$this->_db->setQuery($query);
		if($order->shipments = $this->_db->loadObjectList()){
			$order->order_shipping = $order->shipments[0];
		}else{
			$order->order_shipping = new CMSObject;
			$order->order_shipping->cost = 0;
		}
		


		//add more to the order object for printing
		$order->idx = count($order->items);
		$order->status_name = MyMuseHelper::getStatusName($order->order_status );

		if($order->order_subtotal > (@$order->coupon->discount + @$order->discount)){
			$order->subtotal_before_discount = $order->order_subtotal  + @$order->coupon->discount + @$order->discount;
		}else{
			$order->subtotal_before_discount = $order->order_subtotal;
		}

		$order->order_total = $order->order_subtotal + $order->order_shipping->cost + $order->tax_total;
		if($order->order_total < 0){
			$order->order_total = 0.00;
		}

		//$order->order_currency = MyMuseHelper::getCurrency($this->store->currency);
		
		if($params->get("my_show_sku",0) >0){
			$order->colspan=4;
		}else{
			$order->colspan=3;
		}
		$order->colspan2 = 1;
		$order->do_html = 0;
		$order->downloadable = $downloadable;
		//MymuseHelper::print_pre($order->items[0]); exit;
		return $order;

	}

}
?>