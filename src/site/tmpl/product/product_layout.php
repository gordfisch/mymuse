<?php 
/**
 * @version		4.44.0
 * @package		mymuse
 * @copyright	Copyright © 2021 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author 		info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
$product = $this->item;
/*
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	+                          PRODUCT TITLE							   +
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	+                                 +                                    +
	+       IMAGE                     +        DETAILS                     +
	+     product_show_product_image  +       info_block_show              +
	+                                 +                                    +
	+                                 +                                    +
	+                                 +                                    +
	+                                 +                                    +
	+                                 +                                    +
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	+                         PRODUCT DESCRIPTION                          +
	+                                                                      +
	+                                                                      +
	+                                                                      +
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	+                         PRODUCT PHYSICAL                             +
	+                            (the CD)                                  +
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	+                             ITEMS                                    +
	+                           (T-SHIRTS)                                 +
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	+                             TRACKS                                   +
	+                                                                      +
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	+                           REST OF TEXT                               +
	+                            (OPTIONAL)                                +
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	+                            RECOMMENDS                                +
	+                            (OPTIONAL)                                +
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
*/

$cols = 0;
if( $this->params->get('product_show_product_image') && $this->item->detail_image) {
	$cols++;
}
if( $this->params->get('info_block_show')) {
	$cols++;
}
?>


<div class="product-top blog-items columns-<?php echo $cols; ?>">

	<?php echo $this->loadTemplate('image'); ?>

	
	<?php echo $this->loadTemplate('details'); ?>
		
</div>

<?php  if ($product->introtext) : ?>
<div class="product-description">            
    <?php echo $product->introtext ?>

	<?php if($product->introtext && $product->fulltext && $this->params->get('show_readmore')) : ?>
		<div><a href="#readmore" class="readon"><?php echo JText::_("COM_MYMUSE_READ_MORE"); ?>
        <?php 
        if ($this->params->get('show_readmore_title', 0) != 0) :
            echo JHtml::_('string.truncate', ($product->title), $this->params->get('readmore_limit'));
        endif;
        ?></a></div>
	 <?php endif; ?>
</div>
<?php endif; ?>

<?php echo $this->loadTemplate('physical'); ?>


<?php echo $this->loadTemplate('items'); ?>


<?php echo $this->loadTemplate('tracks'); ?>


<?php if( $this->params->get('split_text') ): ?>
	<div id="readmore" class="product-fulltext">
		<?php echo $this->item->fulltext ?>
	</div>
<?php endif; ?>

<?php if( $this->params->get('show_recommends') ): ?>
	<?php echo $this->loadTemplate('recommends'); ?>
<?php endif; ?>
