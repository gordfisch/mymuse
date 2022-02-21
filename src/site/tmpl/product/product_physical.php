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

	$cols = 3;
	if ($this->params->get('product_show_quantity')) :
		$cols = 4;
	endif;
?>
<!--  PRODUCT PHYSICAL -->

<div class="product-physical">
		<h3><?php echo JText::_('COM_MYMUSE_PRODUCT'); ?></h3>

				<div class="cart-container columns-<?php echo $cols; ?>">

					<div class="header"><?php echo JText::_('COM_MYMUSE_NAME'); ?></div>
					<div class="header"><?php echo JText::_('COM_MYMUSE_COST'); ?></div>
				<?php if ($this->params->get('product_show_quantity')) :?>
					<div class="header"><?php echo JText::_('COM_MYMUSE_QUANTITY'); ?></div>
				<?php endif; ?>
					<div class="header"><?php echo JText::_('COM_MYMUSE_SELECT'); ?></div>


					<div class="mytitle myrow"><?php echo $this->item->title; ?></div>
					<div class="myprice myrow"><?php echo MyMuseHelper::printMoneyPublic($this->item->price); ?></div>
				<?php if ($this->params->get('product_show_quantity')) :?>
					<div class="myquantity myrow"><input class="inputbox" type="text"
							name="quantity[<?php echo $this->item->id; ?>]" size="2" value="1" /></div>
				<?php endif; ?>
					<div class="myselect myrow"><a href="javascript:void(0)"
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
								id="box<?php echo $this->check; $this->check++; ?>" /> </span></div>
				</div>
</div>
<!-- END PRODUCT PHYSICAL -->
<?php endif;  ?>