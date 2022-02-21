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
$cols = 0;
if( $this->params->get('product_show_product_image') && $this->item->detail_image) {
	$cols++;
}
if( $this->params->get('info_block_show')) {
	$cols++;
}

/*
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	+                                 +                                    +
	+                                 +                                    +
	+                                 +                                    +
	+       IMAGE                     +          DETAILS                   +
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

?>

<div class="product columns-<?php echo $cols; ?>">

	<?php echo $this->loadTemplate('image'); ?>

	
	<?php echo $this->loadTemplate('details'); ?>
		
</div>


<div class="product-description">
	<?php echo $this->item->introtext ?>
</div>


<?php echo $this->loadTemplate('physical'); ?>


<?php echo $this->loadTemplate('items'); ?>


<?php echo $this->loadTemplate('tracks'); ?>


<?php if( $this->params->get('split_text') ): ?>
	<div class="product-fulltext">
		<?php echo $this->item->fulltext ?>
	</div>
<?php endif; ?>

<?php if( $this->params->get('show_recommends') ): ?>
	<?php echo $this->loadTemplate('recommends'); ?>
<?php endif; ?>
