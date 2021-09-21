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
	<?php echo JText::_('COM_MYMUSE_FORMATS'); ?>

	<?php if($this->form->getValue('format_key') != ''){
		echo " : ".$this->form->getValue('format_key');
	} ?>
	<?php if($this->form->getValue('format_value') != ''){
		echo " : ".$this->form->getValue('format_value');
	} ?>

</h3>
<form action="<?php echo Route::_('index.php?option=com_mymuse&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="format-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', empty($this->item->id) ? Text::_('COM_MYMUSE_FORMAT_NEW') : Text::_('COM_MYMUSE_FORMAT_EDIT')); ?>
		<div class="row">
			<div class="col-lg-12">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-lg-12">
								<?php echo $this->form->renderField('format_key'); ?>
								<?php echo $this->form->renderField('format_value'); ?>
							</div>
						</div>
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
