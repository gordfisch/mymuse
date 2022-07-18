<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2018. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */


// no direct access
defined('_JEXEC') or die;

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
$input = JFactory::getApplication()->input;

$version = new JVersion;
define('IS_FOUR', $version->isCompatible(4));

// require the helper
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'mymuse.php');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'permission.php');
require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'liveupdate'.DS.'liveupdate.php';

//initialize
$params = MyMuseHelper::getParams();



if($input->get('view','') == 'liveupdate') {
	LiveUpdate::handleRequest();
	return;
}

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_mymuse'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

//see if storage class exists in a plugin
JPluginHelper::importPlugin('mymuse');
//$dispatcher = JEventDispatcher::getInstance();
//$res= $dispatcher->trigger('onMymuseGetStorage', array('com_mymuse'));


$res = JFactory::getApplication()->triggerEvent('onMymuseGetStorage', array('com_mymuse'));


if(isset($res[0]) && is_object($res[0])){
	$GLOBALS['mymuseStorage'] = $res[0];
}else{
	//no plugin. load the default storage class
	require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'mymuseStorage.php';
	$GLOBALS['mymuseStorage'] = new MyMuseStorage($params);
}
MyMuseHelper::setParam('storage', $GLOBALS['mymuseStorage']->type);


$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_mymuse/assets/css/mymuse.css');


// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JControllerLegacy::getInstance('Mymuse'); 
$controller->execute(JFactory::getApplication()->input->get('task'));

$controller->redirect();
