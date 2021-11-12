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
<h2 class="my-title"><?php echo Jtext::_('MYMUSE_ENTER_A_COUPON'); ?></h2>
<table class="mymuse_cart" >
	<tr class="mymuse_cart_top">
		<td colspan="2" class="mymuse_cart_top"><b><?php echo JText::_('MYMUSE_ENTER_COUPON_CODE'); ?></b></td>
	</tr>
	<tr>
		<td>
			<input type="text" class="input" id="coupon" name="coupon" value="" size="50">
			<div class="coupon-submit"><button class="btn uk-button btn-primary" 
			type="submit" ><?php echo JText::_('MYMUSE_SUBMIT'); ?></button></div></td>
	</tr>
</table>
</form>
</div>