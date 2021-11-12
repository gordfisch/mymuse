<!-- START RECORDING INFO  -->
<ul class="product-content">
<?php if ($this->item->product_made_date && $this->item->product_made_date > 0) : ?>
	<li class="product-content-item product_made_date">
		<span class="name"><?php echo JText::_('MYMUSE_PRODUCT_CREATED_LABEL'); ?> : </span>
		<span class="value"><?php echo JHtml::_('date', $this->item->product_made_date, $this->escape(
		$this->params->get('date_format', JText::_('DATE_FORMAT_LC3')))); ?></span>
	</li>
<?php endif; ?>

<?php if ($this->item->product_full_time) : ?>
	<li class="product-content-item product_full_time">
		<span class="name"><?php echo JText::_('MYMUSE_PRODUCT_FULL_TIME_LABEL'); ?> : </span>
		<span class="value"><?php echo $this->item->product_full_time; ?></span>
	</li>
<?php endif; ?>

<?php if ($this->item->product_country) : ?>
	<li class="product-content-item product_country">
		<span class="name"><?php echo JText::_('MYMUSE_PRODUCT_COUNTRY_LABEL'); ?> : </span>
		<span class="value"><?php echo $this->item->product_country; ?></span>
	</li>
<?php endif; ?>

<?php if ($this->item->product_publisher) : ?>
	<li class="product-content-item product_publisher">
		<span class="name"><?php echo JText::_('MYMUSE_PRODUCT_PUBLISHER_LABEL'); ?> : </span>
		<span class="value"><?php echo $this->item->product_publisher; ?></span>
	</li>
<?php endif; ?>

<?php if ($this->item->product_producer) : ?>
	<li class="product-content-item product_producer">
		<span class="name"><?php echo JText::_('MYMUSE_PRODUCT_PRODUCER_LABEL'); ?> : </span>
		<span class="value"><?php echo $this->item->product_producer; ?></span>
	</li>
<?php endif; ?>

<?php if ($this->item->product_studio) : ?>
	<li class="product-content-item product_studio">
		<span class="name"><?php echo JText::_('MYMUSE_PRODUCT_STUDIO_LABEL'); ?> : </span>
		<span class="value"><?php echo $this->item->product_studio; ?></span>
	</li>
<?php endif; ?>
	</ul>
<!-- END RECORDING INFO -->