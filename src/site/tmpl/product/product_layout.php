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
$this->itemClass = "uk-width-1-".$cols."@m";
?>







<section id="product-details">
<div class="product-top blog-items columns-<?php echo $cols; ?> uk-grid">

	<?php echo $this->loadTemplate('image'); ?>

	
	<?php echo $this->loadTemplate('details'); ?>
		
</div>
</section>


<section id="product-physical">
	<?php echo $this->loadTemplate('physical'); ?>
</section>


<section id="product-items">
	<?php echo $this->loadTemplate('items'); ?>
</section>

<?php echo $this->loadTemplate('tracks'); ?>


<?php 
if( $this->params->get('split_text') ): ?>
	<section id="product-readmore">
		<div id="readmore" class="product-fulltext">
			<?php echo $this->item->fulltext ?>
		</div>
	</section>
<?php endif; ?>

<?php 
if( $this->params->get('show_recommends') ):  ?>
	<section id="product-recommends">
		<?php echo $this->loadTemplate('recommends'); ?>
	</section>

<?php endif; ?>
