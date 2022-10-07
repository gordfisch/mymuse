<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;


// Create some shortcuts.
$params		= &$this->params;
$n			= count($this->items);
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$height 	= $this->params->get('category_product_image_height',0);
$inner_cols	= 2;
$uri 		= JUri::getInstance(); 
//echo '$listDirn ='.$listDirn. ' $listOrder = '.$listOrder;

if ($this->params->get('list_show_date') && $this->params->get('order_date')) {
	$inner_cols++;
	$date = $this->params->get('order_date');
	$date_string = 'COM_MYMUSE_'.$date.'_DATE';
}
if($this->params->get('category_show_intro_text')) {
	$inner_cols++;
}
if ($this->params->get('list_show_author', 0)) {
	$inner_cols++;
}
if ($this->params->get('list_show_hits', 0)) {
	$inner_cols++;
}
if ($this->params->get('list_show_price', 0)) {
	$inner_cols++;
}
if ($this->params->get('list_show_discount', 0))  {
	$inner_cols++;
}
if ($this->params->get('list_show_sales', 0)) {
	$inner_cols++;
}



?>
<?php if (empty($this->items)) : ?>

	<?php if ($this->params->get('show_no_products', 1)) : ?>
	<p><?php echo Text::_('COM_MYMUSE_NO_PRODUCTS'); ?></p>
	<?php endif; ?>

<?php else : ?>

<form action="<?php echo htmlspecialchars($uri->toString()); ?>" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="limitstart" value="" />

<?php if ($this->params->get('filter_field') != 'hide' || $this->params->get('show_pagination_limit')) :?>
	<table class="mymuse_cart">
		<tr>
		<?php if ($this->params->get('filter_field') && $this->params->get('filter_field') != 'hide')  : ?>
			<td align="left" width="60%" nowrap="nowrap">
				<?php echo Text::_('COM_MYMUSE_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?>
				<input type="text" name="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" 
				class="inputbox" 
				onchange="this.start.value=0;this.form.submit();" />
			</td>
		<?php endif; ?>
        
		<?php if ($this->params->get('show_pagination_limit')) : ?>
			<td  nowrap="nowrap">
			<?php
				echo '&nbsp;&nbsp;&nbsp;'.Text::_('COM_MYMUSE_DISPLAY_NUM').'&nbsp;';
				echo $this->pagination->getLimitBox();
			?>
			</td>
		<?php endif; ?>
		</tr>
	</table>
	<br />
<?php endif; ?>


<!-- table less -->
<section>
	<ul class="list-products ">
<?php if ($this->params->get('show_headings')) :?>

	<li class="item-container cols-<?php echo $inner_cols; ?>">
		<div class="mymuse_cart_top list-head-image "><?php echo Text::_('COM_MYMUSE_IMAGE'); ?></div>

		<div class="mymuse_cart_top list-head-title "><?php  echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder) ; ?></div>
		<?php if($this->params->get('category_show_intro_text')) :?>
			<div class="mymuse_cart_top list-head-description "><?php echo Text::_('COM_MYMUSE_DESCRIPTION'); ?></div>
		<?php endif; ?>
		<?php if ($this->params->get('list_show_date') && $this->params->get('order_date')) : 
				$date = $this->params->get('order_date');
				?>
			<div class="mymuse_cart_top list-head-date   ">
				<?php if ($date == "created") : ?>
					<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_'.$date.'_DATE', 'a.created', $listDirn, $listOrder); ?>
				<?php elseif ($date == "modified") : ?>
					<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_'.$date.'_DATE', 'a.modified', $listDirn, $listOrder); ?>
				<?php elseif ($date == "published") : ?>
					<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_'.$date.'_DATE', 'a.publish_up', $listDirn, $listOrder); ?>
				<?php elseif ($date == "product_made_date") : ?>
					<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_'.$date.'_DATE', 'a.product_made_date', $listDirn, $listOrder); ?>
				<?php endif; ?></div>
		<?php endif; ?>
		<?php if ($this->params->get('list_show_author')) : ?>
			<div class="mymuse_cart_top list-head-author   "><?php echo JHtml::_('grid.sort', 'JAUTHOR', 'author', $listDirn, $listOrder); ?></div>
		<?php endif; ?>
		<?php if ($this->params->get('list_show_hits')) : ?>
			<div class="mymuse_cart_top list-head-hits   "><?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?></div>
		<?php endif; ?>
		<?php if ($this->params->get('list_show_price')) : ?>
			<div class="mymuse_cart_top list-head-price   "><?php echo JHtml::_('grid.sort', 'COM_MYMUSE_CART_PRICE', 'a.price', $listDirn, $listOrder); ?></div>
		<?php endif; ?>
		<?php if ($this->params->get('list_show_discount')) : ?>
			<div class="mymuse_cart_top list-head-discount   "><?php echo JHtml::_('grid.sort', 'COM_MYMUSE_DISCOUNT', 'a.product_discount', $listDirn, $listOrder); ?></div>
		<?php endif; ?>
		<?php if ($this->params->get('list_show_sales')) : ?>
			<div class="mymuse_cart_top list-head-sales   "><?php echo JHtml::_('grid.sort', 'COM_MYMUSE_SALES', 's.sales', $listDirn, $listOrder); ?></div>
		<?php endif; ?>

	</li>
			
<?php endif; ?>

