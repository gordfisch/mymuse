<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */
use Joomla\CMS\Language\Text;

// No direct access
defined('_JEXEC') or die;

use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\MVC\Controller\AdminController;

class MymuseController extends AdminController
{
	/*var error */
	var $error = null;
	
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable	If true, the view output will be cached
	 * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{

		// Load the submenu.
		$view = Factory::getApplication()->input->get('view', 'mymuse');

		if($view != "product"){
			MymuseHelper::addSubmenu($view);
		}

		parent::display();

		return $this;
	}
	


	


}
