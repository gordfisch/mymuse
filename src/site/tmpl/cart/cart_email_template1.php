<?php 
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2015 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

$params 		= $this->params;
$shopper 		= $this->shopper;
$store 			= $this->store;
$uri 				= Uri::getInstance();
$css_link = $uri::root().(Route::_('components/com_mymuse/assets/css/mymuse.css', false)); 
if(!isset($shopper->profile['name']) && 
		( isset($shopper->profile['first_name']) && isset($shopper->profile['last_name']) )
	){
	$shopper->profile['name'] = $shopper->profile['first_name'].' '.$shopper->profile['last_name'];
	
}

$fields = MymuseHelper::getNoRegFields();
foreach($fields as $field){
	if(!isset($shopper->profile[$field]) && isset($shopper->$field) && $shopper->$field != ''){
		$shopper->profile[$field] = $shopper->$field;
			
	}
}

$need_shipping = 0;
if($params->get('my_use_shipping') && isset($this->order->need_shipping) && $this->order->need_shipping ){
	$need_shipping = 1;
}
$order		= $this->order;
$order_item = $order->items;
$no_items 	= count($order->items);
$Itemid 	= @$this->Itemid;
$user 		= $this->user;
$post_order = array('confirm','makepayment','thankyou','vieworder', 'notify');
$notes_required = $params->get('my_notes_required',0);
$cols = 3;
if($params->get("my_show_sku")):
	$cols++;  
endif;

?>
<!DOCTYPE HTML>
<html lang="en-gb" dir="ltr">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta charset="utf-8" />
	<title><?php echo $store->title; ?></title>
</head>

<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" >    

<table width="100%" cellpadding="3px" class="store_info" style="max-width: 600px">
	<tr>
	<td><a href="<?php echo Uri::root(); ?>"><img align="left" src="<?php echo Uri::root().$params->get('store_thumb_image'); ?>" border="0"></a>
	</td>
	<td>
		<a href="<?php echo Uri::root(); ?>"><?php echo $store->title; ?></a><br />
		<?php echo $params->get('address_1').' '.$params->get('address_2'); ?><br />
		<?php echo $params->get('city').', '.$params->get('state'); ?><br />
		<?php echo $params->get('country').', '.$params->get('zip'); ?><br />
		<?php echo $params->get('phone'); ?><br />
		<a href="mailto: <?php echo $params->get('contact_email'); ?>"><?php echo $params->get('contact_email'); ?></a><br />
	</td>
	</tr>
</table>


<?php if(isset($this->my_email_msg) && $this->my_email_msg != '') : ?>
<div >
	<?php echo $this->my_email_msg; ?>
</div>
<?php endif; ?>



<div >
	<h2><?php echo $this->heading; ?></h2>
	<div ><?php echo $this->message; ?></div>
</div>


<?php if(isset($order->downloadlink) && $order->downloadlink != '' && $order->order_status == "C") : ?>
<div >
	<?php echo $order->downloadlink; ?>
</div>
<?php endif; ?>


