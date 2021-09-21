<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\View\order;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Mymuse\Administrator\Model\OrderModel;

/**
 * View to edit an order.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The Form object
	 *
	 * @var    Form
	 * @since  1.5
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $state;

	/**
	 * The model lists
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $lists;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null): void
	{
		/** @var MymuseModel $model */
		$model       = $this->getModel();
		$this->form  = $model->getForm();
		$this->item  = $model->getItem();
		$this->state = $model->getState();
		$this->lists = $this->get('Lists');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		switch ($task)
		{
			case "mailcustomer":
			{
				include_once( JPATH_SITE.DS.'components'.DS.'com_mymuse'.DS.'mymuse.class.php' );
				$MyMuseStore	=& MyMuse::getObject('store','models');
				$this->store			= $MyMuseStore->getStore();
				
				// Process order plugins
    			$dispatcher				= JDispatcher::getInstance();
    			$extra 					= '';
				JPluginHelper::importPlugin('system');
				$results 				= $dispatcher->trigger('onRenderOrder', array ( ));
				if(isset($results[0])){
					$extra = $results[0];
				}
				$this->extra = $extra;


				JPluginHelper::importPlugin('mymuse');
				$results 				= $dispatcher->trigger('onAfterMyMusePayment', array() );
				$my_email_msg 			= '';
				foreach($results as $res){
					$arr = explode(":",$res);
				    $p = array_shift($arr);
				    $my_email_msg .= implode(":",$arr);
				}
				$this->my_email_msg = $my_email_msg;

				$shopper = $this->item->user;
				@list($shopper->first_name,$shopper->last_name) = explode(" ",$shopper->name);
				$shopper->address1 		= isset($shopper->profile['address1'])? $shopper->profile['address1'] : '';
				$shopper->address2 		= isset($shopper->profile['address2'])? $shopper->profile['address2'] : '';
				$shopper->city 			= isset($shopper->profile['city'])? $shopper->profile['city'] : '';
				$shopper->region 		= isset($shopper->profile['region'])? $shopper->profile['region'] : '';
				$shopper->region 		= isset($shopper->profile['region_name'])? $shopper->profile['region_name'] : $shopper->region;
				$shopper->postal_code 	= isset($shopper->profile['postal_code'])? $shopper->profile['postal_code'] : '';
				$shopper->country		= isset($shopper->profile['country'])? $shopper->profile['country'] : '';
				$shopper->phone			= isset($shopper->profile['phone'])? $shopper->profile['phone'] : '';
				$shopper->mobile		= isset($shopper->profile['mobile'])? $shopper->profile['mobile'] : '';
				$shopper->fax			= isset($shopper->profile['fax'])? $shopper->profile['fax'] : '';
				$this->shopper 			= $shopper;

				$currency = MyMuseHelper::getCurrency($this->item->order_currency);
        		$this->item->currency = $currency;
        		$this->item->do_html = 0;
        		
				$this->params->set('my_currency_code',$currency['currency_code']);
				$this->params->set('my_currency_symbol',$currency['symbol']);

				parent::display('customer');
				
			} break;
			
			default:
			{
				$this->addToolbar();
				parent::display($tpl);
			}
		}
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  Exception
	 */
	protected function addToolbar(): void
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$input  = JFactory::getApplication()->input;
		$input->set('hidemainmenu', true);

		$user       = Factory::getUser();
		$userId     = $user->id;
		$isNew      = ($this->item->id == 0);
		$checkedOut = !(is_null($this->item->checked_out) || $this->item->checked_out == $userId);

		$canDo = ContentHelper::getActions('com_mymuse', 'ORDER', $this->item->id);

		ToolbarHelper::title($isNew ? Text::_('COM_MYMUSE_ORDER_NEW') : Text::_('COM_MYMUSE_ORDER_EDIT'), 'bookmark ORDERs');

		$toolbarButtons = [];

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || $canDo->get('core.create')) )
		{
			ToolbarHelper::apply('order.apply');
			$toolbarButtons[] = ['save', 'ORDER.save'];

		}


		ToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('order.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('order.cancel', 'JTOOLBAR_CLOSE');

		}

		ToolbarHelper::divider();
		ToolbarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/order-view?tmpl=component');
	}
}
