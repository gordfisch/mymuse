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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;

/**
* MyMuse PaymentOffline plugin
*
* @package 		MyMuse
* @subpackage	mymuse
*/
class plgMymusePayment_Offline extends CMSPlugin 
{
	
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
		
		
	/**
	 * Offline Payment method
	 *
	 */
	function onBeforeMyMusePayment($shopper, $store, $order, $params, $Itemid )
	{

		$string = '';
		if($params->get('my_saveorder') == "after"){
			return $string;
		}
		$string .= '
		<form action="'.JRoute::_('index.php?task=thankyou&view=cart').'" method="post" name="adminFormPayOffline">
		<input type="hidden" name="option" value="com_mymuse">
		<input type="hidden" name="notifyCustomer" value="1">
		<input type="hidden" name="id" value="'.$order->id.'">
		<input type="hidden" name="Itemid" value="'.$Itemid.'">	
		<input type="hidden" name="pp" value="payoffline">
		<div id="payoffline_form" class="pull-left">
		<button id="offline" class="btn btn-primary " type="submit" >'. Text::_('MYMUSE_I_WILL_PAY_OFFLINE').'</button>

		</div>
		</form>
		';
		if ($this->params->get('paymentoffline_msg') != ""){
			$string .= "<br />".Text::_($this->params->get('paymentoffline_msg'));
		}
		return $string;
	
	}
	
	function onAfterMyMusePayment()
	{
	
		$email_msg = '';
		if($this->params->get('email_msg')){
			$email_msg = "payoffline:".preg_replace("/\\n/","<br />",$this->params->get('email_msg'));
		
		}
		return $email_msg;
	
	}
} ?>