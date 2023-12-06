<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\View\Products;
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\Registry\Registry;
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
		$this->db            = Factory::getContainer()->get('DatabaseDriver');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();

			// We do not need to filter by language when multilingual is disabled
			if (!Multilanguage::isEnabled())
			{
				unset($this->activeFilters['language']);
				$this->filterForm->removeField('language', 'filter');
			}
			if($this->getLayout() == "check"){
				$this->products = $this->get('Check');
				$tpl = 'check';
			}
		}
		else
		{
			// In article associations modal we need to remove language filter if forcing a language.
			// We also need to change the category filter to show show categories with All or the forced language.
			if ($forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
				$languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);

				// One last changes needed is to change the category filter to just show categories with All language or with the forced language.
				$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
			}
		}
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

		ToolBarHelper::title(Text::_('COM_MYMUSE').' : '.Text::_('COM_MYMUSE_TITLE_PRODUCTS'), 'mymuse.png');


        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.'product.xml';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
			    ToolBarHelper::addNew('product.add','JTOOLBAR_NEW');
		    }

		    if ($canDo->get('core.edit')) {
			    ToolBarHelper::editList('product.edit','JTOOLBAR_EDIT');
		    }

        }

		if ($canDo->get('core.edit.state')) {
            if (isset($this->items[0]->state)) {
			    ToolBarHelper::divider();
			    ToolBarHelper::custom('products.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			    ToolBarHelper::custom('products.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			    ToolBarHelper::custom('products.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true);
            } else {
                //If this component does not use state then show a direct delete button as we can not trash
                ToolBarHelper::deleteList('', 'products.delete','JTOOLBAR_DELETE');
            }

            if (isset($this->items[0]->state)) {
			    ToolBarHelper::divider();
			    //ToolBarHelper::archiveList('products.archive','JTOOLBAR_ARCHIVE');
            }
            if (isset($this->items[0]->checked_out)) {
            	ToolBarHelper::custom('products.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
            ToolBarHelper::custom('products.check', 'publish.png', 'publish.png', 'COM_MYMUSE_TEST_PRODUCTS', false);
		}
        
        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {
		    if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			    ToolBarHelper::deleteList('', 'products.delete','JTOOLBAR_DELETE');
			    ToolBarHelper::divider();
		    } else if ($canDo->get('core.edit.state')) {
			   ToolBarHelper::trash('products.trash','JTOOLBAR_TRASH');
			    ToolBarHelper::divider();
		    }
        }

		if ($canDo->get('core.admin')) {
			ToolBarHelper::preferences('com_mymuse');
		}
		ToolBarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/products-list?tmpl=component');

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
				'a.state' => Text::_('JSTATUS'),
				'a.title' => Text::_('JGLOBAL_TITLE'),
				'category_title' => Text::_('JCATEGORY'),
				'access_level' => Text::_('JGRID_HEADING_ACCESS'),
				'a.created_by' => Text::_('JAUTHOR'),
				'language' => Text::_('JGRID_HEADING_LANGUAGE'),
				'a.created' => Text::_('JDATE'),
				'a.id' => Text::_('JGRID_HEADING_ID'),
				'a.featured' => Text::_('JFEATURED')
		);
	}
	
	/*
	 * getSubCats
	* find cross referenced categories for each product
	* object $items
	*
	* return objects $items
	*/
	function getSubCats(&$items)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		for($i=0; $i < count($items); $i++){
			$query = "SELECT c.title FROM #__mymuse_product_category_xref as x
			LEFT JOIN #__categories as c on x.catid=c.id
			WHERE x.product_id=".$items[$i]->id;
			$items[$i]->subcats = "";
			$db->setQuery($query);
			if($res = $db->loadObjectList()){
				foreach($res as $r){
					$items[$i]->subcats .= $r->title.",";
				}
			}
			$items[$i]->subcats = preg_replace("/,$/","",$items[$i]->subcats);
			
			$items[$i]->attribs = new Registry($items[$i]->attribs);
        
		}
		 
	}
}
