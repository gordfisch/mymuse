<?php
/**
 * @version		$$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

	$lists 				=& $this->lists;
	$order 				=& $this->item;
	$order->colspan 	= 3;
	$order->colspan2 	= 1;
	$shopper 			= $this->shopper;
	$user 				=& $this->user;
	$form 				=& $this->form;
	$extra 				=& $this->extra;
	$params 			= $this->params;
	$store 				= $this->store;

	$downloads = 0;
	$download_header = '<table class="contentpaneopen" style="border-collapse: collapse; max-width: 800px;">
	<tr>
		<td><h3 style="text-decoration: underline;">'.Text::_('COM_MYMUSE_DOWNLOADS_IN_THIS_ORDER').'</h3></td>
	</tr>
</table>
<table class="contentpaneopen">
<tr>
		<td>
		<ul>
';
	

foreach($order->items as $item){ 
	if($item->file_name != ""){

		if($item->product_in_stock == -1){
			$download_header .= '
			<li>'.$item->product_name.': '.Text::_('COM_MYMUSE_PREORDER').'</li>
			';
		}else{
			$download_header .= '
			<li>'.$item->product_name.'</li>
			';
		}

		$downloads = 1;
 	}
} 

$link = ROUTE::_($order->downloadlink);


$download_header .= '
			</ul>
			</td>
		</tr>
	</table>
	';
if($link) {
	$download_header .= '
		<table style="border-collapse: collapse; max-width: 800px;" class="mymuse_cart cart">
		<tr>
			<td><div class="mymuse_header">'.Text::_('COM_MYMUSE_DOWNLOAD_LINK_PLEASE_CLICK').'</div></td>
		</tr>
		<tr>
			<td><a href="'.$link.'">'.$link.'</a></td>
		</tr>
	</table>

	';
}

?>
<!DOCTYPE HTML>
<html lang="en-gb" dir="ltr">
<head>
<title><?php echo $store->title; ?></title>
</head>
    <body>    

    <table width="100%" class="mymuse_cart my-email-header" 
    style="border-collapse: collapse; max-width: 800px;">
      <tr class="mymuse_cart my-email-header">
        <td width="10%" valign="top"  class="mymusecart cart" rowspan="8"><a href="'.JURI::root().'"><img 
        style="padding:0 20px 20px 0" src="<?php echo JURI::root().$params->get('store_thumb_image'); ?>" border="0"></a></td>
  		</tr>

        <tr><td width="90%" class="my-email-header mytitle"><?php echo $store->title; ?></td></tr>
        <tr><td class="my-email-header myaddress"><?php echo $params->get('address_1').' '.$params->get('address_2'); ?></td></tr>
        <tr><td class="my-email-header mycity"><?php echo $params->get('city').', '.$params->get('state'); ?></td></tr>
        <tr><td class="my-email-header mycountry"><?php echo $params->get('country').', '.$params->get('zip'); ?></td></tr>
        <tr><td class="my-email-header myphone">Phone: <?php echo $params->get('phone'); ?></td></tr>
        <tr><td class="my-email-header myemail">Email: <a href="mailto: <?php echo $params->get('contact_email'); ?>"><?php echo $params->get('contact_email'); ?></a></td></tr>
        <tr><td class="my-email-header myweb">Web: <a href="<?php echo JURI::root(); ?>"><?php echo JURI::root(); ?></a></td></tr>

 
    </table>
    <?php echo $this->my_email_msg; ?>



<?php

if($downloads && $order->order_status == "C"){
	echo $download_header;
}
?>
<!-- Begin Order Summary -->
	<h3 style="text-decoration: underline;" class="my-title"><?php echo Text::_('COM_MYMUSE_ORDER_SUMMARY'); ?></h3>
     <table class="mymuse_cart" style="border-collapse: collapse; max-width: 800px;">
        <tr>
            <td width="50%"><?php echo Text::_('COM_MYMUSE_ORDER_NUMBER') ?>:</td>
            <td width="50%"><?php echo sprintf("%08d", $order->id) ?></td>
        </tr>
        <tr>
            <td><?php echo Text::_('COM_MYMUSE_ORDER_DATE') ?>:</td>
            <td><?php echo $order->created ?></td>
        </tr>
        <tr>
            <td><?php echo Text::_('COM_MYMUSE_ORDER_STATUS') ?>:</td>
            <td><?php echo Text::_(MyMuseHelper::getStatusName($order->order_status)) ?></td>
        </tr>
        <tr>
            <td><?php echo Text::_('COM_MYMUSE_ORDER_TOTAL') ?>:</td>
            <td><?php echo MyMuseHelper::printMoney($order->order_total)." ".$order->order_currency ?></td>
        </tr>
    <?php if($order->reservation_fee > 0){ ?>
        <tr>
            <td><?php echo Text::_('COM_MYMUSE_RESERVATION_FEE') ?>:</td>
            <td><?php echo MyMuseHelper::printMoney($order->reservation_fee)." ".$order->order_currency ?></td>
        </tr>
        	<?php if($order->non_res_total > 0){ ?>
        	<tr>
            	<td><?php echo Text::_('COM_MYMUSE_OTHER_CHARGES') ?>:</td>
            	<td><?php echo MyMuseHelper::printMoney($order->non_res_total)." ".$order->order_currency ?></td>
        	</tr>
        	<tr>
            <td><?php echo Text::_('COM_MYMUSE_PAID') ?>:</td>
            <td><?php echo MyMuseHelper::printMoney($order->pay_now)." ".$order->order_currency ?></td>
        </tr>
    	<?php } ?>
    <?php } ?>
	</table>
	<br />
   <!-- Begin 2 column bill-ship to -->
        <h3 style="text-decoration: underline;" class="my-title"><?php echo Text::_('COM_MYMUSE_SHOPPER_INFORMATION') ?></h3>

        <div class="shopper">
            <table class="mymuse_cart" style="border-collapse: collapse; max-width: 800px;">
                <tr class="mymuse_cart_top">
                <td class="mymuse_cart_top" COLSPAN="2"><b><?php echo Text::_('COM_MYMUSE_BILLING_ADDRESS') ?></b></td>
                </tr>
                <tr>
                <td width="50%"><?php echo Text::_('COM_MYMUSE_FULL_NAME') ?>:</td>
                <td width="50%">
                <?php echo $shopper->first_name ?>
        
                <?php echo $shopper->last_name ?>
                </td>
                </tr>
                <?php if($shopper->address1){?>
                <tr VALIGN=TOP>
                <td><?php echo Text::_('COM_MYMUSE_ADDRESS') ?>:</td>
                <td>
                <?php echo $shopper->address1 ?>
                <BR>
                <?php echo $shopper->address2 ?>
                </td>
                </tr>
                <?php }?>
                <?php if($shopper->city){?>
                <tr>
                <td><?php echo Text::_('COM_MYMUSE_CITY') ?>:</td>
                <td><?php echo $shopper->city ?></td>
                </tr>
                <?php }?>
               
                <?php if(isset($shopper->region)){ 
							if(!isset($shopper->region_name)){
								if(!is_numeric($shopper->region)){
									$shopper->region_name = $shopper->region;
								}else{
									$db = JFactory::getDBO();
									$query = "SELECT * FROM #__mymuse_state WHERE id='".$shopper->region."'";
									$db->setQuery($query);
									if($row = $db->loadObject()){
										$shopper->region_name = $row->state_name;
									}
								}
							}
						
						?>
					<tr>
						<td><?php echo Text::_('COM_MYMUSE_STATE') ?>:</td>
						<td><?php echo $shopper->region_name ?></td>
					</tr>
				<?php }?>
                
                
                <?php if($shopper->postal_code){?>
                <tr>
                <td><?php echo Text::_('COM_MYMUSE_ZIP') ?>:</td>
                <td><?php echo $shopper->postal_code ?></td>
                </tr>
                <?php }?>
                <?php if($shopper->country){?>
                <tr>
                <td><?php echo Text::_('COM_MYMUSE_COUNTRY') ?>:</td>
                <td><?php echo $shopper->country ?></td>
                </tr>
                <?php }?>
                <?php if($shopper->phone ){?>
                <tr>
                <td><?php echo Text::_('COM_MYMUSE_PHONE') ?>:</td>
                <td><?php echo $shopper->phone ?></td>
                </tr>
                <?php }?>
                <?php if($shopper->mobile){?>
                <tr>
                <td><?php echo Text::_('COM_MYMUSE_MOBILE') ?>:</td>
                <td><?php echo $shopper->mobile ?></td>
                </tr>
                <?php }?>
                <?php if($shopper->fax){?>
                <tr>
                <td><?php echo Text::_('COM_MYMUSE_FAX') ?>:</td>
                <td><?php echo $shopper->fax ?></td>
                </tr>
                <?php }?>

                <tr>
                <td><?php echo Text::_('COM_MYMUSE_EMAIL') ?>:</td>
                <td><?php echo $shopper->email ?></td>
                </tr>
            </table>
            <!-- End BillTo --> 
        
        </div>

        
        <?php 
        if($params->get('my_use_shipping') 
        		&& $order->order_shipping > 0
        		&& isset($shopper->profile['shipping_first_name'])
        		){
        ?>
        <div class="shopper">
            <table class="mymuse_cart" style="border-collapse: collapse; max-width: 800px;">
                <tr class="mymuse_cart_top">
                <th class="mymuse_cart_top" COLSPAN="2"><b><?php echo Text::_('COM_MYMUSE_SHIPPING_ADDRESS') ?></b></th>
                </tr>
                <tr>
                <td><?php echo Text::_('COM_MYMUSE_COMPANY') ?>:</td>
                <td><?php echo @$shopper->profile['shipping_company'] ?></td>
                </tr>
                <tr>
                <td><?php echo Text::_('COM_MYMUSE_FULL_NAME') ?>:</td>
                <td>
                <?php echo $shopper->profile['shipping_first_name'] ?>
                <?php echo $shopper->profile['shipping_last_name'] ?>
                </td>
                </tr>
                <tr VALIGN=TOP>
                <td><?php echo Text::_('COM_MYMUSE_ADDRESS') ?>:</td>
                <td>
                <?php echo $shopper->profile['shipping_address1'] ?>
                <BR>
                <?php echo $shopper->profile['shipping_address2'] ?>
                </td>
                </tr>
                <tr>
                <td><?php echo Text::_('COM_MYMUSE_CITY') ?>:</td>
                <td><?php echo $shopper->profile['shipping_city'] ?></td>
                </tr>
                <tr>
                <td><?php echo Text::_('COM_MYMUSE_STATE') ?>:</td>
                <td><?php echo $shopper->profile['shipping_region_name'] ?></td>
                </tr>
                <tr>
                <td><?php echo Text::_('COM_MYMUSE_ZIP') ?>:</td>
                <td><?php echo $shopper->profile['shipping_postal_code'] ?></td>
                </tr>
                <tr>
                <td><?php echo Text::_('COM_MYMUSE_COUNTRY') ?>:</td>
                <td><?php echo $shopper->profile['shipping_country'] ?></td>
                </tr>
            </table>
            <!-- End ShipTo -->
        </div>
          <?php 
        }
        ?>
            <!-- End Customer Information --> 
        <br />


		<div style="clear: both;">
		<!-- start of basket -->
		<h3 style="text-decoration: underline;" class="my-title"><?php echo Text::_('COM_MYMUSE_SHOPPING_CART'); ?></h3>
		
		<table class="mymuse_cart" style="border-collapse: collapse; max-width: 800px;">
			<tr class="mymuse_cart_top">
				<th class="mymuse_cart_top" width="55%"><b><?php echo Text::_('COM_MYMUSE_TITLE'); ?></b></th>
				<th class="mymuse_cart_top" align="right" width=15%"><b><?php echo Text::_('COM_MYMUSE_CART_PRICE'); ?></b></th>
				<th class="mymuse_cart_top" align="right" width="15%"><b><?php echo Text::_('COM_MYMUSE_CART_QUANTITY'); ?></b></th>
				<th class="mymuse_cart_top" align="right" width="15%"><b><?php echo Text::_('COM_MYMUSE_CART_SUBTOTAL'); ?></b></th>
			</tr>
		
		<?php
		  // LOOP THRU order_items
		  for ($i=0;$i<count($order->items);$i++) { 
		      if ($i % 2){
		          $class = "row1";
		      }else{
		          $class = "row2";
		      }
		      $order_item[$i] = $order->items[$i];
		?>
		
		    <tr class="<?php echo $class ?>">
		        <td align="left">
		        <?php 
		        if($order_item[$i]->category_name != ''){
		        	echo $order_item[$i]->category_name." : ";
		        }
		        if($order_item[$i]->parent_name != ''){
		        	echo $order_item[$i]->parent_name." : ";
		        }
		        ?>
		        
		        <?php echo $order_item[$i]->product_name; ?>
		        
		        <?php if(isset($order_item[$i]->file_name)){ ?>
		        	 <br /><?php echo $order_item[$i]->file_name; ?> 
		        <?php } ?>

		        <?php if($order_item[$i]->product_in_stock == -1) { 

		        	echo '<span class="preorder">'.Text::_('COM_MYMUSE_PRODUCT_PREORDER_LABEL').'</span>';
		        }
		        ?>



		        </td>
		        <td align="right"> <?php echo MyMuseHelper::printMoney($order_item[$i]->product_item_price); ?></td>
		        <td align="center"><?php echo $order_item[$i]->product_quantity; ?></td>
		        <td align="right"><?php echo MyMuseHelper::printMoney($order_item[$i]->subtotal); ?></td>
		       </tr>
		<?php } ?>
		
		<!--  original subtotal -->
			<tr>
		    	<td class="mobile-hide" colspan="<?php echo $order->colspan; ?>"><b><?php echo Text::_('COM_MYMUSE_CART_SUBTOTAL'); ?>:</b></td>
		        <td align="right" class="myoriginalsubtotal" colspan="<?php echo $order->colspan2; ?>"><b><?php echo MyMuseHelper::printMoney($order->order_subtotal + @$order->coupon_discount +@$order->discount); ?></b></td>
		        
		    </tr>
		    <tr>
		    	<td colspan="<?php echo $order->colspan + $order->colspan2; ?>"><hr style="width: 100%"></td>
		    </tr>
		    
		    
		<?php //for shopper group discount
		if($order->shopper_group_discount > 0.00){ ?>
		    <tr>
		    	<td class="mobile-hide" colspan="<?php echo $order->colspan; ?>"><b><?php echo Text::_('COM_MYMUSE_SHOPPING_GROUP_DISCOUNT'); ?></b>
		    	<?php echo $order->user->shopper_group_name; ?> <?php echo $order->user->shopper_group_discount; ?> %</td>
		        <td align="right" class="myshoppergroupdiscount" colspan="<?php echo $order->colspan2; ?>">(<?php echo MyMuseHelper::printMoney($order->shopper_group); ?>)</td>
		     
		    </tr>
		<?php } ?>
		
		<?php //for regular discount
		if($order->discount > 0.00){ ?>
		    <tr>
		    	<td class="mobile-hide" colspan="<?php echo $order->colspan; ?>"><b><?php echo Text::_('COM_MYMUSE_DISCOUNT'); ?></b>
		    	</td>
		        <td align="right" class="mydiscount" colspan="<?php echo $order->colspan2; ?>">(<?php echo MyMuseHelper::printMoney($order->discount); ?>)</td>
		     
		    </tr>
		<?php } ?>
		
		
		
		<?php if(isset($order->coupon_discount) && $order->coupon_discount != "0.00"){ ?>
		    <tr>
		    	<td colspan="<?php echo $order->colspan; ?>"><b><?php echo Text::_('COM_MYMUSE_YOUR_COUPON'); ?></b> : <?php echo $order->coupon_name ?>:
		        </td>
		        <td align="right" colspan="<?php echo $order->colspan2; ?>"><?php echo MyMuseHelper::printMoney($order->coupon_discount); ?>
		        </td>

		    </tr>
		<?php } ?>
				
		<?php if ($params->get("my_use_shipping") && $order->order_shipping > 0) { ?>
		    <tr>
		    <td colspan="<?php echo $order->colspan; ?>"><b><?php echo Text::_('COM_MYMUSE_SHIPPING') ?></b></td>
		    <td colspan="<?php echo $order->colspan2; ?>" align="right"><?php echo MyMuseHelper::printMoney($order->order_shipping); ?>
		    </td>
		    </tr>
		<?php } ?>
		
		<?php // TAXES
		if(isset($order->tax_array)){

		    foreach($order->tax_array as $key => $val) { ?>
		        <tr>
		        <td colspan="<?php echo $order->colspan; ?>"><b><?php echo $key; ?></b></td>
		        <td colspan="<?php echo $order->colspan2; ?>" align="right"><?php echo MyMuseHelper::printMoney($val); ?></td>
		        </tr>
		<?php  } 
		} ?>
		
		
		<tr>
		    <td colspan="<?php echo $order->colspan; ?>" class="textbox2"><b><?php echo Text::_('COM_MYMUSE_CART_TOTAL') ?>:</b></td>
		    <td colspan="<?php echo $order->colspan2; ?>" class="textbox2" align="right"><b><?php echo MyMuseHelper::printMoney($order->order_total); ?>
		    <?php echo $order->order_currency; ?></b></td>

		</tr>
		
		
		<?php  if($order->reservation_fee > 0){ ?>
		<tr>
		    <td colspan="<?php echo $order->colspan; ?>"><b><?php echo Text::_('COM_MYMUSE_RESERVATION_FEE') ?>:</b></td>
		    <td colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->reservation_fee); ?></b>
		    </td>
		</tr>
			<?php  if($order->non_res_total > 0){ ?>
			<tr>
		    	<td colspan="<?php echo $order->colspan; ?>"><b><?php echo Text::_('COM_MYMUSE_OTHER_CHARGES') ?>:</b></td>
		    	<td colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->non_res_total); ?></b>
		    	</td>
			</tr>
			<tr>
		    	<td colspan="<?php echo $order->colspan; ?>"><b><?php echo Text::_('COM_MYMUSE_PAYNOW') ?>:</b></td>
		    	<td colspan="<?php echo $order->colspan2; ?>" align="right"><b><?php echo MyMuseHelper::printMoney($order->must_pay_now); ?></b>
		    	</td>

			</tr>
			<?php } ?>
		
		<?php } ?>
		
		</table>
		</div>

		<br />


        <?php if($extra){ ?>
        <h3 style="text-decoration: underline;" class="my-title"><?php echo Text::_('COM_MYMUSE EXTRA INFO'); ?></h3>
        <table class="mymuse_cart" style="border-collapse: collapse; max-width: 800px;">
        <tr>
			<td width="100%" valign="top"><?php print_pre($extra); ?> </td>
        </tr>
        </table>
        <?php }?>
        

    </body>
    </html>

