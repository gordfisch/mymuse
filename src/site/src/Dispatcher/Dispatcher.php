<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2021 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Site\Dispatcher;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;

/**
 * ComponentDispatcher class for com_mymuse
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch()
	{
		$checkCreateEdit = ($this->input->get('view') === 'products' && $this->input->get('layout') === 'modal')
			|| ($this->input->get('view') === 'product' && $this->input->get('layout') === 'pagebreak');

		if ($checkCreateEdit)
		{
			// Can create in any category (component permission) or at least in one category
			$canCreateRecords = $this->app->getIdentity()->authorise('core.create', 'com_mymuse')
				|| count($this->app->getIdentity()->getAuthorisedCategories('com_mymuse', 'core.create')) > 0;

			// Instead of checking edit on all records, we can use **same** check as the form editing view
			$values = (array) $this->app->getUserState('com_mymuse.edit.product.id');
			$isEditingRecords = count($values);
			$hasAccess = $canCreateRecords || $isEditingRecords;

			if (!$hasAccess)
			{
				$this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');

				return;
			}
		}

		parent::dispatch();
	}
}
