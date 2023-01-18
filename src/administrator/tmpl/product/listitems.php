<?php
//ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

$params 	= $this->params;
$lists 		= $this->lists;
$listOrder	= $this->escape($this->state->get('item.ordering'));
$listDirn	= $this->escape($this->state->get('item.direction'));
$saveOrder	= $listOrder == 'a.ordering';

$user 		= Factory::getUser();
$userId		= $user->get('id');
$app		= Factory::getApplication();
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_mymuse&task=product.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields3();

$assoc		= isset($app->item_associations) ? $app->item_associations : 0;

if(isset($lists['isNew'])){
	echo Text::_("MYMUSE_SAVE_THEN_ADD_ITEMS");
}else{
$ordering = ($listOrder == 'a.ordering');



?>
<!-- product listitems.php -->
<script type="text/javascript">
Joomla.orderTable = function()
{
	table = document.getElementById("sortTable");
	direction = document.getElementById("directionTable");
	order = table.options[table.selectedIndex].value;
	if (order != '<?php echo $listOrder; ?>')
	{
		dirn = 'asc';
	}
	else
	{
		dirn = direction.options[direction.selectedIndex].value;
	}
	Joomla.tableOrdering(order, dirn, '');
}

</script>

<div id="items" class="row">

	<h2><?php echo Text::_( 'COM_MYMUSE_ITEMS' ); ?></h2>
	
	<form action="index.php" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="view" value="product" /> 
	<input type="hidden" name="layout" value="listitems" /> 
	<input type="hidden" name="option" value="com_mymuse" /> 
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" /> 
	<input type="hidden" name="task" value="" /> 
	<input type="hidden" name="subtype" value="item" /> 
	<input type="hidden" name="boxchecked" value="" /> 
	<input type="hidden" name="parentid" value="<?php echo $this->item->id; ?>" /> 
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<input type="hidden" name="filter_item_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_item_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo HtmlHelper::_('form.token'); 

for ($i=0, $n=count( $this->items ); $i < $n; $i++){

}
	?>
	
	<div id="j-main-container">



		<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo HTMLHelper::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="1%" class="hidden-phone">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th width="25%">
						<?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th class="title" width="10%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_MYMUSE_PRICE', 'a.price', $listDirn, $listOrder); ?>
					</th>
					<th class="text-right" width="10%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_MYMUSE_DISCOUNT', 'a.discount', $listDirn, $listOrder); ?>
					</th>
					<th class="title text-right" width="10%">
						<?php echo HTMLHelper::_('grid.sort', 'COM_MYMUSE_PRODUCT_IN_STOCK_LABEL', 'a.product_in_stock', $listDirn, $listOrder); ?>
					</th>
		
					<?php foreach($lists['attribute_sku'] as $a_sku){ ?>
						<th class="text-center"><?php echo $a_sku->name; ?>
						</th>
					<?php } ?>
					<th width="1%" class="title">
						<?php echo HTMLHelper::_('grid.sort', 'COM_MYMUSE_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
			</tfoot>
			<tbody>
		<?php  if(count($this->items) > 0){ ?>
			<?php
			$k = 0;
			$config = JFactory::getConfig();
		    $tzoffset = $config->get('config.offset');
			$now = JFactory::getDate();
			for ($i=0, $n=count( $this->items ); $i < $n; $i++)
			{
				
				$item = &$this->items[$i];

				$item->checked_out = 0;
				$link 	= 'index.php?option=com_mymuse&task=product.edititem&type=item&id='. $item->id;
				$checked 	= JHTML::_('grid.checkedout',  $item, $i );
				$alt = "p";
				
				$checked 	= JHTML::_('grid.checkedout',  $item, $i );
				
				$canCreate	= $user->authorise('core.create',		'com_joomlamymuse.comtegory.'.$item->catid);
				$canEdit	= $user->authorise('core.edit',			'com_mymuse.product.'.$item->id);
				$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out == $userId || $file->checked_out == 0;
				$canEditOwn	= $user->authorise('core.edit.own',		'com_mymuse.product.'.$item->id) && $item->created_by == $user->get('id');
				$canChange	= $user->authorise('core.edit.state',	'com_mymuse.product.'.$item->id) && $canCheckin;
				
				
				?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
					<td class="order nowrap center hidden-phone">
						<?php
						$iconClass = '';
						if (!$canChange)
						{
							$iconClass = ' inactive';
						}
						elseif (!$saveOrder)
						{
							$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::tooltipText('JORDERINGDISABLED');
						}
						?>
						<span class="sortable-handler<?php echo $iconClass ?>">
							<i class="icon-menu"></i>
						</span>
						<?php if ($canChange && $saveOrder) : ?>
							<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
						<?php endif; ?>
					</td>
					<td class="center hidden-phone">
						<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'products.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
							<?php //echo HTMLHelper::_('contentadministrator.featured', $file->featured, $i, $canChange); ?>
						</div>
					</td>
					<td>
					<a href="<?php echo $link ?>"><?php echo htmlspecialchars($item->title, ENT_QUOTES); ?></a> 
					<?php if(isset($item->product_default) && $item->product_default) {
						?><span class="icon-featured"></span><?php
					} ?>
					</td>
					<td align="right"><?php echo $item->price; ?></td>
					<td align="right"><?php echo $item->product_discount; ?></td>
					
					<td align="right"><?php echo $item->product_in_stock; ?></td>
					<?php foreach($lists['attribute_sku'] as $a_sku){?>
						<td class="text-center"><?php echo $item->attributes[$a_sku->name]; ?>
						</td>
					<?php } ?>
					<td>
						<?php echo $item->id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>


<?php } ?>
			</tbody>
			</table>
		</form>

</div>
<hr>
<hr>
<div id="attributes" class="row">
	<div class="col-md-11">
    <?php 

    if(isset($lists['attribute_sku']) && is_array($lists['attribute_sku'])){ ?>
    	<h2><?php echo Text::_('COM_MYMUSE_ITEM_ATTRIBUTES'); ?></h2>
    	<table class="table table-striped" id="articleList">
			<thead>
				<tr>
					<th class="">
						<?php echo Text::_('COM_MYMUSE_NAME'); ?>
					</th>
					<th class="">
						<?php echo Text::_('COM_MYMUSE_BASE'); ?>
					</th>
					<th  class="">
						<?php echo Text::_('COM_MYMUSE_CSS'); ?>
					</th>
				</tr>
			</thead>
			<tbody>

    	<?php 
    	foreach($lists['attribute_sku'] as $a_sku){ ?>
						<tr>
							<td><?php echo $a_sku->name; ?></td>
							<td><?php echo $a_sku->extra_base; ?></td>
							<td><?php echo $a_sku->extra_css; ?></td>
						</tr>
		<?php } ?>
			</tbody>
		</table>

		<a href="index.php?option=com_mymuse&task=product.create_items&view=product&layout=listitems&id=<?php echo $this->item->id; ?>"><button class="btn btn-small button-apply btn-warning">
			<?php echo Text::_('COM_MYMUSE_CREATE_ITEMS'); ?></button></a><br />
			<?php echo Text::_('COM_MYMUSE_CREATE_ITEMS_DESC'); ?>

	<?php } ?>
	</div>
</div>
<?php } ?>