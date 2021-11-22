<?php 
$Itemid		= $this->Itemid;
?>
<!--  HEADING / MINICART / TITLE / ICONS -->
<?php echo $this->item->event->beforeDisplayHeader; ?>

<?php if ($this->params->get('show_page_heading', 0)) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php endif; ?>

<?php 

if($this->params->get('show_minicart')) :?>
	<!--  INLINE MINICART  -->
	<!--  the cart box  -->
	<div id="mini-cart pull-left">
		<div class="mini-cart-top">
			<div class="mini-cart-content">
				<div class="mini-cart-cart"></div>
				<div class="mini-cart-text" id="mini-cart-text"><?php
	if($this->cart['idx']) :
	    $word = ($this->cart['idx'] == 1) ? "item" : "items"; 
	    echo $this->cart['idx']." $word";
	endif;
	?></div>
				<div class="mini-cart-link" id="mini-cart-link"><?php
	if($this->cart['idx']) :
	    echo '<a href="'.JRoute::_('index.php?option=com_mymuse&view=cart&task=showcart&Itemid='.$Itemid).'">'.JText::_('COM_MYMUSE_VIEW_CART').'</a>';
	else :
	    echo JText::_('COM_MYMUSE_YOUR_CART_IS_EMPTY');
	endif;
	?></div>
			</div>
		</div>
	</div>
	<div class="clear"></div>
	<!--  END INLINE MINICART  -->
<?php  endif; ?>



<?php 
if ( $this->params->get('filter_field') != 'hide' || $this->params->get('show_pagination_limit') ) : ?>
<!--  FILTERS  -->
<form method="post"
	action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>"
	id="adminForm">
	<input type="hidden" name="option" value="com_mymuse" /> <input
		type="hidden" name="view" value="product" /> <input type="hidden"
		name="catid" value="<?php echo $this->item->catid; ?>" /> <input
		type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" /> <input
		type="hidden" name="filter_order"
		value="<?php echo $this->sortColumn; ?>" /> <input type="hidden"
		name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
	<input type="hidden" name="filter_alpha"
		value="<?php echo $this->filterAlpha; ?>" />
	<table class="mymuse_cart">
		<tr>
		<?php if ($this->params->get('filter_field') != 'hide') : ?>
			<td align="left" width="60%" nowrap="nowrap">
				<?php echo JText::_('COM_MYMUSE_TITLE_FILTER').'&nbsp;'; ?>
				<input type="text" name="searchword"
				value="<?php echo $this->escape($this->state->get('list.searchword')); ?>"
				style="width: 80%" onchange="this.start.value=0;this.form.submit();" />
			</td>
		<?php endif; ?>
		</tr>
	</table>
	<br />
</form>
<!--  END FILTERS  -->
<?php endif; ?>





<?php  if ( !$this->params->get('show_intro') ) :
	echo $this->item->event->afterDisplayTitle;
endif; ?>

<?php echo $this->item->event->beforeDisplayProduct; ?>


<?php $useDefList = (($this->params->get('show_author')) or ($this->params->get('show_category')) or ($this->params->get('show_parent_category'))
	or ($this->params->get('show_create_date')) or ($this->params->get('show_modify_date')) or ($this->params->get('show_publish_date'))
	or ($this->params->get('show_hits'))); ?>

<?php if ($useDefList) : ?>
<!-- PRODUCT ATTRIBUTES -->
<dl class="article-info">
	<dt class="article-info-term"><?php  echo JText::_('COM_MYMUSE_PRODUCT_INFO'); ?></dt>
<?php endif; ?>

<?php if ($this->params->get('show_parent_category') && $this->item->parent_slug != '1:root') : ?>
	<dd class="parent-category-name">
	<?php	$title = $this->escape($this->item->parent_title);
	$url = '<a href="'.JRoute::_(myMuseHelperRoute::getCategoryRoute($this->item->parent_id)).'">'.$title.'</a>';?>
	<?php if ($this->params->get('link_parent_category') and $this->item->parent_slug) : ?>
		<?php echo JText::sprintf('COM_MYMUSE_PARENT', $url); ?>
	<?php else : ?>
		<?php echo JText::sprintf('COM_MYMUSE_PARENT', $title); ?>
	<?php endif; ?>
	</dd>
<?php endif; ?>



<?php if ($this->params->get('show_create_date')) : ?>
	<dd class="create">
	<?php echo JText::sprintf('COM_MYMUSE_CREATED_DATE_ON', JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2'))); ?>
	</dd>
<?php endif; ?>

<?php if ($this->params->get('show_modify_date')) : ?>
	<dd class="modified">
	<?php echo JText::sprintf('COM_MYMUSE_LAST_UPDATED', JHtml::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC2'))); ?>
	</dd>
<?php endif; ?>

<?php if ($this->params->get('show_publish_date')) : ?>
	<dd class="published">
	<?php echo JText::sprintf('COM_MYMUSE_PUBLISHED_DATE_ON', JHtml::_('date', $this->item->publish_up, JText::_('DATE_FORMAT_LC2'))); ?>
	</dd>
<?php endif; ?>

<?php if ($this->params->get('show_author') && !empty($this->item->author )) : ?>
	<dd class="createdby">
	<?php $author = $this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author; ?>
	<?php if (!empty($this->item->contactid) && $this->params->get('link_author') == true): ?>
	<?php
		$needle = 'index.php?option=com_contact&view=contact&id=' . $this->item->contactid;
		$menu = JFactory::getApplication()->getMenu();
		$item = $menu->getItems('link', $needle, true);
		$cntlink = !empty($item) ? $needle . '&Itemid=' . $item->id : $needle;
	?>
		<?php echo JText::sprintf('COM_MYMUSE_WRITTEN_BY', JHtml::_('link', JRoute::_($cntlink), $author)); ?>
	<?php else: ?>
		<?php echo JText::sprintf('COM_MYMUSE_WRITTEN_BY', $author); ?>
	<?php endif; ?>
	</dd>
<?php endif; ?>

<?php if($this->params->get('show_hits')) : ?>
	<dd class="hits">
	<?php echo JText::sprintf('COM_MYMUSE_PRODUCT_HITS', $this->item->hits); ?>
	</dd>
<?php endif; ?>

<?php if ($useDefList) : ?>
	</dl>
<!-- END ATTRIBUTES -->
<?php endif; ?>

<!--  END HEADING -->
