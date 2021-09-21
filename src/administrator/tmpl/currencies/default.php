<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;



HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == '';

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_mymuse&task=currencies.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>


<form action="<?php echo Route::_('index.php?option=com_mymuse&view=currencies'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
				?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>

	<table class="table" id="articleList">
		<caption id="captionTable" class="sr-only">
			<?php echo Text::_('COM_MYMUSE_COUPONS_TABLE_CAPTION'); ?>,
			<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
			<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
		</caption>
		<thead>
			<tr>
				<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
				</th>

				<th width="1%" class="center">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
                <th width="30%">
					<?php echo JHtml::_('grid.sort',  'COM_MYMUSE_TITLE', 'a.currency_name', $listDirn, $listOrder); ?>
				</th>
				<th width="30%">
					<?php echo JHtml::_('grid.sort',  'COM_MYMUSE_CURRENCY_CODE_LABEL', 'a.currency_code', $listDirn, $listOrder); ?>
				</th>
				<th width="30%">
					<?php echo JHtml::_('grid.sort',  'COM_MYMUSE_CURRENCY_SYMBOL_LABEL', 'a.symbol', $listDirn, $listOrder); ?>
				</th>


                <?php if (isset($this->items[0]->id)) { ?>
                <th width="1%" class="nowrap">
                    <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                </th>
                <?php } ?>
			</tr>
		</thead>

		<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
		<?php foreach ($this->items as $i => $item) :
			$ordering	= ($listOrder == 'a.ordering');
			$canCreate	= $user->authorise('core.create',		'com_mymuse');
			$canEdit	= $user->authorise('core.edit',			'com_mymuse');
			$canCheckin	= $user->authorise('core.manage',		'com_mymuse');
			$canChange	= $user->authorise('core.edit.state',	'com_mymuse');
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="order nowrap center hidden-phone">
					<?php if ($canChange) :
						$disableClassName = '';
						$disabledLabel	  = '';

						if (!$saveOrder) :
							$disabledLabel    = JText::_('JORDERINGDISABLED');
							$disableClassName = 'inactive tip-top';
						endif; ?>
						<span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
							<i class="icon-menu"></i>
						</span>
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo ''; ?>" class="width-20 text-area-order " />
					<?php else : ?>
						<span class="sortable-handler inactive" >
							<i class="icon-menu"></i>
						</span>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
                <td class="center">
                <a href="index.php?option=com_mymuse&view=currency&task=currency.edit&id=<?php echo (int) $item->id; ?>"><?php echo $item->currency_name; ?></a>
				</td>
				<td class="center">
					    <?php echo $item->currency_code; ?>
				</td>
				<td class="center">
					    <?php echo $item->symbol; ?>
				</td>
                <?php if (isset($this->items[0]->id)) { ?>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
                <?php } ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

				<?php // Load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

					
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
