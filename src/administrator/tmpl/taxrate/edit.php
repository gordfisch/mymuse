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
$this->item->name = $this->item->tax_name;
?>
<h3>
	<?php echo Text::_('COM_MYMUSE_TAX_RATES'); ?>

	<?php if($this->form->getValue('tax_name') != ''){
		echo " : ".$this->form->getValue('tax_name');
	} ?>
	<?php if($this->form->getValue('country') != ''){
		echo " : ".$this->form->getValue('country');
	} ?>

</h3>
<form action="<?php echo Route::_('index.php?option=com_mymuse&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="taxrate-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', empty($this->item->id) ? Text::_('COM_MYMUSE_NEW_TAX_RATE') : Text::_('COM_MYMUSE_EDIT_TAX_RATE')); ?>
		<div class="row">
			<div class="col-lg-9">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-lg-9">
								<?php echo $this->form->renderField('tax_name'); ?>
								<?php echo $this->form->renderField('tax_rate'); ?>
								<?php echo $this->form->renderField('tax_applies_to'); ?>
								<?php echo $this->form->renderField('country'); ?>
								<?php echo $this->form->renderField('province'); ?>
								<?php echo $this->form->renderField('tax_format'); ?>
								<?php echo $this->form->renderField('compounded'); ?>
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

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
