<?php 
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		htp://www.joomlamymuse.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

use Joomla\CMS\Uri\Uri;

$order		= $this->order;
$order_item = $order->items;
$Itemid 	= @$this->Itemid;
$user 		= $this->user;
$params 	= $this->params;
$task		= $this->task;
$got_flash  = 0;
$post_order = array('confirm','makepayment','thankyou','vieworder', 'notify');
$notes_required = $params->get('my_notes_required',0);

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('formbehavior.chosen', 'select');



$cols = 4;
if($params->get("my_show_cart_preview") && $got_flash):
	$cols++;  
endif;

if($params->get("my_show_sku")):
	$cols++;  
endif;

if(@$order->do_html): 
	$cols++;  
endif;



for ($i=0;$i<count($order->items); $i++) : 
    if(!isset($order->items[$i])) :
        continue;
    endif;
    if(isset($order_item[$i]->flash)) :
        $got_flash = 1;
    endif;
    if(isset($order_item[$i]->variation_select)) :
        $got_variation = 1;
    endif;
endfor;

?>

		<?php if($order->do_html) : ?>
			<form action="<?php Route::_("index.php?option=com_mymuse&view=cart&task=checkout") ?>" method="post" name="adminForm" id="adminForm">
		<?php endif; ?>

		<!-- start of basket -->

		<h2><?php echo Text::_('COM_MYMUSE_SHOPPING_CART'); ?></h2> 
		  
		<?php if($params->get("my_show_cart_preview") && $params->get('product_player_type') == "single" && isset($order->flash)) : ?>
			<div id="product_player" 
			<?php if($params->get('product_player_height')) : ?>
			style="height: <?php echo $params->get('product_player_height'); ?>px"
			<?php endif; ?>
			><?php echo $order->flash; ?>
			</div>
		<?php endif; ?>
		<?php if($params->get('product_player_type') == "playlist") : ?>
			<div id="product_player" ><?php echo $product->flash; ?>
			</div>
			
		<?php endif; ?>
		<div style="clear: both"></div>
		
		 
		<?php if($params->get('product_player_type') == "each") : ?>
			<div class="" id="product_player" ></div>
		<?php endif; ?>
		
