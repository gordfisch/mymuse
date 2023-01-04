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

use Joomla\CMS\Language\Text;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

// no direct access
defined('_JEXEC') or die('Restricted access');


if(isset(($this->item->recommends)) && is_countable($this->item->recommends) && count($this->item->recommends)) : 
$cols = count($this->item->recommends);


?>

	<h3><?php echo Text::_('COM_MYMUSE_RELATED_ITEMS'); ?></h3>

	<div class="product-recommends mymuse-container">
		<div class="my-grid columns-<?php echo $cols ?>">	
		<?php foreach($this->item->recommends as $item) : ?>
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

