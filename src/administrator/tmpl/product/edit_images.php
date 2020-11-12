<!-- product edit_images.php -->
<div class="row">
	<div class="col-12 col-lg-12">	
		<fieldset class="adminform form-horizontal">
			<legend><?php echo JText::_('COM_MYMUSE_IMAGES') ?></legend>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('list_image'); ?>
				</div>
				<div id="list-image" class="controls">
					<?php echo $this->form->getInput('list_image'); ?>
					<?php if($this->item->list_image && file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.$this->item->list_image)){?>
						<img src="<?php  echo JURI::root().DIRECTORY_SEPARATOR.$this->item->list_image; ?>" />
					<?php } ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('detail_image'); ?>
				</div>
				<div id="detail-image" class="controls">
					<?php echo $this->form->getInput('detail_image'); ?>
					<?php if($this->item->list_image && file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.$this->item->detail_image)){?>
						<img src="<?php  echo JURI::root().DIRECTORY_SEPARATOR.$this->item->detail_image; ?>" />
					<?php } ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('product_images'); ?>
				</div>
				<div id="images-folder" class="controls">
					<?php echo $this->form->getInput('product_images'); ?>
				</div>
			</div>
		</fieldset>
	</div>
</div>
