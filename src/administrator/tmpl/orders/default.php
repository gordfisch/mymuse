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
$saveOrder = $listOrder == 'a.ordering';


?>


<form action="<?php echo Route::_('index.php?option=com_mymuse&view=orders'); ?>" method="post" name="adminForm" id="adminForm">
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
			<?php echo Text::_('COM_MYMUSE_ORDERS_TABLE_CAPTION'); ?>,
			<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
			<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
		</caption>
		<thead>
			<tr>
				<td class="w-1 text-center">
					<?php echo HTMLHelper::_('grid.checkall'); ?>
				</td>
				<th scope="col" class="w-1 text-center d-none d-md-table-cell">
					<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
				</th>

				<th scope="col" class="w-10 text-center d-none d-md-table-cell">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_MYMUSE_NAME', 'u.last_name', $listDirn, $listOrder); ?>
				</th>
				<th scope="col" class="w-10 text-center d-none d-md-table-cell">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_MYMUSE_DATE', 'c.created', $listDirn, $listOrder); ?>
				</th>
				<th scope="col" class="w-10 text-center d-none d-md-table-cell">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_MYMUSE_STATUS', 'c.order_status', $listDirn, $listOrder); ?>
				</th>
				<th scope="col" class="w-10 text-center d-none d-md-table-cell">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_MYMUSE_SUBTOTAL', 'c.order_subtotal', $listDirn, $listOrder); ?>
				</th>
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
				<td>
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td class="text-center">
					<a href="index.php?option=com_mymuse&view=order&task=order.edit&id=<?php echo (int) $item->id; ?>"><?php echo (int) $item->id; ?></a>
				</td>
				<td class="text-center">
					<?php echo $item->shopper; ?>
				</td>
				<td class="text-center">
					<?php echo $item->created; ?>
				</td>
				 <td class="text-center">
					<?php echo $item->status_name; ?>
					<?php if($item->backordered){
						echo '<span class="alert alert-danger">Backordered item(s)</span>';
					} ?>
					<?php if($item->preordered){
						echo '<span class="alert alert-warning">Preordered item(s)</span>';
					} ?>
				</td>
				<td class="text-center">
					<?php if($item->order_subtotal < 0.00){
						echo '-';
					}else{
						echo $item->order_subtotal; 
					}?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

				<?php // Load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

					
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
