<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\View\Productattributeskus;
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

/**
 * View class for a list of taxrates.
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
	 * A \JForm instance with filter fields.
	 *
	 * @var    \JForm
	 * @since  3.6.3
	 */
	public $filterForm;

	/**
	 * An array with active filters.
	 *
	 * @var    array
	 * @since  3.6.3
	 */
	public $activeFilters;

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
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->canDo         = MymuseHelper::getActions('com_mymuse');
		$this->db            = Factory::getDbo();

		$this->lists		= $this->get('Lists');
		$this->parent 		= $this->get('Parent');
		$this->sortfields 	= $this->getSortFields();

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
	 * @since	1.6
	 */
	protected function addToolbar()
	{


		$state	= $this->get('State');
		$canDo = $this->canDo;
		$link = '<a style="text-decoration:underline" href="index.php?option=com_mymuse&view=product&layout=edit&subtype=item&id='.$this->parent->id.'">';
		$link .= $this->parent->title.'</a>';
		ToolBarHelper::title(Text::_('COM_MYMUSE_TITLE_PRODUCTATTRIBUTES')." : ".$link, 'mymuse.png');

        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.'product.xml';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
			    ToolBarHelper::addNew('productattributesku.add','JTOOLBAR_NEW');
		    }

		    if ($canDo->get('core.edit')) {
			    ToolBarHelper::editList('productattributesku.edit','JTOOLBAR_EDIT');
		    }

        }

		if ($canDo->get('core.edit.state')) {
            ToolBarHelper::deleteList('', 'productattributeskus.remove','JTOOLBAR_DELETE');
		}


		ToolBarHelper::addNew('productattributesku.myreturn','com_MYMUSE_RETURN_TO_ITEMS');
		
		ToolBarHelper::help('', false, 'http://www.joomlamymuse.com/documentation/documentation-2-5/153-product-items-list-attributes?tmpl=component');

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
				'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
				'a.product_parent_id' => Text::_('COM_MYMUSE_PRODUCT'),
				'a.name' => Text::_('JGLOBAL_TITLE'),
				'a.id' => Text::_('JGRID_HEADING_ID')
		);
	}
	
}
