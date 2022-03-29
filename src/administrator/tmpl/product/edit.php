<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

// no direct access
defined('_JEXEC') or die;


use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_contenthistory');
$wa->useScript('keepalive')
	->useScript('form.validate');


$lists = $this->lists;

$startOffset = 'details';
if($lists['subtype'] == "file"){
	$startOffset = 'tracks';
}
if($lists['subtype'] == "item"){
	$startOffset = 'items';
}

?>
<h2><?php echo empty($this->item->id) ? Text::_('COM_MYMUSE_NEW_PRODUCT') : Text::sprintf('COM_MYMUSE_EDIT_PRODUCT', $this->item->id); ?></h2>
<!-- product edit.php -->
<form action="<?php echo Route::_('index.php?option=com_mymuse&view=product&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_MYMUSE_DETAILS')); ?>
			<?php echo $this->loadTemplate('details'); ?>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'description', Text::_('COM_MYMUSE_DESCRIPTION')); ?>
			<?php echo $this->loadTemplate('description'); ?>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('COM_MYMUSE_IMAGES')); ?>
			<?php echo $this->loadTemplate('images'); ?>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'recording', Text::_('COM_MYMUSE_RECORDING_DETAILS')); ?>
			<div class="col-12 col-lg-6">
				<fieldset id="fieldset-recording" class="options-form">
					<legend><?php echo Text::_('COM_MYMUSE_RECORDING_DETAILS'); ?></legend>
						<?php echo $this->form->renderFieldset('recording'); ?>
				</fieldset>

			</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'physical', Text::_('COM_MYMUSE_PHYSICAL_DETAILS')); ?>
			<div class="col-12 col-lg-6">
				<fieldset id="fieldset-status" class="options-form">
					<legend><?php echo Text::_('COM_MYMUSE_PHYSICAL_DETAILS'); ?></legend>
						<h3><?php echo Text::_('COM_MYMUSE_PHYSICAL_DETAILS_INTRO'); ?></h3>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('product_in_stock'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('product_in_stock'); ?>
							</div>
						</div>
						<?php echo $this->form->renderFieldset('physical'); ?>
				</fieldset>
			</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

	
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_MYMUSE_PUBLISHING')); ?>
			<div class="row">
				<div class="col-12 col-lg-6">
					<fieldset id="fieldset-publishingdata" class="options-form">
						<legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
						<div>
						<?php
                        $this->fields = array(
                            'publish_up',
                            'publish_down',
                            'featured_up',
                            'featured_down',
                            array('created', 'created_time'),
                            array('created_by', 'created_user_id'),
                            'created_by_alias',
                            array('modified', 'modified_time'),
                            array('modified_by', 'modified_user_id')
                        );



                        echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
						</div>
					</fieldset>
				</div>
				<div class="col-12 col-lg-6">
					<fieldset id="fieldset-metadata" class="options-form">
						<legend><?php echo Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
						<div>
						<?php echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
						</div>
					</fieldset>
				</div>
			</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>


		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>


		<?php if(isset($this->item->alias) && $this->item->alias != ""){ ?>
			<input type="hidden" name="old_alias" value="<?php echo $this->item->alias;?>" />
		<?php }?>
		<?php if(isset($this->item->artistid) && $this->item->artistid != ""){ ?>
			<input type="hidden" name="old_artistid"
			value="<?php echo $this->item->artistid;?>" />
		<?php } ?>
		<input type="hidden" name="task" value="" /> 
		<input type="hidden" name="subtype" value="product" /> 
		<input type="hidden" name="return" value="<?php echo $this->input->get('return');?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
		</form>
			
	</div>

