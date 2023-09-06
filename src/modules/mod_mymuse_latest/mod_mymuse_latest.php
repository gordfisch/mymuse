<?php
/**
 * @version		$Id: mod_mymuse_latest_nexgen.php 1493 2015-07-22 14:58:42Z gfisch $
 * @package		mymuse
 * @copyright	Copyright © 2011 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Service\Mymuse;
use Joomla\Module\MymuseLatest\Site\Helper\MymuseLatestHelper;


require_once( JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_mymuse'.DIRECTORY_SEPARATOR.'custom.php');
$app 			= Factory::getApplication();
PluginHelper::importPlugin('mymuse');

$res			= $app->triggerEvent('onGetFullPlaylist');
$arr 			= $res[0];

$indexes 		= $arr[0];
$playlist 		= $arr[1];

$lang 			= Factory::getLanguage();
$extension 		= 'com_mymuse';
$base_dir 		= JPATH_SITE;
$language_tag 	= 'en-GB';
$reload 		= true;
$lang->load($extension, $base_dir, $language_tag, $reload);


//$mparams 		= MyMuseHelper::getParams();
//$params->merge($mparams);

$params->def('maximum_shown', 5);
$params->def('type_shown', 'albums');
$params->def('module_number', 1);

$items			= MymuseLatestHelper::getResults($params);


require(ModuleHelper::getLayoutPath('mod_mymuse_latest'));
