<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
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
$user      = Factory::getUser();
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
else
{
	$orderingColumn = 'created';
}

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_content&task=products.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}


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

	


					<table class="table itemList" id="articleList">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_CONTENT_ARTICLES_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
		<thead>
			<tr>
				<th width="1%" class="nowrap center hidden-phone">
					<?php echo Html::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
				</th>
				<th width="1%" class="center">
					<?php echo Html::_('grid.checkall'); ?>
				</th>
				<th width="1%" class="nowrap center">
					<?php echo Html::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
				</th>
				<th style="min-width:100px" class="nowrap">
					<?php echo Html::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap hidden-phone">
					<?php echo Html::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo Html::_('searchtools.sort', 'MYMUSE_ARTIST', 'artist_title', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo Html::_('searchtools.sort', 'JCATEGORY', 'category_title', $listDirn, $listOrder); ?>
				</th>
				
				<th width="10%" class="nowrap hidden-phone">
					<?php echo Html::_('searchtools.sort', 'MYMUSE_DATE', 'a.' . $orderingColumn, $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo Html::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo Html::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo Html::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo $columns; ?>">
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :

			$item->max_ordering = 0;
			$ordering   = ($listOrder == 'a.ordering');
			$canCreate  = $user->authorise('core.create',     'com_joomlamymuse.category.' . $item->catid);
			$canEdit    = $user->authorise('core.edit',       'com_mymuse.product.' . $item->id);
			$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
			$canEditOwn = $user->authorise('core.edit.own',   'com_mymuse.product.' . $item->id) && $item->created_by == $userId;
			$canChange  = $user->authorise('core.edit.state', 'com_mymuse.product.' . $item->id) && $canCheckin;
		
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="order nowrap center hidden-phone">
							<?php
							$iconClass = '';
							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
							elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . Html::_('tooltipText', 'JORDERINGDISABLED');
							}
							?>
							<span class="sortable-handler<?php echo $iconClass ?>">
								<span class="icon-menu" aria-hidden="true"></span>
							</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order" />
							<?php endif; ?>
						</td>
				<td class="center">
					<?php echo Html::_('grid.id', $i, $item->id); ?>
				</td>
				<td class="center">
					<div class="btn-group">
						<?php echo Html::_('jgrid.published', $item->state, $i, 'products.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
						<?php echo Html::_('mymuseadministrator.featured', $item->featured, $i, $canChange); ?>
						<?php // Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							Html::_('actionsdropdown.' . ((int) $item->state === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'products');
							Html::_('actionsdropdown.' . ((int) $item->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'products');
							echo Html::_('actionsdropdown.render', $this->escape($item->title));
						}
						?>
					</div>
				</td>
				<td class="has-context">
					<div class="pull-left break-word">
						<?php if ($item->checked_out) : ?>
							<?php echo Html::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'products.', $canCheckin); ?>
						<?php endif; ?>
						<?php if ($canEdit || $canEditOwn) : ?>
							<a class="hasTooltip" href="<?php echo Route::_('index.php?option=com_mymuse&task=product.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
								<?php echo $this->escape($item->title); ?></a>
						<?php else : ?>
							<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
						<?php endif; ?>
						<span class="small break-word">
							<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
						</span>
					</div>
				</td>
				<td class="small hidden-phone">
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<td >
					<?php echo $this->escape($item->artist_title);  ?>
				</td>
				<td>
					<?php echo $this->escape($item->category_title); 
					if($item->subcats){
						echo "<br />(".$item->subcats.")";
					} ?>
				</td>
				
				<td class="nowrap small hidden-phone">
					<?php
					$date = $item->{$orderingColumn};
					echo $date > 0 ? Html::_('date', $date, JText::_('DATE_FORMAT_LC4')) : '-';
					?>
				</td>
				<td class="hidden-phone center">
							<span class="badge badge-info">
								<?php echo (int) $item->hits; ?>
							</span>
						</td>
				<td>
					<?php if ($item->language=='*'):?>
						<?php echo JText::alt('JALL','language'); ?>
					<?php else:?>
						<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
					<?php endif;?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php endif; ?>


				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>