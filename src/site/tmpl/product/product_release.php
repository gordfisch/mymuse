<!-- START RELEASE INFO  --> 
    <ul class="product-content">
		<li class="product-content-item">
            <span class="name"><?php echo JText::_('MYMUSE_ARTIST'); ?></span>
			<span class="value"><?php echo $this->item->artist_title;?></span>
        </li>
        
        
    <?php if($this->params->get('show_category')) : ?>
        <li class="product-content-item">
            <span class="name category"><?php echo JText::_('MYMUSE_CATEGORY'); ?></span>
			<span class="value"><?php echo $this->item->category_title;?></span>
        </li>
    <?php endif; ?>
        
        <li class="product-content-item">
            <span class="name"><?php echo JText::_('MYMUSE_CATALOG'); ?></span>
			<span class="value"><?php echo $this->item->product_sku;?></span>
        </li>

		<!--  PRODUCT ALL TRACKS -->
    <?php if($this->all_tracks) : ?>
        <li class="product-content-item-actions">
            <span class="mypreviews tracks jp-gui ui-widget"><?php echo $this->item->tracks[0]->flash; ?></span>
			<span class="value product-full product-full-title"> <a href="javascript:void(0)" id="box_<?php echo $all_tracks->id; ?>"><?php echo JText::_('MYMUSE_BUY_FULL_RELEASE'); ?> &#10010;</a></span>
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
            </li>
    <?php endif;?>
    </ul>
	<!-- END RELEASE INFO --> 