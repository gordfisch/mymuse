<?php
/**
 * @version     $Id$
 * @package     com_mymuse4
 * @copyright   Copyright (C) 2022. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;


// Create a shortcut for params.
$params 	= &$this->item->params;
$canEdit	= $this->item->params->get('access-edit');
$lang 		= Factory::getLanguage();
$link 		= RouteHelper::getProductRoute($this->item->id, 0, $this->item->language, '');
?>

<?php if ($this->item->state == 0) : ?>
<div class="system-unpublished">
<?php endif; ?>


<div class="leading blog-items columns-2">
	<div class="left">
	<?php if ($params->get('category_show_product_image') && $this->item->list_image): ?>
		<div class="list-image">
			<a href="<?php echo Route::_(RouteHelper::getProductRoute($this->item->id, $this->item->catid, $this->item->language, 'product')); ?>"
			><img src="<?php echo $this->item->list_image; ?>" 
			alt="<?php echo htmlspecialchars($this->item->list_image); ?>" border="0" 
			<?php if ($params->get('category_product_image_height', 0)) : ?>
			style="height: <?php echo $params->get('category_product_image_height'); ?>px"
			<?php endif; ?>
			/></a>
		</div>
	<?php endif; ?>
	</div>
	<div class="right">
		<?php echo LayoutHelper::render('joomla.content.blog_style_default_item_title', $this->item); ?>

		<?php if (!$params->get('category_show_intro_text')) : ?>
			<?php echo $this->item->event->afterDisplayTitle; ?>
		<?php endif; ?>

		<?php echo $this->item->event->beforeDisplayContent; ?>


		<?php if($params->get('category_show_intro_text')) :?>
			<?php echo $this->item->introtext; ?>
		<?php endif; ?>


		<?php 
		if ($params->get('category_show_readmore') && $this->item->readmore) :
			if ($params->get('access-view')) :
				$link = Route::_(RouteHelper::getProductRoute($this->item->slug, $this->item->catid));
			else :
				$menu = Factory::getApplication()->getMenu();
				$active = $menu->getActive();
				$itemId = $active->id;
				$link1 = Route::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
				$returnURL = Route::_(MyMuseHelperRoute::getProductRoute($this->item->slug, $this->item->catid));
				$link = new URI($link1);
				$link->setVar('return', base64_encode($returnURL));
			endif;
		?>
				<p class="readmore">
						<a href="<?php echo $link; ?>">
							<?php if (!$params->get('access-view')) :
								echo Text::_('COM_MYMUSE_REGISTER_TO_READ_MORE');
							elseif ($readmore = $this->item->alternative_readmore) :
								echo $readmore;
								if ($params->get('store_show_readmore_title', 0) != 0) :
								    echo HtmlHelper::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
								endif;
							elseif ($params->get('show_readmore_title', 0) == 0) :
								echo Text::sprintf('COM_MYMUSE_READ_MORE_TITLE');
							else :
								echo Text::_('COM_MYMUSE_READ_MORE').' ';
								echo HtmlHelper::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
							endif; ?></a>
				</p>
		<?php endif; ?>

	</div>

</div>

<?php if ($this->item->state == 0) : ?>
</div>
<?php endif; ?>

<div class="item-separator"></div>
<?php echo $this->item->event->afterDisplayContent; ?>
