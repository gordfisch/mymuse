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

if($this->params->get('my_order_msg',0)){
	echo '<div class="my_order_msg">'.$this->sparams->get('my_order_msg',0).'</div>';
}

$results = $this->results;
?>
<table class="mymuse_cart">
	<tr>
	<?php foreach($results as $r){ ?>
		<td valign="top"><?php echo $r; ?></td>
	<?php } ?>
	</tr>
</table>