<div class="order_summary">

	<h2><?php echo Text::_('COM_MYMUSE_ORDER_SUMMARY') ?></h2>
    <table width="100%" cellpadding="3px" style="max-width: 600px">
 
        <!-- Begin Order Summary -->
        <tr>
            <td width="20%"><?php echo Text::_('COM_MYMUSE_ORDER_NUMBER') ?>:</td>
            <td ><?php echo sprintf("%08d", $order->id) ?></td>
        </tr>
        <tr>
            <td ><?php echo Text::_('COM_MYMUSE_ORDER_DATE') ?>:</td>
            <td ><?php echo $order->created ?></td>
        </tr>
        <tr>
            <td ><?php echo Text::_('COM_MYMUSE_ORDER_STATUS') ?>:</td>
            <td ><?php echo Text::_('COM_MYMUSE_'.strtoupper($order->status_name)) ?></td>
        </tr>
        <tr>
            <td ><?php echo Text::_('COM_MYMUSE_ORDER_TOTAL') ?>:</td>
            <td ><?php echo MymuseHelper::printMoney($order->order_total)." ".$order->order_currency['currency_code'] ?></td>
        </tr>
    <?php if($order->reservation_fee > 0) : ?>
        <tr>
            <td ><?php echo Text::_('COM_MYMUSE_RESERVATION_FEE') ?>:</td>
            <td ><?php echo MymuseHelper::printMoney($order->reservation_fee)." ".$order->order_currency['currency_code'] ?></td>
        </tr>
	    <?php if($order->non_res_total > 0) : ?>
	    	<tr>
	        	<td ><?php echo Text::_('COM_MYMUSE_OTHER_CHARGES') ?>:</td>
	        	<td ><?php echo MymuseHelper::printMoney($order->non_res_total)." ".$order->order_currency['currency_code'] ?></td>
	    	</tr>
	    	<tr>
	        <td ><?php echo Text::_('COM_MYMUSE_PAID') ?>:</td>
	        <td ><?php echo MymuseHelper::printMoney($order->pay_now)." ".$order->order_currency['currency_code'] ?></td>
	    </tr>
		<?php endif; ?>
	<?php endif; ?>
    <?php if(isset($this->plugin) && $this->plugin != '') : ?>
        <tr>
            <td ><?php echo Text::_('COM_MYMUSE_PAID') ?>:</td>
            <td ><?php echo $this->plugin?></td>
        </tr>
    <?php endif; ?>
	</table>

</div>

<div class="shopper_info">
<!-- Begin bill-ship to -->
        <h2><?php echo Text::_('COM_MYMUSE_SHOPPER_INFORMATION') ?></h2>
		
<?php if($need_shipping) : //show shipping ?>
	<table width="100%" cellpadding="3px" >
		<tr>
			<td >
<?php endif; ?>
            <table width="100%" cellpadding="3px" style="max-width: 600px">
                <tr >
                	<td  colspan="2"><b><?php echo Text::_('COM_MYMUSE_BILLING_ADDRESS') ?></b></td>
                </tr>
                
                <tr>
                	<td width="20%"><?php echo Text::_('COM_MYMUSE_FULL_NAME') ?>:</td>
                	<td >
                	<?php echo $shopper->profile['name'] ?>
                	</td>
                </tr>
                <tr>
                	<td ><?php echo Text::_('COM_MYMUSE_EMAIL') ?>:</td>
                	<td ><?php echo $shopper->profile['email'] ?></td>
                </tr>
                
            <?php if(isset($shopper->profile)) : ?>
            
              <?php if(isset($shopper->profile['phone']) && $shopper->profile['phone'] != '') : ?> 
                <tr>
                	<td ><?php echo Text::_('COM_MYMUSE_PHONE') ?>:</td>
                	<td ><?php echo $shopper->profile['phone'] ?></td>
                </tr>
              <?php endif; ?>
              
              <?php if(isset($shopper->profile['mobile']) && $shopper->profile['mobile'] != '') : ?> 
                <tr>
                	<td ><?php echo Text::_('COM_MYMUSE_MOBILE') ?>:</td>
                	<td ><?php echo $shopper->profile['mobile'] ?></td>
                </tr>
              <?php endif; ?>
              
              <?php if(isset($shopper->profile['address1']) && $shopper->profile['address1'] !='') : ?> 
                <tr>
                	<td ><?php echo Text::_('COM_MYMUSE_ADDRESS') ?>:</td>
                	<td >
                	<?php echo $shopper->profile['address1'] ?>
                	
                	<?php echo @$shopper->profile['address2'] ?>
                	</td>
                </tr>
              <?php endif; ?>
              
              <?php if(isset($shopper->profile['city']) && $shopper->profile['city'] != '') : ?> 
                <tr>
                	<td ><?php echo Text::_('COM_MYMUSE_CITY') ?>:</td>
                	<td ><?php echo $shopper->profile['city'] ?></td>
                </tr>
              <?php endif; ?>
              
              <?php if(isset($shopper->profile['region_name']) && $shopper->profile['region_name'] != '') : ?>
                <tr>
                	<td ><?php echo Text::_('COM_MYMUSE_STATE') ?>:</td>
                	<td ><?php echo $shopper->profile['region_name'] ?></td>
                </tr>
              <?php endif; ?>
              
              <?php if(isset($shopper->profile['country']) && $shopper->profile['country'] != '') : ?>
                <tr>
                	<td ><?php echo Text::_('COM_MYMUSE_COUNTRY') ?>:</td>
                	<td ><?php echo $shopper->profile['country'] ?></td>
                </tr>
            <?php  endif; ?>
            
            <?php if(isset($shopper->profile['postal_code']) && $shopper->profile['postal_code'] != ''):  ?>
                <tr>
                	<td ><?php echo Text::_('COM_MYMUSE_ZIP') ?>:</td>
                	<td ><?php echo $shopper->profile['postal_code'] ?></td>
                </tr>
              <?php endif; ?>
              
			<?php endif; //end if profile?>
                
            </table>
            <!-- End BillTo --> 
           
