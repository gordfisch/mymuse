<?php 
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2015 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$params 		= $this->params;
$shopper 		= $this->shopper;
$store 			= $this->store;
$uri 				= Uri::getInstance();
$css_link = $uri::root().(Route::_('components/com_mymuse/assets/css/mymuse.css', false)); 
?>
<!DOCTYPE HTML>
<html lang="en-gb" dir="ltr">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta charset="utf-8" />

	
<title><?php echo $store->title; ?></title>


</head>
    <body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" >    
<style>
	<?php 
include_once(JPATH_SITE.DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_mymuse".DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."css".DIRECTORY_SEPARATOR."mymuse.css");
?>
.store.email-header {
	display: grid;
  grid-template-columns: 1fr 4fr;
}
.store.email-header > div {
	padding: 20px;
}
</style>
<div class="store email-header" style="display: grid;
  grid-template-columns: 1fr 4fr;">
	<div class="logo" style="padding: 20px;"><a href="<?php echo Uri::root(); ?>"><img align="left" src="<?php echo Uri::root().$params->get('store_thumb_image'); ?>" border="0"></a>
	</div>
	<div style="padding: 20px;">
		<?php echo $store->title; ?><br />
		<?php echo $params->get('address_1').' '.$params->get('address_2'); ?><br />
		<?php echo $params->get('city').', '.$params->get('state'); ?><br />
		<?php echo $params->get('country').', '.$params->get('zip'); ?><br />
		<?php echo $params->get('phone'); ?><br />
		<a href="mailto: <?php echo $params->get('contact_email'); ?>"><?php echo $params->get('contact_email'); ?></a><br />
		<a href="<?php echo JURI::root(); ?>"><?php echo JURI::root(); ?></a>
	</div>
</div>

<?php if(isset($this->my_email_msg) && $this->my_email_msg != '') : ?>
<div>
	<?php echo $this->my_email_msg; ?>
</div>
<?php endif; ?>


