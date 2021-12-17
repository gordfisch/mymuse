<?php 
/**
 * @version		$$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(isset(($this->recommends)) && is_countable($this->recommends) && count($this->recommends)) : ?>
	<div class="product-recommends">
		
		<h3><?php echo JText::_('COM_MYMUSE_RELATED_ITEMS'); ?></h3>

		<div id="mymuse-container">	
		<?php foreach($this->recommends as $item) : ?>
			<?php if($item->list_image) :?>
			<div class="related-item">
			<a href="<?php echo $item->url; ?>"><img src="<?php echo $item->list_image; ?>"></a>
			</div>
			<?php endif; ?>
		<?php endforeach; ?>
		</div>
	</div>
	
<?php endif; ?>
<div style="clear: both;"></div>

