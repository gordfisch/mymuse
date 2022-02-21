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
<h2 class="my-title"><?php echo JText::_('COM_MYMUSE_YOUR_ORDER_HISTORY'); ?></h2>

<table class="mymuse_cart">
	<thead>
	<tr>
		<th class="myorderid" width="10%"><?php echo Text::_('COM_MYMUSE_ORDER_ID'); ?></th>
		<th class="mydate" width="50%"><?php echo Text::_('COM_MYMUSE_DATE'); ?></th>
		<th class="myorderstatus" width="40%"><?php echo Text::_('COM_MYMUSE_ORDER_STATUS'); ?></th>
		<th class="mytotal" width="40%" align="right"><?php echo Text::_('COM_MYMUSE_ORDER_TOTAL'); ?></th>
		
	</tr>
	</thead>
	<?php  
	$i = 0;
	foreach($this->orders as $order){ ?>
	<tr>
		<td class="myorderid"><a id="row<?php echo $i; $i++; ?>" href="<?php echo $order->url; ?>"><?php echo $order->id; ?></a></td>
		<td class="mydate" ><?php echo $order->created; ?></td>
		<td class="myorderstatus"><?php echo Text::_('COM_MYMUSE_'.strtoupper(MyMuseHelper::getStatusName($order->order_status))) ?></td>
		<td class="mytotal"><?php echo MyMuseHelper::printMoney($order->order_total); ?></td>
		
	</tr>
	<?php } ?>
</table>
