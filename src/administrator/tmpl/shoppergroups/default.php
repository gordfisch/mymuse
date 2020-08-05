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


<form action="<?php echo Route::_('index.php?option=com_mymuse&view=shoppergroups'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				//echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
				?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>

	<table class="table" id="articleList">
		<caption id="captionTable" class="sr-only">
			<?php echo Text::_('COM_MYMUSE_SHOPPERGROUPS'); ?>,
			<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
			<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
		</caption>
		<thead>
			<tr>
				<td class="w-1 text-center">
					<?php echo HTMLHelper::_('grid.checkall'); ?>
				</td>

				<th scope="col" class="w-1 text-center">
					<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
				</th>
				<th scope="col">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_MYMUSE_TITLE', 'a.usergroup_id', $listDirn, $listOrder); ?>
				</th>
				<th scope="col" class="w-10 text-center d-none d-md-table-cell">
					<?php echo Text::_('COM_MYMUSE_SHOPPERGROUP_DESC_LABEL'); ?>
				</th>
				<th scope="col" class="w-10 text-center d-none d-md-table-cell">
					<?php echo HTMLHelper::_('searchtools.sort', 'COM_MYMUSE_SHOPPERGROUP_DISCOUNT_LABEL', 'a.discount', $listDirn, $listOrder); ?>
				</th>

				<th scope="col" class="w-5 d-none d-md-table-cell">
					<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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
			<tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->catid; ?>">
				<td class="text-center">
					<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
				</td>


				<td class="text-center">
					<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'shoppergroups.', $canChange); ?>
				</td>
				<th scope="row">
					<div class="break-word">

							<a href="<?php echo Route::_('index.php?option=com_mymuse&task=shoppergroup.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
								<?php echo $this->escape($item->title); ?></a>
		
					</div>
				</th>

				<td class="small d-none d-md-table-cell">
					<?php echo $item->shopper_group_description; ?>
				</td>
				<td class="small d-none d-md-table-cell">
					<?php echo $item->discount; ?>
				</td>

				<td class="d-none d-md-table-cell">
					<?php echo $item->id; ?>
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
