<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2023 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

use Joomla\CMS\Button\FeaturedButton;
use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Button\TransitionButton;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;
use Joomla\Utilities\ArrayHelper;

HTMLHelper::_('behavior.multiselect');

$app       = Factory::getApplication();
$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if (strpos($listOrder, 'publish_up') !== false)
{
	$orderingColumn = 'publish_up';
}
elseif (strpos($listOrder, 'publish_down') !== false)
{
	$orderingColumn = 'publish_down';
}
elseif (strpos($listOrder, 'modified') !== false)
{
	$orderingColumn = 'modified';
}
elseif (strpos($listOrder, 'product_release_date') !== false)
{
    $orderingColumn = 'product_release_date';
}
else
{
	$orderingColumn = 'created';
}

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_mymuse&task=product.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HtmlHelper::_('draggablelist.draggable');
}

$workflow_enabled  	= false;
$workflow_state    	= false;
$workflow_featured 	= false;
$this->vote 		= false;
$assoc = Associations::isEnabled();
?>


<form action="<?php echo Route::_('index.php?option=com_mymuse&view=products'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>

	


	<table class="table itemList" id="trackList">
		<caption id="captionTable" class="sr-only">
			<?php echo Text::_('COM_MYMUSE_PRODUCTS_TABLE_CAPTION'); ?>,
			<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
			<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
		</caption>
		<thead>
			<tr>
				<td class="w-1 text-center">
					<?php echo HtmlHelper::_('grid.checkall'); ?>
				</td>
				<th scope="col" class="w-1 text-center d-none d-md-table-cell">
					<?php echo HtmlHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'fas fa-sort'); ?>
				</th>
				<?php if ($workflow_enabled) : ?>
				<th scope="col" class="w-1 text-center">
					<?php echo HtmlHelper::_('searchtools.sort', 'JSTAGE', 'ws.title', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>
				<th scope="col" class="w-1 text-center d-none d-md-table-cell">
					<?php echo HtmlHelper::_('searchtools.sort', 'JFEATURED', 'a.featured', $listDirn, $listOrder); ?>
				</th>
				<th scope="col" class="w-1 text-center">
					<?php echo HtmlHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
				</th>
				<th scope="col" style="min-width:100px">
					<?php echo HtmlHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th scope="col" class="w-10 d-none d-md-table-cell">
					<?php echo HtmlHelper::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
				</th>

				<th scope="col" class="w-10 d-none d-md-table-cell">
					<?php echo HtmlHelper::_('searchtools.sort', 'COM_MYMUSE_ARTIST', 'artist_title', $listDirn, $listOrder); ?>
				</th>
				<th scope="col" class="w-10 d-none d-md-table-cell">
					<?php echo HtmlHelper::_('searchtools.sort', 'JCATEGORY', 'category_title', $listDirn, $listOrder); ?>
				</th>


				
				<?php if (Multilanguage::isEnabled()) : ?>
					<th scope="col" class="w-10 d-none d-md-table-cell">
						<?php echo HtmlHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<th scope="col" class="w-10 d-none d-md-table-cell text-center">
					<?php echo HtmlHelper::_('searchtools.sort', 'COM_MYMUSE_DATE_' . strtoupper($orderingColumn), 'a.' . $orderingColumn, $listDirn, $listOrder); ?>
				</th>
				<th scope="col" class="w-3 d-none d-lg-table-cell text-center">
					<?php echo HtmlHelper::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
				</th>
				<?php if ($this->vote) : ?>
					<th scope="col" class="w-3 d-none d-md-table-cell text-center">
						<?php echo HtmlHelper::_('searchtools.sort', 'JGLOBAL_VOTES', 'rating_count', $listDirn, $listOrder); ?>
					</th>
					<th scope="col" class="w-3 d-none d-md-table-cell text-center">
						<?php echo HtmlHelper::_('searchtools.sort', 'JGLOBAL_RATINGS', 'rating', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<th scope="col" class="w-3 d-none d-lg-table-cell">
					<?php echo HtmlHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
			<?php foreach ($this->items as $i => $item) :
				$item->max_ordering = 0;
				$canEdit    = $user->authorise('core.edit', 'com_mymuse.product.' . $item->id);
				$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || is_null($item->checked_out);
				$canEditOwn = $user->authorise('core.edit.own', 'com_mymuse.product.' . $item->id) && $item->created_by == $userId;
				$canChange  = $user->authorise('core.edit.state', 'com_mymuse.product.' . $item->id) && $canCheckin;

				?>
			<tr class="row<?php echo $i % 2; ?>"
				data-draggable-group="<?php echo $item->catid; ?>"
			>
				<td class="text-center">
					<?php echo HtmlHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
				</td>
				<td class="text-center d-none d-md-table-cell">
					<?php
					$iconClass = '';
					if (!$canChange)
					{
						$iconClass = ' inactive';
					}
					elseif (!$saveOrder)
					{
						$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
					}
					?>
					<span class="sortable-handler<?php echo $iconClass ?>">
						<span class="fas fa-ellipsis-v" aria-hidden="true"></span>
					</span>
					<?php if ($canChange && $saveOrder) : ?>
						<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
					<?php endif; ?>
				</td>

				<!-- td class="text-center">
					<?php echo HTMLHelper::_('mymuseadministrator.featured', $item->featured, $i, $canChange); ?>
				</td -->
				<td class="text-center d-none d-md-table-cell">
				<?php
					$options = [
						'task_prefix' => 'products.',
						'disabled' => $workflow_featured || !$canChange,
						'id' => $item->id,
						'cid' => $item->id,
					];

					echo (new FeaturedButton)
						->render((int) $item->featured, $i, $options, null, null);
				?>
				</td>
				<td class="article-status text-center">
								<?php
									$options = [
										'task_prefix' => 'products.',
										'disabled' => $workflow_state || !$canChange,
										'id' => 'state-' . $item->id
									];

									echo (new PublishedButton)->render((int) $item->state, $i, $options, $item->publish_up, $item->publish_down);
								?>
								</td>



				<th scope="row" class="has-context">
					<div class="break-word">
						<?php if ($item->checked_out) : ?>
							<?php echo HtmlHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'products.', $canCheckin); ?>
						<?php endif; ?>
						<?php if ($canEdit || $canEditOwn) : ?>
							<a href="<?php echo Route::_('index.php?option=com_mymuse&task=product.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->title); ?>">
								<?php echo $this->escape($item->title); ?></a>
						<?php else : ?>
							<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
						<?php endif; ?>
							<div class="small break-word">
								<?php if (empty($item->note)) : ?>
									<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
								<?php else : ?>
									<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
								<?php endif; ?>
							</div>

					</div>
				</th>
				<td class="small d-none d-md-table-cell">
					<?php echo $this->escape($item->access_level); ?>
				</td>

				<td >
					<?php echo $this->escape($item->artist_title);  ?>
				</td>
				<td>
					<?php echo $this->escape($item->category_title); 
					if(isset($item->subcats)){
						echo "<br />(".$item->subcats.")";
					} ?>
				</td>
				<?php if ($assoc) : ?>
					<td class="d-none d-md-table-cell">
						<?php if ($item->association) : ?>
							<?php echo HtmlHelper::_('mymuseadministrator.association', $item->id); ?>
						<?php endif; ?>
					</td>
				<?php endif; ?>
				<?php if (Multilanguage::isEnabled()) : ?>
					<td class="small d-none d-md-table-cell">
						<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
					</td>
				<?php endif; ?>


				<td class="small d-none d-md-table-cell text-center">
					<?php
					$date = $item->{$orderingColumn};
					echo $date > 0 ? HtmlHelper::_('date', $date, Text::_('DATE_FORMAT_LC4')) : '-';
					?>
				</td>
				<td class="d-none d-lg-table-cell text-center">
					<span class="badge badge-info">
						<?php echo (int) $item->hits; ?>
					</span>
				</td>
				<?php if ($this->vote) : ?>
					<td class="d-none d-md-table-cell text-center">
						<span class="badge badge-success">
						<?php echo (int) $item->rating_count; ?>
						</span>
					</td>
					<td class="d-none d-md-table-cell text-center">
						<span class="badge badge-warning">
						<?php echo (int) $item->rating; ?>
						</span>
					</td>
				<?php endif; ?>
				<td class="d-none d-lg-table-cell">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php endif; ?>
	<?php // load the pagination. ?>
	<?php echo $this->pagination->getListFooter(); ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>