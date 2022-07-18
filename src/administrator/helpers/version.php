<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

class MymuseVersion
{
	public $version = '';
	public $key = 'FW6AL534B2';
	// Unused
	public $revision = null;
	
	public function __construct() {
		if (preg_match('/<version>([0-9.]+)<\/version>/s', file_get_contents(JPATH_ADMINISTRATOR.'/components/com_mymuse/mymuse.xml'), $match)) {
			$this->version = $match[1];
		}
	}

	public function __toString() {
		return $this->version;
	}
}