<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mymuse
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 * @copyright   Copyright (C) 2022 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Site\View\Cart;

\defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Helper\AssociationHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Site\Service\Mymuse;

class HtmlView extends BaseHtmlView
{
	/**
	 * MyMuseCheckout object ref
	 *
	 * @var object
	 */
	var $MyMuseCheckout = null;

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
	 * shopper object
	 *
	 * @var object
	 */
	var $shopper = null;

	/**
	 * Itemid 
	 *
	 * @var int
	 */
	var $Itemid = 0;

	/**
	 * task
	 *
	 * @var string
	 */
	var $task = '';

	/**
	 * original_task
	 *
	 * @var string
	 */
	var $original_task = '';

	/**
	 * user
	 *
	 * @var object
	 */
	var $user = null;

	/**
	 * _db database object
	 *
	 * @var object
	 */
	var $_db = null;


	public function __construct()       {
        parent::__construct(); 
        parent::setLayout('cart');    

        $this->MyMuseCheckout 	=& Mymuse::getObject('Checkout','helper');

        $this->MyMuseCart 		=& Mymuse::getObject('Cart','helper');
        $this->cart 			=& $this->MyMuseCart ->cart;

        $this->MyMuseStore		=& Mymuse::getObject('Store','model');
        $this->store 			= $this->MyMuseStore->getStore();
        $this->currency 		= $currency		= $this->store->currency;

        $this->MyMuseShopper  	=& Mymuse::getObject('Shopper','model');
        $this->shopper			= $this->MyMuseShopper->getShopper();
        $this->user				= Factory::getUser();

        $this->_db 				= Factory::getDBO();
    }
        

