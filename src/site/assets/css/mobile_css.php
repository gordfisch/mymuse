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

	
	.myselect:before { content: "'.JText::_('MYMUSE_SELECT').'";}
	.mytitle:before { content: "'.JText::_('MYMUSE_TITLE').'";}
	.mytime:before { content: "'.JText::_('MYMUSE_TIME').'";}
	.myfilesize:before { content: "'.JText::_('MYMUSE_FILE_SIZE').'";}
	.mydownloads:before { content: "'.JText::_('MYMUSE_DOWNLOADS').'";}
	.myexpiry:before { content: "'.JText::_('MYMUSE_EXPIRES').'";}
	.myprice:before { content: "'.JText::_('MYMUSE_CART_PRICE').'";}
	.mypreviews:before { content: "'.JText::_('MYMUSE_PLAY').'";}
	.myquantity:before { content: "'.JText::_('MYMUSE_QUANTITY').'";}
	.mysku:before { content: "'.JText::_('MYMUSE_CART_SKU').'";}
	.mysubtotal:before { content: "'.JText::_('MYMUSE_CART_SUBTOTAL').'";}
	.myaction:before { content: "'.JText::_('MYMUSE_CART_ACTION').'";}

	.myoriginalsubtotal:before { content: "'.JText::_('MYMUSE_CART_ORIGINAL_SUBTOTAL').'";}
	.myshoppergroupdiscount:before { content: "'.JText::_('MYMUSE_DISCOUNT').'";}
	.mynewsubtotal:before { content: "'.JText::_('MYMUSE_CART_NEW_SUBTOTAL').'";}
	.myshipping:before { content: "'.JText::_('MYMUSE_SHIPPING').'";}
	.mytotal:before { content: "'.JText::_('MYMUSE_TOTAL').'";}
	.myupdatecart:before { content: "'.JText::_('MYMUSE_UPDATE_CART').'";}
	.mycoupon:before { content: "'.JText::_('MYMUSE_YOUR_COUPON').'";}
	
	.myreservationfee:before { content: "'.JText::_('MYMUSE_RESERVATION_FEE').'";}
	.myothercharges:before { content: "'.JText::_('MYMUSE_OTHER_CHARGES').'";}
	.mypaynow:before { content: "'.JText::_('MYMUSE_PAYNOW').'";}
	.myshipmethod:before { content: "'.JText::_('MYMUSE_SHIP_METHOD').'";}
	.mydiscount:before { content: "'.JText::_('MYMUSE_DISCOUNT').'";}
	
	.myimage:before { content: "'.JText::_('MYMUSE_IMAGE').'";}
	.myauthor:before { content: "'.JText::_('JAUTHOR').'";}
	.myhits:before { content: "'.JText::_('JGLOBAL_HITS').'";}
	.mysales:before { content: "'.JText::_('MYMUSE_SALES').'";}
	.mydate-modified:before { content: "'.JText::_('MYMUSE_MODIFIED_DATE').'";}
	.mydate-created:before { content: "'.JText::_('MYMUSE_CREATED_DATE').'";}
	.mydate-published:before { content: "'.JText::_('MYMUSE_PUBLISHED_DATE').'";}
	.mydate-product_made_date:before { content: "'.JText::_('MYMUSE_RELEASE').'";}
	.myartist:before { content: "'.JText::_('MYMUSE_ARTIST').'";}
	
	.myorderid:before { content: "'.JText::_('MYMUSE_ORDER_ID').'";}
	.mydate:before { content: "'.JText::_('MYMUSE_DATE').'";}
	.myorderstatus:before { content: "'.JText::_('MYMUSE_ORDER_STATUS').'";}
	.myordernumber:before { content: "'.JText::_('MYMUSE_ORDER_NUMBER').'";}
	.myorderdate:before  { content: "'.JText::_('MYMUSE_ORDER_DATE').'";}
	.mypaid:before  { content: "'.JText::_('MYMUSE_PAID').'";}
	
	.myfullname:before { content: "'.JText::_('MYMUSE_FULL_NAME').'";}
	.myemail:before { content: "'.JText::_('MYMUSE_EMAIL').'";}
	.myphone:before { content: "'.JText::_('MYMUSE_PHONE').'";}
	.myaddress:before { content: "'.JText::_('MYMUSE_ADDRESS').'";}
	.mycity:before { content: "'.JText::_('MYMUSE_CITY').'";}
	.myzip:before { content: "'.JText::_('MYMUSE_ZIP').'";}
	.myregion:before { content: "'.JText::_('MYMUSE_STATE').'";}
	.mycountry:before { content: "'.JText::_('MYMUSE_COUNTRY').'";}
	.mycompany:before { content: "'.JText::_('MYMUSE_COMPANY').'";}
	.myfax:before { content: "'.JText::_('MYMUSE_FAX').'";}
			
	.mychoose:before { content: "'.JText::_('MYMUSE_CHOOSE').'";}
	.myshipmethod:before { content: "'.JText::_('MYMUSE_SHIP_METHOD').'";}
	.mysummarytotal:before { content: "'.JText::_('MYMUSE_TOTAL').'";}
			
	
';


if(isset($order->tax_array) && count($order->tax_array)){
	reset($order->tax_array);
	foreach($order->tax_array as $key => $val){
		$mobile_style .= '.'.strtolower(preg_replace("/_/","", $key)).':before { 
				content: "'.JText::_(preg_replace("/_/"," ", $key)).'";  
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