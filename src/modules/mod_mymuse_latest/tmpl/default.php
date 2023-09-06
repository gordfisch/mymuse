<?php 
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


// no direct access
defined('_JEXEC') or die('Restricted access'); 

?>


<?php if(count($items)){

        $blogClass = $params->get('blog_class', ''); ?>
        <?php if ((int) $params->get('num_columns') > 1) : ?>
            <?php $blogClass .= (int) $params->get('multi_column_order', 0) === 0 ? ' masonry-' : ' columns-'; ?>
            <?php $blogClass .= (int) $params->get('num_columns'); ?>
            <?php $blogItemClass = " uk-width-1-".(int) $params->get('num_columns')."@m"; ?>
            <?php $blogItemClass .= " uk-width-1-2@s"; ?>
        <?php endif; ?>
        <div class="com-mymuse-category-blog__items blog-items <?php echo $blogClass; ?> uk-grid">


        <?php foreach ($items as $key => &$item) : ?>
            <div class="com-mymuse-category-blog__item blog-item <?php echo $blogItemClass; ?>"
                itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">

            <?php
            $link = RouteHelper::getProductRoute($item->id, $item->catid, '', '');
            ?>
                <div class="item-content">

                    <div class="list-image">
                        <a href="<?php echo Route::_($link); ?>"
                        ><img src="<?php echo $item->list_image; ?>" 
                        alt="<?php echo htmlspecialchars($item->list_image); ?>" border="0" 
                        /></a>
                    </div>


                    <div class="feature-title">
                        <h3>
                                <a href="<?php echo Route::_($link); ?>">
                                <?php echo $item->title; ?></a>

                        </h3>
                    </div>
                    
                </div>
            </div>
        <?php endforeach; ?>

        </div>



<?php } ?>