<section>
    <ul class="mymuse-container mymuse-cart">
    <li class="item-container cols-<?php echo $cols; ?>">
	

		<div class="mytitle mymuse-cart-top "><?php echo Text::_('COM_MYMUSE_TITLE'); ?></div>

	<?php if($params->get("my_show_cart_preview") && $got_flash) : ?>  
		<div class="mypreviews mymuse-cart-top "><?php echo Text::_('COM_MYMUSE_PREVIEWS'); ?></div>
	<?php endif; ?>	
		

	<?php if($params->get("my_show_sku")): ?>
		<div class="mysku mymuse-cart-top "><?php echo Text::_('COM_MYMUSE_CART_SKU'); ?></div>
	<?php endif; ?>

		<div class="myprice mymuse-cart-top "><?php echo Text::_('COM_MYMUSE_CART_PRICE'); ?></div>
	
		<div class="myquantity mymuse-cart-top "><?php echo Text::_('COM_MYMUSE_CART_QUANTITY'); ?></div>
	
		<div class="mysubtotal mymuse-cart-top "><?php echo Text::_('COM_MYMUSE_CART_SUBTOTAL'); ?></div>

	<?php if(@$order->do_html): ?>
		<div class="myaction mymuse-cart-top "><?php echo Text::_('COM_MYMUSE_CART_ACTION'); ?>&nbsp;<?php echo $order->update_form; ?></div>		    
	<?php endif; ?>
	</li>
		<?php
		  // LOOP THRU order_items
		  for ($i=0;$i<count($order_item); $i++) : ?>
		  	<li class="item-container cols-<?php echo $cols; ?>">
		        <div class="mytitle mycart-inner" data-name="<?php echo Text::_('COM_MYMUSE_TITLE'); ?>">
		        <?php if(isset($order_item[$i]->artist->title) && $params->get('my_show_category_name')): ?>
		        	 <?php echo $order_item[$i]->artist->title; ?> : 
		        <?php endif; ?>
		        
		        <?php if(isset($order_item[$i]->parent_title)): ?>
		        	 <?php echo $order_item[$i]->parent_title; ?> :
		        <?php endif; ?>

		        <?php echo $order_item[$i]->title; ?>

		        <?php if(isset($order_item[$i]->file_name) && $order_item[$i]->file_name != ''): ?>
		        	 <br /><?php echo $order_item[$i]->file_name; ?> 
		        <?php endif; ?>

		        <?php if(isset($order_item[$i]->attributes) && is_array($order_item[$i]->attributes)): 
		        	foreach($order_item[$i]->attributes as $attrib): ?>
		        	 <br /><?php echo $attrib->attribute_name; ?>: <?php echo $attrib->attribute_value; ?> 
		        <?php endforeach; 
		    	endif;?>

		        <?php if($order->items[$i]->backordered || $order_item[$i]->product_in_stock == -1): 
		        		if($order->items[$i]->product_physical):
		        			$mymuse_msg =  Text::_('COM_MYMUSE_BACKORDERED');
		        		else:
		        			$mymuse_msg =  Text::_('COM_MYMUSE_PREORDERED');
		        		endif;

		        	?>
		        		<span class="mymuse_msg"><b><?php echo $mymuse_msg ?></b></span>
		        <?php endif; ?>
		        </div>

		    <?php if($params->get("my_show_cart_preview") && $got_flash): ?>  
			        <div class="mypreviews tracks jp-gui ui-widget mycart-inner" data-name="<?php echo Text::_('COM_MYMUSE_PREVIEWS'); ?>"><?php 
	                echo isset($order_item[$i]->flash)?  $order_item[$i]->flash : ''; ?></div>
			<?php endif; ?>	

		    <?php if($params->get("my_show_sku")): ?>
		        <div class="mysku mycart-inner" data-name="<?php echo Text::_('COM_MYMUSE_CART_SKU'); ?>"><?php echo $order_item[$i]->product_sku; ?></div>
		    <?php endif; ?>
		    
		        <div class="myprice mycart-inner" data-name="<?php echo Text::_('COM_MYMUSE_CART_PRICE'); ?>">
		        <?php 
		        if( isset($this->licence) &&  !in_array($task, $post_order) && isset($order_item[$i]->price['licence']) ):
		        	foreach($order_item[$i]->price['licence'] as $j=>$licence): 
		        		if($this->my_licence == $j):
		        			echo '<div id="item_price_'.$i.'" class="price" >'.
		        			MyMuseHelper::printMoney($order_item[$i]->price['licence'][$j]['price']).'</div>';
		        		endif;
		        	endforeach;
		        else:
		        	echo MyMuseHelper::printMoney($order_item[$i]->product_item_price);
		        endif;
		        ?>
		        </div>
		        
		   <?php if($order->do_html && $order_item[$i]->quantity): ?>
		        <div class="myquantity mycart-inner" data-name="<?php echo Text::_('COM_MYMUSE_CART_QUANTITY'); ?>"> <input class="inputbox" type="text" size="4" maxlength="4" name="quantity[<?php echo $order_item[$i]->id ?>]" id="quantity<?php echo $order_item[$i]->id ?>"
		        value="<?php echo $order_item[$i]->quantity;?>"   />&nbsp;</div>
		        
		    <?php else: ?>
		        <div class="myquantity mycart-inner" data-name="<?php echo Text::_('COM_MYMUSE_CART_QUANTITY'); ?>"><?php echo $order_item[$i]->quantity; 
		        	if($params->get('my_add_stock_zero',0) && $order_item[$i]->quantity == 0) :
		        		echo " ".Text::_('COM_MYMUSE_BACKORDERED');
		        	endif;
		        ?></div>
		    <?php endif; ?>  
		        <div class="mysubtotal mycart-inner" data-name="<?php echo Text::_('COM_MYMUSE_CART_SUBTOTAL'); ?>">
		        	<span id="product_item_subtotal_<?php echo $i ?>">
		        	<?php echo MyMuseHelper::printMoney($order_item[$i]->product_item_subtotal); ?>
		        	</span>
		        </div>
		    
		    <?php if($order->do_html): ?>
		        <div class="myaction mycart-inner" data-name="<?php echo Text::_('COM_MYMUSE_CART_ACTION'); ?>">
		        	<div><a href="<?php echo $order_item[$i]->delete_url; ?>"><span class="btn btn-danger" ><?php echo Text::_('COM_MYMUSE_DELETE'); ?></span></a> <a href="javascript:void(0)" onclick="document.getElementById('adminForm').submit()">
				<span class="btn btn-info" ><?php echo Text::_('COM_MYMUSE_UPDATE'); ?></span></a></div></div>
		    <?php endif; ?>

			</li>
		<?php endfor; ?>




		
			<?php 

			//ORIGINAL SUBTOTAL (show if there are discounts, etc)
			if($order->discount > 0.00 || ($params->get("my_use_coupons") && @$order->coupon->id)
			|| count($order->tax_array) > 0) : ?>
			<!--  original subtotal -->
				<li class="item-container summary cols-<?php echo $cols; ?>">
			    	<div class="cart"><?php echo Text::_('COM_MYMUSE_CART_SUBTOTAL'); ?></div>
			    	<div></div><div></div>
			        <div class="myoriginalsubtotal cart" data-name="<?php echo Text::_('COM_MYMUSE_CART_SUBTOTAL'); ?>">
			        	<span id="subtotal_before_discount">
			        	<?php echo MyMuseHelper::printMoney($order->subtotal_before_discount); ?>
			        	</span>
			       	</div>
			        <?php if(@$order->do_html): ?><div class="cart">&nbsp;</div><?php endif; ?>
			    </li>
			<?php endif; ?>
			
			<?php //for shopper group discount 
			if($order->shopper_group_discount > 0.00): ?>
			    <li class="item-container summary cols-<?php echo $cols; ?>">
			    	<div class="cart" ><?php echo Text::_('COM_MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?>
			    	<?php echo $order->shopper_group_name; ?> <?php echo $user->shopper_group->discount; ?> %</div>
			    	<div></div><div></div>
			        <div class="myshoppergroupdiscount cart" data-name="<?php echo Text::_('COM_MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?>">
			        	<span id="shopper_group_discount">(<?php echo MyMuseHelper::printMoney($order->shopper_group_discount); ?>)
			        	</spanv>
			        </div>
			        <?php if(@$order->do_html): ?><div class="cart">&nbsp;</div><?php endif; ?>
			    </li>
			<?php endif; ?>
			
			<?php //for regular discount
			if($order->discount > 0.00): ?>
			    <li class="item-container summary cols-<?php echo $cols; ?>">
			    	<div class="cart" ><?php echo Text::_('COM_MYMUSE_DISCOUNT'); ?>
			    	</div>
			    	<div></div><div></div>
			        <div class="mydiscount cart" data-name="<?php echo Text::_('COM_MYMUSE_SHOPPING_DISCOUNT'); ?>">
			        	<span id="discount">- <?php echo MyMuseHelper::printMoney($order->discount); ?>
			        	</span>
			        </div>
			        <?php if(@$order->do_html): ?><div class="cart">&nbsp;</div><?php endif; ?>
			    </li>
			<?php endif; ?>
					

			<?php //COUPONS
			if($params->get("my_use_coupons") && @$order->coupon->id): ?>
			    <li class="item-container summary cols-<?php echo $cols; ?>">
			    	<div class="cart" ><?php echo Text::_('COM_MYMUSE_YOUR_COUPON'); ?> <?php echo $order->coupon->title ?></div>
			    	<div></div><div></div>

			        <div class="mycoupon cart" data-name="<?php echo Text::_('COM_MYMUSE_YOUR_COUPON'); ?>">
			        	<span id="coupon_discount"> - <?php echo MyMuseHelper::printMoney($order->coupon->discount); ?> 
			        	</span>
			        </div>
			        <?php if(@$order->do_html): ?><div class="cart">&nbsp;</div><?php endif; ?>
			    </li>
			<?php endif; ?>
					
			
			<?php // TAXES
			if(isset($order->tax_array)  && count($order->tax_array)):
			    foreach($order->tax_array as $key=>$val):
			    	$pre_key = preg_replace("/_/","", $key);
			    	$key = preg_replace("/_/"," ", $key);
			    	?>
			        <li class="item-container summary cols-<?php echo $cols; ?>">
			        	<div class="cart" ><?php echo Text::_('COM_MYMUSE_TAX'); ?> <?php echo $key; ?></div>
			        	<div></div><div></div>
			        	<div class="mytax cart" data-name="<?php echo $key; ?>">
			        		<span id="tax<?php echo strtolower($pre_key); ?>"><?php echo MyMuseHelper::printMoney($val); ?>
			        		</span>
			        	</div>
			        	<?php if(@$order->do_html): ?><div class="cart">&nbsp;</div><?php endif; ?>
			        </li>
			<?php  endforeach; 
			endif; ?>
			
			<?php // SHIPPING
			if ($params->get("my_use_shipping") && @$order->order_shipping->cost > 0) : ?>
			    <li class="item-container summary cols-<?php echo $cols; ?>">
				    <div class="cart" data-name="<?php echo Text::_('COM_MYMUSE_SHIPPING'); ?>"><?php echo Text::_('COM_MYMUSE_SHIPPING') ?><span id="order_shipping_name">
				    <?php echo $order->order_shipping->ship_carrier_name ?> <?php echo $order->order_shipping->ship_method_name ?></span></div>
				    <div></div><div></div>
				    <div class="myshipping cart">
				    	<span id="order_shipping_cost">
				    	<?php echo MyMuseHelper::printMoney($order->order_shipping->cost); ?>
				    	</span>
				    </div>
				    <?php if(@$order->do_html): ?><div class="cart">&nbsp;</div><?php endif; ?>
			    </li>
			<?php endif; ?>

			<?php // TOTALS ?>
			<li class="item-container summary totals cols-<?php echo $cols; ?>">
			    <div class="cart" data-name="<?php echo Text::_('COM_MYMUSE_CART_TOTAL'); ?>"><?php echo Text::_('COM_MYMUSE_CART_TOTAL') ?>:</div>

			    <div></div><div></div>

			    <div class="mytotal cart">
			    	<span id="mytotal"><?php echo MyMuseHelper::printMoney($order->order_total); ?>
			    	</span>
			    </div>
			    <?php if($order->do_html): ?>
			        <div class="cart" ><a href="<?php echo Route::_("index.php?option=com_mymuse&task=cartClear"); ?>"><?php echo Text::_('COM_MYMUSE_CART_CLEAR') ?></a></div>
			    <?php endif; ?>
			</li>
			
			
			<?php  if($order->reservation_fee > 0): ?>
			<li class="item-container summary cols-<?php echo $cols; ?>" data-name="<?php echo Text::_('COM_MYMUSE_RESERVATION_FEE'); ?>">
			    <div class="cart"><?php echo Text::_('COM_MYMUSE_RESERVATION_FEE') ?>:</div>
			    <div></div><div></div>
			    <div class="myreservationfee cart">
			    	<span id="reservation_fee"><b><?php echo MyMuseHelper::printMoney($order->reservation_fee); ?></b>
			    	</span>
			    </div>
			    <?php if(@$order->do_html): ?><div class="cart">&nbsp;</div><?php endif; ?>
			
			</li>
			

			<?php  if($order->non_res_total > 0): ?>
				<li class="item-container summary cols-<?php echo $cols; ?>" data-name="<?php echo Text::_('COM_MYMUSE_OTHER_CHARGES'); ?>">
			    	<div class="cart"><?php echo Text::_('COM_MYMUSE_OTHER_CHARGES') ?>:</div>
			    	<div></div><div></div>
			    	<div class="myothercharges cart">
			    		<span id=">non_res_total"><b><?php echo MyMuseHelper::printMoney($order->non_res_total); ?></b>
			    		</span>
			    	</div>
			    	<?php if(@$order->do_html): ?><div class="cart">&nbsp;</div><?php endif; ?>
			    
				</li>
				
			<?php endif; ?>

				<li class="item-container summary cols-<?php echo $cols; ?>">
			    	<div class="cart" data-name="<?php echo Text::_('COM_MYMUSE_PAYNOW'); ?>"><?php echo Text::_('COM_MYMUSE_PAYNOW') ?>:</div>
			    	<div></div><div></div>
			    	<div class="mypaynow cart"align="right">
			    		<span id="must_pay_now">
			    		<?php echo MyMuseHelper::printMoney($order->must_pay_now); ?>
			    		</span>
			    	</div>
			    	<?php if(@$order->do_html): ?><div class="cart">&nbsp;</div><?php endif; ?>
				</li>
			<?php endif; ?>

</ul>
</section>



		
    <?php 
    //NOTES REQUIRED?
if($notes_required  && $user->username == 'buyer'):
	
    $notes = isset($order->notes['notes'])? $order->notes['notes'] : @$order->notes; 

    if(!is_array($notes)):
    	$jason = json_decode($notes);
    	if(is_array($jason)){
    		$notes = $jason['notes'];
    	}
    	if(is_object($jason)){
    		$notes = $jason->notes;
    	}
    else;
    	$notes = '';
    endif;


    if(!in_array($task, $post_order)): ?>		
		<h3><?php echo Text::_($params->get("my_notes_header"))?></h3>
        <?php echo Text::_($params->get("my_notes_msg"))?>
        <textarea class="required" style="height: 200px; width:90%;" name="notes" rows="10" cols="5"><?php 
        
        echo $notes; 
        
        ?></textarea>
<?php 
	elseif($notes) : ?>
		<h3><?php echo Text::_($params->get("my_notes_header"))?></h3>
		<?php echo nl2br($notes);
	endif;
endif;
?>
        
    <?php if(!in_array($task, $post_order) && $user->username == 'buyer'): ?>
    <!--  
        <div class="pull-left myupdate cart"><button class="button uk-button " 
				type="submit" >
				<?php echo Text::_('COM_MYMUSE_UPDATE_CART'); ?></button>
		</div>
	-->
	<?php endif; ?>	
	
		<?php if($order->do_html): ?></form><?php endif; ?>
		
		
		<?php 

		if(isset($order->show_checkout) && $order->show_checkout): 
		    // add the checkout link
		?> 
	
			<?php if($user->username == '' && $params->get('my_registration') == "full_guest"): ?>
				<div class="float-start cart">
					<button class="btn btn-info" type="button"
					onclick="location.href='<?php echo Route::_("index.php?option=com_mymuse&task=guestcheckout&view=cart&Itemid=$Itemid") ?>'">
					Checkout as a guest.</button>
				</div>
			<?php endif; ?>
			
			<div class="float-start cart">
				<button id="continue-shopping" class="btn btn-primary pull-left" 
				type="button" 
				onclick="location.href='<?php echo $params->get('my_continue_shopping'); ?>'">
				<?php echo Text::_('COM_MYMUSE_CONTINUE_SHOPPING'); ?></button>
			</div>
				
				
	  		<?php

	  		 if(($user->username == 'buyer' && $notes_required && isset($notes) && $notes != '')
				|| $user->username == 'buyer' && !$notes_required
				|| $user->username != 'buyer'): 
	  			?>
				<div class="float-end cart">
					<button id="checkout" class="btn btn-primary" type="button" 
					onclick="location.href='<?php echo Route::_("index.php?option=com_mymuse&view=cart&task=checkout") ?>'">
					<?php echo Text::_('COM_MYMUSE_CHECKOUT'); ?></button>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>	
		<?php endif; 
		if(isset($order->waited)):
			echo "<!-- waited:".$order->waited." -->";
		endif;
		
		?>

