<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;


HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

HTMLHelper::_('script', 'com_contenthistory/admin-history-versions.js', ['version' => 'auto', 'relative' => true]);

?>
<h3>
	<?php echo JText::_('COM_MYMUSE_TITLE_SHOPPERGROUP'); ?>

	<?php if($this->form->getValue('title') != ''){
		echo " : ".$this->form->getValue('title');
	} ?>

</h3>
<form action="<?php echo Route::_('index.php?option=com_mymuse&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="shippergroup-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', empty($this->item->id) ? Text::_('COM_MYMUSE_SHOPPERGROUP_NEW') : Text::_('COM_MYMUSE_SHOPPERGROUP_EDIT')); ?>
		<div class="row">
			<div class="col-lg-9">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-lg-9">
								<?php echo $this->form->renderField('usergroups_id'); ?>
								<?php echo $this->form->renderField('shopper_group_description'); ?>
								<?php echo $this->form->renderField('tax_applies_to'); ?>
								<?php echo $this->form->renderField('discount'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-3">
				<div class="card">
					<div class="card-body">
						<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
					</div>
				</div>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>
	<?php echo $this->form->getInput('checked_out'); ?>
	<?php echo $this->form->getInput('checked_out_time'); ?>
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
