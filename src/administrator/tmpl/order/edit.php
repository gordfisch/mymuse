<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

HTMLHelper::_('script', 'com_contenthistory/admin-history-versions.js', ['version' => 'auto', 'relative' => true]);

$print = JFactory::getApplication()->input->get('print',0);
if($print){
	
	$content = '
	window.addEvent("load", function() { 
      window.print(); 
	});

	';
	$document = Factory::getDocument();
	$document->addScriptDeclaration($content);

}else{
	echo '<a href="index.php?option=com_mymuse&view=order&layout=edit&id='.$this->item->id.'&tmpl=component&print=1">'.JText::_("Print").'</a>';
}

?>
<h3>
	<?php echo JText::_('COM_MYMUSE_ORDER'); ?>

	<?php if($this->form->getValue('id') != ''){
		echo " : ".$this->form->getValue('id');
	} ?>
	<?php if($this->item->user->name != ''){
		echo " : ".$this->item->user->name;
	} ?>

</h3>
<form action="<?php echo Route::_('index.php?option=com_mymuse&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="taxrate-form" class="form-validate">


	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_MYMUSE_DETAILS')); ?>
		<div class="row">
			<div class="col-lg-6">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<?php echo $this->form->renderField('id'); ?>
							<?php echo $this->form->renderField('order_status'); ?>
							<?php echo $this->form->renderField('update_on_confirm'); ?>
							<?php echo $this->form->renderField('order_number'); ?>
							<?php echo $this->form->renderField('order_subtotal'); ?>
							<?php echo $this->form->renderField('order_shipping'); ?>
							<?php echo $this->form->renderField('order_total'); ?>
							<?php echo $this->form->renderField('currency'); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<?php echo $this->form->renderField('coupon_name'); ?>
							<?php echo $this->form->renderField('coupon_discount'); ?>
							<?php echo $this->form->renderField('discount'); ?>
							<?php echo $this->form->renderField('shopper_group_discount'); ?>
							<?php echo $this->form->renderField('created'); ?>
							<?php echo $this->form->renderField('modified'); ?>
							<?php echo $this->form->renderField('notes'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-lg-6">
				<div class="card">
					<div class="card-body">

	<fieldset class="adminform">
		<h2><?php echo JText::_('COM_MYMUSE_CUSTOMER') ?></h2>
		<table class="admintable" width="100%">
			<tr VALIGN=top>
				<td width=50%><!-- Begin BillTo -->

				<table class="adminlist">
					<tr>
						<td COLSPAN=2><b><?php echo JText::_('COM_MYMUSE_BILLING_ADDRESS') ?></b></td>
					</tr>

					<tr>
						<td><?php echo JText::_('COM_MYMUSE_FULL_NAME') ?>:</td>
						<td><?php echo $this->item->user->name ?></td>
					</tr>
					<?php if(isset($this->item->user->profile['address1'])){ ?>
					<tr valign="top">
						<td><?php echo JText::_('COM_MYMUSE_ADDRESS') ?>:</td>
						<td><?php echo $this->item->user->profile['address1'] ?> <BR>
						<?php echo isset($this->item->user->profile['address2'])? $this->item->user->profile['address2'] : '' ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['city'])){ ?>
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_CITY') ?>:</td>
						<td><?php echo $this->item->user->profile['city'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['region']) || isset($this->item->user->profile['region_name'])){ 
							
								if($this->item->user->profile['region'] && !is_numeric($this->item->user->profile['region'])){
									$this->item->user->profile['region_name'] = $this->item->user->profile['region'];
								}else{
									$db = JFactory::getDBO();
									$query = "SELECT * FROM #__mymuse_state WHERE id='".$this->item->user->profile['region']."'";
									$db->setQuery($query);
									if($row = $db->loadObject()){
										$this->item->user->profile['region'] = $row->id;
										$this->item->user->profile['region_name'] = $row->state_2_code;
									}
								}
							
						?>
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_STATE') ?>:</td>
						<td><?php echo $this->item->user->profile['region_name'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['postal_code'])){ ?>
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_ZIP') ?>:</td>
						<td><?php echo $this->item->user->profile['postal_code'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['country'])){ ?>
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_COUNTRY') ?>:</td>
						<td><?php echo $this->item->user->profile['country'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['phone'])){ ?>
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_PHONE') ?>:</td>
						<td><?php echo $this->item->user->profile['phone'] ?></td>
					</tr>
					<?php }?>
					
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_EMAIL') ?>:</td>
						<td><?php echo $this->item->user->email ?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_SHOPPERGROUP') ?>:</td>
						<td><?php echo $this->item->user->shopper_group_name ?> (<?php echo $this->item->user->shopper_group_discount; ?>%)</td>
					</tr>
				</table>
				<!-- End BillTo --></td>

				<td width=50%><?php 
				if($this->params->get('my_use_shipping') && $this->item->order_shipping > 0){
					?>
				<table class="adminlist">
					<tr>
						<td COLSPAN=2><b><?php echo JText::_('COM_MYMUSE_SHIPPING_ADDRESS') ?></b></td>
					</tr>
					<?php  if(isset($this->item->user->profile['shipping_company'])){?>
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_COMPANY') ?>:</td>
						<td><?php echo $this->item->user->profile['shipping_company'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['shipping_first_name'] )){?>
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_FULL_NAME') ?>:</td>
						<td><?php echo $this->item->user->profile['shipping_first_name'] ?> 
						<?php 
						if(isset($this->item->user->profile['shipping_last_name'])){
							echo $this->item->user->profile['shipping_last_name']; 
						}
							?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['shipping_address1'] )){?>
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_ADDRESS') ?>:</td>
						<td><?php echo $this->item->user->profile['shipping_address1'] ?> <BR>
						<?php echo $this->item->user->profile['shipping_address2'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['shipping_city'] )){?>
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_CITY') ?>:</td>
						<td><?php echo $this->item->user->profile['shipping_city'] ?></td>
					</tr>
					<?php }?>
					
					<?php if(isset($this->item->user->profile['shipping_region']) || isset($this->item->user->profile['shipping_region_name'])){ 
							
								if(!is_numeric($this->item->user->profile['shipping_region'])){
									$this->item->user->profile['shipping_region_name'] = $this->item->user->profile['shipping_region'];
								}else{
									$db = JFactory::getDBO();
									$query = "SELECT * FROM #__mymuse_state WHERE id='".$this->item->user->profile['shipping_region']."'";
									$db->setQuery($query);
									if($row = $db->loadObject()){
									
										$this->item->user->profile['shipping_region_name'] = $row->state_2_code;
									}
								}
							
						?>
					<tr>
					
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_STATE') ?>:</td>
						<td><?php echo $this->item->user->profile['shipping_region_name'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['shipping_postal_code'] )){?>
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_ZIP') ?>:</td>
						<td><?php echo $this->item->user->profile['shipping_postal_code'] ?></td>
					</tr>
					<?php }?>
					<?php if(isset($this->item->user->profile['shipping_country'] )){?>
					<tr>
						<td><?php echo JText::_('COM_MYMUSE_COUNTRY') ?>:</td>
						<td><?php echo $this->item->user->profile['shipping_country'] ?></td>
					</tr>
					<?php } ?>
				</table>
				<!-- End ShipTo --> <?php } ?></td>
			</tr>
		</table>
		</fieldset>
		


		<fieldset class="adminform">
				<h2><?php echo JText::_('COM_MYMUSE_ORDER_ITEMS') ?></h2>
		<?php $string = '
        		<table class="adminlist" cellpadding="5">
		<tr>
		<th align="left"><b>'. JText::_('Title') .'</b></th>
        ';
     $colspan = 3;
     if($this->params->get("my_show_sku")){ 
         $string .= '
		<th><b>'. JText::_('COM_MYMUSE_SKU') .'</b></th>
        ';
        $colspan = 4;
     }
     $string .= '
		<th align="right"><b>'. JText::_('COM_MYMUSE_PRICE') .'</b></th>
		<th align="right"><b>'. JText::_('COM_MYMUSE_QUANTITY') .'</b></th>
		<th align="right"><b>'. JText::_('COM_MYMUSE_SUBTOTAL') .'</b></th> 
		<th align="right"><b>'. JText::_('COM_MYMUSE_DOWNLOADS') .'</b></th> 
		<th align="right"><b>'. JText::_('COM_MYMUSE_EXPIRES') .'</b></th> 
		</tr>
		';
		

		  // LOOP THRU order_items
		for ($i = 0; $i < count($this->item->items); $i++) { 
	      	if ($i % 2){
	          $class = "row1";
	      	}else{
	          $class = "row0";
	      	}
	      	if($this->item->items[$i]->end_date){
	      		$end_date = date("Y-m-d h:i:s",$this->item->items[$i]->end_date);
	      	}else{
	      		$end_date = '';
	      	}
	      	if($this->item->items[$i]->product_in_stock == -1){
				$class .= " alert alert-danger";
			} 


			$string .= '
		    <tr class="'.$class .'">
		        <td>';
		    if($this->item->items[$i]->product_in_stock == -1 && $this->item->items[$i]->file_name == ''){
		    	$string .= '<a id="shipit" href="index.php?option=com_mymuse&view=order&layout=edit&task=order.shipitem&item_id='.$this->item->items[$i]->id.'&id='.$this->item->id.'">[Ship]</a> ';
		    }
		        
		    if($this->item->items[$i]->category_name != '' && $this->params->get('COM_MYMUSE_show_category')){
		    	$string .= $this->item->items[$i]->category_name .' : ';
		    }
		    if($this->item->items[$i]->parent_name != ''){
		    	$string .= $this->item->items[$i]->parent_name .' : ';
		    }
		        

			$string .= $this->item->items[$i]->product_name;
		
			if($this->item->items[$i]->file_name){
				$string .= ":<br />".$this->item->items[$i]->file_name;
			}
		
		
			$string .=	'</td>
		        ';
		    if($this->params->get("my_show_sku")){ 
                $string .= '
		        <td>'. $this->item->items[$i]->product_sku .'</td>
                ';
            }
            $string .= '
		        <td align="right">'. MyMuseHelper::printMoney($this->item->items[$i]->product_item_price) .' </td>
		        <td align="right">'. $this->item->items[$i]->product_quantity .'</td>
		        <td align="right">'. MyMuseHelper::printMoney($this->item->items[$i]->subtotal) .'</td>
		        <td align="right">'. $this->item->items[$i]->downloads .'</td>
		        <td align="right">'. $end_date .'</td>
		       </tr>
		       ';
	 	} 
