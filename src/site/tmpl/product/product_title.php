<?php if( $this->params->get("show_title",0) ) : ?>
	<h1><?php echo $this->item->title; ?></h1>
<?php endif; ?>

<?php echo $this->item->event->afterDisplayTitle; ?>