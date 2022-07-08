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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Service\Mymuse;

$document = Factory::getDocument();
$js_path = Uri::Root().'modules/mod_mymuse_amplitude/assets/js/amplitude.min.js';
$css_path = Uri::Root().'modules/mod_mymuse_amplitude/assets/css/amplitude.css';

//HTMLHelper::_('script', $js_path, ['version' => 'auto', 'relative' => true], ['type' => 'module']);
$document->addStyleSheet($css_path );

PluginHelper::importPlugin('mymuse');
$app        = Factory::getApplication();
$results    = $app->triggerEvent('onGetPlaylist',array(true));
if(!$results){
    return;
}
$playlist   = $results[0];
require(JModuleHelper::getLayoutPath('mod_mymuse_amplitude'));