<?php 
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div class="coupon-add">
<form action="index.php" method="post" name="adminForm">
<input type="hidden" name="option" value="com_mymuse">
<input type="hidden" name="task" value="couponadd">
<input type="hidden" name="Itemid" value="<?php echo @$this->Itemid; ?>">
<h2 class="my-title"><?php echo Jtext::_('COM_MYMUSE_ENTER_A_COUPON'); ?></h2>


<ul class="mymuse-container" >

	<li>
		<div>
			<input type="text" class="input" id="coupon" name="coupon" value="" size="50">
		</div>
	</li>
	<li>
		<div class="coupon-submit"><button class="btn uk-button btn-primary" 
			type="submit" ><?php echo JText::_('COM_MYMUSE_SUBMIT'); ?></button>
		</div>
	</li>
</ul>
</form>
</div>