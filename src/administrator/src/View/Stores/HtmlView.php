<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\View\Stores;
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

/**
 * View class for a list of stores.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The item data.
	 *
	 * @var   object
	 * @since 1.6
	 */
	protected $items;

	/**
	 * The pagination object.
	 *
	 * @var   \Joomla\CMS\Pagination\Pagination
	 * @since 1.6
	 */
	protected $pagination;

	/**
	 * The model state.
	 *
	 * @var   CMSObject
	 * @since 1.6
	 */
	protected $state;


	/**
	 * An ACL object to verify user rights.
	 *
	 * @var    CMSObject
	 * @since  3.6.3
	 */
	protected $canDo;

	/**
	 * An instance of DatabaseDriver.
	 *
	 * @var    DatabaseDriver
	 * @since  3.6.3
	 */
	protected $db;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->canDo         = MymuseHelper::getActions('com_mymuse');
		$this->db            = Factory::getContainer()->get('DatabaseDriver');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$canDo = $this->canDo;
		$user  = Factory::getApplication()->getIdentity();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('MYMUSE').' : '.Text::_('COM_MYMUSE_TITLE_STORES'), 'stores store');


		if ($canDo->get('core.edit.state') || $canDo->get('core.admin'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			$childBar->publish('stores.publish', 'JTOOLBAR_PUBLISH', true);
			$childBar->unpublish('stores.unpublish', 'JTOOLBAR_UNPUBLISH', true);


			if ($canDo->get('core.delete'))
			{
				$childBar->delete('stores.delete')
					->text('JTOOLBAR_DELETE')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			$toolbar->preferences('com_mymuse');
		}

		ToolbarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/store-edit?tmpl=component');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(			
				'a.state' => Text::_('JSTATUS'),
				'a.id' => Text::_('JGRID_HEADING_ID'),
		);
	}
}
