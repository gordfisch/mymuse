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


use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Mymuse\Site\Service\Mymuse;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Administrator\Model\OrderModel;
use Joomla\Component\Mymuse\Site\Model\StoreModel;
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
	    	$input 		= Factory::getApplication()->input;
	    	$input->set('jform[checked_out]','0');
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
						(isset($form['update_on_confirm']) && $form['update_on_confirm'] == "1") ){

						$db = Factory::getContainer()->get('DatabaseDriver');

						$query = "SELECT oi.*, p.product_physical, p.title, p.product_in_stock as product_product_in_stock FROM #__mymuse_order_item as oi 
						LEFT JOIN #__mymuse_product 
						as p on p.id=oi.product_id
						WHERE order_id=$id AND p.product_physical=1";
						$db->setQuery($query);
						$items = $db->loadObjectList();
				
						foreach($items as $item){
							//see if we can update stock
							if (!MymuseHelper::updateStock($item->product_id, $item->product_quantity)) {

								$msg = $item->title.' '.Text::_('COM_MYMUSE_EXCEEDS_AVAILABLE_STOCK').' ';
								$msg .= Text::_('COM_MYMUSE_AVAILABLE_STOCK')." ".$item->product_product_in_stock;
								$this->setRedirect( 'index.php?option=com_mymuse&view=order&task=order.edit&id='.$id, $msg, 'error' );
								return false;
							}
						}

					}
					$this->mail_client();
					$this->msg .= Text::_( 'COM_MYMUSE_EMAILED_CUSTOMER' )." ";

				}
				if($task == 'apply'){
	
					$this->msg .= Text::_( 'COM_MYMUSE_ORDER_SAVED' ).$model->getError();
	        		$this->setRedirect( 'index.php?option=com_mymuse&view=order&task=order.edit&id='.$id, $this->msg );
				}else{
					$this->msg .= Text::_( 'COM_MYMUSE_ORDER_SAVED' ).$model->getError();
					$this->setRedirect( 'index.php?option=com_mymuse&view=orders', $this->msg );
				}
				
			}else{
				$this->msg = Text::_( 'COM_MYMUSE_ERROR_SAVING_ORDER' ).$model->getError();
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
			$input 		= Factory::getApplication()->input;
	    	$post 		= $input->post->getArray();
			$form 		= $post['jform'];
			$id 		= $input->get('id','');

			$params 	= MyMuseHelper::getParams();


			if($params->get('my_debug')){
				$debug = "OrderController: mail_client: \n";
				$debug .= "ORDER: $id \nSTATUS: ".$form['order_status']."\n" ;
				MyMuseHelper::logMessage( $debug  );
			}

			$input->set( 'view', 'order' );
			$input->set( 'task', 'mailcustomer'  );

			$MyMuseStore  	=& Mymuse::getObject('store','model');
			$store 			= $MyMuseStore->getStore();
			$model 			= $this->getModel();
			$order			= $model->getItem();

			$language = Factory::getLanguage();
			$language_tag = $language->get('lang');

			$extension = 'com_mymuse';
			$base_dir = JPATH_SITE;
			
			$language->load($extension, $base_dir, $language_tag, true);
			$this->subject = Text::_('COM_MYMUSE_ORDER_STATUS_CHANGED');

			$input->set( 'layout', 'order');
			ob_start();
			$this->display();
			$message= ob_get_contents();
			ob_end_clean();

			//if using no_reg
			if($params->get('my_registration') == "no_reg"){
				$registry = new Registry;
				$registry->loadString($order->notes);
			}

			// SEND MAIL TO BUYER
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
	     	$recipient = $order->user->email;
	     	if($params->get('my_cc_webmaster')){
	     		$recipient = array($order->user->email, $params->get('my_webmaster'));
	     	}
	     	$mailer->addRecipient($recipient);
	     	//subject, body
	     	$subject = Text::_('COM_MYMUSE_ORDER_STATUS_CHANGED')." ".$store->title;
			$subject = html_entity_decode($subject, ENT_QUOTES,'UTF-8');
	     	$mailer->setSubject($subject);
	     	$mailer->setBody($message);

	     	$send = $mailer->Send();
	     	if ( $send !== true ) {
	     		Factory::getApplication()->enqueueMessage( 'Error sending email: ' . $send->getError() );
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
			$input 			= Factory::getApplication()->input;
			$id 			= $input->get('id',0);
			$order_item_id 	= $input->get('item_id',0);
			$MyMuseHelper 	= new MyMuseHelper;

			if(!$id){
				Factory::getApplication()->enqueueMessage(Text::_("COM_MYMUSE_CANNOT_SHIP_NO_ORDER_ID"), 'error');
				$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='. $id );
				return false;
			}
			if(!$order_item_id){
				Factory::getApplication()->enqueueMessage(Text::_("COM_MYMUSE_CANNOT_SHIP_NO_ORDER_ITEM_ID"), 'error');
				$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='. $id );
				return false;
			}
			$db = Factory::getContainer()->get('DatabaseDriver');
			$query = "SELECT * FROM #__mymuse_order_item WHERE id=$order_item_id";
			$db->setQuery($query);
			$order_item = $db->loadObject();

			//see if we can update stock
			if (!$MyMuseHelper->updateStock($order_item->product_id, $order_item->product_quantity)) {
				$query = "SELECT product_in_stock, title FROM #__mymuse_product WHERE id=".$order_item->product_id;
				$db->setQuery($query);
				$res = $db->loadObject();
				$msg = $res->title.' '.Text::_('COM_MYMUSE_EXCEEDS_AVAILABLE_STOCK').' ';
				$msg .= Text::_('COM_MYMUSE_AVAILABLE_STOCK')." ".$res->product_in_stock;
				$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='. $id, $msg, 'error' );
				return false;
			}

			
			$query = "UPDATE #__mymuse_order_item SET product_in_stock = 1 WHERE id=$order_item_id";

			$db->setQuery($query);
			if(!$db->execute()){
				$error = $db->getErrorMsg();
				Factory::getApplication()->enqueueMessage(Text::_("COM_MYMUSE_CANNOT_SHIP_DB_ERROR") .$error, 'error');
				$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='. $id );
				return false;
			}

			$this->mail_client();
			
			Factory::getApplication()->enqueueMessage(Text::_("COM_MYMUSE_BACKORDER_REMOVED"), 'message');
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
		$model = $this->getModel('Order', '', array());

		// Preset the redirect
		$this->setRedirect(Route::_('index.php?option=com_mymuse&view=orders' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}


    /**
     * alertPreorders: reset downloads and email customer for each order with a preorder for this product
     *
     * @access    public
     */
    function alertPreorders()
    {   

        $db = Factory::getContainer()->get('DatabaseDriver');
        $input = Factory::getApplication()->input;
        $id = $input->get( 'id','','INT' );
        $app = Factory::getApplication();
        
        // find all product ids
        $query = "SELECT id from #__mymuse_product WHERE parentid=$id";
        
        $db->setQuery($query);
        $res = $db->loadObjectList();
        $IN = array($id);
        foreach($res as $prod) {
            $IN[] = $prod->id;
        }
        
        $IN = implode(",",$IN);
        
        $query = "SELECT order_id 
        FROM #__mymuse_order_item as i
        LEFT JOIN #__mymuse_order as o on o.id=i.order_id
        WHERE i.product_id IN ($IN) 
        AND i.product_in_stock='-1' 
        AND o.order_status = 'C'
        GROUP BY i.order_id";


        $db->setQuery($query);
        $orders = $db->loadObjectList();

        foreach($orders as $order){
            $input->set( 'id',$order->order_id );
            if($this->resetDownloads()){
                $app->enqueueMessage("Sent notice to order with id ".$order->order_id , 'message');
            }else{
                $app->enqueueMessage("Problem with order with id ".$order->order_id , 'warning');
            }
        }
        
        $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=edit&id='.$id);
        return true;
        
    }
	/**
	 * reset_downloads
	 * method to rest the downloads for a customer, then mail them the download link display
	 * 
	 * @return void
	 */
	function resetDownloads()
	{
		$params = MyMuseHelper::getParams();
		$app 	= Factory::getApplication();
		//reset
		$model = new OrderModel;

		$input 	= Factory::getApplication()->input;
		$id 	= $input->get( 'id','' );

		if(!isset($id)){
			$this->msg = JTex::_('COM_MYMUSE_ORDER_ID_NOT_FOUND');
			$app->enqueueMessage($this->msg, 'error');
			$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id=' );
			return false;
		}

		if(!$model->resetDownloads()){
			$this->msg = $model->getError();
			$app->enqueueMessage($this->msg, 'error');
			$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='.$id);
			return false;
		}

		//email customer
		$date = date('Y-m-d h:i:s');
		if($params->get('my_debug')){
			$debug = "OrdeController:resetDownloads: \n";
			$debug .= "ORDER: ".$id. "\nSTATUS: C\n" ;
			MyMuseHelper::logMessage( $debug  );
		}


		$input->set( 'task', 'mailcustomer');
		$input->set( 'layout', 'order');
		
		$MyMuseStore	= Mymuse::getObject('store','model');
		$store			= $MyMuseStore->getStore();
		$params 		= new Registry( $store->params );
	
		$language 		= Factory::getLanguage();
		$extension 		= 'com_mymuse';
		$base_dir 		= JPATH_SITE;

		$language->load($extension, $base_dir, 'en-GB', true);
		$language->load($extension, $base_dir, null, true);
	
		$this->order = $order = $model->getItem($id);

		ob_start();
		$this->display();
		$message .= ob_get_contents();
		ob_end_clean();

		$order = $model->getItem($id);
	
		$user_email 	= $order->user->email;
	
		// SEND MAIL TO BUYER
		$this->subject = text::_('COM_MYMUSE_ORDER_STATUS_CHANGED')." ".$store->title;
		$subject = html_entity_decode($this->subject, ENT_QUOTES,'UTF-8');
	
		$fromname = $params->get('contact_first_name')." ".$params->get('contact_last_name');
		$mailfrom = $params->get('contact_email');
		
		$mailer = Factory::getMailer();
		$mailer->isHTML(true);
		$mailer->setSender(array($mailfrom,$fromname));
		$mailer->addRecipient($user_email);
		$mailer->setSubject($subject);
		$mailer->setBody($message);
		$send = $mailer->Send();
		if ( $send !== true ) {
			echo 'Error sending email: ' . $send->getError();
		}
		
		//redirect to edit page
		$this->msg = "Downloads Reset for Order with id $id";
		$this->setRedirect( 'index.php?option=com_mymuse&view=order&layout=edit&id='.$id, $this->msg);
		return true;
	
	}


}