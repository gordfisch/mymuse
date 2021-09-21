<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Mymuse\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;

/**
 * Ordercontroller class.
 *
 * @since  1.6
 */
class OrderController extends FormController
{
	use VersionableControllerTrait;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_MYMUSE_ORDER';


		function save($key = NULL, $urlVar = NULL)
		{
	    	$input 		= JFactory::getApplication()->input;
	    	$post 		= $input->post->getArray();
			$form 		= $post['jform'];
			$old_status = $input->get('old_status','');
			$id 		= $input->get('id','');
			$task	 	= $input->get('task','save');
			$params     = MyMuseHelper::getParams();

		    $model = $this->getModel();
		    $this->msg = '';
			if(parent::save()){
				if($old_status != $form['order_status'])
				{
					
					//should we update stock here? 
					if ($form['order_status'] == "C" && $params->get('my_use_stock') && 
						(isset($form['update_on_confirm']) && $form['update_on_confirm'] == "on") ){

						$db = JFactory::getDBO();
						$MyMuseHelper = new MyMuseHelper;

						$query = "SELECT oi.*, p.product_physical, p.title, p.product_in_stock as product_product_in_stock FROM #__mymuse_order_item as oi 
						LEFT JOIN #__mymuse_product 
						as p on p.id=oi.product_id
						WHERE order_id=$id AND p.product_physical=1";
						$db->setQuery($query);
						$items = $db->loadObjectList();
				
						foreach($items as $item){
							//see if we can update stock
							if (!$MyMuseHelper->updateStock($item->product_id, $item->product_quantity)) {

								$msg = $item->title.' '.JText::_('COM_MYMUSE_EXCEEDS_AVAILABLE_STOCK').' ';
								$msg .= JText::_('COM_MYMUSE_AVAILABLE_STOCK')." ".$item->product_product_in_stock;
								$this->setRedirect( 'index.php?option=com_mymuse&view=order&task=order.edit&id='.$id, $msg, 'error' );
								return false;
							}
						}

					}
					$this->mail_client();
					$this->msg .= JText::_( 'COM_MYMUSE_EMAILED_CUSTOMER' )." ";

				}
				if($task == 'apply'){
					$this->msg .= JText::_( 'COM_MYMUSE_ORDER_SAVED' ).$model->getError();
	        		$this->setRedirect( 'index.php?option=com_mymuse&view=order&task=order.edit&id='.$id, $this->msg );
				}else{
					$this->msg .= JText::_( 'COM_MYMUSE_ORDER_SAVED' ).$model->getError();
					$this->setRedirect( 'index.php?option=com_mymuse&view=orders', $this->msg );
				}
				
			}else{
				$this->msg = JText::_( 'COM_MYMUSE_ERROR_SAVING_ORDER' ).$model->getError();
	        	$this->setRedirect( 'index.php?option=com_mymuse&view=order&task=order.edit&id='.$id, $this->msg );
			}
		}
		

