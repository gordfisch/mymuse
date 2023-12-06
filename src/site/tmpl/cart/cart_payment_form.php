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
<div class="mymuse_cart payment-buttons">

	<?php foreach($results as $r){ ?>
		<div class="payment-button"><?php echo $r; ?></div>
	<?php } ?>

</div>