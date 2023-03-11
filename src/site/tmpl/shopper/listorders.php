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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;


$params 	= $this->params;
?>
<h2 class="my-title"><?php echo Text::_('COM_MYMUSE_YOUR_ORDER_HISTORY'); ?></h2>

		
<section>
    <ul class="mymuse-container mymuse-list">
    <li class="list-orders item-container">
	
		<div class="myorderid mymuse_cart_top "><?php echo Text::_('COM_MYMUSE_ORDER_ID'); ?></div>
		<div class="mydate mymuse_cart_top "><?php echo Text::_('COM_MYMUSE_DATE'); ?></div>
		<div class="myorderstatus mymuse_cart_top "><?php echo Text::_('COM_MYMUSE_ORDER_STATUS'); ?></div>
		<div class="mytotal mymuse_cart_top "><?php echo Text::_('COM_MYMUSE_ORDER_TOTAL'); ?></div>

	</li>

	<?php  
	$i = 0;
	if($this->orders and is_countable($this->orders)) {
		foreach($this->orders as $order){ ?>
		<li class="list-orders item-container">
			<div class="mycart-inner myorderid" data-name="<?php echo Text::_('COM_MYMUSE_ORDER_ID'); ?>"><a id="row<?php echo $i; $i++; ?>" href="<?php echo $order->url; ?>"><?php echo $order->id; ?></a></div>
			<div class="mycart-inner mydate" data-name="<?php echo Text::_('COM_MYMUSE_DATE'); ?>" ><?php echo $order->created; ?></div>
			<div class="mycart-inner myorderstatus" data-name="<?php echo Text::_('COM_MYMUSE_ORDER_STATUS'); ?>"><?php echo Text::_('COM_MYMUSE_'.strtoupper(MyMuseHelper::getStatusName($order->order_status))) ?></div>
			<div class="mycart-inner mytotal" data-name="<?php echo Text::_('COM_MYMUSE_ORDER_TOTAL'); ?>"><?php echo MyMuseHelper::printMoney($order->order_total); ?></div>
			
		</li>
		<?php } 
	}?>
</ul>