<?php foreach ($this->items as $i => $product) : ?>


	 <li class=" item-container cols-<?php echo $inner_cols; ?>">
		<div class="mycart-inner list-image " data-name="<?php echo Text::_('COM_MYMUSE_IMAGE'); ?>"><a href="<?php echo JRoute::_(RouteHelper::getProductRoute($product->id, $this->category->id)); ?>">
			<img <?php if($height) : ?> style="height:<?php echo $height; ?>px"
				<?php endif; ?> src="<?php echo $product->list_image; ?>"
				alt="<?php echo htmlspecialchars($product->list_image); ?>" />
			</a>
		</div>

		
		<div class="mycart-inner list-title " data-name="<?php echo Text::_('JGLOBAL_TITLE'); ?>"><a href="<?php 
				echo JRoute::_(RouteHelper::getProductRoute($product->id, $this->category->id)); ?>">
				<?php echo $this->escape($product->title); ?>
				</a>
				<?php if ($this->params->get('list_show_artist', 0)) : ?>


				<?php endif; ?>








		</div>
		<?php if($this->params->get('category_show_intro_text')) :?>
			<div class="mycart-inner list-desc " data-name="<?php echo Text::_('COM_MYMUSE_DESCRIPTION'); ?>"><?php echo JHtml::_('string.truncate', strip_tags($product->introtext), 200, true); ?>
		<?php endif;

			?>

			<?php if ($this->params->get('show_readmore') && $product->readmore) :
				if ($product->access) :
					$link = JRoute::_(RouteHelper::getProductRoute($product->slug, $product->catid));
				else :
					$menu = Factory::getApplication()->getMenu();
					$active = $menu->getActive();
					$itemId = $active->id;
					$link1 = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
					$returnURL = JRoute::_(RouteHelper::getProductRoute($product->slug, $product->catid));
					$link = new JURI($link1);
					$link->setVar('return', base64_encode($returnURL));
				endif;
			?>
					<span class="readmore">
							<a href="<?php echo $link; ?>">
								<?php if (!$product->access) :
									echo Text::_('COM_MYMUSE_REGISTER_TO_READ_MORE');
								elseif ($readmore = $product->alternative_readmore) :
									echo $readmore;
									if ($thisparams->get('show_readmore_title', 0) != 0) :
									    echo JHtml::_('string.truncate', ($product->title), $this->params->get('readmore_limit'));
									endif;
								elseif ($this->params->get('show_readmore_title', 0) == 0) :
									echo Text::sprintf('COM_MYMUSE_READ_MORE_TITLE');
								else :
									echo Text::_('COM_MYMUSE_READ_MORE').' ';
									echo JHtml::_('string.truncate', ($product->title), $this->params->get('readmore_limit'));
								endif; ?></a>
					</spans>
			<?php endif; ?>
			</div>

			<?php if ($this->params->get('list_show_date')) : ?>
				<div class="mycart-inner list-date mydate  " data-name="<?php echo Text::_($date_string); ?>"><?php 
						if($product->displayDate != '0000-00-00'){
							echo JHtml::_('date', $product->displayDate, $this->escape(
							$this->params->get('date_format', Text::_('DATE_FORMAT_LC3')))); 
						}
						?>
				</div>
			<?php endif; ?>

			<?php if ($this->params->get('list_show_author')) : ?>
				<div class="mycart-inner list-author   myauthor" data-name="<?php echo Text::_('JAUTHOR'); ?>"><?php 
						if(!empty($product->author )) : 
							$author =  $product->author ?>
						<?php $author = ($product->created_by_alias ? $product->created_by_alias : $author);?>

						<?php if (!empty($product->contactid ) &&  $this->params->get('link_author') == true):?>
							<?php echo JHtml::_(
									'link',
									JRoute::_('index.php?option=com_contact&view=contact&id='.$product->contactid),
									$author
							); ?>

						<?php else :?>
							<?php echo $author; ?>
						<?php endif; ?>
						<?php endif; ?>	
				</div>
			<?php endif; ?>

			<?php if ($this->params->get('list_show_hits')) : ?>
				<div class="mycart-inner list-hits   myhits" data-name="<?php echo Text::_('JGLOBAL_HITS'); ?>"><?php echo $product->hits; ?>
				</div>
			<?php endif; ?>

			<?php if ($this->params->get('list_show_price')) : ?>
				<div class="mycart-inner list-price   myprice" data-name="<?php echo Text::_('COM_MYMUSE_CART_PRICE'); ?>"><?php echo myMuseHelper::printMoney($product->price); ?>
				</div>
			<?php endif; ?>

			<?php if ($this->params->get('list_show_discount')) : ?>
				<div class="mycart-inner list-discount   mydiscount" data-name="<?php echo Text::_('COM_MYMUSE_DISCOUNT'); ?>"><?php 
						if($product->product_discount > 0){
							echo myMuseHelper::printMoney($product->product_discount); 
						}else{
							echo "-";
						}
						?>
				</div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_sales')) : ?>
				<div class="mycart-inner list-sales   mysales" data-name="<?php echo Text::_('COM_MYMUSE_SALES'); ?>"><?php echo $product->sales; ?>
				</div>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_comments')) : ?>
				<div class="mycart-inner list-comments   mycomments" data-name="<?php echo Text::_('COMMENTS_LIST_HEADER'); ?>"><?php 
						$count = JComments::getCommentsCount($product->id, 'com_mymuse');
						if($count){
							echo $count ;
						}
					?>
				</div>
			<?php endif; ?>
	</li>
<?php endforeach; ?>
	</ul>
</section>


<?php // Add pagination links ?>
<?php if (!empty($this->items)) : ?>

	<?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
	<div class="pagination">

		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
		 	<p class="counter">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
		<?php endif; ?>

		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php endif; ?>

<?php  endif; ?>
</form>
<?php endif; ?>
