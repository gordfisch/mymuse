<?php
use Joomla\CMS\Language\Text;
?>
<!-- product edit_details.php -->

<div class="row">
	<div class="col-12 col-lg-6">
		<fieldset id="fieldset-details" class="options-form">
			<legend><?php echo Text::_('COM_MYMUSE_PRODUCT_IDENTITY'); ?></legend>
            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('id'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('id'); ?>
                </div>
            </div>

            <?php echo $this->form->renderFieldset('identity'); ?>
				
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('media_rls'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('media_rls'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('media_link'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('media_link'); ?>
					</div>
				</div>
				
				
	
		</fieldset>
	</div>
  
	<div class="col-12 col-lg-6">

		<fieldset id="fieldset-price" class="options-form">
			<legend><?php echo Text::_('COM_MYMUSE_PRICE'); ?></legend>

				<?php
				 if( !$this->params->get('my_price_by_product')){ 
					//price by track and physical
					?>	
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('price'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('price'); ?>
								</div>
							</div>

				<?php 
				}elseif(1 == $this->params->get('my_price_by_product')){ 
					$fieldSets = $this->form->getFieldsets('attribs'); 
					$physical = 0;

					foreach($this->params->get('my_formats') as $variation=>$format)
					{	
					 	foreach ($fieldSets as $name => $fieldSet)
					 	{

							foreach ($this->form->getFieldset($name) as $field)
							{

								if (preg_match("/physical/",$field->name) && !$physical)
								{
									echo $field->renderField();
									$physical++;
								}

								if (preg_match("/$format/",$field->name))
								{
									echo $field->renderField();
								}
							} 
					
				 		} 
					}

				 } ?>
 			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('product_discount'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('product_discount'); ?>
				</div>
			</div>

		</fieldset>
		<fieldset id="fieldset-status" class="options-form">
			<legend><?php echo Text::_('COM_MYMUSE_STATUS'); ?></legend>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('product_release_date'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('product_release_date'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('special_status'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('special_status'); ?>
					</div>
				</div>

				<a href="index.php?option=com_mymuse&view=product&layout=edit&task=alertPreorders&id=<?php echo $this->item->id; ?>"><?php
					echo JText::_('COM_MYMUSE_ALERT_CUSTOMERS'); ?></a>


	
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('state'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('state'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('access'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('access'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('featured'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('featured'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('language'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('language'); ?>
					</div>
				</div>
				
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('recommend'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('recommend'); ?>
					</div>
				</div>
			
		</fieldset>
	</div>
</div>
			







