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
	<?php echo empty($this->item->id) ? Text::_('COM_MYMUSE_COUPON_NEW') : Text::_('COM_MYMUSE_COUPON_EDIT'); ?>

	<?php if($this->form->getValue('currency_name') != ''){
		echo " : ".$this->form->getValue('currency_name');
	} ?>
	<?php if($this->form->getValue('currency_code') != ''){
		echo " : ".$this->form->getValue('currency_code');
	} ?>

</h3>
<form action="<?php echo Route::_('index.php?option=com_mymuse&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="coupon-form" class="form-validate">


	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_MYMUSE_DETAILS')); ?>
		<div class="row">
			<div class="col-lg-6">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-lg-9">
								<?php echo $this->form->renderField('id'); ?>
								<?php echo $this->form->renderField('currency_name'); ?>
								<?php echo $this->form->renderField('currency_code'); ?>
								<?php echo $this->form->renderField('symbol'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-lg-9">
								<?php echo $this->form->renderField('state'); ?>
								<?php echo $this->form->renderField('coupon_value'); ?>
								<?php echo $this->form->renderField('coupon_value_type'); ?>
								<?php echo $this->form->renderField('coupon_max_uses'); ?>
								<?php echo $this->form->renderField('coupon_max_uses_per_user'); ?>
								<?php echo $this->form->renderField('start_date'); ?>
								<?php echo $this->form->renderField('expiration_date'); ?>
								
							</div>
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
