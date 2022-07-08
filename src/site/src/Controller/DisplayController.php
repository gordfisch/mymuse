<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2021 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Language;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Http\Response;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

use Joomla\Component\Mymuse\Site\Helper\AssociationHelper;
use Joomla\Component\Mymuse\Site\Helper\CartHelper;
use Joomla\Component\Mymuse\Site\Helper\CheckoutHelper;
use Joomla\Component\Mymuse\Site\Helper\QueryHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Site\Helper\ShopperHelper;
use Joomla\Component\Mymuse\Site\Model\CategoriesModel;
use Joomla\Component\Mymuse\Site\Model\CategoryModel;
use Joomla\Component\Mymuse\Site\Model\FeaturedModel;
use Joomla\Component\Mymuse\Site\Model\FormModel;
use Joomla\Component\Mymuse\Site\Model\MymuseModel;
use Joomla\Component\Mymuse\Site\Model\ProductModel;
use Joomla\Component\Mymuse\Site\Model\ProductsModel;
use Joomla\Component\Mymuse\Site\Model\StoreModel;
use Joomla\Component\Mymuse\Site\Service\Mymuse;

/**
 * Mymuse Component Controller
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{

	/**
	 * input
	 *
	 * @var object
	 */
	var $input = null;

	/**
	 * original_task
	 *
	 * @var string
	 */
	var $original_task = '';

	/**
	 * params
	 *
	 * @var object
	 */
	var $params = null;

	/**
	 * instances array of objects
	 *
	 * @var array
	 */
	var $instances = array();

	/**
	 * MyMuseCart object ref
	 *
	 * @var object
	 */
	var $MyMusecart = null;

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
	 * MyMuseStore object ref
	 *
	 * @var object
	 */
	var $MyMuseStore = null;

	/**
	 * MyMuseProduct object ref
	 *
	 * @var object
	 */
	var $MyMuseProduct = null;

	/**
	 * MyMuseCheckout object ref
	 *
	 * @var object
	 */
	var $MyMuseCheckout = null;


	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   \input              $input    Input
	 *
	 * @since   3.0.1
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{


		$this->input = Factory::getApplication()->input;

		// Product frontpage Editor pagebreak proxying:
		if ($this->input->get('view') === 'product' && $this->input->get('layout') === 'pagebreak')
		{
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}
		// Product frontpage Editor product proxying:
		elseif ($this->input->get('view') === 'products' && $this->input->get('layout') === 'modal')
		{
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		}

		$this->params = MymuseHelper::getParams();
		$lang 	= Factory::getLanguage();
		$rtl 	= $lang->get('rtl');

		$Doc = Factory::getDocument();

		if(!$this->params->get('my_disable_css',0)){
			$Doc->addStyleSheet( Uri::base() . 'components/com_mymuse/assets/css/mymuse.css' );
		}
		if($rtl){
			$Doc->addStyleSheet( Uri::base() . 'components/com_mymuse/assets/css/mymuse_rtl.css' );
		}
		$Doc->addScript( Uri::base() . 'components/com_mymuse/assets/javascript/mymuse.js' );

		parent::__construct($config, $factory, $app, $input);

		$this->MyMuseCart		= Mymuse::getObject('Cart');
		$this->MyMuseShopper	= Mymuse::getObject('Shopper','model');
		$this->shopper 			= $this->MyMuseShopper->getShopper();
		$this->MyMuseStore		= Mymuse::getObject('Store', 'model');
		$this->MyMuseProduct	= Mymuse::getObject('Product', 'model');
		$this->MyMuseCheckout	= Mymuse::getObject('Checkout');
		$this->original_task    = $this->input->get('task');
		$this->input->set('original_task', $this->original_task);

		ini_set('memory_limit',"512M");
		ini_set('max_execution_time',"120");
		ini_set("log_errors", 1);
		ini_set("error_log", "php-error.log");


	}
	/**
	 * Returns a reference to a global MyMuse object, only creating it if it
	 * doesn't already exist. The default is to look in the helpers directory.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $MyMuseStore 	=& MyMuse::getObject('Store','model');</pre>
	 *
	 * @param	string	$client		type of class.
	 * @param	string	$type 		An optional type, default helper
	 * @param	array	$config 	An optional associative array of configuration settings.
	 * @return	MyMuse	The MyMuse object.
	 * @since	1.5
	 */
	public static function &getObject($client, $type='helper', $config = array(), $renew = '')
	{
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		if (empty($instances[$client]) || $renew == "renew")
		{
			// Create an object
			if($type == 'model'){
				$classname = '\\Joomla\\Component\\Mymuse\\Site\\Model\\'.ucfirst($client).'Model';
			}else{
				$classname = '\\Joomla\\Component\\Mymuse\\Site\\Helper\\'.ucfirst($client). 'Helper';
			}
			$instance = new $classname($config);
			$instances[$client] =& $instance;
		}

		return $instances[$client];
	}
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  \Joomla\CMS\MVC\Controller\BaseController  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{

		$safeurlparams = array(
			'catid' => 'INT',
			'id' => 'INT',
			'cid' => 'ARRAY',
			'year' => 'INT',
			'month' => 'INT',
			'limit' => 'UINT',
			'limitstart' => 'UINT',
			'showall' => 'INT',
			'return' => 'BASE64',
			'filter' => 'STRING',
			'filter_order' => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search' => 'STRING',
			'print' => 'BOOLEAN',
			'lang' => 'CMD',
			'productid' => 'INT',
			'variation' => 'ARRAY',
			'Itemid' => 'INT',
			'view' => 'STRING',
			'layout' => 'STRING');


		$task  = $this->input->get('task');

		if('ajaxtogglecart' == $task){
			$cachable = false;
			$vName = 'cart';
			$this->input->set('view', $vName);
			$this->ajaxtogglecart();
			return;
		}

		$cachable = true;

		/**
		 * Set the default view name and format from the Request.
		 * Note we are using a_id to avoid collisions with the router and the return page.
		 * Frontend is a bit messier than the backend.
		 */

		$id    = $this->input->getInt('a_id');
		$vName = $this->input->getCmd('view', 'categories');
		$this->input->set('view', $vName);

		$user = $this->app->getIdentity();

		if ($user->get('id')
			|| ($this->input->getMethod() === 'POST'
			&& (($vName === 'category' && $this->input->get('layout') !== 'blog') || $vName === 'archive' )))
		{
			$cachable = false;
		}

		

		// Check for edit form.
		if ($vName === 'form' && !$this->checkEditId('com_mymuse.edit.product', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 403);
		}

		if ($vName === 'product')
		{
			// Get/Create the model
			if ($model = $this->getModel($vName))
			{
				if (ComponentHelper::getParams('com_mymuse')->get('record_hits', 1) == 1)
				{
					$model->hit();
				}
			}
		}

		parent::display($cachable, $safeurlparams);

		return $this;
	}

	/**
	 * ajaxtogglecart
	 *
	 * Given  product id, add it to cart, unless it is there already, then delete from cart
	 * return json encoded string with message, cat idx
	 *
	 * return string
	 */
	function ajaxtogglecart()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$productid  = $input->get('productid', 0, 'int');
        $variation  = $input->get('variation');

        $msg = '';
		if(!$productid ){
			$data = array();
		}else{
			$v_array[$productid] = $variation;
			$input->set('variation', $v_array);
			$variation = $v_array;
			$db = Factory::getDBO();
			$query = "SELECT title from #__mymuse_product WHERE id =$productid";
			$db->setQuery($query);
			$title = $db->loadResult();
			
			$incart = 0;
			for ($i=0;$i<$this->MyMuseCart->cart["idx"];$i++) {
				if($this->MyMuseCart->cart[$i]["product_id"] == $productid){
					$incart = 1;
				}
			}
	
			if($incart){
				// let us remove it
				$this->MyMuseCart->delete($productid );
				$msg .= Text::_("COM_MYMUSE_DELETED")." ".$title;
				$action = "deleted";
			}else{
				//let us add it
				if(!$this->MyMuseCart->addToCart()){
					$msg .= $this->MyMuseCart->getError();
					$action = "failed";
				}else{
					$msg .= Text::_("COM_MYMUSE_ADDED")." ".$title;
					$action = "added";
				}
			}
			$messageQueue = Factory::getApplication()->getMessageQueue();
			if(count($messageQueue)){
				foreach($messageQueue as $queue){
					$msg .= "<br />".$queue['type'].': '.$queue['message'];
				}

			}

			$data = array('action'=>$action, 'msg'=>$msg, 
            'idx' => $this->MyMuseCart->cart['idx'],
            'variation'=> $variation[$productid]);
		}
	
		//save the cart in the session
		$this->MyMuseCart->setCart();
	
		$rand = UserHelper::genRandomPassword(8);
		$document = Factory::getDocument();
		$document->setMimeEncoding('application/json');
		$app->setHeader("Expires","Sun, 19 Nov 1978 05:00:00 GMT");
		$app->setHeader("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
		$app->setHeader("Cache-Control", "no-store, no-cache, must-revalidate");
		$app->setHeader("Cache-Control", "post-check=0, pre-check=0", false);
		$app->setHeader("Pragma", "no-cache");
		$app->setHeader('Content-Disposition','attachment;filename="coupon_'.$rand .'.json"');
		echo json_encode($data);
		exit;
	}
	
	
	function ajaxupdatecart()
	{
		$app = Factory::getApplication();
		$input = Factory::getApplication()->input;
		$productid  = $input->get('productid', 0, 'int');
		$variation  = $input->get('variation', 0, 'ARRAY');
		if(!$productid ){
			$data = array();
		}else{
			$order_subtotal = 0.00;
			$product_item_subtotal = 0.00;
			$product_item_price = 0.00;
			$tax_total = 0.00;
			
			
			$db = Factory::getDBO();
			$query = "SELECT title from #__mymuse_product WHERE id =$productid";
			$db->setQuery($query);
			$title = $db->loadResult();
			
			if(!$this->MyMuseCart->updateCart()){
				$msg = "ERROR: ".$this->MyMuseCart->error;
			}
			$order = $this->MyMuseCart->buildOrder(0,1);
			$order_subtotal = $order->order_subtotal;
			$tax_total = $order->tax_total;
			
			for ($i=0;$i<count($order->items);$i++) {
				if($order->items[$i]->id  == $productid){
					$product_item_subtotal = $order->items[$i]->product_item_subtotal;
					$product_item_price = $order->items[$i]->product_item_price;
					$msg = Text::_('MYMUSE_UPDATED_CART');
				}
				$msg = Text::_('MYMUSE_UPDATED_CART');
			}
			$data = array(
					'id' => $productid,
					'msg'=>  $msg,
					'order_subtotal'=>$order_subtotal,
					'tax_total'=>$tax_total,
					'product_item_subtotal'=>$product_item_subtotal,
					'product_item_price'=>$product_item_price,
			);

		}
		
		//save the cart in the session
		$this->MyMuseCart->setCart();
	
		$rand = JUserHelper::genRandomPassword(8);
		$document = Factory::getDocument();
		$document->setMimeEncoding('application/json');
		$app->setHeader("Expires","Sun, 19 Nov 1978 05:00:00 GMT");
		$app->setHeader("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
		$app->setHeader("Cache-Control", "no-store, no-cache, must-revalidate");
		$app->setHeader("Cache-Control", "post-check=0, pre-check=0", false);
		$app->setHeader("Pragma", "no-cache");
		$app->setHeader('Content-Disposition','attachment;filename="coupon_'.$rand .'.json"');
		echo json_encode($data);
		exit;
	}


	/**
	 * updatecart
	 * update the cart
	 *
	 */
	public function updatecart()
	{

		if(!$this->MyMuseCart->updateCart( )){
			$msg = $this->MyMuseCart->error;
			$this->setRedirect( Route::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
			return false;
		}
		$this->input->set('view', 'cart');
		$this->input->set('layout', 'cart');
		//save the cart in the session
		$this->MyMuseCart->setCart();
		$this->display();
	}

	/**
	 * cartdelete
	 * delete an item to the cart
	 *
	 */
	public function cartdelete()
	{

		$product_id = $this->input->get('product_id',0);

		if(!$this->MyMuseCart->delete($product_id )){
			$msg = $this->MyMuseCart->error;
			$this->setRedirect( Route::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
			return false;
		}
		$this->setRedirect( Route::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid) );
		//save the cart in the session
		$this->MyMuseCart->setCart();
		return;
	}

	/**
	 * cartclear
	 * clear the cart
	 *
	 */
	public function cartClear()
	{

		if(!$this->MyMuseCart->reset()){
			$msg = $this->MyMuseCart->error;
			$this->setRedirect( Route::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
			return false;
		}
		//save the cart in the session
		$this->MyMuseCart->cart = array();
      	$this->MyMuseCart->cart["idx"] = 0;
		$this->MyMuseCart->setCart();
		$this->setRedirect( Route::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid));
		return;
	}

	
	/**
	 * savenoreg
	 * save no registration, try to log in as guest buyer
	 *
	 */
	public function savenoreg()
	{	
		
		if($this->MyMuseShopper->savenoreg()){
			$this->setRedirect( Route::_("index.php?option=com_mymuse&task=checkout&Itemid=".$this->Itemid));
			return true;
		}else{
			// Redirect back to the registration screen.
			// enqueued messages will display
			echo "got here"; exit;
			$this->setRedirect( Route::_("index.php?option=com_mymuse&view=shopper&task=register&Itemid=".$this->Itemid));
			return false;
		}
	}


	/**
	 * checkout
	 * take me to the checkout page
	 *
	 */
	public function checkout()
	{
		

		$user = Factory::getUser();
		$app = Factory::getApplication();

		//no_reg and not logged in
        if(!$user->get('id') && $this->params->get('my_registration') == "no_reg"){
        	
        	$plugin = PluginHelper::getPlugin('user', 'mymusenoreg');
        	
        	if(!$plugin || !count($plugin)){
       
        		//plugin is not on, try to login as buyer
        		if(!$this->MyMuseShopper->savenoreg()){
        			echo $this->MyMuseShopper->getError();
        			echo "Could not Log in"; 
        			return false;
        		}else{
        			$this->shopper 	=  $this->MyMuseShopper->getShopper();
        			$url = Route::_("index.php?option=com_mymuse&task=checkout&view=cart&Itemid=".$this->Itemid);
        			$this->setRedirect( $url );
        			return true;
        		}
        	    
        	}else{
        		$url = Route::_(URI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid);
        		$return = base64_encode($url);

        		//$msg = Text::_("COM_MYMUSE_PLEASE_COMPLETE_THE_FORM");
        		$this->setRedirect( Route::_("index.php?option=com_mymuse&view=shopper&task=register&Itemid=".$this->Itemid));
        		return true;
        	}
        }
        //
        //no_reg, logged in but no form yet
        if($user->get('id') && ($this->params->get('my_registration') == "no_reg") && !$this->shopper->perms){
        	//$msg = Text::_("COM_MYMUSE_PLEASE_COMPLETE_THE_FORM");
        	$this->setRedirect( Route::_("index.php?option=com_mymuse&view=shopper&task=register&Itemid=".$this->Itemid) );
        	return false;
        	
        }
        
        // not logged in and jomsocial
        if(!$user->get('id') && $this->params->get('my_registration') == "jomsocial"){
            $msg = Text::_("COM_MYMUSE_PLEASE_LOGIN_OR_REGISTER_BELOW");
            $this->setRedirect( Route::_('index.php?option=com_community'), $msg );
            return false;
        }
       
        //user and shopper but missing fields, so no shopper perms
        if($user->get('id') && $this->shopper->id && !$this->shopper->perms){
        	
        	$url = Route::_(URI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid);
        	$return = base64_encode($url);

            $msg = Text::_("COM_MYMUSE_PLEASE_FILL_IN_MISSING_ITEMS") .": ".$this->MyMuseShopper->getError();
        	$this->setRedirect( Route::_('index.php?option=com_users&view=profile&layout=edit&return='.$return.'&Itemid='.$this->Itemid), $msg );
            return false;
        }
        
        //normal registration 
		if(!$this->shopper->perms){

			$url = Route::_(URI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid);
        	$return = base64_encode($url);
			
			$rpage = strtolower($this->params->get('my_registration_redirect'));
			$msg = Text::_("COM_MYMUSE_PLEASE_LOGIN_OR_REGISTER");
        	$this->setRedirect( Route::_('index.php?option=com_users&view='.$rpage.'&return='.$return.'&Itemid='.$this->Itemid), $msg );
            return false;
		}

		// see if any plugins want to check the cart
		$this->Itemid		= $this->input->get('Itemid', 0);

		$results 	= $app->triggerEvent('onBeforeMyMuseCheckout',
				array(&$this->shopper, &$this->store, &$this->MyMuseCart->cart, &$this->params, &$this->Itemid) );
		
		if(is_array($results)){
			foreach($results as $result){
				eval($result);
			}
		}
		

		if($this->params->get('my_use_shipping') 
		&& !isset($this->MyMuseCart->cart['ship_method_id'])
		&& $this->MyMuseCart->shipping_needed() ){
			
			$this->input->set('task', 'shipping');
			$this->shipping();
			return true;
		}
		
		
		//See if we want to skip the confirm page
		if($this->params->get('my_checkout','regular') == "skip_confirm"){
			$this->input->set('task','confirm');
			$this->confirm();
			return true;
		}

		$this->input->set('view', 'cart');
		$this->input->set('layout', 'cart');
		
		$this->display();

	}
	
	/**
	 * shipping
	 * display shipping options
	 *
	 */
	public function shipping()
	{

		$app = Factory::getApplication();

		if(!isset($this->shopper->perms)){
			$url = Route::_(URI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid);
        	$return = base64_encode($url);
			$msg = Text::_("COM_MYMUSE_PLEASE_LOGIN_OR_REGISTER");;
        	$rpage = strtolower($this->params->get('my_registration_redirect','login'));
        	$this->setRedirect( Route::_('index.php?option=com_users&view='.$rpage.'&return='.$return), $msg );
            return false;
		}else{

			PluginHelper::importPlugin('mymuse');
			$this->order		= $this->MyMuseCart->buildOrder();
			$results = $app->triggerEvent('onListMyMuseShipping',
					array($this->shopper, $this->store, $this->order, $this->params) );

			$res = array();
            if(isset($results)){
				foreach($results as $r){
					$res = array_merge($res, $r);
				}
			}
			//if we only have one shipping, add it to the order
			if(count($res) == 1 && $this->params->get('my_add_shipping_auto',0)){
				$url =  'index.php?option=com_mymuse&task=confirm&shipmethodid='.$res['0']->id;
				$url .= '&Itemid='.$this->Itemid;
				$url = Route::_($url);
				$msg = Text::_('MYMUSE_SHIPPING_ADDED')." ".$res['0']->ship_carrier_name." ";
				$msg .= $res['0']->ship_method_name.": ".MyMuseHelper::printMoney($res['0']->cost);
				$this->setRedirect( $url, $msg );
				return false;
			}
			
			//show shipping options
			$this->input->set('view', 'cart');
			$this->input->set('layout', 'cart');
			$this->display();
		}
	}
	
	/**
	 * confirm
	 * they have confirmed the order, do some checks and save it
	 *
	 */
	public function confirm()
	{
		
		$app = Factory::getApplication();

		// are they logged in?
		if(!$this->shopper->perms){
			$url = Route::_(URI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid);
        	$return = base64_encode($url);
			$msg = Text::_("COM_MYMUSE_PLEASE_LOGIN_OR_REGISTER");;
        	$rpage = strtolower($this->params->get('my_registration_redirect','login'));
        	$this->setRedirect( 'index.php?option=com_users&view='.$rpage.'&return='.$return, $msg );
            return false;
		}
		
		// need shipping?
		if($this->params->get('my_use_shipping') && $this->MyMuseCart->shipping_needed()){
			$shipmethodid = $this->input->get('shipmethodid', 0);
			if(!$shipmethodid){
				$msg = Text::_('MYMUSE_SHIP_METHOD_ID_IS_NOT_VALID');
				$this->setRedirect( Route::_('index.php?option=com_mymuse&task=shipping&Itemid='.$this->Itemid), $msg );
				return false;
			}else{
                $order 		= $this->MyMuseCart->buildOrder( 0, 1 );
                $this->MyMuseCart->cart['shipmethodid'] = $shipmethodid;

                PluginHelper::importPlugin('mymuse');
                $results = $app->triggerEvent('onCaclulateMyMuseShipping', array($order, $shipmethodid ));
  
				$this->MyMuseCart->cart['shipping'] = $results[0];

			}
		}

		//save the order
		if($this->MyMuseCart->cart['idx']){
			$this->input->set('view', 'cart');
			$this->input->set('layout', 'cart');
	
			if($this->params->get('my_saveorder') != "after"){
				// save the order
				if(!$this->MyMuseShopper->order = $this->MyMuseCheckout->save( )){
					$msg = $this->MyMuseCheckout->error;
					$this->setRedirect( Route::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
					return false;
				}
			
				if($this->MyMuseShopper->order->order_status == "C"){
					$this->input->set('task', 'makemail');
					$this->display();
					$this->input->set('task', 'confirm');
				}
				
				$this->MyMuseCart->cart['orderid'] = $this->MyMuseShopper->order->id;
				if($this->MyMuseShopper->order->order_total == 0.00){
					$this->input->set('task', 'thankyou');
					$this->thankyou();
					return true;
				}
			}else{
				//$message = Text::_('COM_MYMUSE_ORDER_WILL_BE_SAVED_AFTER');
				//Factory::getApplication()->enqueueMessage($message, 'error');
			}

			$this->display();
	
		}else{
			$this->input->set('view', 'cart');
			$this->input->set('layout', 'cart');
			$this->display();
		}
	}

	/**
	 * process_payment
	 * process a payment
	 *
	 */
	public function process_payment()
	{
		$session 		= Factory::getSession();
		$plugin 		= $session->get('process_payment',0);
		$orderid 		= $this->input->get('orderid',0);
		
		
		if($plugin && $orderid){
			$dispatcher		= JDispatcher::getInstance();
			$this->order 	= $this->MyMuseCheckout->getOrder($orderid);
			$func 			= 'on'.ucfirst($plugin).'ProcessPayment';
			PluginHelper::importPlugin('mymuse');
			$results = $dispatcher->trigger($func,
				array($this->shopper, $this->store, $this->order, $this->params, $this->Itemid) );
		}else{
			$msg = "Could not find plugin or orderid";
			$this->setRedirect( Route::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
			return false;
			
		}
	}
	
	
	
	/**
	 * makepayment
	 * make a payment
	 *
	 */
	public function makepayment()
	{
		$mainframe = Factory::getApplication();
		
		
		if(!$this->shopper->perms){
			$url = Route::_(URI::base().'index.php?option=com_mymuse&view=cart&layout=cart&Itemid='.$this->Itemid);
        	$return = base64_encode($url);
			$msg = Text::_("COM_MYMUSE_PLEASE_LOGIN_OR_REGISTER");;
        	$rpage = strtolower($this->params->get('my_registration_redirect','login'));
        	$this->setRedirect( Route::_('index.php?option=com_users&view='.$rpage.'&return='.$return), $msg );
            return false;

		}  
            
		if(!$this->MyMuseCart->cart['orderid'] && $this->params->get('my_shop_test')){
			if($this->params->get('my_saveorder') != "after"){
				// save the order
				if(!$this->MyMuseShopper->order = $this->MyMuseCheckout->save( )){
					$msg = $this->MyMuseCheckout->error;
					$this->setRedirect( Route::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$this->Itemid), $msg );
					return false;
				}
				$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder($this->MyMuseShopper->order->id);
					
				if($this->params->get('my_shop_test')){
					$this->input->set('view', 'cart');
					$this->input->set('layout', 'cart');
				}
			}
		}elseif($this->MyMuseCart->cart['orderid']){
			
			$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder($this->MyMuseCart->cart['orderid']);
			
			if($this->params->get('my_shop_test')){
				$this->input->set('view', 'cart');
				$this->input->set('layout', 'cart');

			}else{
				$this->input->set('task', 'thankyou');
				$this->input->set('view', 'cart');
				$this->input->set('layout', 'cart');
			}
		}else{
			$this->input->set('view', 'cart');
			$this->input->set('layout', 'empty');
		}
		$this->display();

	}

	/**
	 * thankyou
	 * thank you after payment
	 *
	 */
	public function thankyou()
	{
		$errorName = $this->input->get('errorName', 0);
		if($errorName){
			$errorMsg = $this->input->get('errorMsg', '');
			$msg = $errorName." : ".$errorMsg;
			$this->setRedirect("index.php", $msg);
		}
		$session = Factory::getSession();
		$session->set("process_payment","");

		//get order
		$db 			= Factory::getDBO();
		$user			= Factory::getUser();
		$user_id 		= $user->get('id');
		$orderid 		= $this->input->get('orderid', 0);
		$session 		= Factory::getSession();
		$order_number 	= $session->get("order_number",0);
		$app 			= Factory::getApplication();

		$st 			= $this->input->get('st', 0);
		$after			= $this->input->get('after', 0);
		$tx 			= $this->input->get('tx', 0);
		$date 			= date('Y-m-d h:i:s');
		
		
		$pp 			= $this->input->get('pp', 0);
		
		$pesapal_merchant_reference = $this->input->get('pesapal_merchant_reference', 0);
		if($pesapal_merchant_reference){
			$order_number = $pesapal_merchant_reference;
		}

		if($after || $this->params->get('my_saveorder') == "after"){
			// See if there is a transaction value
			
			if($tx){
				$q = "SELECT order_id FROM #__mymuse_order_payment WHERE
				transaction_id='$tx'";
				$db->setQuery($q);
				$orderid = $db->loadResult();
				if($this->params->get('my_debug')){
					$debug = "Thankyou: Got orderid from transaction: $orderid";
					MyMuseHelper::logMessage( $debug  );
				}
			}
			
			if(!$orderid && $pp !== 'paymentoffline' && $this->params->get('my_registration') == "no_reg"){
				//get the last orderid
				$q1 = "SELECT id from #__mymuse_order WHERE 
				notes LIKE '%". $user->get('email')  ."%' ORDER BY id DESC LIMIT 0,1";
				$db->setQuery($q1);
				$orderid = $db->loadResult();
				if($this->params->get('my_debug')){
					$debug = "Thankyou: Got last orderid : $orderid";
					MyMuseHelper::logMessage( $debug  );
				}
			}elseif(!$orderid && $pp !== 'paymentoffline'){
				//get the last orderid
				$q1 = "SELECT id from #__mymuse_order WHERE
				user_id = '$user_id' ORDER BY id DESC LIMIT 0,1";
				$db->setQuery($q1);
				$orderid = $db->loadResult();
			}
			
			if($orderid){
				$this->MyMuseCart->reset();
			}
		}
		
		if(!$orderid && $order_number){
			$q = "SELECT id from #__mymuse_order WHERE order_number='".$order_number."' ";
			$db->setQuery($q);
			$orderid = $db->loadResult();
		}
		
		if(!$orderid && $this->params->get('my_saveorder') == "after"){
			// no id
			$msg = Text::_("COM_MYMUSE_NO_ORDER_WAITING");
			$this->setRedirect(Route::_("index.php?option=com_mymuse&view=shopper&layout=waiting"), $msg);
			return false;
		}
		
		
		if(!$orderid){
			// no id
			$msg = Text::_("COM_MYMUSE_NO_ORDER_ID").' ';
			$this->setRedirect("index.php", $msg);
			return false;
		}
		
		$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder( $orderid );

		if(!isset($this->MyMuseShopper->order->id)){
			// no order
			$msg = Text::_("COM_MYMUSE_NO_ORDER");
			$this->setRedirect("index.php", $msg);
			return false;
		}
		
		if($this->MyMuseShopper->order->user_id != $user_id ){
			// not the right user!!
			$msg = Text::_("COM_MYMUSE_USER_ORDER_OWNER_MISMATCH");
			$this->setRedirect("index.php", $msg);
			return false;
		}
	
		$notifyCustomer = $this->input->get('notifyCustomer', 0);
		if($notifyCustomer){
			$this->input->set('task', 'makemail');
			$this->input->set('view', 'cart');
			$this->display();
		}

	
		if($st === "Completed" && $this->MyMuseShopper->order->order_status != "C"){
			// waiting for IPN
			sleep(3);
			$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder( $orderid );
			$this->MyMuseShopper->order->waited = 3;
		}
		if($st === "Completed" && $this->MyMuseShopper->order->order_status != "C"){
			// waiting for IPN
			sleep(3);
			$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder( $orderid );
			$this->MyMuseShopper->order->waited = 6;
		}
		if($st === "Completed" && $this->MyMuseShopper->order->order_status != "C"){
			// waiting for IPN
			sleep(3);
			$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder( $orderid );
			$this->MyMuseShopper->order->waited = 9;
		}
		if($st === "Completed" && $this->MyMuseShopper->order->order_status != "C"){

			$uri = URI::getInstance();
			$msg = Text::_("COM_MYMUSE_WAITING_FOR_IPN"). " <a href='".$uri->toString()."'>".$uri->toString()."</a>";
			
			$this->setRedirect('index.php', $msg);
			return false;
		}
		
		

		if($this->MyMuseShopper->order->order_status == "C"){
			//already confirmed 
			
			$results = $app->triggerEvent('onAfterMyMuseConfirm', 
				array(&$this->shopper, &$this->store, &$this->params, &$this->Itemid) );

			if(is_array($results)){
				foreach($results as $result){
					eval($result);
				}
			}
		
			if($this->MyMuseShopper->order->downloadable){
				//save the download page
				$this->input->set('task', 'downloads');
				$this->input->set('id', $this->MyMuseShopper->order->order_number);
				ob_start();
				$this->downloads();
				$download_page = ob_get_contents();
				ob_end_clean();
				$this->input->set('download_page',$download_page);
			}
			
			$this->input->set('task', 'vieworder');
			$this->input->set('view', 'cart');
			$this->input->set('layout', 'cart');
			$this->display();
			
		}else{

			$this->input->set('task', 'vieworder');
			$this->input->set('view', 'cart');
			$this->input->set('layout', 'cart');
			$this->display();
		}
	}

	/**
	 * vieworder
	 * display an order
	 *
	 */
	public function vieworder()
	{
		//get order
		$db 		= Factory::getDBO();
		$user		= Factory::getUser();
		$user_id 	= $user->get('id');
		$orderid 	= $this->input->get('orderid', 0);
		$session 	= Factory::getSession();
		$order_number = $session->get("order_number",0);
		$st 		= $this->input->get('st', 0);
		$this->params 	= MyMuseHelper::getParams();
		
		if(!$user_id ){
			// not a user!!
			if($this->params->get('my_registration') == "no_reg"){
				$msg = Text::_("JGLOBAL_AUTH_ACCESS_DENIED");;
				$this->setRedirect( Route('index.php'), $msg );
			}else{
				$msg = Text::_("COM_MYMUSE_PLEASE_LOGIN_OR_REGISTER");;
        		$rpage = strtolower($this->params->get('my_registration_redirect','login'));
        		$this->setRedirect( Route::_('index.php?option=com_users&view='.$rpage.'&return='.$return), $msg );
            return false;
			}
		}

		if($order_number && !$orderid){
			$q = "SELECT id from #__mymuse_order WHERE order_number='".$order_number."' ORDER BY id DESC";
			$db->setQuery($q);
			$orderid = $db->loadResult();
		}
		
		$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder( $orderid );

		if(intval($this->MyMuseShopper->order->user_id) != intval($user_id) ){
			// not the right user!!
			$msg = Text::_("COM_MYMUSE_USER_ORDER_OWNER_MISMATCH");
			$this->setRedirect("index.php", $msg);
			return false;
		}
		
		if($this->MyMuseShopper->order->downloadable
				&& $this->MyMuseShopper->order->order_status == "C"){
			//save the download page
				$this->input->set('task', 'downloads');
				$this->input->set('id', $this->MyMuseShopper->order->order_number);
				ob_start();
				$this->downloads();
				$download_page = ob_get_contents();
				ob_end_clean();
				$this->input->set('download_page',$download_page);
		}		
		$this->input->set('task', 'vieworder');
		$this->input->set('view', 'cart');
		$this->input->set('layout', 'cart');
		$this->display();
	}
		 
	/**
	 * paycancel
	 * cancel a payment
	 *
	 */
	public function paycancel()
	{
		$this->params 	= MyMuseHelper::getParams();
		
		if($this->params->get('my_saveorder') == "after"){
			//there won't be an order
		}else{
			// get order
			$db = Factory::getDBO ();
			$user = Factory::getUser ();
			$user_id = $user->get ( 'id' );
			$id = $this->input->get ( 'id', 0 );
			$session = Factory::getSession ();
			$order_number = $session->get ( "order_number", 0 );
			
			if ($order_number) {
				$q = "SELECT id from #__mymuse_order WHERE order_number='" . $order_number . "' ORDER BY id DESC";
				$db->setQuery ( $q );
				$id = $db->loadResult ();
			}
			
			$this->MyMuseShopper->order = $this->MyMuseCheckout->getOrder ( $id );
			
			if ($this->MyMuseShopper->order->user_id != $user_id) {
				// not the right user!!
				$msg = Text::_ ( "COM_MYMUSE_USER_ORDER_OWNER_MISMATCH" );
				$this->setRedirect ( "index.php", $msg );
				return false;
			}
		}
		$this->input->set('view', 'cart');
		$this->input->set('layout', 'cart');
		$this->display();
	}

		 

	/**
	 * downloads
	 * display downloads page
	 *
	 */
	public function downloads()
	{
		
		$shopper = $this->shopper;
		$uri = Uri::getInstance();
		$current = $uri->toString();

		if(!$shopper->perms){
			$url = $current;;
			$return = base64_encode($url);
			$msg = Text::_("COM_MYMUSE_PLEASE_LOGIN_OR_REGISTER");;
			$rpage = strtolower($this->params->get('my_registration_redirect','login'));
        	$this->setRedirect( 'index.php?option=com_users&view='.$rpage.'&return='.$return, $msg );
			return false;
		}

		$this->input->set('view', 'store');
		$this->input->set('layout', 'store');
		$this->display();
		return true;
	}
	
	/**
	 * accdownloads
	 * downloads for no registration
	 *
	 */
	public function accdownloads()
	{	
		$this->input->set('task', 'downloads');
		$this->input->set('view', 'store');
		$this->input->set('layout', 'store');
		$this->display();
		return true;
	}
		 
	/**
	 * downloadfile
	 * get the file and send it back
	 *
	 */
	public function downloadfile()
	{
		
		$shopper =  $this->MyMuseShopper->getShopper();
		if(!isset($shopper->perms)){
			$url = URI::root()."index.php?option=com_mymuse&view=cart&layout=cart";
			$return = base64_encode($url);
			$msg = Text::_("COM_MYMUSE_PLEASE_LOGIN_OR_REGISTER");;
			$rpage = strtolower($this->params->get('my_registration_redirect','login'));
        	$this->setRedirect( 'index.php?option=com_users&view='.$rpage.'&return='.$return, $msg );
			return false;
		}
		$this->input->set('view', 'store');
		$this->input->set('layout', 'store');
		if(!$this->display()){
			$id = $this->input->get('id',0);
			$msg = $this->input->get('msg',0);
			if($this->params->get('my_registration') == "no_reg"){
				$current =  Route::_("index.php?option=com_mymuse&view=cart&task=accdownloads&id=".$id."&item_id=");
			}else{
				$current =  Route::_("index.php?option=com_mymuse&view=cart&task=downloads&id=".$id."&item_id=");
			}
			$this->setRedirect( $current, $msg );
			return false;
		}
		return true;
	}

	/**
	 * notify
	 * catch the post from the payment processor, return required responses, update orders and do mailouts
	 * 
	 */
	public function notify()
	{
		$this->input->set('view', 'cart');
		$this->input->set('layout', 'cart');
		$this->display();

	}
	
	public function makemail()
	{
		$this->input->set('view', 'cart');
		$this->input->set('layout', 'cart');
		$this->display();
	
	}
	/**
	 * rate
	 * Store a vote on a product
	 * 
	 */
	public function rate()
	{
		$db = Factory::getDBO();
		$index = $this->input->get('index', '');
		$productid = $this->input->get('productid', '');
		$rating = $this->input->get('user_rating', '');
		$url = $this->input->get('url', '');

		$query = "SELECT id FROM #__mymuse_product WHERE id='$productid'";
		$db->setQuery($query);
		$id = $db->loadResult();

		$model = $this->getModel('product');
		if ($model->storeVote($id, $rating)) {
			$data = Text::_('MYMUSE_PRODUCT_VOTE_SUCCESS');
		} else {
			$data = Text::_('MYMUSE_PRODUCT_VOTE_FAILURE');
		}

		$this->setRedirect($url, $data);

		return true;
		
	}
	
	/**
	 * rateajax
	 * store a vote on a product using ajax
	 *
	 */
	public function rateajax()
	{
		$db = Factory::getDBO();
		$index = $this->input->get('index', '');
		$cat_prod = $this->input->get('cat_prod', '');
		/**
		Array
		(
				[option] => com_mymuse
				[task] => rateajax
				[index] => 0
				[cat_prod] => Iron Brew: Are You My Sister
				[rating] => 3
				[title] => Are You My Sister (3.33 MB)
				[Itemid] =>
				[return] => L215bXVzZXRlc3QyNS9pbmRleC5waHA/b3B0aW9uPWNvbV9teW11c2UmdGFzaz1yYXRlYWpheA==
		)
		*/
		list($cat,$prod) = explode(":",$cat_prod);
		$cat = trim($cat);
		$prod= trim($prod);
		$rating = $this->input->get('rating', '');
	
	
		$query = "SELECT id FROM #__mymuse_product WHERE title='$prod'";
		$db->setQuery($query);
		$id= $db->loadResult();
		if(!$id){
			exit;
		}
	
		$model = $this->getModel('product');
		if ($model->storeVote($id, $rating)) {
			$data = Text::_('MYMUSE_PRODUCT_VOTE_SUCCESS');
		} else {
			$data = Text::_('MYMUSE_PRODUCT_VOTE_FAILURE');
		}
	
	
		$document = Factory::getDocument();
		$document->setMimeEncoding('text/html');
		JResponse::setHeader("Expires", "Sun, 19 Nov 1978 05:00:00 GMT");
		JResponse::setHeader("Last-Modified",  gmdate("D, d M Y H:i:s") . " GMT");
		JResponse::setHeader("Cache-Control", "no-store, no-cache, must-revalidate");
		JResponse::setHeader("Cache-Control", "post-check=0, pre-check=0", false);
		JResponse::setHeader("Pragma", "no-cache");
	
		echo $data;
		exit;
	
	}

	/**
	 * ajaxupdatelicence
	 * 
	 *
	 */
	public function ajaxupdatelicence()
	{
		$input = Factory::getApplication()->input;
		$my_licence  = $input->get('my_licence', 0);
		$session = Factory::getSession();
	
		$session->set("my_licence",$my_licence);
		$order = $this->MyMuseCart->buildOrder(0,1);
		$msg = Text::_('MYMUSE_LICENCE_UPDATED');
		unset($order->update_form);
		unset($order->update_url);
		foreach($order->items as $item){
			unset($item->parent);
			unset($item->artist);
			unset($item->_upload_errors);
			unset($item->metadata);
		}
		$data = array(
			'msg'=>  $msg,
			'order'=>$order,
		);
		//echo "licence = $my_licence";
		//save the cart in the session
		$session->set("cart",$this->MyMuseCart->cart);
	
		$rand = JUserHelper::genRandomPassword(8);
		$document = Factory::getDocument();
		$document->setMimeEncoding('application/json');
		JResponse::setHeader("Expires","Sun, 19 Nov 1978 05:00:00 GMT");
		JResponse::setHeader("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
		JResponse::setHeader("Cache-Control", "no-store, no-cache, must-revalidate");
		JResponse::setHeader("Cache-Control", "post-check=0, pre-check=0", false);
		JResponse::setHeader("Pragma", "no-cache");
		JResponse::setHeader('Content-Disposition','attachment;filename="coupon_'.$rand .'.json"');
		echo json_encode($data);
		exit;
	}
	/*
	 * send_ipn
	*
	* For testing pesapal, url must have pesapal_merchant_reference
	*/
	public function send_ipn()
	{
		$pesapalNotification="CHANGE";
		$pesapalTrackingId=md5(time());
		$pesapal_merchant_reference=$_GET['pesapal_merchant_reference'];
		$url = 'index.php?option=com_mymuse&task=notify';
		$url .= "&pesapal_notification_type=$pesapalNotification";
		$url .= "&pesapal_merchant_reference=$pesapal_merchant_reference";
		$url .= "&pesapal_transaction_tracking_id=$pesapalTrackingId";
		$this->setRedirect( $url);
		return false;
	}
	
	/*
	 * send_status
	*
	* For testing pesapal
	*/
	
	public function send_status()
	{
		echo "STATUS=CONFIRMED";
		exit;
	
	}
		
		
}