<?php if($need_shipping) : //show shipping ?>
		</td >
	</tr>
	<tr>
		<td >
            <table width="100%" cellpadding="3px" style="max-width: 600px">
                <tr >
                    <td colspan="2"><b><?php echo Text::_('COM_MYMUSE_SHIPPING_ADDRESS') ?></b></td>
                </tr>
             <?php if(isset($shopper->profile['shipping_first_name']) && isset($shopper->profile['shipping_last_name'])) : ?>
                <tr>
                    <td width="20%"><?php echo Text::_('COM_MYMUSE_NAME') ?>:</td>
                    <td ><?php
                    echo $shopper->profile['shipping_first_name']." ".$shopper->profile['shipping_last_name'] ?></td>
                </tr>
            <?php endif; ?>
            <?php if(isset($shopper->profile['shipping_address1'])) : ?>
                <tr VALIGN=TOP>
                    <td ><?php echo Text::_('COM_MYMUSE_ADDRESS') ?>:</td>
                    <td >
                    <?php echo $shopper->profile['shipping_address1'] ?>
                    <br />
                    <?php echo isset($shopper->profile['shipping_address2'])? $shopper->profile['shipping_address2'] : '' ?>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if(isset($shopper->profile['shipping_city'])): ?>
                <tr>
                    <td ><?php echo Text::_('COM_MYMUSE_CITY') ?>:</td>
                    <td ><?php echo $shopper->profile['shipping_city'] ?></td>
                </tr>
            <?php endif; ?>
            <?php if(isset($shopper->profile['shipping_region_name'])) : ?>
                <tr>
                    <td ><?php echo Text::_('COM_MYMUSE_STATE') ?>:</td>
                    <td ><?php echo $shopper->profile['shipping_region_name'] ?></td>
                </tr>
            <?php endif; ?>
            <?php if(isset($shopper->profile['shipping_country'])) : ?>
                <tr>
                    <td ><?php echo Text::_('COM_MYMUSE_COUNTRY') ?>:</td>
                    <td ><?php echo $shopper->profile['shipping_country'] ?></td>
                </tr>
            <?php endif; ?>
            <?php if(isset($shopper->profile['shipping_postal_code'])) : ?>
                <tr>
                    <td ><?php echo Text::_('COM_MYMUSE_ZIP') ?>:</td>
                    <td ><?php echo $shopper->profile['shipping_postal_code'] ?></td>
                </tr>
            <?php endif; ?>
            </table>
            <!-- End ShipTo -->
		</td >
	</tr>
	</table>
<?php endif; ?>
            <!-- End Customer Information --> 
</div>


