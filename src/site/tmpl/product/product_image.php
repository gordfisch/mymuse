<?php if( ($this->params->get('product_show_product_image') && $this->item->detail_image)) :?>
	<div class="product-image">
		<img src="<?php echo JURI::Root().$this->item->detail_image;?>"
			alt="<?php echo $this->item->title;?>"
			title="<?php echo $this->item->title;?>" 
			id="img_<?php echo $this->item->id; ?>"
			class="box-shadow"
			/>
	</div>
<?php endif; ?>