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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;


$row = $this->item;
$params = $this->params;
$attribute_skus = $this->attribute_skus;
$attributes = $this->attributes;
$lists = $this->lists;
JFilterOutput::objectHTMLSafe( $row );


?>
<!-- product edititems.php -->
		<script  type="text/javascript">
		<!--

		function submitbutton(pressbutton)
		{
			var form = document.adminForm;

			if (pressbutton == 'cancelitem') {
				submitform( pressbutton );
				return;
			}

			// do field validation

			if (form.title.value == ""){
				alert( "<?php echo Text::_( 'COM_MYMUSE_ITEM_MUST_HAVE_A_TITLE', true ); ?>" );
			} else if (form.product_sku.value == ""){
				alert( "<?php echo Text::_( 'COM_MYMUSE_ITEM_MUST_HAVE_AN_SKU', true ); ?>" );

			} else {

				submitform( pressbutton );
			}
		}
		//-->
		</script>
		<?php $title = empty($this->item->id) ? Text::_('COM_MYMUSE_NEW_ITEM') : Text::_('COM_MYMUSE_EDIT_ITEM').' '. $this->item->title; ?>
		<h3><?php echo isset($row->parent->title)? $row->parent->title : ''; ?>
			<?php echo $title; ?>
		</h3>
		<form action="index.php" method="post" name="adminForm" id="adminForm">
<div id="j-main-container">

		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<!--  DETAILS TAB -->
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_MYMUSE_DETAILS', true)); ?>

		<div class="row">
    		<div class="col-12 col-lg-6">
    			<fieldset id="fieldset-details" class="options-form">
				<legend><?php echo Text::_('COM_MYMUSE_DETAILS'); ?></legend>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('id'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('title'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('title'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('product_sku'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('product_sku'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('price'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('price'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('product_discount'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('product_discount'); ?>
					</div>
				</div>
				
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
						<?php echo $this->form->getLabel('language'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('language'); ?>
					</div>
				</div>
				
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('product_in_stock'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('product_in_stock'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('product_default'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('product_default'); ?>
					</div>
				</div>
				</fieldset>
			</div>
		
			<div class="col-12 col-lg-6">
				<fieldset id="fieldset-attributes" class="options-form">
				<legend><?php echo Text::_('COM_MYMUSE_ITEM_ATTRIBUTES'); ?></legend>
		
			<?php
			foreach($attribute_skus as $attribute_sku){
					?>
				<div class="control-group">
						<div class="control-label"><?php echo $attribute_sku->name; ?>
				</div>
				<div class="controls">
					<input class="inputbox" type="text"
						name="attribute_value[<?php echo $attribute_sku->id; ?>]"
						id="attribute_value.<?php echo $attribute_sku->id; ?>" size="30"
						maxlength="255" value="<?php echo @$this->attributes[$attribute_sku->name]; ?>" /> 
						<input
						type="hidden"
						name="attribute_name[<?php echo $attribute_sku->id; ?>]"
						value="<?php echo $attribute_sku->name ?>" />
				</div>
			<?php } ?>
	
				</fieldset>
			</div>
		</div>

		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<!--  DIMENSIONS TAB -->
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'tracks', Text::_('COM_MYMUSE_DIMENSIONS', true)); ?>
		<div class="row">
			<div class="col-12">
				<?php echo $this->form->renderFieldset('physical'); ?>
				
			</div>
		</div>


		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<!--  IMAGES TAB -->
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'tracks', Text::_('COM_MYMUSE_IMAGES', true)); ?>
			
		<fieldset class="adminform form-horizontal">

			<legend><?php echo JText::_('COM_MYMUSE_IMAGES') ?></legend>
			<div class="pull-left span5">

				<!-- div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('list_image'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('list_image'); ?>
						<?php if($this->item->list_image && file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.$this->item->list_image)){?>
							<img src="<?php  echo JURI::root().DIRECTORY_SEPARATOR.$this->item->list_image; ?>" />
						<?php } ?>
					</div>
				</div -->
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('detail_image'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('detail_image'); ?>
						<?php if($this->item->list_image && file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.$this->item->detail_image)){?>
							<img src="<?php  echo JURI::root().DIRECTORY_SEPARATOR.$this->item->detail_image; ?>" />
						<?php } ?>
					</div>
				</div>

		</fieldset>

		<?php echo HTMLHelper::_('uitab.endTab'); ?>


		<input type="hidden" name="jform[product_physical]" value="1">
		<input type="hidden" name="jform[state]" value="1">
		<input type="hidden" name="jform[access]" value="1">
		<input type="hidden" name="jform[product_downloadable]" value="0">
		<input type="hidden" name="parentid" value="<?php echo $row->parentid ?>">
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="jform[parentid]" value="<?php echo $row->parentid ?>">
		<input type="hidden" name="view" value="product" />
		<input type="hidden" name="jform[catid]" value="<?php echo $row->parent->catid; ?>" />
		<input type="hidden" name="jform[version]" value="<?php echo $row->version; ?>" />
		<input type="hidden" name="jform[catid]" value="<?php echo $row->catid; ?>" />
		<input type="hidden" name="jform[artistid]" value="<?php echo $this->item->parent->artistid; ?>" />
		<input type="hidden" name="subtype" value="item" />
		<input type="hidden" name="option" value="com_mymuse" />
		<input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_( 'form.token' ); ?>
		</form>
</div>
