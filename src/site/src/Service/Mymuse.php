<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2021 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Object\CMSObject;


/**
 * Content Component Category Tree
 *
 * @since  1.6
 */
class Mymuse extends CMSObject
{

	/**
	 * instances array of objects
	 *
	 * @var array
	 */
	var $instances = array();


	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.7.0
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);
		
	}

	/**
	 * Returns a reference to a global MyMuse object, only creating it if it
	 * doesn't already exist. The default is to look in the helpers directory.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $MyMuseStore 	=& MyMuse::getObject('Store','model');</pre>
	 *
	 * @param	string	$client		type of class.
	 * @param	string	$type 		An optional type, default helper
	 * @param	array	$config 	An optional associative array of configuration settings.
	 * @return	MyMuse	The MyMuse object.
	 * @since	1.5
	 */
	public static function &getObject($client, $type='helper', $config = array(), $renew = '')
	{
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		if (empty($instances[$client]) || $renew == "renew")
		{
			// Create an object
			if($type == 'model'){
				$classname = '\\Joomla\\Component\\Mymuse\\Site\\Model\\'.ucfirst($client).'Model';
			}else{
				$classname = '\\Joomla\\Component\\Mymuse\\Site\\Helper\\'.ucfirst($client). 'Helper';
			}
			$instance = new $classname($config);
			$instances[$client] =& $instance;
		}

		return $instances[$client];
	}
}
