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

namespace Joomla\Component\Mymuse\Site\View\Shopper;

\defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Helper\AssociationHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Site\Helper\CartHelper;
use Joomla\Component\Mymuse\Site\Model\StoreModel;
use Joomla\Component\Mymuse\Site\Service\Mymuse;

class HtmlView extends BaseHtmlView
{

	/**
	 * MyMuseShopper object ref
	 *
	 * @var object
	 */
	var $MyMuseShopper = null;

	/**
	 * shopper object ref
	 *
	 * @var object
	 */
	var $shopper = null;

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
	 * user
	 *
	 * @var object
	 */
	var $user = null;


	function __construct()       {
    	parent::__construct();
    	
        $layout = Factory::getApplication()->input->get('layout', 'register');
        parent::setLayout($layout);  

        $this->MyMuseShopper  	=& Mymuse::getObject('Shopper','model');
        $this->MyMuseShopper  	=& Mymuse::getObject('Shopper','model');
        $this->shopper 			= $this->MyMuseShopper->getShopper();
        $this->MyMuseCart  		= Mymuse::getObject('cart','helper');
        $this->MyMuseStore  	= Mymuse::getObject('store','model');
        $this->store 			= $this->MyMuseStore->_store;
        $this->user				= Factory::getUser();

    }
        
	function display($tpl = null){

		$jinput 		= Factory::getApplication()->input;
		$app 			= Factory::getApplication();
		$params 		= MyMuseHelper::getParams();
		$return 		= $jinput->get('return','');
		
		// Get the view data.
		$this->data		= $this->get('Data');
		$this->form		= $this->get('Form');
		$this->state	= $this->get('State');
		$this->params	= $this->state->get('params');

		//MyMuseHelper::print_pre($this->shopper); exit;
		if(!$this->shopper->id && $this->user->get('id')){
			// not a shopper but already user
			// try to make first and last names
			list($shopper->first_name, $shopper->last_name) = explode(" ", $this->user->get('name'), 2);

		}
	
		$this->pageclass_sfx = $this->params->get('pageclass_sfx')? htmlspecialchars($this->params->get('pageclass_sfx')) : '';
		
		$document		= Factory::getDocument();
		$pathway		= $app->getPathway();
		$Itemid			= $jinput->get('Itemid', 0, 'INT');
		
    	$this->Itemid = $Itemid;
		$this->params = $params;
		$this->return = $return;

		

		if($this->getLayout() == "thank_you"){
			$st 		= $jinput->get('st', 0);
			$heading 	= Jtext::_('COM_MYMUSE_THANK_YOU');
			$message 	= Jtext::_('COM_MYMUSE_WE_HAVE_RECEIVED_YOUR_ORDER');

			if(isset($this->MyMuseShopper->order->payments[0]->plugin) && $this->MyMuseShopper->order->payments[0]->plugin == "paypal"){
				$message .= Jtext::_('COM_MYMUSE_PAYPAL_THANKYOU');
			}

			$link 		= "index.php?option=com_mymuse&task=vieworder&orderid=";
			$link 		.= $this->MyMuseShopper->order->id;

			
			if($Itemid){
				$link 		.= "&Itemid=$Itemid";
			}
			if($st){
				$link 		.= "&st=$st";
			}
			$message 	= $message.'<br /><a href="'.$link.'">'.Jtext::_('COM_MYMUSE_HERE_IS_YOUR_ORDER').'</a>';
			$this->heading = $heading;
			$this->message = $message;
			parent::display();
			return true;
		}
		if($this->getLayout() == "waiting"){
			$heading 	= Jtext::_('COM_MYMUSE_THANK_YOU');
			$link 		= "index.php?option=com_mymuse&task=thankyou";
		
			if($Itemid){
				$link 		.= "&Itemid=$Itemid";
			}
			$link 		.= "&st=10";
			$message 	= '<a href="'.$link.'">'.Jtext::_('COM_MYMUSE_CHECK_ORDER_WAITING').'</a>';
				
			$this->heading = $heading;
			$this->message = $message;
			$this->setLayout("thank_you");
			parent::display();
			return true;
				
		}
		if($this->getLayout() == "listorders"){
			
			if($params->get('my_registration') == "no_reg"){
				return false;
			}

			$orders = $this->MyMuseShopper->getOrders();
			
			$this->orders = $orders;
			parent::display();
			return true;
		}
		if($this->getLayout() == "edit" && $params->get('my_registration') == "no_reg"){
			return false;

		}
		$continue = 1;
		if(!$this->MyMuseCart->cart['idx']){
			$continue = 0;
		}
		$this->continue = $continue; 

		parent::display($tpl);

	}

}
?>