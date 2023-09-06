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


use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Service\Mymuse;



// no direct access
defined('_JEXEC') or die('Restricted access');



$MyMuseCart 	=& Mymuse::getObject('Cart','helper');
$cart 			=& $MyMuseCart->cart;
$order 			= $MyMuseCart->buildOrder( 0 );
$params			= MyMuseHelper::getParams();
$jinput 		= Factory::getApplication()->input;
$Itemid			= $jinput->get("Itemid", "", "string");
$checkoutUrl 	= 'index.php?option=com_mymuse&task=checkout&Itemid='.$Itemid;

if(!$params->get('my_disable_css',0)){
	// add css
	$style = Uri::root().'components/com_mymuse/assets/css/mymuse.css';

	$Doc = Factory::getDocument();
	$Doc->addStyleSheet( $style );

}

require(ModuleHelper::getLayoutPath('mod_mymuse_minicart'));