<div class="cart">
	<h2><?php echo Text::_('COM_MYMUSE_SHOPPING_CART') ?></h2>
	    <table width="100%" cellpadding="3px" style="max-width: 600px">
	    <tr style="border-bottom: 1px solid #cccccc">
		

			<th align="left" style="font-weight: bold;"><?php echo Text::_('COM_MYMUSE_TITLE'); ?></th>

		<?php if($params->get("my_show_sku")): ?>
			<th align="left" style="font-weight: bold;"><?php echo Text::_('COM_MYMUSE_CART_SKU'); ?></th>
		<?php endif; ?>

			<th align="right" style="font-weight: bold;"><?php echo Text::_('COM_MYMUSE_CART_PRICE'); ?></th>
		
			<th align="right" style="font-weight: bold;"><?php echo Text::_('COM_MYMUSE_CART_QUANTITY'); ?></th>
		
			<th align="right" style="font-weight: bold;"><?php echo Text::_('COM_MYMUSE_CART_SUBTOTAL'); ?></th>
		</tr>
			<?php
			  // LOOP THRU order_items
			  for ($i=0;$i<count($order_item); $i++) : ?>
			  	<tr <?php if($i+1 == $i<count($order_item)): ?> style="border-bottom: 1px solid #cccccc" 
			  		<?php endif; ?>>
				    
			        <td >
			        <?php if(isset($order_item[$i]->artist->title) && $params->get('my_show_category_name')) : ?>
			        	 <?php echo $order_item[$i]->artist->title; ?> : 
			        <?php endif; ?>
			        
			        <?php if(isset($order_item[$i]->product->parent->title)) : ?>
			        	 <?php echo $order_item[$i]->product->parent->title; ?> :
			        <?php endif; ?>

			        <?php echo $order_item[$i]->title; ?>
			        
			        <?php if(isset($order_item[$i]->format) && $order_item[$i]->format != '') : ?>
			        	 : <?php echo strtoupper($order_item[$i]->format); ?> 
			        <?php elseif(isset($order_item[$i]->ext) && $order_item[$i]->ext != '') : ?>
			        	 : <?php echo $order_item[$i]->ext ?> 
			        <?php endif; ?>

			        <?php if($order->items[$i]->backordered || $order_item[$i]->product_in_stock == -1) : 
			        		if($order->items[$i]->product_physical){
			        			$mymuse_msg =  Text::_('COM_MYMUSE_BACKORDERED');
			        		}else{
			        			$mymuse_msg =  Text::_('COM_MYMUSE_PREORDERED');
			        		}

			        	?>
			        		<span ><?php echo $mymuse_msg ?></span>
			        <?php endif; ?>
			        </td>


			    <?php if($params->get("my_show_sku")) : ?>
			        <td ><?php echo $order_item[$i]->product_sku; ?></td>
			    <?php endif; ?>
			    
			        <td align="right">
			        	<?php echo MymuseHelper::printMoney($order_item[$i]->product_item_price); ?>
			        </td>
			        

			        <td align="right"><?php echo $order_item[$i]->quantity; 
			        if($params->get('my_add_stock_zero',0) && $order_item[$i]->quantity == 0) :
			        	echo " ".Text::_('COM_MYMUSE_BACKORDERED');
			        endif;
			        ?></td>
			
			        <td align="right">
			        	<?php echo MymuseHelper::printMoney($order_item[$i]->product_item_subtotal); ?>
			        </td>
				</tr>
			<?php endfor; ?>




				<?php //ORIGINAL SUBTOTAL (show if there are discounts, etc)
				if($order->discount > 0.00 || ($params->get("my_use_coupons") && @$order->coupon->id)
				|| count($order->tax_array) > 0) : ?>
				<!--  original subtotal -->
					<tr >
				    	<td  style="font-weight: bold;"><?php echo Text::_('COM_MYMUSE_CART_SUBTOTAL'); ?></td>
				    	
				        <td align="right" colspan="<?php echo $cols; ?>" style="font-weight: bold;">
				        	<?php echo MymuseHelper::printMoney($order->subtotal_before_discount); ?>
				       	</td>
				    </tr>
				<?php endif; ?>
				
				<?php //for shopper group discount 
				if($order->shopper_group_discount > 0.00) : ?>
				    <tr >
				    	<td  style="font-weight: bold;"><?php echo Text::_('COM_MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?>
				    	<?php echo $order->shopper_group_name; ?> <?php echo $user->shopper_group->discount; ?> %</td>

				        <td  style="font-weight: bold;" align="right" colspan="<?php echo $cols; ?>">
				        	(<?php echo MymuseHelper::printMoney($order->shopper_group_discount); ?>)
				        </td>

				    </tr>
				<?php endif; ?>
				
				<?php //for regular discount
				if($order->discount > 0.00) : ?>
				    <tr >
				    	<td style="font-weight: bold;" ><?php echo Text::_('COM_MYMUSE_DISCOUNT'); ?></td>

				        <td style="font-weight: bold;" align="right" colspan="<?php echo $cols; ?>">
				        	- <?php echo MymuseHelper::printMoney($order->discount); ?>
				        	
				        </td>

				    </tr>
				<?php endif; ?>
						

				<?php //COUPONS
				if($params->get("my_use_coupons summary") && @$order->coupon->id) : ?>
				    <tr >
				    	<td  style="font-weight: bold;"><?php echo Text::_('COM_MYMUSE_YOUR_COUPON'); ?> <?php echo $order->coupon->title ?></td>
				        <td style="font-weight: bold;" align="right" colspan="<?php echo $cols; ?>">
				        	<?php echo MymuseHelper::printMoney($order->coupon->discount); ?> 
				        </td>

				    </tr>
				<?php endif; ?>
						
				
				<?php // TAXES
				if(isset($order->tax_array)  && count($order->tax_array)) :
				    foreach($order->tax_array as $key=>$val) :
				    	$pre_key = preg_replace("/_/","", $key);
				    	$key = preg_replace("/_/"," ", $key);
				    	?>
				        <tr>
				        	<td style="font-weight: bold;" ><?php echo $key; ?></td>
				        	<td style="font-weight: bold;" align="right" colspan="<?php echo $cols; ?>">
				        		<?php echo MymuseHelper::printMoney($val); ?>
				        	</td>

				        </tr>
				<?php  endforeach; 
				endif; ?>
				
				<?php // SHIPPING
				if ($params->get("my_use_shipping") && @$order->order_shipping->cost > 0) : ?>
				    <tr >
				    <td style="font-weight: bold;" ><?php echo Text::_('COM_MYMUSE_SHIPPING') ?><span id="order_shipping_name">
				    <?php echo $order->order_shipping->ship_carrier_name ?> <?php echo $order->order_shipping->ship_method_name ?></span></td>
				    <td style="font-weight: bold;" align="right" colspan="<?php echo $cols; ?>">
				    	<?php echo MymuseHelper::printMoney($order->order_shipping->cost); ?>
				    </td>

				    </tr>
				<?php endif; ?>

				<?php // TOTALS ?>
				<tr >
				    <td style="font-weight: bold;" ><?php echo Text::_('COM_MYMUSE_CART_TOTAL') ?>:</td>
				    <td style="font-weight: bold;" align="right" colspan="<?php echo $cols; ?>">
				    	<?php echo MymuseHelper::printMoney($order->order_total); ?>
				    </td>

				</tr>
				
				
				<?php  if($order->reservation_fee > 0) : ?>
				<tr >
				    <td style="font-weight: bold;"><?php echo Text::_('COM_MYMUSE_RESERVATION_FEE') ?>:</td>

				    <td style="font-weight: bold;" align="right" colspan="<?php echo $cols; ?>">
						<?php echo MymuseHelper::printMoney($order->reservation_fee); ?></b>
				    </td>

				</tr>
				<?php  if($order->non_res_total > 0) : ?>
					<tr>
				    	<td style="font-weight: bold;" align="right"><?php echo Text::_('COM_MYMUSE_OTHER_CHARGES') ?>:</td>
				    	<td style="font-weight: bold;" align="right" colspan="<?php echo $cols; ?>">
				    		<?php echo MymuseHelper::printMoney($order->non_res_total); ?></b>
				    	</td>

					</tr>
					<tr >
				    	<td style="font-weight: bold;" ><?php echo Text::_('COM_MYMUSE_PAYNOW') ?>:</td>
				    	<td style="font-weight: bold;" align="right" colspan="<?php echo $cols; ?>">
				    		<?php echo MymuseHelper::printMoney($order->must_pay_now); ?>
				    	</td>

					</tr>
				<?php endif; ?>
			
			<?php endif; ?>

	</table>


</div>












    </body>
    </html>