//end of items
	 	$string .= '
	 			<tr>
	 		    	<td colspan="'.$colspan.'" align="right">&nbsp;</td>
	 		        <td>&nbsp;</td>
	 		        <td>&nbsp;</td>
	 				<td>&nbsp;</td>
	 		    </tr>
	 		   '; 
	$string .= '
			<tr">
		    	<td colspan="'.$colspan.'" align="right">'.JText::_('COM_MYMUSE_SUBTOTAL').':</td>
		        <td align="right">'.MyMuseHelper::printMoney($this->item->order_subtotal + @$this->item->coupon_discount + @$this->item->discount).'</td>
		        <td>&nbsp;</td>
				<td>&nbsp;</td>
		    </tr>
		   '; 
		    
	if($this->item->discount > 0.00){
		$string .= '
		    <tr>
		    	<td colspan="'.$colspan.'" align="right">'.JText::_('COM_MYMUSE_DISCOUNT').':
		    	</td>
		        <td align="right">'.MyMuseHelper::printMoney($this->item->discount).'</td>
		        <td>&nbsp;</td>
				<td>&nbsp;</td>
		    </tr>
	
		';
	}
	
		if($this->item->shopper_group_discount > 0.00){
			$string .= '
			<tr>
		    	<td colspan="'.$colspan.'" align="right">'.JText::_('COM_MYMUSE_CART_ORIGINAL_SUBTOTAL').':</td>
		        <td align="right">'.MyMuseHelper::printMoney($this->item->shopper_group_discount + $this->item->order_subtotal).'</td>
		        <td>&nbsp;</td>
				<td>&nbsp;</td>
		    </tr>

		    <tr>
		    	<td colspan="'.$colspan.'" align="right">'.JText::_('COM_MYMUSE_SHOPPING_GROUP_DISCOUNT').':
		    	'.$this->item->user->shopper_group_name.' '.$this->item->user->shopper_group_discount.' %</td>
		        <td align="right">'.MyMuseHelper::printMoney($this->item->shopper_group_discount).'</td>
		        <td>&nbsp;</td>
				<td>&nbsp;</td>
		    </tr>
 
		';
		}

		if($this->params->get('my_use_coupons') && $this->item->coupon_name){
			$string .= '
		    <tr>
		    <td  align="right" colspan="'.$colspan.'">'.JText::_("MYMUSE_COUPON")." : ". $this->item->coupon_name. ':
		        </td>
		        <td colspan="1" align="right"> - '. MyMuseHelper::printMoney($this->item->coupon_discount) .'
		        </td>
		        <td></td>
		    <td></td>
		    </tr>
		    ';
		}
		if($this->item->discount > 0.00 || 
				($this->params->get('my_use_coupons') && $this->item->coupon_name) || 
				$this->item->shopper_group_discount > 0.00){
			$string .= '<tr>
		    	<td colspan="'.$colspan.'" align="right">'.JText::_('COM_MYMUSE_CART_NEW_SUBTOTAL').':</td>
		        <td align="right">'.MyMuseHelper::printMoney($this->item->order_subtotal).'</td>
		        <td>&nbsp;</td>
				<td>&nbsp;</td>
		    </tr>';
		}
		
		
		if ($this->params->get('my_use_shipping') && $this->item->order_shipping > 0 && $this->item->order_shipments) { 
			$string .= '
		    <tr>
		    <td colspan="'.$colspan.'" align="right">' .JText::_('COM_MYMUSE_SHIPPING'). ': '
							.$this->item->order_shipments[0]->ship_carrier_name.': '
							.$this->item->order_shipments[0]->ship_method_name.'
		    </td>
		    <td colspan="1" align="right">'. MyMuseHelper::printMoney($this->item->order_shipping). '
		    </td>
		    <td></td>
		    <td></td>
		    </tr>
		    ';
		}
		
		// TAXES
		if(@$this->item->tax_array){
			reset($this->item->tax_array);
		    while(list($key,$val) = each($this->item->tax_array)){
		    	$key = preg_replace("/_/"," ", $key);
		    	$string .= '
		        <tr>
		        <td colspan="'.$colspan.'" align="right">'. $key. '</td>
		        <td colspan="1" align="right">'. MyMuseHelper::printMoney($val). '</td>
		        <td></td>
		    	<td></td>
		        </tr>
		        ';
		    } 
		}
		
		$string .= '
		<tr>
		    <td colspan="'.$colspan.'" align="right" class="textbox2"><b>' .JText::_('COM_MYMUSE_TOTAL'). ':</b></td>
		    <td colspan="1" align="right" class="textbox2"><b>' .MyMuseHelper::printMoney($this->item->order_total). ' 
		    ' .$this->item->order_currency.'</b></td>
		    <td></td>
		    <td></td>

		</tr>
		';
		if($this->item->reservation_fee > 0.00){
		$string .= '
			
		<tr>
		    <td colspan="'.$colspan.'" align="right" class="textbox2"></td>
		    <td colspan="1" align="right" class="textbox2"></td>
		    <td></td>
		    <td></td>
		</tr>
		<tr>
		    <td colspan="'.$colspan.'" align="right" class="textbox2"><b>' .JText::_('COM_MYMUSE_RESERVATION_FEE'). ':</b></td>
		    <td colspan="1" align="right" class="textbox2"><b>' .MyMuseHelper::printMoney($this->item->reservation_fee). ' </b></td>
		    <td></td>
		    <td></td>
		</tr>
		';
		if($this->item->non_res_fee > 0.00){
			$string .= '
			<tr>
		    	<td colspan="'.$colspan.'" align="right" class="textbox2"><b>' .JText::_('COM_MYMUSE_OTHER_CHARGES'). ':</b></td>
		    	<td colspan="1" align="right" class="textbox2"><b>' .MyMuseHelper::printMoney($this->item->non_res_fee). ' </b></td>
		    	<td></td>
		    	<td></td>
			</tr>
			
			';
			}
		$owing = $this->item->order_total - $this->item->paid_to_date; 
		$string .= '
		<tr>
			<td colspan="'.$colspan.'" align="right" class="textbox2"><b>' .JText::_('COM_MYMUSE_PAID_TO_DATE'). ':</b></td>
			<td colspan="1" align="right" class="textbox2"><b>' .MyMuseHelper::printMoney($this->item->paid_to_date). ' </b></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
		    <td colspan="'.$colspan.'" align="right" class="textbox2"><b>' .JText::_('COM_MYMUSE_OWING'). ':</b></td>
		    <td colspan="1" align="right" class="textbox2"><b>' .MyMuseHelper::printMoney($owing). ' </b></td>
		    <td></td>
		    <td></td>
		</tr>
		';
		}
		
		$string .= '
		</table>
		';
		echo $string;
		?>
        	</fieldset>

        	<?php if($this->item->downloadlink){ ?>
		 <table cellpadding="4">
 		<tr>
            <td>
            <fieldset class="adminform">
			  <h2><?php echo JText::_('COM_MYMUSE_DOWNLOADS') ?></h2>
			  <table class="admintable">
			    <tr>
			    <td width="600" valign="top">
        			<?php echo $this->item->downloadlink; ?> 
        		</td>
        		</tr>
        		<tr>
			    <td width="600" valign="top">
        			<a 
href="index.php?option=com_mymuse&view=order&layout=edit&task=order.resetDownloads&id=<?php echo $this->item->id; ?>">
<?php echo JText::_('COM_MYMUSE_RESET_DOWNLOADS') ?></a>
        		</td>
        		</tr>
        	  </table>
        	</fieldset>
        	</td>
        </tr>
        </table>
        <br />
        
        <?php if($this->item->downloads){ ?>
        <table cellpadding="4">
        <tr>
        	<th>Name</th>
        	<th>Email</th>
        	<th>Date</th>
        	<th>Filename</th>
        </tr>
        <?php foreach($this->item->downloads as $d){ ?>
        <tr>
        	<td><?php echo $d->user_name; ?></td>
        	<td><?php echo $d->user_email; ?></td>
        	<td><?php echo $d->date; ?></td>
        	<td><?php echo $d->product_filename; ?></td>
        </tr>
        <?php } ?>
        
        
        
        <?php }?>
<?php }?>
 </table>
