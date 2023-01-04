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
use Joomla\CMS\Language\Text;

// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
$shopper 	= $this->shopper;
$order 		= $this->order;
$params 	= $this->params;
?>
     <ul class="mymuse-container">
 
        <!-- Begin Order Summary -->
        <li class="mymuse-grid-1-2">
            <div class="mymuse-cart-top"><b><?php echo Text::_('COM_MYMUSE_ORDER_SUMMARY') ?></b></div>
            <div></div>
        </li>
        <li class="mymuse-grid-1-2">
            <div class="mobile-hide"><?php echo Text::_('COM_MYMUSE_ORDER_NUMBER') ?>:</div>
            <div class="myordernumber"><?php echo sprintf("%08d", $order->id) ?></div>
        </li>
        <li class="mymuse-grid-1-2">
            <div class="mobile-hide"><?php echo Text::_('COM_MYMUSE_ORDER_DATE') ?>:</div>
            <div class="myorderdate"><?php echo $order->created ?></div>
        </li>
        <li class="mymuse-grid-1-2">
            <div class="mobile-hide"><?php echo Text::_('COM_MYMUSE_ORDER_STATUS') ?>:</div>
            <div class="myorderstatus"><?php echo Text::_('COM_MYMUSE_'.strtoupper($order->status_name)) ?></div>
        </li>
        <li class="mymuse-grid-1-2">
            <div class="mobile-hide"><?php echo Text::_('COM_MYMUSE_ORDER_TOTAL') ?>:</div>
            <div class="mysummarytotal"><?php echo MyMuseHelper::printMoney($order->order_total)." ".$order->order_currency['currency_code'] ?></div>
        </li>
    <?php if($order->reservation_fee > 0){ ?>
        <li class="mymuse-grid-1-2">
            <div class="mobile-hide"><?php echo Text::_('COM_MYMUSE_RESERVATION_FEE') ?>:</div>
            <div class="myreservationfee"><?php echo MyMuseHelper::printMoney($order->reservation_fee)." ".$order->order_currency['currency_code'] ?></div>
        </li>
    <?php if($order->non_res_total > 0){ ?>
    	<li class="mymuse-grid-1-2">
        	<div class="mobile-hide"><?php echo Text::_('COM_MYMUSE_OTHER_CHARGES') ?>:</div>
        	<div class="myothercharges"><?php echo MyMuseHelper::printMoney($order->non_res_total)." ".$order->order_currency['currency_code'] ?></div>
    	</li>
    	<li class="mymuse-grid-1-2">
            <div class="mobile-hide"><?php echo Text::_('COM_MYMUSE_PAID') ?>:</div>
            <div class="mypaid"><?php echo MyMuseHelper::printMoney($order->pay_now)." ".$order->order_currency['currency_code'] ?></div>
        </li>
    	<?php } ?>
    <?php } ?>
    <?php if(isset($this->plugin) && $this->plugin != ''){ ?>
        <li class="mymuse-grid-1-2">
            <div class="mobile-hide"><?php echo Text::_('COM_MYMUSE_PAID') ?>:</div>
            <div class="mypaid"><?php echo $this->plugin?></div>
        </li>
    <?php } ?>
	</ul>

        