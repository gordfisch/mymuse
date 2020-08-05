<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;


HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$app = Factory::getApplication();
$input = $app->input;

$assoc = Associations::isEnabled();

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('details', 'item_associations', 'jmetadata');
$this->useCoreUI = true;

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
$fieldsetsInContact = ['contact', 'contact2'];
$fieldsetsInDownloads = ['downloads', 'directories'];
$fieldsetsInStore = ['store', 'store2'];
$this->ignore_fieldsets = array_merge($fieldsetsInStore, $fieldsetsInContact, $fieldsetsInDownloads);


?>
<form action="<?php echo Route::_('index.php?option=com_mymuse&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">



	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_MYMUSE_STORE_DESCRIPTION')); ?>
			<div class="row">
				<div class="col-lg-9">
					<div>
						<div class="card-body">
							<fieldset class="adminform">
								<?php echo $this->form->getLabel('description'); ?>
								<?php echo $this->form->getInput('description'); ?>
							</fieldset>
						</div>
					</div>
				</div>
				<div class="col-lg-3">
					<div class="bg-white px-3">
					<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
					</div>
				</div>
			</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>



			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'css', Text::_('COM_MYMUSE_EDIT_CSS')); ?>
				<fieldset class="adminform">
					<textarea cols="220" rows="80" name="COM_MYMUSE_css" id="COM_MYMUSE_css" style="width:100%"><?php echo $this->css; ?></textarea>
				</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>


			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'Contact', Text::_('COM_MYMUSE_CONTACTS_LABEL')); ?>
				<div class="row">
					
					<?php foreach ($fieldsetsInContact as $fieldset) : ?>
						<div class="col-12 col-lg-6">
						<fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
							<legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
							<div>
							<?php echo $this->form->renderFieldset($fieldset); ?>
							</div>
						</fieldset>
						</div>
					<?php endforeach; ?>
					
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'Downloads', Text::_('COM_MYMUSE_DOWNLOADS_LABEL')); ?>
				<div class="row">
					
					<?php foreach ($fieldsetsInDownloads as $fieldset) : ?>
						<div class="col-12 col-lg-6">
						<fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
							<legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
							<div>
							<?php echo $this->form->renderFieldset($fieldset); ?>
							</div>
						</fieldset>
						</div>
					<?php endforeach; ?>
					
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'Store', Text::_('COM_MYMUSE_STORE_OPTIONS_LABEL')); ?>
				<div class="row">
					
					<?php foreach ($fieldsetsInStore as $fieldset) : ?>
						<div class="col-12 col-lg-6">
						<fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
							<legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
							<div>
							<?php echo $this->form->renderFieldset($fieldset); ?>
							</div>
						</fieldset>
						</div>
					<?php endforeach; ?>
					
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'Reports', Text::_('COM_MYMUSE_FRONT_END_SALES_REPORTS_LABEL')); ?>
				<div class="row">
					

						<div class="col-12 col-lg-6">
						<fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
							<legend><?php echo Text::_('COM_MYMUSE_FRONT_END_SALES_REPORTS_LABEL'); ?></legend>
							<div>
							<?php echo $this->form->renderFieldset('reports'); ?>
							</div>
						</fieldset>
						</div>

					
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'Testing', Text::_('COM_MYMUSE_TEST_OPTIONS_LABEL')); ?>
				<div class="row">
					

						<div class="col-12 col-lg-6">
						<fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
							<legend><?php echo Text::_('COM_MYMUSE_TEST_OPTIONS_LABEL'); ?></legend>
							<div>
							<?php echo $this->form->renderFieldset('testing'); ?>
							</div>
						</fieldset>
						</div>

					
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>



			<?php $fieldSets = $this->form->getFieldsets('params'); ?>
			<?php foreach ($fieldSets as $name => $fieldSet) : ?>
			<?php endforeach; ?>


		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
		<input type="hidden" name="task" value="" />
		<?php echo $this->form->getInput('checked_out'); ?>
        <?php echo $this->form->getInput('checked_out_time'); ?>
		<?php echo JHtml::_('form.token'); ?>
	  </div>

</form>