<?php 
/**
 * @version		$Id: product_physical.php 1986 2018-07-22 19:11:13Z gfisch $
 * @package		mymuse
 * @copyright	Copyright © 2018 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author 		info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */

use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

// no direct access
defined('_JEXEC') or die('Restricted access');

if( $this->item->product_physical && !count($this->item->items)) : 
	//print_r($this->item->attribs);
	//print_r($this->item->price);
?>
<!--  PRODUCT PHYSICAL -->

<div class="product-physical">
		<h3><?php echo JText::_('COM_MYMUSE_PRODUCT'); ?></h3>
		<table class="mymuse_cart cart">
			<thead>
				<tr class="mymuse_cart cart">

					<th class="mytitle cart" align="left" width="40%"><?php echo JText::_('COM_MYMUSE_NAME'); ?></th>
				

					<th class="myprice  cart" align="center" width="10%"><?php echo JText::_('COM_MYMUSE_COST'); ?></th>
					
       			<?php if ($this->params->get('product_show_quantity')) :?>
        				<th class="myquantity  cart" align="left" width="10%"><?php echo JText::_('COM_MYMUSE_QUANTITY'); ?></th>
      	    	<?php endif; ?>
      	    	
      	    	<th class="myselect  cart" align="left" width="20%"><?php echo JText::_('COM_MYMUSE_SELECT'); ?></th>
				</tr>
			</thead>
			<tr>
				<!--   td class="myselect"><span class="mycheckbox"><input type="checkbox" name="productid[]" 
				value="<?php echo $this->item->id; ?>" id="box<?php echo $this->check; $this->check++; ?>" 
				
				<?php if(isset($count) && $count == 1){ ?>
				CHECKED="CHECKED"
				<?php } ?>
				/></span></td -->
				<td class="mytitle cart"><?php echo $this->item->title; ?></td>
				
        			
				<td class="myprice cart"><?php  
				//if(!isset($this->item->price)){
				//	$this->item->price = $this->item->attribs->get('product_price_physical');
				//}

				echo MyMuseHelper::printMoneyPublic($this->item->price);
				?></td>
			<?php if ($this->params->get('product_show_quantity')) :?>
				<td class="myquantity cart"><input class="inputbox" type="text"
					name="quantity[<?php echo $this->item->id; ?>]" size="2" value="1" />
				</td>
			<?php endif; ?>
			<td class="myselect cart" nowrap><a href="javascript:void(0)"
					id="box_<?php echo $this->item->id; ?>"><img
						id="cart_image_<?php echo $this->item->id; ?>"
						src="<?php
                    if(in_array($this->item->id, $this->products)) :
                       echo JRoute::_("components/com_mymuse/assets/images/cart.png");
                    else :
                        echo JRoute::_("components/com_mymuse/assets/images/checkbox.png");
                     endif;
                 ?>"></a> <span class="mycheckbox"><input
						style="display: none;" type="checkbox" name="productid[]"
						value="<?php echo $this->item->id; ?>"
						id="box<?php echo $this->check; $this->check++; ?>" /> </span></td>
			</tr>
		</table>
</div>
<!-- END PRODUCT PHYSICAL -->
<?php endif;  ?>