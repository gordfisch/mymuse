<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2011 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */

// no direct access

use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\MymuseLatest\Site\Helper\MymuseLatestHelper;
use Joomla\CMS\HTML\HTMLHelper;

$mparams 	= MymuseHelper::getParams();
$params->merge($mparams);
$doc = Factory::getDocument();
$doc->addStyleSheet( 'modules/mod_mymuse_latest/mod_mymuse_latest_style.css' );

$params->def('maximum_shown', 5);
$params->def('type_shown', 'tracks');
$params->def('module_number', 1);

$list	= MymuseLatestHelper::getResults($params);

// ui js and css

$document = Factory::getDocument();

$document->addStyleSheet('https://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css');

if($params->get('type_shown') == "tracks" && $params->get('show_track_preview')){
	
	$site_url = preg_replace("#administrator/#","",JURI::base());
	$swf_path = JURI::root() .'/plugins/mymuse/audio_html5/Jplayer.swf';

	// player set to play the first list[0]
	$supplied = array();
	$trs[0]['src'] = $list[0]->path;
	$ext = pathinfo($list[0]->path, PATHINFO_EXTENSION);
	if($ext == "ogg"){
		$ext = "oga";
	}
	if(!isset($MyMuseHelper->extarray[$ext])){
		
	}
	$trs[0]['type'] = $MyMuseHelper->extarray[$ext];
	$trs[0]['ext'] = $ext;
	
	$supplied[] = $ext;
	
		
	if(isset($list[0]->file_preview_2) && $list[0]->file_preview_2 != ''){
		$trs[1]['src'] = $list[0]->path_2;
		$ext = pathinfo($list[0]->path_2, PATHINFO_EXTENSION);
		if($ext == "ogg"){
			$ext = "oga";
		}
		$trs[1]['type'] = $MyMuseHelper->extarray[$ext];
		$trs[1]['ext'] = $ext;
	
		if(!in_array($ext,$supplied)){
			$supplied[] = $ext;
		}
	}
	if(isset($list[0]->file_preview_3) && $list[0]->file_preview_3 != ''){
		$trs[2]['src'] = $list[0]->path_3;
		$ext = pathinfo($list[0]->path_3, PATHINFO_EXTENSION);
		if($ext == "ogg"){
			$ext = "oga";
		}
		$trs[2]['type'] = $MyMuseHelper->extarray[$ext];
		$trs[2]['ext'] = $ext;
	
		if(!in_array($ext,$supplied)){
			$supplied[] = $ext;
		}
	}
	$supplied = implode(", ",$supplied);
	$media = '';
	foreach($trs as $source){
		$media .= $source['ext'].': "'.$source['src'].'",'."\n";
	
	}
		
	$media = preg_replace("/,\\n$/","",$media);
	
	
	$document = Factory::getDocument();
	JHtml::_('jquery.framework');
	
	$css_path = $site_url.'modules'.DIRECTORY_SEPARATOR.'mod_mymuse_latest'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'circle.player.css';

	$document->addStyleSheet( $css_path );
	
	//load jplayer?
	$match = 0;
	while(list($url,$arr) = each($document->_scripts)){
		if(preg_match("/jquery.jplayer.min.js/", $url)){
			$match = 1;
		}
	}
	if(!$match){
		$js_path = $site_url.'plugins/mymuse/audio_jplayer/js/jquery.jplayer.min.js';
		$document->addScript( $js_path );
	}
	//load jplayer inspector?
	$match = 0;
	while(list($url,$arr) = each($document->_scripts)){
		if(preg_match("/jquery.jplayer.inspector.js/", $url)){
			$match = 1;
		}
	}
	if(!$match){
		$js_path = $site_url.'plugins/mymuse/audio_jplayer/js/jquery.jplayer.inspector.js';
		$document->addScript( $js_path );
	}
	
	$js_path = $site_url.'modules'.DIRECTORY_SEPARATOR.'mod_mymuse_latest'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'jquery.transform2d.js';
	$document->addScript( $js_path );
	$js_path = $site_url.'modules'.DIRECTORY_SEPARATOR.'mod_mymuse_latest'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'jquery.grab.js';
	$document->addScript( $js_path );
	$js_path = $site_url.'modules'.DIRECTORY_SEPARATOR.'mod_mymuse_latest'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'mod.csstransforms.min.js';
	$document->addScript( $js_path );
	$js_path = $site_url.'modules'.DIRECTORY_SEPARATOR.'mod_mymuse_latest'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'circle.jplayer.js';
	$document->addScript( $js_path );
	$js = '
	var myCirclePlayer;
	//<![CDATA[


jQuery(document).ready(function(){

	/*
	 * Instance CirclePlayer inside jQuery doc ready
	 *
	 * CirclePlayer(jPlayerSelector, media, options)
	 *   jPlayerSelector: String - The css selector of the jPlayer div.
	 *   media: Object - The media object used in jPlayer("setMedia",media).
	 *   options: Object - The jPlayer options.
	 *
	 * Multiple instances must set the cssSelectorAncestor in the jPlayer options. Defaults to "#cp_container_1" in CirclePlayer.
	 *
	 * The CirclePlayer uses the default supplied:"m4a, oga" if not given, which is different from the jPlayer default of supplied:"mp3"
	 * Note that the {wmode:"window"} option is set to ensure playback in Firefox 3.6 with the Flash solution.
	 * However, the OGA format would be used in this case with the HTML solution.
	 */

	myCirclePlayer = new CirclePlayer("#jquery_jplayer_m",
	{
		'.$media.'
	}, {
		cssSelectorAncestor: "#cp_container_1",
		swfPath: "'.$swf_path.'",
		wmode: "window",
		keyEnabled: true
	},
	
	"'.$supplied.'"
	);


	
});
//]]>';
	$document->addScriptDeclaration($js);
	foreach($list as $track){
		
	}
	
}

require(JModuleHelper::getLayoutPath('mod_mymuse_latest'));