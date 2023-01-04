<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2022 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;


?>
<?php echo $this->store->event->beforeDisplayHeader; ?>

<div class="mymuse blog<?php echo $this->pageclass_sfx;?> " >
<?php if ( $this->params->get('show_page_heading',0)!=0) : ?>
	<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php endif; ?>

<h2><?php echo $this->store->title; ?></h2>

<?php echo $this->store->event->afterDisplayTitle; ?>

<?php echo $this->store->event->beforeDisplayProduct; ?>

<div class="store-description"><?php echo $this->store->description; ?></div>
	
<?php 
if (!empty($this->lead_items) || !empty($this->intro_items) || !empty($this->link_items) ) : ?>
	<h2><?php echo Text::_("COM_MYMUSE_FEATURED") ?></h2>
<?php endif; ?>

<?php $leadingcount = 0; ?>
<?php if (!empty($this->lead_items)) : ?>
		<div class="com-content-category-blog__items blog-items items-leading <?php echo $this->params->get('blog_class_leading'); ?>">
			<?php foreach ($this->lead_items as &$item) : ?>
				<div class="com-content-category-blog__item blog-item"
					itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
						<?php
						$this->item = & $item;
						echo $this->loadTemplate('leading');
						?>
				</div>
				<?php $leadingcount++; ?>
			<?php endforeach; ?>
		</div>
<?php endif; ?>

<?php
	$introcount=(count($this->intro_items));
	$counter=0;

?>
	<?php if (!empty($this->intro_items)) : ?>
		<?php $blogClass = $this->params->get('blog_class', ''); ?>
		<?php if ((int) $this->params->get('num_columns') > 1) : ?>
			<?php $blogClass .= (int) $this->params->get('multi_column_order', 0) === 0 ? ' masonry-' : ' columns-'; ?>
			<?php $blogClass .= (int) $this->params->get('num_columns'); ?>
		<?php endif; ?>
		<div class="com-content-category-blog__items blog-items <?php echo $blogClass; ?>">
		<?php foreach ($this->intro_items as $key => &$item) : ?>
			<div class="com-content-category-blog__item blog-item"
				itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
					<?php
					$this->item = & $item;
					echo $this->loadTemplate('item');
					?>
			</div>
		<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if (!empty($this->link_items)) : ?>
		<div class="items-more">
			<?php echo $this->loadTemplate('links'); ?>
		</div>
	<?php endif; ?>








<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
		<div class="pagination">
						<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
						<p class="counter">
								<?php echo $this->pagination->getPagesCounter(); ?>
						</p>

				<?php endif; ?>
				<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
<?php  endif; ?>

</div>


<?php echo $this->store->event->afterDisplayProduct; ?>