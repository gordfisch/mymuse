<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;


if(isset($order->items) && count($order->items)){
$i = 0;
?>
<section>
  <ul class="mymuse-container mymuse-cart mini-cart">
    <li class="item-container cols-3">
    	<div class="mytitle mymuse-cart-top "><?php echo JText::_('COM_MYMUSE_TITLE') ?></div>
    	<div class="myquantity mymuse-cart-top "><?php echo JText::_('COM_MYMUSE_CART_QUANTITY') ?></div>
    	<div class="mysubtotal mymuse-cart-top "><?php echo JText::_('COM_MYMUSE_CART_SUBTOTAL') ?></div>
    </li>

	<?php foreach($order->items as $item) { ?>
	<li class="item-container cols-3">		    
		<div class="mytitle mycart-inner" data-name="<?php echo JText::_('COM_MYMUSE_TITLE'); ?>"><?php echo $item->title; ?></div>
		<div class="myquantity mycart-inner" data-name="<?php echo JText::_('COM_MYMUSE_CART_QUANTITY'); ?>"><?php echo $item->quantity; ?></div>
		<div class="mysubtotal mycart-inner" data-name="<?php echo JText::_('COM_MYMUSE_CART_SUBTOTAL'); ?>"><?php echo MymuseHelper::printMoney($item->product_item_subtotal); ?></div>

	</li>
	<?php } ?>	


	<?php if($order->discount > 0.00 || ($params->get("my_use_coupons") && @$order->coupon->id)
		|| count($order->tax_array) > 0){ ?>
	<!--  original subtotal -->
			<li class="item-container cols-3">	
		    	<div class="mysubtotal mycart-inner mymuse-mobile-hide"><b><?php echo JText::_('COM_MYMUSE_CART_SUBTOTAL'); ?></b></div>
		        <div class="myoriginalsubtotal mycart-inner" data-name="<?php echo JText::_('COM_MYMUSE_CART_SUBTOTAL'); ?>"><?php echo MymuseHelper::printMoney($order->subtotal_before_discount); ?></div>
		    </li>
	<?php } ?>	
	
	<?php 
	if($order->shopper_group_discount > 0.00){ 
		//for shopper group discount
		?>
	    <li class="item-container cols-3">	
	    	<div class="myshoppergroupdiscount mycart-inner mymuse-mobile-hide" ><b><?php echo JText::_('COM_MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?></b></div>
	        <div class="myshoppergroupdiscount mycart-inner" data-name="<?php echo JText::_('COM_MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?>">(<?php echo MymuseHelper::printMoney($order->shopper_group_discount); ?>)</div>
	    </li>
	<?php } ?>

	<?php 
	if($order->discount > 0.00){ 
		//for regular discount
		?>
	    <li class="item-container cols-3">	
	    	<div class="mydiscount mycart-inner mymuse-mobile-hide" ><b><?php echo JText::_('COM_MYMUSE_DISCOUNT'); ?></b></div>
	        <div class="mydiscount mycart-inner" data-name="<?php echo JText::_('COM_MYMUSE_DISCOUNT'); ?>">- <?php echo MymuseHelper::printMoney($order->discount); ?></div>
	    </li>
	<?php } ?>
	
	
	
	<?php //COUPONS
	if($params->get("my_use_coupons") && @$order->coupon->id){ ?>
	    <li class="item-container cols-3">	
	    	<div class="mycoupon mycart-inner mymuse-mobile-hide" ><b><?php echo JText::_('COM_MYMUSE_YOUR_COUPON'); ?></b> <?php echo $order->coupon->title ?></div>
	        <div class="mycoupon mycart-inner" data-name="<?php echo JText::_('COM_MYMUSE_YOUR_COUPON'); ?>">-<?php echo MymuseHelper::printMoney($order->coupon->discount); ?> </div>

	    </li>
	<?php } ?>
			
	<?php // SHIPPING
	if ($params->get("my_use_shipping") && @$order->order_shipping->cost > 0) { ?>
	    <li class="item-container cols-3">	
	    	<div  class="myshipping mycart-inner mymuse-mobile-hide"><b><?php echo JText::_('COM_MYMUSE_SHIPPING') ?></b></div>
	    	<div class="myshipping mycart-inner" data-name="<?php echo JText::_('COM_MYMUSE_SHIPPING'); ?>"><?php echo MymuseHelper::printMoney($order->order_shipping->cost); ?>
	    </li>

	<?php } ?>
	
	<?php // TAXES
	if(isset($order->tax_array)  && count($order->tax_array)){
	    foreach($order->tax_array as $key=>$val){
	    	$pre_key = preg_replace("/_/","", $key);
	    	$key = preg_replace("/_/"," ", $key);
	    	?>
	        <li class="item-container cols-3">	
	       		<div class="mytax mycart-inner mymuse-mobile-hide"></div>
	       		<div></div>
	        	<div class="mytax mycart-inner" data-name="<?php echo $key; ?>"><?php echo MymuseHelper::printMoney($val); ?></div>
			</li>
	<?php  } 
	} ?>

		
	<li class="item-container cols-3">	
		<div class="mytotal mycart-inner mymuse-mobile-hide "><b><?php echo JText::_('COM_MYMUSE_CART_TOTAL') ?></b></div>
		<div></div>
		<div class="mytotal mycart-inner" data-name="<?php echo JText::_('COM_MYMUSE_CART_TOTAL'); ?>"><b><?php echo MymuseHelper::printMoney($order->order_total); ?></b></div>
	</li>


  </ul>

  <a href="index.php?option=com_mymuse&task=checkout"><?php echo JText::_('COM_MYMUSE_CHECKOUT') ?></a>
<section>
<?php
}else{ 
	echo JText::_('COM_MYMUSE_YOUR_CART_IS_EMPTY');
}
?>