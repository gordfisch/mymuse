<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

$app = Factory::getApplication();

$category = $this->category;
$uri = JUri::getInstance(); 
$cat_uri = $uri->toString();
$description = ($this->category->description != '')? $this->category->description : $this->category->title;
$description = HtmlHelper::_('string.truncate', ($description), 200);
$document   = Factory::getDocument();
$document->setMetaData( 'og:site_name',nl2br($this->escape($this->store->title))  );
$document->setMetaData( 'og:type', 'article');
$document->setMetaData( 'og:url', $cat_uri);
$document->setMetaData( 'og:title', $this->escape($this->category->title));
$document->setMetaData( 'og:description', strip_tags($description));
$document->setMetaData( 'og:image', JURI::Root().$this->category->getParams()->get('image'));

$document->setMetaData( 'twitter:title', $this->escape($this->category->title));
$document->setMetaData( 'twitter:card', 'summary_large_image');
$document->setMetaData( 'twitter:site', $this->params->get('twitter_handle'));
$document->setMetaData( 'twitter:creator', $this->params->get('twitter_handle'));
$document->setMetaData( 'twitter:url', $cat_uri);
$document->setMetaData( 'twitter:description', strip_tags($description));
$document->setMetaData( 'twitter:image', JURI::Root().$this->category->getParams()->get('image'));


$this->category->text = $this->category->description;


$app->triggerEvent('onContentPrepare', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$this->category->description = $this->category->text;

$results = $app->triggerEvent('onContentAfterTitle', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayTitle = trim(implode("\n", $results));

$results = $app->triggerEvent('onContentBeforeDisplay', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$beforeDisplayContent = trim(implode("\n", $results));

$results = $app->triggerEvent('onContentAfterDisplay', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayContent = trim(implode("\n", $results));

$htag    = $this->params->get('show_page_heading') ? 'h2' : 'h1';

?>
<div class="com-mymuse-category-blog blog" itemscope itemtype="https://schema.org/Blog">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
        </div>
    <?php endif; ?>

    <?php if ($this->params->get('show_category_title', 1)) : ?>
    <<?php echo $htag; ?>>
        <?php echo $this->category->title; ?>
    </<?php echo $htag; ?>>
    <?php endif; ?>
    <?php echo $afterDisplayTitle; ?>


    <?php if ($this->params->get('show_cat_tags', 1) && !empty($this->category->tags->itemTags)) : ?>
        <?php $this->category->tagLayout = new FileLayout('joomla.content.tags'); ?>
        <?php echo $this->category->tagLayout->render($this->category->tags->itemTags); ?>
    <?php endif; ?>


    <?php if ($beforeDisplayContent || $afterDisplayContent || $this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
        <div class="category-desc clearfix">

            <?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
                <?php echo LayoutHelper::render(
                    'joomla.html.image',
                    [
                        'src' => $this->category->getParams()->get('image'),
                        'alt' => empty($this->category->getParams()->get('image_alt')) && empty($this->category->getParams()->get('image_alt_empty')) ? false : $this->category->getParams()->get('image_alt'),
                    ]
                ); ?>
            <?php endif; ?>

            <?php echo $beforeDisplayContent; ?>

            <?php if ($this->params->get('show_description') && $this->category->description) : ?>
                <?php echo HTMLHelper::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
            <?php endif; ?>
            
            <?php echo $afterDisplayContent; ?>
        </div>
    <?php endif; ?>



    <?php if ($this->maxLevel != 0 && !empty($this->children[$this->category->id])) : ?>
        <div class="com-mymuse-category-blog__children cat-children">
    
            <?php if ($this->params->get('show_category_heading_title_text', 1) == 1) : ?>
                <h3> <?php echo Text::_('JGLOBAL_SUBCATEGORIES'); ?> </h3>
            <?php endif; ?>
            <?php echo $this->loadTemplate('children'); ?> </div>
    <?php endif; ?>





    <?php if (empty($this->lead_items) && empty($this->link_items) && empty($this->intro_items)) : ?>
        <?php if ($this->params->get('show_no_articles', 1)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                    <?php echo Text::_('COM_MYMUSE_NO_PRODUCTS'); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

     <?php if (!empty($this->lead_items) || !empty($this->link_items) || !empty($this->intro_items)) : ?>
        <h2><?php echo Text::_("COM_MYMUSE_PRODUCTS"); ?></h2>
    <?php endif; ?>

    <?php if (!empty($this->lead_items)) : ?>
        <div class="com-mymuse-category-blog__items blog-items items-leading <?php echo $this->params->get('blog_class_leading'); ?>">
            <?php foreach ($this->lead_items as &$item) : ?>
                <div class="com-mymuse-category-blog__item blog-item" itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
                    <?php
                    $this->item = &$item;
                    echo $this->loadTemplate('item');
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php 

    if (!empty($this->intro_items)) : ?>

        <?php 

        $blogClass = $this->params->get('blog_class', ''); ?>
        <?php if ((int) $this->params->get('num_columns') > 1) : ?>
            <?php $blogClass .= (int) $this->params->get('multi_column_order', 0) === 0 ? ' masonry-' : ' columns-'; ?>
            <?php $blogClass .= (int) $this->params->get('num_columns'); ?>
            <?php $blogItemClass = " uk-width-1-".(int) $this->params->get('num_columns')."@m"; ?>
            <?php $blogItemClass .= " uk-width-1-2@s"; ?>
        <?php endif; ?>
        <div class="com-mymuse-category-blog__items blog-items <?php echo $blogClass; ?> uk-grid">
        <?php foreach ($this->intro_items as $key => &$item) : ?>
            <div class="com-mymuse-category-blog__item blog-item <?php echo $blogItemClass; ?>"
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


    
    <?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
        <div class="com-mymuse-category-blog__navigation w-100">
            <?php if ($this->params->def('show_pagination_results', 1)) : ?>
                <p class="com-mymuse-category-blog__counter counter float-end pt-3 pe-2">
                    <?php echo $this->pagination->getPagesCounter(); ?>
                </p>
            <?php endif; ?>
            <div class="com-mymuse-category-blog__pagination">
                <?php echo $this->pagination->getPagesLinks(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
