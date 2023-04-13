<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Site\View\Categories\HtmlView as CatsView;

$lang   = Factory::getLanguage();
$user   = Factory::getUser();
$groups = $user->getAuthorisedViewLevels();
$cols   = $this->params->get('subcat_columns', '');
$blogClass = $this->params->get('blog_class', '');
if ((int) $this->params->get('subcat_columns') > 1) :
    $blogClass .= (int) $this->params->get('multi_column_order', 0) === 0 ? ' masonry-' : ' columns-';
    $blogClass .= (int) $this->params->get('subcat_columns');
endif; 
?>


<?php if ($this->maxLevel != 0 && count($this->children[$this->category->id]) > 0) : ?>
    <div class="com-mymuse-category-blog__items blog-items <?php echo $blogClass; ?>  uk-grid">

    <?php foreach ($this->children[$this->category->id] as $id => $child) : ?>
        <?php // Check whether category access level allows access to subcategories. 
        $child->numitems = CatsView::_getProductCount($child);

        ?>
        <?php if (in_array($child->access, $groups)) : ?>
            <?php if ($this->params->get('show_empty_categories') || $child->numitems || count($child->getChildren())) : 

            ?>
            <div class="com-content-category-blog__child uk-width-1-<?php echo $cols; ?>@m uk-margin">
                <?php if ($lang->isRtl()) : ?>
                <h3 class="page-header item-title">
                    <?php if ($this->params->get('show_cat_num_articles', 1)) : ?>
                        <span class="badge bg-info tip">
                            <?php echo $child->getNumItems(true); ?>
                        </span>
                    <?php endif; ?>
                    <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($child->id, $child->language)); ?>">
                    <?php echo $this->escape($child->title); ?></a>

                    <?php if ($this->maxLevel > 1 && count($child->getChildren()) > 0) : ?>
                        <a href="#category-<?php echo $child->id; ?>" data-bs-toggle="collapse" class="btn btn-sm float-end" aria-label="<?php echo Text::_('JGLOBAL_EXPAND_CATEGORIES'); ?>"><span class="icon-plus" aria-hidden="true"></span></a>
                    <?php endif; ?>
                </h3>

                <?php else : ?>

                <h3 class="page-header item-title"><a href="<?php echo Route::_(RouteHelper::getCategoryRoute($child->id, $child->language)); ?>">
                    <?php echo $this->escape($child->title); ?></a>
                    <?php if ($this->params->get('show_cat_num_articles', 1)) : ?>
                        <span class="badge bg-info">
                            <?php echo Text::_('COM_MYMUSE_NUM_ITEMS'); ?>&nbsp;
                            <?php echo $child->getNumItems(true); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($this->maxLevel > 1 && count($child->getChildren()) > 0) : ?>
                        <a href="#category-<?php echo $child->id; ?>" data-bs-toggle="collapse" class="btn btn-sm float-end" aria-label="<?php echo Text::_('JGLOBAL_EXPAND_CATEGORIES'); ?>"><span class="icon-plus" aria-hidden="true"></span></a>
                    <?php endif; ?>
                </h3>
                <?php endif; ?>

                <?php 

                if ($this->params->get('show_subcat_image') == 1 && $child->getParams()->get('image')) :?>
                <div class="subcat-image"><a href="<?php echo JRoute::_(RouteHelper::getCategoryRoute($child->id));?>">
                    <img src="<?php echo $child->getParams()->get('image'); ?>"
                    <?php if ($this->params->get('category_image_height')) : ?>
                        height="<?php echo $this->params->get('category_image_height'); ?>"
                    <?php endif; ?> /></a>
                </div>
                
                <?php endif; ?>

                <?php if ($this->params->get('show_subcat_desc') == 1) : ?>
                    <?php if ($child->description) : ?>
                        <?php if ($this->params->get('subcat_desc_truncate')) : 
                            $child->description = HtmlHelper::_('string.truncate', ($child->description), $this->params->get('subcat_desc_truncate'));
                            $child->description = str_replace("...",'',$child->description);
                            $child->description = preg_replace("~</p>$~",' ...</p>',$child->description);
                        
                        endif; ?>


                    <div class="com-content-category-blog__description category-desc">
                        <?php echo HTMLHelper::_('content.prepare', $child->description, '', 'com_content.category'); ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($this->maxLevel > 1 && count($child->getChildren()) > 0) : ?>
                <div class="com-content-category-blog__children collapse fade" id="category-<?php echo $child->id; ?>">
                    <?php
                    $this->children[$child->id] = $child->getChildren();
                    $this->category = $child;
                    $this->maxLevel--;
                    echo $this->loadTemplate('children');
                    $this->category = $child->getParent();
                    $this->maxLevel++;
                    ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif;
