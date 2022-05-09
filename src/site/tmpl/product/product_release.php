<?php

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;

?>
<!-- START RELEASE INFO  --> 
    <ul class="product-content">
		<li class="product-content-item mymuse-grid-1-2">
            <div class="name"><?php echo JText::_('COM_MYMUSE_ARTIST'); ?></div>
			<div class="value"><?php echo $this->item->artist_title;?></div>
        </li>
        
        
    <?php if($this->params->get('show_category')) : ?>
        <li class="product-content-item mymuse-grid-1-2">
            <div class="name category"><?php echo JText::_('COM_MYMUSE_CATEGORY'); ?></div>
			<div class="value"><?php echo $this->item->category_title;?></div>
        </li>
    <?php endif; ?>
        
        <li class="product-content-item mymuse-grid-1-2">
            <div class="name"><?php echo JText::_('COM_MYMUSE_CATALOG'); ?></div>
			<div class="value"><?php echo $this->item->product_sku;?></div>
        </li>

		<!--  PRODUCT ALL TRACKS -->
    <?php if($this->all_tracks) : ?>
        <li class="product-content-item-actions  mymuse-grid-1-2">
            <div class="mypreviews tracks jp-gui ui-widget"><?php echo $this->item->tracks[0]->flash; ?></div>
			<div class="value product-full product-full-title"> <a href="javascript:void(0)" id="box_<?php echo $all_tracks->id; ?>"><?php echo JText::_('COM_MYMUSE_BUY_FULL_RELEASE'); ?> &#10010;</a>
            <?php
            if($this->params->get('my_price_by_product')) :
                foreach($this->params->get('my_formats') as $format) : 
                
                    echo '<span id="'.$format.'_'.$this->all_tracks->id.' class="price">';
                    $product_price_all = 'product_price_'.$format.'_all';
                    echo MyMuseHelper::printMoneyPublic($product_price_all); 
                    //echo $format." ".$product_price_all."<br />"; 
                    ?>
                    </span>
				<?php endforeach;?>
                    <span class="format"> <?php 
                    if(isset($this->item->tracks[0]->variation_select)) :
                        echo $this->item->tracks[0]->variation_select;
                    endif;?>
                	</span>
            <?php else :?>
            		<span class="price"><?php 
            		echo MyMuseHelper::printMoneyPublic($all_tracks->price); ?>
            		</span>
            <?php endif;?>
            </div>
            </li>
    <?php endif;?>
    </ul>
	<!-- END RELEASE INFO --> 