		/**
		 * mail_client
		 * 
		 * produce email of order and send to client
		 * @return void
		 */
		function mail_client()
		{        	
			// let's send mail about the change
			$input 		= JFactory::getApplication()->input;
	    	$post 		= $input->post->getArray();
			$form 		= $post['jform'];
			$id 		= $input->get('id','');

			$params 	= MyMuseHelper::getParams();
			$date 		= date('Y-m-d h:i:s');

			if($params->get('my_debug')){
				$debug = $date."\n#####################\nORDER SAVE\n";
				$debug .= "ORDER: $id \nSTATUS: ".$form['order_status']."\n" ;
				MyMuseHelper::logMessage( $debug  );
			}

			$input->set( 'view', 'order' );
			$input->set( 'task', 'mailcustomer'  );


			
			include_once( JPATH_SITE.DS.'components'.DS.'com_mymuse'.DS.'COM_MYMUSE.class.php' );
			$MyMuseStore  	=& MyMuse::getObject('store','models');
			$store 			= $MyMuseStore->getStore();
			$model 			= $this->getModel();
			$order			= $model->getItem();

			$language = JFactory::getLanguage();
			$uparams = $order->user->getParameters();
			$language_tag = $uparams->get('language');
			if(!$language_tag){
				$language_tag = $language->get('lang');
			}
			$extension = 'com_mymuse';
			$base_dir = JPATH_SITE;
			
			$language->load($extension, $base_dir, $language_tag, true);

			
			

			//include_once( JPATH_SITE.DS.'components'.DS.'com_mymuse'.DS.'templates'.DS.'mail_html_header.php' );
			$input->set( 'layout', 'order');
			ob_start();
			$this->display();
			$message= ob_get_contents();
			ob_end_clean();

			//if using no_reg
			if($params->get('my_registration') == "no_reg"){
				$registry = new JRegistry;
				$registry->loadString($order->notes);
			}

			// SEND MAIL TO BUYER
	     	$mailer = JFactory::getMailer();
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
	     	$recipient = $order->user->email;
	     	if($params->get('my_cc_webmaster')){
	     		$recipient = array($order->user->email, $params->get('my_webmaster'));
	     	}
	     	$mailer->addRecipient($recipient);
	     	//subject, body
	     	$subject = Jtext::_('COM_MYMUSE_ORDER_STATUS_CHANGED')." ".$store->title;
			$subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
	     	$mailer->setSubject($subject);
	     	$mailer->setBody($message);

	     	$send = $mailer->Send();
	     	if ( $send !== true ) {
	     		JFactory::getApplication()->enqueueMessage( 'Error sending email: ' . $send->getError() );
	     	}
			$input->set( 'layout', 'edit');
			$input->set( 'task', 'save'  );
			$input->set( 'email_sent', '1' );
		}



		/**
		 * shipitem
		 * 
		 * mark order_item as not backordered any more (product_in_stock = 1)
		 * @return void
		 */
		function shipitem()
		{
			$input 			= JFactory::getApplication()->input;
			$id 			= $input->get('id',0);
			$order_item_id 	= $input->get('item_id',0);
			$MyMuseHelper 	= new MyMuseHelper;

			if(!$id){
				JFactory::getApplication()->enqueueMessage(JText::_("COM_MYMUSE_CANNOT_SHIP_NO_ORDER_ID"), 'error');
				$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='. $id );
				return false;
			}
			if(!$order_item_id){
				JFactory::getApplication()->enqueueMessage(JText::_("COM_MYMUSE_CANNOT_SHIP_NO_ORDER_ITEM_ID"), 'error');
				$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='. $id );
				return false;
			}
			$db = JFactory::getDBO();
			$query = "SELECT * FROM #__mymuse_order_item WHERE id=$order_item_id";
			$db->setQuery($query);
			$order_item = $db->loadObject();

			//see if we can update stock
			if (!$MyMuseHelper->updateStock($order_item->product_id, $order_item->product_quantity)) {
				$query = "SELECT product_in_stock, title FROM #__mymuse_product WHERE id=".$order_item->product_id;
				$db->setQuery($query);
				$res = $db->loadObject();
				$msg = $res->title.' '.JText::_('COM_MYMUSE_EXCEEDS_AVAILABLE_STOCK').' ';
				$msg .= JText::_('COM_MYMUSE_AVAILABLE_STOCK')." ".$res->product_in_stock;
				$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='. $id, $msg, 'error' );
				return false;
			}

			
			$query = "UPDATE #__mymuse_order_item SET product_in_stock = 1 WHERE id=$order_item_id";

			$db->setQuery($query);
			if(!$db->execute()){
				$error = $db->getErrorMsg();
				JFactory::getApplication()->enqueueMessage(JText::_("COM_MYMUSE_CANNOT_SHIP_DB_ERROR") .$error, 'error');
				$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='. $id );
				return false;
			}

			$this->mail_client();
			
			JFactory::getApplication()->enqueueMessage(JText::_("COM_MYMUSE_BACKORDER_REMOVED"), 'message');
			$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='. $id );

		}

	/**
	 * Method to run batch operations.
	 *
	 * @param   string  $model  The model
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function batch($model = null)
	{
		$this->checkToken();

		// Set the model
		$model = $this->getModel('Taxrate', '', array());

		// Preset the redirect
		$this->setRedirect(Route::_('index.php?option=com_mymuse&view=taxrate' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}
}