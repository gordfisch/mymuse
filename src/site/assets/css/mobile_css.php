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

use Joomla\CMS\Language\Text;

//Grab taxes
$MyMuseCart 	=& MyMuse::getObject('cart','helpers');
$cart 			=& $MyMuseCart->cart;
$order 			= $MyMuseCart->buildOrder( 1,0 );
$mobile_style 	= '';

if(isset($order->tax_array) && count($order->tax_array)){
	reset($order->tax_array);
	foreach($order->tax_array as $key => $val){
		$mobile_style .= '.'.strtolower(preg_replace("/_/","", $key)).' {
				align: right;
				}
				';
	}
}

$mobile_style .= '
/* Only Phones */
@media (max-width: 767px) {
	/*
	 Label the data
	 
	 TODO: mytaxname mytax
	*/

	
	.myselect:before { content: "'.Text::_('COM_MYMUSE_SELECT').'";}
	.mytitle:before { content: "'.Text::_('COM_MYMUSE_TITLE').'";}
	.mytime:before { content: "'.Text::_('COM_MYMUSE_TIME').'";}
	.myfilesize:before { content: "'.Text::_('COM_MYMUSE_FILE_SIZE').'";}
	.mydownloads:before { content: "'.Text::_('COM_MYMUSE_DOWNLOADS').'";}
	.myexpiry:before { content: "'.Text::_('COM_MYMUSE_EXPIRES').'";}
	.myprice:before { content: "'.Text::_('COM_MYMUSE_CART_PRICE').'";}
	.mypreviews:before { content: "'.Text::_('COM_MYMUSE_PLAY').'";}
	.myquantity:before { content: "'.Text::_('COM_MYMUSE_QUANTITY').'";}
	.mysku:before { content: "'.Text::_('COM_MYMUSE_CART_SKU').'";}
	.mysubtotal:before { content: "'.Text::_('COM_MYMUSE_CART_SUBTOTAL').'";}
	.myaction:before { content: "'.Text::_('COM_MYMUSE_CART_ACTION').'";}

	.myoriginalsubtotal:before { content: "'.Text::_('COM_MYMUSE_CART_ORIGINAL_SUBTOTAL').'";}
	.myshoppergroupdiscount:before { content: "'.Text::_('COM_MYMUSE_DISCOUNT').'";}
	.mynewsubtotal:before { content: "'.Text::_('COM_MYMUSE_CART_NEW_SUBTOTAL').'";}
	.myshipping:before { content: "'.Text::_('COM_MYMUSE_SHIPPING').'";}
	.mytotal:before { content: "'.Text::_('COM_MYMUSE_TOTAL').'";}
	.myupdatecart:before { content: "'.Text::_('COM_MYMUSE_UPDATE_CART').'";}
	.mycoupon:before { content: "'.Text::_('COM_MYMUSE_YOUR_COUPON').'";}
	
	.myreservationfee:before { content: "'.Text::_('COM_MYMUSE_RESERVATION_FEE').'";}
	.myothercharges:before { content: "'.Text::_('COM_MYMUSE_OTHER_CHARGES').'";}
	.mypaynow:before { content: "'.Text::_('COM_MYMUSE_PAYNOW').'";}
	.myshipmethod:before { content: "'.Text::_('COM_MYMUSE_SHIP_METHOD').'";}
	.mydiscount:before { content: "'.Text::_('COM_MYMUSE_DISCOUNT').'";}
	
	.myimage:before { content: "'.Text::_('COM_MYMUSE_IMAGE').'";}
	.myauthor:before { content: "'.Text::_('JAUTHOR').'";}
	.myhits:before { content: "'.Text::_('JGLOBAL_HITS').'";}
	.mysales:before { content: "'.Text::_('COM_MYMUSE_SALES').'";}
	.mydate-modified:before { content: "'.Text::_('COM_MYMUSE_MODIFIED_DATE').'";}
	.mydate-created:before { content: "'.Text::_('COM_MYMUSE_CREATED_DATE').'";}
	.mydate-published:before { content: "'.Text::_('COM_MYMUSE_PUBLISHED_DATE').'";}
	.mydate-product_made_date:before { content: "'.Text::_('COM_MYMUSE_RELEASE').'";}
	.myartist:before { content: "'.Text::_('COM_MYMUSE_ARTIST').'";}
	
	.myorderid:before { content: "'.Text::_('COM_MYMUSE_ORDER_ID').'";}
	.mydate:before { content: "'.Text::_('COM_MYMUSE_DATE').'";}
	.myorderstatus:before { content: "'.Text::_('COM_MYMUSE_ORDER_STATUS').'";}
	.myordernumber:before { content: "'.Text::_('COM_MYMUSE_ORDER_NUMBER').'";}
	.myorderdate:before  { content: "'.Text::_('COM_MYMUSE_ORDER_DATE').'";}
	.mypaid:before  { content: "'.Text::_('COM_MYMUSE_PAID').'";}
	
	.myfullname:before { content: "'.Text::_('COM_MYMUSE_FULL_NAME').'";}
	.myemail:before { content: "'.Text::_('COM_MYMUSE_EMAIL').'";}
	.myphone:before { content: "'.Text::_('COM_MYMUSE_PHONE').'";}
	.myaddress:before { content: "'.Text::_('COM_MYMUSE_ADDRESS').'";}
	.mycity:before { content: "'.Text::_('COM_MYMUSE_CITY').'";}
	.myzip:before { content: "'.Text::_('COM_MYMUSE_ZIP').'";}
	.myregion:before { content: "'.Text::_('COM_MYMUSE_STATE').'";}
	.mycountry:before { content: "'.Text::_('COM_MYMUSE_COUNTRY').'";}
	.mycompany:before { content: "'.Text::_('COM_MYMUSE_COMPANY').'";}
	.myfax:before { content: "'.Text::_('COM_MYMUSE_FAX').'";}
			
	.mychoose:before { content: "'.Text::_('COM_MYMUSE_CHOOSE').'";}
	.myshipmethod:before { content: "'.Text::_('COM_MYMUSE_SHIP_METHOD').'";}
	.mysummarytotal:before { content: "'.Text::_('COM_MYMUSE_TOTAL').'";}
			
	
';


if(isset($order->tax_array) && count($order->tax_array)){
	reset($order->tax_array);
	foreach($order->tax_array as $key => $val){
		$mobile_style .= '.'.strtolower(preg_replace("/_/","", $key)).':before { 
				content: "'.Text::_(preg_replace("/_/"," ", $key)).'";  
				white-space: nowrap;
				padding-right: 7%;
				margin-right: 7%;
				width: 23%;
				display: inline-block;
				border-right: 1px solid #ccc;
				}
				.'.strtolower(preg_replace("/_/","", $key)).'
				{ 
					clear:both;
				}
				';
	}
}
	
$mobile_style .= '
}
';