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

use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// no direct access
defined('_JEXEC') or die('Restricted access');
$params 	= $this->params;
?>

<?php if($this->order->need_shipping && count($this->shipMethods) == 0){ ?>
	<div class="message alert"><?php echo Text::_('COM_MYMUSE_NO_SHIPPING_AVAILABLE'); ?></div>
<?php  }else{ ?>


<form action="<?php echo Route::_('index.php?option=com_mymuse&task=confirm&Itemid='.$this->Itemid); ?>" 
method="post" name="adminForm">


<?php if($this->order->need_shipping){ ?>

<ul class="mymuse-container mymuse-cart">

	<li class="mymuse-shipping item-container my-grid columns-3">
		<div class="myselect mymuse_cart_top"><b><?php echo Text::_('COM_MYMUSE_SELECT'); ?></b></div>
		<div class="myshipmethod mymuse_cart_top "><b><?php echo Text::_('COM_MYMUSE_SHIP_METHOD'); ?></b></div>
		<div class="myprice mymuse_cart_top "><b><?php echo Text::_('COM_MYMUSE_COST'); ?></b></div>
	</li>

<?php foreach($this->shipMethods as $sm){ ?>
	<li class="mymuse-shipping item-container my-grid columns-3">
		<div class="myselect mycart-inner" data-name="<?php echo Text::_('COM_MYMUSE_SELECT'); ?>"><input type="radio" name="shipmethodid" value="<?php echo $sm->id; ?>" 
			id="shipmethodid<?php echo $sm->id; ?>" /></div>
		<div class="myshipmethod mycart-inner" data-name="<?php echo Text::_('COM_MYMUSE_SHIP_METHOD'); ?>"><?php echo $sm->ship_carrier_name." ".$sm->ship_method_name; ?></div>
		<div class="myprice mycart-inner" data-name="<?php echo Text::_('COM_MYMUSE_COST'); ?>"><?php echo MyMuseHelper::printMoney($sm->cost); ?></div>
	</li>
<?php } ?>

	<li class="mymuse-shipping item-container my-grid columns-3">
		<div class="pull-left mymuse_button_left">
		<button class="btn uk-button btn-primary" type="submit" id="shipping">
		<?php echo $this->button; ?>
		</button></div>
		<div></div>
		<div></div>
	</li>
</ul>

<?php  }else{ ?>
	<input type="hidden" name="shipmethodid" value="60">
		<?php echo Text::_('COM_MYMUSE_NO_SHIPPING_NEEDED')?> <input type="submit" class="btn uk-button btn-primary" name="confirm" value="<?php echo Text::_('Next'); ?>">
<?php }?>
</form>

<?php } ?>