</fieldset>


					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'payments', Text::_('COM_MYMUSE_PAYMENTS')); ?>
		<fieldset class="adminform">

			<legend><?php echo JText::_('COM_MYMUSE_PAYMENTS') ?></legend>
				<?php 
					  		$row1 = '	
					  		<tr>
					  			<td valign="top">'.JText::_('COM_MYMUSE_ID').'</td>';
					  		$row2 = '	
					  		<tr>
					  			<td valign="top">'.JText::_('COM_MYMUSE_DATE').'</td>';
					  		$row3 = '	
					  		<tr>
					  			<td valign="top">'.JText::_('COM_MYMUSE_METHOD').'</td>';
					  		$row4 = '	
					  		<tr>
					  			<td valign="top">'.JText::_('COM_MYMUSE_AMOUNT').'</td>';
					  		$row5 = '	
					  		<tr>
					  			<td valign="top">'.JText::_('COM_MYMUSE_CURRENCY').'</td>';
					  		$row6 = '	
					  		<tr>
					  			<td valign="top">'.JText::_('COM_MYMUSE_RATE').'</td>';
					  		$row7 = '	
					  		<tr>
					  			<td valign="top">'.JText::_('COM_MYMUSE_FEES').'</td>';
					  		$row8 = '	
					  		<tr>
					  			<td valign="top">'.JText::_('COM_MYMUSE_TRANS_ID').'</td>';
					  		$row9 = '	
					  		<tr>
					  			<td valign="top">'.JText::_('COM_MYMUSE_STATUS').'</td>';
					  		$row10 = '	
					  		<tr>
					  			<td valign="top">'.JText::_('COM_MYMUSE_DESCRIPTION').'</td>';
				
			
				if(count($this->item->order_payments)){
					  foreach($this->item->order_payments as $payment){ 

					    $row1 .= '	
					  			<td>'.$payment->id.'</td>';
					    $row2 .= '	
					  			<td>'.$payment->date.'</td>';
					    $row3 .= '	
					  			<td>'.$payment->plugin .' '.$payment->institution.'</td>';
					    $row4 .= '	
						  		<td>'.$payment->amountin .'</td>';
					    $row5 .= '	
						  		<td>'.$payment->currency .'</td>';
					    $row6 .= '	
					  			<td>'.$payment->rate.'</td>';
					    $row7 .= '	
					  			<td>'.$payment->fees .'</td>';
					    $row8 .= '	
					  			<td>'.$payment->transaction_id.'</td>';
					    $row9 .= '	
					  			<td>'.$payment->transaction_status.'</td>';
					    $row10 .= '	
					  			<td>'.$payment->description.'</td>';

					  }
				}	
					  		

				if(!$print){
					  	
					    $row1 .= '	
					  			<td>'.JText::_('ADD').'</td>';
					    $row2 .= '	
					  			<td valign="top" nowrap="nowrap">'.$this->form->getInput('payment_date').'</td>';
					    $row3 .= '	
					  			<td>'.$this->lists['plugins'].'<br />'.JText::_('COM_MYMUSE_OTHER').'<br />
					    	<input type="text" name="payment_institution" value="" size="10" /></td>';
					    $row4 .= '	
					  			<td><input type="text" name="payment_amountin" value="" size="6" /></td>';
					    $row5 .= '	
					  			<td>'.$this->lists['currencies'].'</td>';
					    $row6 .= '	
					  			<td><input type="text" name="payment_rate" value="1.00000" size="5" /></td>';
					    $row7 .= '	
					  			<td><input type="text" name="payment_fees" value="0.00" size="5" /></td>';
					    $row8 .= '	
					  			<td><input type="text" name="payment_transaction_id" value="" /></td>';
					    $row9 .= '	
					  			<td><input type="text" name="payment_transaction_status" value="" size="5" /></td>';
					    $row10 .= '	
					  			<td><textarea name="payment_description" rows="3" cols="30" style="width:200px"></textarea></td>';

				}
				
				//finsih off the row
				$row1 .= '
				</tr>';
				$row2 .= '
				</tr>';
				$row3 .= '
				</tr>';
				$row4 .= '
				</tr>';
				$row5 .= '
				</tr>';
				$row6 .= '
				</tr>';
				$row7 .= '
				</tr>';
				$row8 .= '
				</tr>';
				$row8 .= '
				</tr>';
				$row10 .= '
				</tr>';

			
				?>
					  			
				<table class="admintable">
					<?php echo $row1; ?>
					<?php echo $row2; ?>
					<?php echo $row3; ?>
					<?php echo $row4; ?>
					<?php echo $row5; ?>
					<?php echo $row6; ?>
					<?php echo $row7; ?>
					<?php echo $row8; ?>
					<?php echo $row9; ?>
					<?php echo $row10; ?>

				</table>
				
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>
<?php echo $this->form->getInput('checked_out'); ?>
<?php echo $this->form->getInput('checked_out_time'); ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="old_status" value="<?php echo $this->item->order_status; ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
