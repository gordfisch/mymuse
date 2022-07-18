<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Mymuse\Site\Model;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Mymuse model for the Joomla Mymuse component.
 *
 * @since  4.0.0
 */
class MymuseModel extends BaseDatabaseModel
{
	/**
	 * @var string message
	 */
	protected $message;
	/**
	* Get the message
	 *
	 * @return  string  The message to be displayed to the user
	 */
	public function getMsg()
	{
		if (!isset($this->message))
		{
			$this->message = 'Hello Mooosers!';
		}
		return $this->message;
	}
}