<?php
/**
 * @version		$Id: storage_s3.php 1 2023 14:04:05Z gfisch $
 * @package		mymuse
 * @copyright	Copyright © 2023 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Plugin\PluginHelper;


if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}


class plgMymuseStorage_s3 extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	
	
	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An array that holds the plugin configuration
	 */
	public function __construct(&$subject, $config)
	{
		require(__DIR__.DS.'mymuseStorage.php');
		parent::__construct($subject, $config);
	}

	public function onMymuseGetStorage($context = null){
		$storage  =  new MymuseStorage($this->params);
		return $storage;
	}

}
?>