	public function display($tpl = null)
	{

		$this->_db 		= Factory::getDBO();
		$params 		= MyMuseHelper::getParams();
		$app 			= Factory::getApplication();
		$jinput 		= $app->input;
		$this->Itemid 	= $jinput->get("Itemid",'');
		$this->task 	= $jinput->get('task', '', 'CMD');
		$task 			= $this->task;
		$this->originaltask 	= $jinput->get('original_task', '', 'CMD');

//MymuseHelper::print_pre($jinput);
		if($task == "notify"){
			$this->notify();
			exit;
		}
		
		if($task == 'makemail'){
			$pp 			= $jinput->get('pp', '');
			$orderid 		= $jinput->get('orderid', 0);
			$session 		= Factory::getSession();
			$order_number 	= $session->get("order_number",0);
			if(!$orderid && $order_number){
				$q = "SELECT id from #__mymuse_order WHERE order_number='".$order_number."' ";
				$this->_db->setQuery($q);
				$orderid = $this->_db->loadResult();
			}
			$order = $this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder( $orderid );
			$order->user = Factory::getUser($order->user_id);
			
			//if we are using no_reg
			if($params->get('my_registration') == "no_reg" || $order->user->username == "buyer"){
				$fields = MyMuseHelper::getNoRegFields();
				$registry = new Registry;
				$registry->loadString($order->notes);
				foreach($fields as $field){
					if($registry->get($field)){
						$order->user->profile[$field] = $registry->get($field);
						//echo $field." ".$registry->get($field)."<br />";
					}else{
						$order->user->profile[$field] = '';
					}
				}
				if(isset($order->user->profile['first_name'])){
					$order->user->name = $order->user->profile['first_name']." ".@$order->user->profile['last_name'];
				}
				if(isset($order->user->profile['name'])){
					$order->user->name = $order->user->profile['name'];
				}
				
			}else{
				// get user details
				
				$profile_key = $params->get('my_profile_key', 'mymuse');
				
				// Load the profile data from the database.
				$query = 'SELECT profile_key, profile_value FROM #__user_profiles' .
						' WHERE user_id = '.(int) $order->user_id .
						' AND profile_key LIKE \''.$profile_key.'.%\'' .
						' ORDER BY ordering';
				$this->_db->setQuery($query);

				try 
				{
					$results = $this->_db->loadRowList();
				}
				catch (Exception $e)
				{
					$this->setError($e->getMessage());
					return false;
				}

				// Merge the profile data.
				$order->user->profile = array();
				foreach ($results as $v) {
					$k = str_replace("$profile_key.", '', $v[0]);
					$order->user->profile[$k] = trim(json_decode($v[1], true),'"');	
				}
				if(!isset($order->user->profile['email'])){
					$order->user->profile['email'] = $order->user->email;
				}
			}

			if(is_array($order->order_currency)){
				$order->currency_code = $order->order_currency['currency_code'];
			}else{
				$order->currency_code = $order->order_currency;
			}

			$result = Array
			(
					'plugin' => $pp,
					'myorder' => '1',
					'message_sent' => '1',
					'message_received' => '1',
					'order_found' =>'1',
					'order_verified' => '1',
					'order_completed' =>'1',
					'order_number' => $order->order_number,
					'order_id' => $order->id,
					'payer_email' => $order->user->profile['email'],
					'payment_status' => $order->status_name,
					'txn_id' => '',
					'error' => '',
					'user_email' => $order->user->profile['email'],
					'userid' => $order->user_id,
					'amountin' => $order->order_total,
					'currency' => $order->currency_code,
					'rate' => '',
					'fees' => '',
					'transaction_id' => '',
					'transaction_status' => $order->status_name
			);

			$this->makeMail($result);
			return true;
		}
		
		if($task == "coupon"){
			parent::display("coupon");
		}
		
		
		//regular cart functions
		$document		= Factory::getDocument();
		$document->setTitle( Text::_('COM_MYMUSE_SHOPPING_CART') );
		
		//are we returning after buying more items? remove shipping
		unset($this->cart['shipping']);
		
		$this->params = $params;
		$this->task = $task;

		$heading 			= '';
		$message 			= '';
		$footer 			= '';
		$edit 				= true;

		// set the heading for the top of page
		// find the order attached to the shopper object, or build it from session cart
		switch ($task)
		{
				
			case "checkout":
				$this->order = $order 		= $this->MyMuseCart ->buildOrder( $edit );
				$heading 	= Text::_('COM_MYMUSE_CHECKOUT');
				$message 	= Text::_('COM_MYMUSE_MAKE_ANY_FINAL_CHANGES');
				if(isset($order) && is_object($order)){
					$order->show_checkout = 0;
					$order->show_summary  = 0;
				}
				break;
				
			case "shipping":
				$this->order = $order 		= $this->MyMuseCart ->buildOrder( $edit );
				if(isset($order->need_shipping) && $order->need_shipping){
					$heading 	= Text::_('COM_MYMUSE_SHIPPING');
					$message 	= Text::_('COM_MYMUSE_CHOOSE_SHIPPING_METHOD');
				}else{
					$heading 	= Text::_('COM_MYMUSE_SHIPPING');
					$message 	= Text::_('COM_MYMUSE_NO_SHIPPING_NEEDED');
				}

				$order->show_checkout = 0;
				$order->show_summary  = 0;
				break;
				
			case "confirm":

				$edit 		= false;
				
				if($params->get('my_saveorder') != "after" && isset($this->MyMuseShopper->order->id)){
	
					$this->order = $order 		= $this->MyMuseCheckout->getOrder($this->MyMuseShopper->order->id);
				
					if($order->order_total == 0.00){
						$heading 	= Text::_('COM_MYMUSE_CONFIRM');
						$message 	= Text::_('COM_MYMUSE_ACCEPT_ORDER');
						$order->show_checkout = 0;
						$order->show_summary  = 0;
						$free = 1;
					}else{
			
						$heading 	= Text::_('COM_MYMUSE_CONFIRM');
						$message 	= Text::_('COM_MYMUSE_CHOOSE_PAYMENT_METHOD');
						$order->show_checkout = 0;
						$order->show_summary  = 0;
					}
				}else{
					// this is the after payment option
					$heading 	= Text::_('COM_MYMUSE_CONFIRM');
					$message 	= Text::_('COM_MYMUSE_CHOOSE_PAYMENT_METHOD');
					$this->order = $order 		= $this->MyMuseCart ->buildOrder( 0, 1 );

					/**
					$order->order_number 		= session_id();
					//save the faux order number in the session
					$session = Factory::getSession();
					$session->set("order_number",$order->order_number);
					*/
					
					if($this->cart['idx'] > 0){
						$order->show_checkout = 0;
						$order->show_summary  = 0;
					}
				}
				
				if(isset($order->notes) && $this->user->username == "buyer"){

					$registry = new Registry;
					$registry->loadString($order->notes);
					$order->notes = $registry->toArray();
				}
				break;
				
			case "makepayment":
				$this->order = $order 	= $this->MyMuseShopper->order;
				$currency 	= $order->order_currency;
				$edit 		= false;
				$heading 	= Text::_('COM_MYMUSE_THANK_YOU');
				$message 	= Text::_('COM_MYMUSE_WE_HAVE_RECEIVED_YOUR_ORDER');
				$order->show_checkout = 0;
				$order->show_summary  = 1;
				break;
				
			case "vieworder":
				$st 			= $jinput->get('st', '');
				$this->order 	= $order 	= $this->MyMuseShopper->order;
				

				$currency 	= $order->order_currency;
				$edit 		= false;
				$heading 	= Text::_('COM_MYMUSE_THANK_YOU');
				
				if($order->downloadable && $order->order_status == "C"){
					$message  .= $order->downloadlink;
				}else{
					$message   = Text::_('COM_MYMUSE_HERE_IS_YOUR_ORDER');
				}
			
				$order->show_checkout = 0;
				$order->show_summary  = 1;
				break;
				
			case "paycancel":
				$edit 		= false;
				if($params->get('my_saveorder') == "after"){
					$this->order = $order 		= $this->MyMuseCart ->buildOrder( $edit );
				}else{
					$this->order = $order 		= $this->MyMuseCheckout->getOrder($this->MyMuseShopper->order->id);
				}
				$heading 	= Text::_('COM_MYMUSE_CONFIRM');
				$message 	= Text::_('COM_MYMUSE_PAY_CANCEL');
				$order->show_checkout = 1;
				$order->show_summary  = 1;
				break;
				
			default:
				if($this->cart['idx'] > 0){
					$this->order = $order 		= $this->MyMuseCart ->buildOrder( $edit );
					//MymuseHelper::print_pre($order->items[0]->price); exit;	
					$order->show_checkout = 1;
					//$footer = $this->MyMuseCart ->getRecommended();
				}
				break;
		}

		// check for order
		if(!isset($order->items) || !count($order->items)) {
			//Hmm nothing to display...
			parent::display('empty');
			return false;
		}else{
			//get tracks
			// TRACKS
			/*
			if($count = count($order->items)){
				$count = count($order->items);
				if(count($order->items) && $params->get('product_player_type') == "single"){
					
				$j = 0;
				//MyMuseHelper::print_pre($order->items[0]);
				foreach($order->items as $i => $track) {
					if(!isset($track->parentid) || $track->parentid == 0){
						continue;
					}
					$site_url = MyMuseHelper::getSiteUrl($track->id,'0');
					$site_path = MyMuseHelper::getSitePath($track->id,'0');
					$flash = '';
					if(isset($track->file_preview) && $track->file_preview){
						$track->path = $site_url.$track->file_preview;
						$track->real_path = $site_path.$track->file_preview;
							
						if(substr_count($track->file_type,"video")){
							//movie
							$flash = '<!-- Begin Player -->';
							$results = $app->triggerEvent('onPrepareMyMuseVidPlayer',array(&$track,'single',0,0,$j) );
							if(is_array($results) && isset($results[0]) && $results[0] != ''){
								$flash .= $results[0];
							}
							$flash .= '<!-- End Player -->';
						}elseif(substr_count($track->file_type,"audio")){
							//audio
							$flash = '<!-- Begin Player -->';
							$results = $app->triggerEvent('onPrepareMyMuseMp3Player',array(&$track,'single',0,0,$j));
							if(is_array($results) && isset($results[0]) && $results[0] != ''){
								$flash .= $results[0];
							}
							$flash .= '<!-- End Player -->';
						}
			
						$order->items[$i]->flash = $flash;
						$j++;
					}
			
				}
				// make a controller for the play/pause buttons
				$results = $app->triggerEvent('onPrepareMyMuseMp3PlayerControl',array(&$order->items) );
				
				//get the player itself
				reset($order->items);
				$flash = '';
				$audio = 0;
				$video = 0;
				foreach($order->items as $track){
					if(isset($track->file_preview) && $track->file_preview){
						$flash .= '<!-- Begin Player -->';
						if(substr_count($track->file_type,"video") && !$video){
							//movie
				
							$results = $app->triggerEvent('onPrepareMyMuseVidPlayer',array(&$track,'singleplayer') );
				
							if(is_array($results) && isset($results[0]) && $results[0] != ''){
								$flash .= $results[0];
							}
							$video = 1;
							$order->flash_type = "video";
							if($audio){
								$order->flash_type = "mix";
							}
								
						}elseif(substr_count($track->file_type,"audio") && !$audio){
							//audio
							$results = $app->triggerEvent('onPrepareMyMuseMp3Player',array(&$track,'singleplayer') );
				
							if(is_array($results) && isset($results[0]) && $results[0] != ''){
								$flash .= $results[0];
							}
							$audio = 1;
							$order->flash_type = "audio";
							if($video){
								$order->flash_type = "mix";
							}
						}
						$flash .= '<!-- End Player -->';
						$order->flash = $flash;
						$order->flash_id = $track->id;
						if($order->flash_type != "mix"){
							break;
						}elseif($audio && $video){
							break;
						}
				
					}
				}
				}
			}
			*/

		}

				
		$this->order = $order;
		$this->currency = $this->store->currency;
		
		//START CAPTURING THE DISPLAY PARTS
		
		//download page if necessary
		$download_page = $jinput->get('download_page','', 'RAW');
		$this->download_page = $download_page;
		
		//licence if necessary
		//display the licence if necessary
		if(2 == $params->get('my_price_by_product',0)){
			$session = Factory::getSession();
			$my_licence = $session->get("my_licence",0);
			$this->assignRef('my_licence', $my_licence);
			$my_licence_text = '';
			for ($i = 0; $i < 5; $i++){
				if(null != $params->get('my_license_'.$i.'_name')
						&& null != $params->get('my_license_'.$i.'_price')){
					$licence[$i]['name'] = $params->get('my_license_'.$i.'_name');
					$licence[$i]['price'] = $params->get('my_license_'.$i.'_price');
					$licence[$i]['desc'] = $params->get('my_license_'.$i.'_desc');
					$licences[$i] = JHTML::_('select.option',   $i, $params->get('my_license_'.$i.'_name') );
					if($i == $my_licence){
						$my_licence_text = $licence[$i]['name'];
					}
				}
			}
			$lists['licences'] = JHTML::_('select.genericlist',  $licences, 'licence', 
					'class="inputbox" size="1" id="licence"', 'value', 'text', $this->my_licence );
			$this->licence = $licence;	
			$this->lists = $lists;
			
			$this->my_licence_text = $my_licence_text;
		}

		// show the heading
		if($heading){
			$this->heading = $heading;
			$this->message = $message;
			ob_start();
			parent::display('checkout_header');
			$checkout_header = ob_get_contents();
			ob_end_clean();
			$this->checkout_header = $checkout_header;
		}

		// do we need an order summary? Only if we already have a saved order
		if($task == "makepayment" || $task == "vieworder"){
			ob_start();
			parent::display('order_summary');
			$order_summary = ob_get_contents();
			ob_end_clean();
			$this->order_summary = $order_summary;
		}

		// display the cart part!
		ob_start();
		parent::display('cart');
		$this->cart_display = ob_get_contents();
		ob_end_clean();
		$this->cart_display = $this->cart_display;

		//display coupon?
		if($this->params->get("my_use_coupons") && (preg_match("/shipping|addtocart|updatecart|cartdelete|showcart|checkout/",$this->task) || $this->task == '') && !isset($this->order->coupon->id) && $this->user->get('id') > 0){

			ob_start();
			parent::display('coupon');
			$this->cart_coupon = ob_get_contents();
			ob_end_clean();
			$this->cart_coupon = $this->cart_coupon;
		}
		
		//display the licence if necessary
		if(2 == $params->get('my_price_by_product',0)){

			ob_start();
			parent::display('licence');
			$this->cart_licence = ob_get_contents();
			ob_end_clean();
			$this->cart_licence = $this->cart_licence;

		}

		//display the shopper info, if we have one
		
		if($heading && $this->user->get('id') > 0){
			if($params->get('my_notes_required',0) && !$order->notes && $this->user->username == 'buyer')
			{
				
			}else{
			
				ob_start();
				parent::display('shopper_info'); 
				$shopper_info = ob_get_contents();
				ob_end_clean();
				$this->shopper_info = $shopper_info;
			}
		}

		if($task == "checkout"){

			if($params->get('my_use_shipping') && $order->need_shipping){
				$task = "shipping";
				$button = Text::_('COM_MYMUSE_SHIPPING_BUTTON');
			}else{
				$task = "confirm";
				$button  = Text::_('COM_MYMUSE_CONFIRM_BUTTON');
			}
			$this->button = $button;
			$this->task = $task;
			
			if($params->get('my_notes_required',0) && !$order->notes && $this->user->username == 'buyer')
			{
			
			}else{
				ob_start();
				parent::display("next_form");
				$next_form = ob_get_contents();
				ob_end_clean();
				$this->next_form = $next_form;
			}
			
		}elseif($task== "shipping"){

			PluginHelper::importPlugin('mymuse');
			
			$results = $app->triggerEvent('onListMyMuseShipping', 
			array($this->shopper, $this->store, $this->order, $params) );
			$res = array();
            if(isset($results)){
				foreach($results as $r){
					$res = array_merge($res, $r);
				}
			}
            
			$this->shipMethods = $res;
			$button = Text::_('COM_MYMUSE_CHOOSE_SHIPPING');
			$this->button = $button;
			ob_start();
			parent::display("shipping_form");
			$shipping_form = ob_get_contents();
			ob_end_clean();
			$this->shipping_form = $shipping_form;
			
		}elseif($task == "confirm" || $task == "paycancel" || 
		($task == "vieworder" && $this->order->order_status == "P") ){
			
			if(isset($free) && $free == 1){
				$task = "thankyou";
				$button = Text::_('COM_MYMUSE_ACCEPT');
				$this->button = $button;
				ob_start();
				parent::display("next_form");
				$next_form = ob_get_contents();
				ob_end_clean();
				$this->thankyou_form = $thankyou_form;
			}
			
			elseif($params->get('my_shop_test')){
				$task = "makepayment";
				$button = Text::_('COM_MYMUSE_TEST_STORE');
				$this->assignRef('button', $button);
				ob_start();
				parent::display("next_form");
				$makepayment_form = ob_get_contents();
				ob_end_clean();
				$this->makepayment_form = $makepayment_form;
			}
			
			elseif(!$jinput->get('pp')){
				
				 /* payment plugins */
				//save the order number in the session
				if(!isset($order->order_number)){
					$order->order_number = '';
				}

				$session = Factory::getSession();
				$session->set("order_number",$order->order_number);
				
				PluginHelper::importPlugin('COM_MYMUSE');
			
				$results = $app->triggerEvent('onBeforeMyMusePayment', 
				array($this->shopper, $this->store, $this->order, $params, $this->Itemid) );
			
				$this->results = $results;
				
				ob_start();
				parent::display("payment_form");
				$payment_form= ob_get_contents();
				ob_end_clean();
				$this->payment_form = $payment_form;

			}
		}

	
		// show the footer
		
		if($footer){
			$this->footer = $footer;
			
			ob_start();
			parent::display('checkout_footer');
			$checkout_footer = ob_get_contents();
			ob_end_clean();
			$this->checkout_footer = $checkout_footer;
			
		}
	
		parent::display();
	}
	
