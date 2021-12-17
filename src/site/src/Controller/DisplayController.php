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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Http\Response;
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

/**
 * Mymuse Component Controller
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{

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
	 * MyMuseStore object ref
	 *
	 * @var object
	 */
	var $MyMuseStore = null;


	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
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
		$Doc = Factory::getDocument();
		$Doc->addStyleSheet( Uri::base() . 'components/com_mymuse/assets/css/mymuse.css' );

		parent::__construct($config, $factory, $app, $input);

		$this->MyMuseCart		= $this->getObject('Cart');
		$this->MyMuseShopper	= $this->getObject('Shopper','model');
		$this->MyMuseStore		= $this->getObject('Store', 'model');

	}
	/**
	 * Returns a reference to a global MyMuse object, only creating it if it
	 * doesn't already exist. The default is to look in the helpers directory.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $MyMuseStore 	=& MyMuse::getObject('Store','model');</pre>
	 *
	 * @access	public
	 * @param	string	$client		type of class.
	 * @param	string	$type 		An optional type, default helper
	 * @param	array	$config 	An optional associative array of configuration settings.
	 * @return	MyMuse	The MyMuse object.
	 * @since	1.5
	 */
	static function &getObject($client, $type='helper', $config = array(), $renew = '')
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
			'Itemid' => 'INT');


		$task  = $this->input->getInt('task');
		if('ajaxtogglecart'== $task){
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
		$jinput = $app->input;
		$productid  = $jinput->get('productid', 0, 'int');
        $variation  = $jinput->get('variation', 0, 'ARRAY');
		if(!$productid ){
			$data = array();
		}else{

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
				$msg = JText::_("MYMUSE_DELETED")." ".$title;
				$action = "deleted";
			}else{
				//let us add it
				if(!$this->MyMuseCart->addToCart()){
					$msg = $this->MyMuseCart->getError();
					$action = "failed";
				}else{
					$msg = JText::_("MYMUSE_ADDED")." ".$title;
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
		$session = Factory::getSession();
		$session->set("cart",$this->MyMuseCart->cart);
	
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
		$jinput = JFactory::getApplication()->input;
		$productid  = $jinput->get('productid', 0, 'int');
		$variation  = $jinput->get('variation', 0, 'ARRAY');
		if(!$productid ){
			$data = array();
		}else{
			$order_subtotal = 0.00;
			$product_item_subtotal = 0.00;
			$product_item_price = 0.00;
			$tax_total = 0.00;
			
			
			$db = JFactory::getDBO();
			$query = "SELECT title from #__mymuse_product WHERE id =$productid";
			$db->setQuery($query);
			$title = $db->loadResult();
			
			if(!$this->MyMuseCart->updateCart()){
				$msg = "ERROR: ".$this->MyMuseCart->error;
			}
			$order = $this->MyMuseCart->buildOrder(0,1);
			$order_subtotal = $order->order_subtotal;
			
			//print_pre($order);
			$tax_total = $order->tax_total;
			
			for ($i=0;$i<count($order->items);$i++) {
				if($order->items[$i]->id  == $productid){
					$product_item_subtotal = $order->items[$i]->product_item_subtotal;
					$product_item_price = $order->items[$i]->product_item_price;
					$msg = JText::_('MYMUSE_UPDATED_CART');
				}
				$msg = JText::_('MYMUSE_UPDATED_CART');
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
		$session = JFactory::getSession();
		$session->set("cart",$this->MyMuseCart->cart);
	
		$rand = JUserHelper::genRandomPassword(8);
		$document = JFactory::getDocument();
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
		
}
