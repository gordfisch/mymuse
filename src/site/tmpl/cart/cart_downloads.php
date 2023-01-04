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
use Joomla\CMS\Router\Route;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$order = $this->order;
$params = $this->params;
$id = $this->id;

?>
<script type="text/javascript">
function mydownload(url,item_id){
	var current = '<?php echo $this->current; ?>'+item_id;
	setTimeout("location.href='"+current+"'",3000);
	window.location.href=url;
}
</script>
<h1 class="cart-header"><?php echo Text::_('COM_MYMUSE_DOWNLOAD_PAGE') ?></h1>
		   <ul class="mymuse-container" >
        <li class="mymuse-cart-top mymuse-grid-1-2">
        	<div class="mymuse-cart-top"><?php echo Text::_('COM_MYMUSE_ORDER_SUMMARY') ?></div>
            <div></div>
        </li>
        <li class="mymuse-grid-1-2">
        	<div class=" mymuse-label"><?php echo Text::_('COM_MYMUSE_ORDER_NUMBER') ?>:</div>
        	<div class="myfullname mymuse-value"><?php echo sprintf("%08d", $order->id) ?></div>
        </li>
        <li class="mymuse-grid-1-2">
        	<div class=" mymuse-label"><?php echo Text::_('COM_MYMUSE_ORDER_DATE') ?>:</div>
        	<div class="myfullname mymuse-value"><?php echo $order->created ?></div>
        </li>
        <li class="mymuse-grid-1-2">
        	<div class=" mymuse-label"><?php echo Text::_('COM_MYMUSE_ORDER_STATUS') ?>:</div>
        	<div class="myfullname mymuse-value"><?php echo Text::_('COM_MYMUSE_'.strtoupper($order->status_name)) ?></div>
        </li>
	</ul>
	<ul class="mymuse-container" >
        <li class="mymuse-cart-top item-container cols-4">
        	<div class="mymuse-cart-top"><?php echo Text::_('COM_MYMUSE_FILENAME'); ?></div>
            <div class="mymuse-cart-top"><?php echo Text::_('COM_MYMUSE_NUMBER_DOWNLOADS'); ?></div>
            <div class="mymuse-cart-top"><?php echo Text::_('COM_MYMUSE_FILE_SIZE'); ?></div>
            <div class="mymuse-cart-top"><?php echo Text::_('COM_MYMUSE_EXPIRES'); ?></div>
        </li>

	<?php 
		foreach($order->items as $item){ 
				if($item->product->product_physical){
					continue;
				}
				if($params->get('my_use_zip')){
					$test = 1;
				}else{
					$test = $item->product->product_downloadable;
				}
				if($test){
				?>
		<li class=" item-container cols-4">
			<div class="mytitle cart" data-name="<?php echo Text::_('COM_MYMUSE_FILENAME'); ?>"><?php 

				$end_date = $item->end_date? $item->end_date : time()*2;
				$my_download_max = $params->get('my_download_max')? $params->get('my_download_max') : ($item->downloads+1)*2;

				if($item->product_in_stock == "-1"){
						?><span class="mymuse_msg"><?php echo Text::_('COM_MYMUSE_PREORDERED'); ?></span><?php

				}elseif($item->downloads < $my_download_max && $end_date > time()){
						$url = Route::_('index.php?option=com_mymuse&view=store&task=downloadfile&id='.$id.'&item_id='.$item->id);
					
						?><a href="javascript:void(0);" onclick="mydownload('<?php echo $url; ?>','<?php echo $item->id; ?>');">
				<?php } ?>
				
				<?php echo $item->product_name; ?>

				<?php 
				if($item->format){
					echo ": ".$item->format;
				}
				if($item->file_name){
					echo "<br />".$item->file_name;
				}
				 
				if($item->product_in_stock != "-1" && $item->downloads < $my_download_max && $end_date > time()){ 
					?></a><?php 
				} ?>


				</div>
				
				<div class="mydownload cart" data-name="<?php echo Text::_('COM_MYMUSE_NUMBER_DOWNLOADS'); ?>"><?php echo $item->downloads; ?></div>
				<div class="myfilesize cart" data-name="<?php echo Text::_('COM_MYMUSE_FILE_SIZE'); ?>"><?php echo MyMuseHelper::ByteSize($item->file_length); ?></div>
				<div class="myexpiry cart" data-name="<?php echo Text::_('COM_MYMUSE_EXPIRES'); ?>"><?php if($item->end_date < time()){ ?><span style="color : #c30;">*</span> <?php } ?>
				<?php 
				if($item->end_date){
					$date = JFactory::getDate($item->end_date);
					$mydate = $date->format($params->get('my_date_format'));
					echo $mydate;
				}
					?></div>
		</li>
	<?php } 
	} ?>
    </ul>