	/**
	 * notify
	 * catch the post from whatever payment processor, return required responses, 
	 * update orders and do mailouts
	 * 
	 */
	function notify()
	{
		ini_set('log_errors', 1);
		ini_set('error_log', JPATH_ROOT.DS.'components'.DS.'com_mymuse'.DS.'php_error' );
		
		
		$app = Factory::getApplication();
		$jinput = $app->input;
		$params = MyMuseHelper::getParams();
		

		$date = date('Y-m-d h:i:s');
        if($params->get('my_debug')){
            $debug = "CART VIEW NOTIFY FUNCTION\n";
            MyMuseHelper::logMessage( $debug  );
        }
		$result = array();
	
		// see if any plugins wants to deal with notification
		// plugin should run MyMuseHelper::orderStatusUpdate
     	$results 		= $app->triggerEvent('onMyMuseNotify', array($params, $this->Itemid) );
     	foreach($results as $r){
            if($params->get('my_debug')){
     			$debug = " Notify: Result from Plugin\n" . print_r( $r, true ). "\n\n";
        		MyMuseHelper::logMessage( $debug  );
  			}
     		if($r['myorder']){
     			$result = $r;
     		}
     	}
 	
     	if(!count($result)){
     		
     		if($params->get('my_debug')){
     			$debug = "Notify: Did not get a result!\n";
     			$debug .= "-------END-------\n";
        		MyMuseHelper::logMessage( $debug  );
  			}
  			exit;
     	}

        $this->store = $this->MyMuseStore->_store;
        $this->store_params = new Registry;
        $this->store_params->loadString($this->store->params);
  		
     	// get mailer object
     	$mailer = Factory::getMailer();
     	$mailer->isHTML(false);
     	$mailer->Encoding = 'base64';
     	// from
     	$fromname = $params->get('contact_first_name')." ".$params->get('contact_last_name');
     	$mailfrom = $params->get('contact_email');
     	$sender = array(
     			$mailfrom,
     			$fromname );
     	$mailer->setSender($sender);
     	//recipient
     	$recipient = $mailfrom;
     	if($params->get('my_cc_webmaster')){
     		$recipient = array($mailfrom, $params->get('my_webmaster'));
     	}
     	$mailer->addRecipient($recipient);

        if($params->get('my_debug')){
        	$debug = "Notify: Making response emails \n";
        	MyMuseHelper::logMessage( $debug  );
        }
        
        //special for pesapal
        if($result['plugin'] == "payment_pesapal"){
        	$pesapalNotification=$_GET['pesapal_notification_type'];
        	$pesapalTrackingId=$_GET['pesapal_transaction_tracking_id'];
        	$pesapal_merchant_reference=$_GET['pesapal_merchant_reference'];
        	$resp="pesapal_notification_type=$pesapalNotification&pesapal_transaction_tracking_id=$pesapalTrackingId&pesapal_merchant_reference=$pesapal_merchant_reference";
        	ob_start();
        	echo $resp;
        	ob_flush();
        	$debug .= "Notify: Sent to Pesapal: $resp \n";
        }
        	
        //Make message
        if($result['order_completed'] == "ALREADY_COMPLETED"){
  			if($params->get('my_debug')){
  				$debug = "Notify: ".$result['plugin'].": Order was already completed: ".$result['payment_status']." \n\n";
  				MyMuseHelper::logMessage( $debug  );
  			}
        }elseif(!$result['message_sent'] || !$result['message_received']){
        	if($params->get('my_debug')){
        		$debug = "Notify: ".$result['plugin'].": Fatal Error\n\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	
        	$subject = $result['plugin'].": Notify Fatal Error";
            $message = "Hello,
            A fatal error occured while processing a transaction.
            ----------------------------------
            Plugin: ".$result['plugin']."

            ".$result['error'];
            

            $subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
            $mailer->setSubject($subject);
            $mailer->setBody($message);
            $send = $mailer->Send();

            
        }elseif(!$result['order_verified']){
        	if($params->get('my_debug')){
        		$debug = "Notify: ".$result['plugin'].": Order was not VERIFIED \n\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	$subject = $result['plugin'].": Order was not VERIFIED";
            $message = "Hello,
            Received a response but it was not VERIFIED.
            ----------------------------------
            Plugin: ".$result['plugin']."

            ".$result['error'];
            
            $subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
            $mailer->setSubject($subject);
            $mailer->setBody($message);
            $send = $mailer->Send();
            
            
        }elseif(!$result['order_found']){
        	if($params->get('my_debug')){
        		$debug = "Notify: ".$result['plugin'].": Order was not found \n\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	$subject = $result['plugin'].": Order was not found";
            $message = "Hello,
            Received a response but we could not find the order.
            ----------------------------------
            Plugin: ".$result['plugin']."

            ".$result['error'];
            
        	$subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
            $mailer->setSubject($subject);
            $mailer->setBody($message);
            $send = $mailer->Send();
            
        }elseif(!$result['order_completed']){

        	
        	if($params->get('my_debug')){
        		$debug = "Notify: ".$result['plugin'].": Order was not completed: ".$result['payment_status']." \n\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	$subject = $result['plugin'].": Order was not completed: ".$result['payment_status'];
            $message = "Hello,
            Received a response but if was not marked completed: ".$result['payment_status']."
            ----------------------------------
            Plugin: ".$result['plugin']."

            ".$result['error'];
        	
            $subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
            $mailer->setSubject($subject);
            $mailer->setBody($message);
            $send = $mailer->Send();
            
        }else{
        	//all is good! all is well!

        	if($params->get('my_debug')){
        		$debug = "Notify: All is good \n";
        		MyMuseHelper::logMessage( $debug  );
        	}
 
        	if(!$this->makeMail($result)){
        		$debug = "Notify: makeMail failed \n";
        		MyMuseHelper::logMessage( $debug  );
        		
        	}
            
        	//now log the payment
        	$payment['order_id'] 			= $result['order_id'];
        	$payment['date'] 				= date('Y-m-d h:i:s');
        	$payment['plugin'] 				= $result['plugin'];
        	$payment['institution'] 		= @$result['institution'];
        	$payment['amountin'] 			= $result['amountin'];
        	$payment['currency'] 			= $result['currency'];
        	$payment['rate'] 				= @$result['rate'];
        	$payment['fees'] 				= @$result['fees'];
        	$payment['transaction_id'] 		= $result['transaction_id'];
        	$payment['transaction_status'] 	= $result['transaction_status'];
        	$payment['description'] 		= $result['description'];
        	 
        	$MyMuseHelper = new MyMuseHelper;
        	if(!$MyMuseHelper->logPayment($payment)){
        		$debug = "Notify: !!Log Payment Error: ".$MyMuseHelper->getError()."\n\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	if($params->get('my_debug')){
        		$debug = "Notify: Payment logged\n";
        		MyMuseHelper::logMessage( $debug  );
        	}
        	

        	
        	if($params->get('my_plugin_email')){

        		// mail admin
        		$subject = $result['plugin']." txn on your site";
        		$message = "Hello,\n\n";
        		$message .= $result['plugin']." txn on your site!\n";
        		$message .= "-----------------------------------------------------------\n";
        		$message .= "Transaction ID: ".$result['txn_id']."\n";
        		$message .= "Payer Email: ".$result['payer_email']."\n";
        		$message .= "Order ID: ".$result['order_id']."\n";
        		$message .= "Order Number: ".$result['order_number']."\n";
        		$message .= "Payment Status returned by ".$result['plugin'].": ".$result['payment_status']."\n";


        		$mailer = Factory::getMailer();
        		$mailer->isHTML(false);
        		$mailer->Encoding = 'base64';
        		// from
        		$fromname = $params->get('contact_first_name')." ".$params->get('contact_last_name');
        		$mailfrom = $params->get('contact_email');
        		$sender = array(
        				$mailfrom,
        				$fromname );
        		$mailer->setSender($sender);
        		//recipient
        		$recipient = $mailfrom;
        		$mailer->addRecipient($recipient);
        		if($params->get('my_cc_webmaster')){
        			$recipient = array($mailfrom, $params->get('my_webmaster'));
        			$mailer->addRecipient($recipient);
        		}
        		
        		$mailer->setSubject($subject);
        		$mailer->setBody($message);
        		$send = $mailer->Send();
        	}
        	
        	
        	
        	// update stock on sale!!
        	$debug = "** CHECK STOCK **\n";
        	if ($params->get('my_use_stock')) {
        		$order = $this->MyMuseCheckout->getOrder($result['order_id']);
        		for($i = 0; $i < count($order->items); $i++) {
 					$order->items[$i]->product = $this->MyMuseCart ->getProduct($order->items[$i]->product_id);

 					//physical but not set on pre-order
        			if($order->items[$i]->product->product_physical && 
        				!($order->items[$i]->attribs['special_status'] == "pre_order") &&
        				!($order->items[$i]->attribs['special_status'] == "coming_soon")
        			){
        				if (!$MyMuseHelper->updateStock($order->items[$i]->product->id, $order->items[$i]->quantity)) {
        					$this->_db= Factory::getDBO();
        					$debug .= "$date Could not update stock\n".$this->_db->getErrorMsg()."\n";
        				}
        				$debug .= "$date Subtracted ".$order->items[$i]->quantity. " From ".$order->items[$i]->product->title."\n\n";
        			}
        		}
        	}else{
        		$debug .= "$date use_stock was ".$params->get('my_use_stock')."\n";
        	}
        }
	   



  		if($params->get('my_debug')){
            $debug .= "-------END NOTIFY FUNCTION-------";
        	MyMuseHelper::logMessage( $debug  );
  		}
  		//PAYUNITY SEND THE THANK YOU URL
  		if($result['plugin'] == "payment_payunity"){
  			echo JURI::base().JRoute::_("index.php?option=com_mymuse&task=thankyou&orderid=".$order->id);
  			exit;
  		}
  		
  		// We have a redirect
        if(isset($result['redirect']) && $result['redirect'] != ""){
        	$message = isset($result['error'])? preg_replace("/\n/", "<br />",$result['error']) : ''; 
        	$type = isset($result['error'])? 'error' : '';
        	$app->redirect(JRoute::_($result['redirect'], false), $message, $type);
        }
        
		exit;
	}
	
	function makeMail($result)
	{


        $this->store_params 	= new Registry;
        $this->store_params->loadString($this->store->params);
        $date = date('Y-m-d h:i:s');
     	
     	$params 		= MyMuseHelper::getParams();
     	$app 			= Factory::getApplication();
		$jinput 		= $app->input;

		$order 			= $this->MyMuseCheckout->getOrder($result['order_id']);
		$order->user	= Factory::getUser($order->user_id);
			
		//if we are using no_reg
		if($params->get('my_registration') == "no_reg" || $order->user->username == "buyer"){
			$fields = MyMuseHelper::getNoRegFields();
			$registry = new Registry;
			$registry->loadString($order->notes);
			foreach($fields as $field){
				if($registry->get($field)){
					$order->user->profile[$field] = $registry->get($field);
					//echo $field." ".$registry->get($field)."<br />";
				}else{
					$order->user->profile[$field] = '';
				}
			}
			if(isset($order->user->profile['first_name'])){
				$order->user->name = $order->user->profile['first_name']." ".@$order->user->profile['last_name'];
			}
			if(isset($order->user->profile['name'])){
				$order->user->name = $order->user->profile['name'];
			}
		
		}else{
			// get user details
		
			$profile_key = $params->get('my_profile_key', 'COM_MYMUSE');
		
			// Load the profile data from the database.
			$query = 'SELECT profile_key, profile_value FROM #__user_profiles' .
					' WHERE user_id = '.(int) $order->user_id .
					' AND profile_key LIKE \''.$profile_key.'.%\'' .
					' ORDER BY ordering';
			$this->_db->setQuery($query);

			try 
			{
				$results = $this->_db->loadRowList();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}

			// Merge the profile data.
			$order->user->profile = array();
			foreach ($results as $v) {
				$k = str_replace("$profile_key.", '', $v[0]);
				$order->user->profile[$k] = trim(json_decode($v[1], true),'"');
			}
			if(!isset($order->user->profile['email'])){
				$order->user->profile['email'] = $order->user->email;
			}
		}

		if(!isset($order->user->profile['shopper_group']) || $order->user->profile['shopper_group'] < 1){
			$order->user->profile['shopper_group'] = 1;
		}

		$order->user->shopper_group = $this->MyMuseShopper->getShopperGroup($order->user->profile['shopper_group']);
		$order->user->discount = $order->user->shopper_group->discount;
		$order->user->shopper_group_name = $order->user->shopper_group->shopper_group_name;
		
		$this->user = $shopper = $order->user;
		
		if(2 == $params->get('my_price_by_product',0)){
			$licence = explode("|", $order->licence);
			$my_licence = trim($licence[0]);

			$my_licence_text = $params->get('my_license_'.$my_licence.'_name');
			$my_licence_desc = $params->get('my_license_'.$my_licence.'_desc');
			$this->my_licence = $my_licence;
			$this->my_licence_text = $my_licence_text;
			$this->my_licence_desc = $my_licence_desc;
		}
		

		if(is_object($order) && $params->get('my_debug')){
			$debug = "makeMail: Order = ".$order->id."\n";
			//$debug .= "makeMail user = ".print_r($this->user,true)."\n";
			MyMuseHelper::logMessage( $debug  );
		}
		
		$currency 		= $order->order_currency;
		$heading 		= Text::_('COM_MYMUSE_THANK_YOU');
		$message 		= Text::_('COM_MYMUSE_HERE_IS_YOUR_ORDER');
		
		if($order->notes && ($params->get('my_registration') == "no_reg" || $this->user->username == "buyer") ){

			$debug = "makeMail: Order Notes = ".print_r($order->notes,true)."\n";
			MyMuseHelper::logMessage( $debug  );
			//$accparams = new Registry( $order->notes);
			$registry = new Registry;
			$notes_params = $registry->loadString($order->notes);
			$order->notes = $registry->toArray();

			$this->user->set('email',$notes_params->get('email'));
			$this->user->set('name',$notes_params->get('first_name')." ".$notes_params->get('last_name'));
			$shopper->name          = isset($order->notes['name'])? $order->notes['name'] : '';
			$shopper->email         = isset($order->notes['email'])? $order->notes['email'] : '';
			$shopper->first_name    = isset($order->notes['first_name'])? $order->notes['first_name'] : '';
			$shopper->last_name     = isset($order->notes['last_name'])? $order->notes['last_name'] : '';
			$shopper->address1 		= isset($order->notes['address1'])? $order->notes['address1'] : '';
			$shopper->address2 		= isset($order->notes['address2'])? $order->notes['address2'] : '';
			$shopper->city 			= isset($order->notes['city'])? $order->notes['city'] : '';
			$shopper->postal_code 	= isset($order->notes['postal_code'])? $order->notes['postal_code'] : '';
			$shopper->country       = isset($order->notes['country'])? $order->notes['country'] : '';
			$shopper->region_name   = isset($order->notes['region_name'])? $order->notes['region_name'] : '';
		}
		 
		$user_email 	= $this->user->email;
		$task = $jinput->get('task','');

		$this->params = $params;
		$this->task = $task;
		$this->shopper = $shopper;

		$this->order = $order;
		$this->currency = $currency;
		$this->heading = $heading;
		$this->message = $message;
		
		$subject =  $this->store->title." - ".Text::_('COM_MYMUSE_ORDER_CONFIRMATION');
		
		
		$subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
		$download_header = '';
		 
		if($params->get('my_debug')){
			$debug = "makeMail: Downloadable = ".$order->downloadable."\n";
			MyMuseHelper::logMessage( $debug  );
		}
		 
		//see if there is a message
		$my_email_msg 	= $params->get('my_email_msg');

		if($result['plugin']){
			PluginHelper::importPlugin('mymuse',$result['plugin']);

			$results = $app->triggerEvent('onAfterMyMusePayment', array() );
			$pp = $result['plugin'];
			foreach($results as $res){
				//if(preg_match("/$pp/", $res)){
					$arr = explode(":",$res);
                    $p = array_shift($arr);
        			$my_email_msg .= implode(":",$arr);
				//}
			}
		}
		$this->my_email_msg = $my_email_msg;
		
		if($params->get('my_debug')){
			$debug = "makeMail: Extra Email message: $my_email_msg \n\n";
			MyMuseHelper::logMessage( $debug  );
		}
		
		//include_once( JPATH_ROOT.DS.'components'.DS.'com_mymuse'.DS.'templates'.DS.'mail_html_header.php' );
		 
		
		$contents  = '';
		$do_not_display_children = 1;
		$this->do_not_display_children = $do_not_display_children;
		
		ob_start();
		parent::display('email_header');
		$header = ob_get_contents();
		ob_end_clean();


		
		ob_start();
		parent::display('checkout_header');
		parent::display('order_summary');
		parent::display('shopper_info');
		parent::display('cart');
		parent::display('email_footer');
		$contents .= ob_get_contents();
		ob_end_clean();

		//make sure the payment status is Completed
		if($order->order_status == "C"){
			$message = $header . $order->downloadlink . $contents;
		}else{
			$message = $header . $contents;
		}
		 
		if($params->get('my_debug')){
			//$debug = "makeMail: Email message: $message \n\n";
			//MyMuseHelper::logMessage( $debug  );
		}
		
		
		// email client $user_email, and cc store owner $mailfrom
		// get mailer object
		$mailer = Factory::getMailer();
		$mailer->isHTML(true);
		$mailer->Encoding = 'base64';
		// from
		$fromname = $params->get('contact_first_name')." ".$params->get('contact_last_name');
		$mailfrom = $params->get('contact_email');
		$sender = array(
				$mailfrom,
				$fromname );
		$mailer->setSender($sender);
		
		//recipient
		$recipient = array($user_email);
		$mailer->addRecipient($recipient);
		
		//bcc_recipient
		$bcc_recipient = '';
		if($params->get('my_plugin_email')){
			//bcc admin
			$bcc_recipient = array($mailfrom);
		}
		
		if($params->get('my_cc_webmaster')){
			//bcc webmaster
			$bcc_recipient = array($mailfrom, $params->get('my_webmaster'));
		}
		if($bcc_recipient){
			$mailer->addBcc($bcc_recipient);
		}
		
		
		$mailer->setSubject($subject);
		$mailer->setBody($message);
		
		$rs = $mailer->Send();
		 
		if ($rs instanceof Exception){
			$debug = "makeMail: Error sending email to $user_email: " . $rs->__toString();
		
		}elseif (empty($rs)){
			$debug = "makeMail: Error sending email to $user_email: return from mailer was empty";
		} else {
			$debug = "makeMail: Mail sent to $user_email";
		}
		if($params->get('my_debug')){
			MyMuseHelper::logMessage( $debug  );
		}
		return true;	
	}

}
