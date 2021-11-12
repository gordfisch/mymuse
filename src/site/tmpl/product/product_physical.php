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
// no direct access
defined('_JEXEC') or die('Restricted access');
$product 	=& $this->item;
?>
<!--  PRODUCT PHYSICAL -->
		<h3><?php echo JText::_('MYMUSE_PRODUCT'); ?></h3>
		<table class="mymuse_cart cart">
			<thead>
				<tr class="mymuse_cart cart">

					<th class="mytitle cart" align="left" width="40%"><?php echo JText::_('MYMUSE_NAME'); ?></th>
					
				<?php  if($this->params->get('product_show_filetime', 0)) :?>
       				<th class="mytime  cart" align="center" width="10%">
       					<?php echo JText::_('MYMUSE_TIME'); ?></th>
       			<?php endif; ?>

					<th class="myprice  cart" align="center" width="10%"><?php echo JText::_('MYMUSE_COST'); ?></th>
					
       			<?php if ($this->params->get('product_show_quantity')) :?>
        			<th class="myquantity  cart" align="left" width="10%"><?php echo JText::_('MYMUSE_QUANTITY'); ?></th>
      	    	<?php endif; ?>
      	    	
      	    		<th class="myselect  cart" align="left" width="20%"><?php echo JText::_('MYMUSE_SELECT'); ?></th>
				</tr>
			</thead>
			<tr>
				<!--   td class="myselect"><span class="mycheckbox"><input type="checkbox" name="productid[]" 
				value="<?php echo $product->id; ?>" id="box<?php echo $this->check; $this->check++; ?>" 
				
				<?php if(isset($count) && $count == 1){ ?>
				CHECKED="CHECKED"
				<?php } ?>
				/></span></td -->
				<td class="mytitle cart"><?php echo $product->title; ?></td>
				
				<!--  TIME COLUMN -->
        		<?php  if($this->params->get('product_show_filetime', 0)) : ?>	
        			<td class="mymuse_cart_top mytime">
        			<?php echo $this->item->product_full_time ?>
        			</td>
        		<?php endif; ?>
        			
				<td class="myprice cart"><?php  echo MyMuseHelper::printMoneyPublic($product->price);
				?></td>
			<?php if ($this->params->get('product_show_quantity')) :?>
				<td class="myquantity cart"><input class="inputbox" type="text"
					name="quantity[<?php echo $product->id; ?>]" size="2" value="1" />
				</td>
			<?php endif; ?>
			<td class="myselect cart" nowrap><a href="javascript:void(0)"
					id="box_<?php echo $product->id; ?>"><img
						id="cart_image_<?php echo $product->id; ?>"
						src="<?php
                    if(in_array($product->id, $this->products)) :
                       echo JRoute::_("components/com_mymuse/assets/images/cart.png");
                    else :
                        echo JRoute::_("components/com_mymuse/assets/images/checkbox.png");
                     endif;
                 ?>"></a> <span class="mycheckbox"><input
						style="display: none;" type="checkbox" name="productid[]"
						value="<?php echo $product->id; ?>"
						id="box<?php echo $this->check; $this->check++; ?>" /> </span></td>
			</tr>
		</table>

		<!-- END PRODUCT PHYSICAL -->