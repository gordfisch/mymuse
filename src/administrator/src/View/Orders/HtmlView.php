<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\View\Orders;
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;
use Joomla\Database\DatabaseDriver;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

/**
 * View class for a list of orders.
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

		foreach($this->items as $item){
			if(isset($item->notes) and $item->notes){
				$registry = new Registry;
				$registry->loadString($item->notes);
				if($registry->get('first_name')){
					$item->shopper = $registry->get('first_name')." ".$registry->get('last_name');
				}
				if($registry->get('name')){
					$item->shopper = $registry->get('name');
				}
			}
			//any items backordered?
			$item->backordered = 0;
			$query = "SELECT count(*) from #__mymuse_order_item WHERE order_id=".$item->id."
			AND product_in_stock='-1' AND file_name = ''";

			$this->db->setQuery($query);
			$res = $this->db->loadResult();
			if($res){
				$item->backordered = 1;
			}
			//any items preordered?
			$item->preordered = 0;
			$query = "SELECT count(*) from #__mymuse_order_item WHERE order_id=".$item->id."
			AND product_in_stock='-1' AND file_name != ''";

			$this->db->setQuery($query);
			$res = $this->db->loadResult();
			if($res){
				$item->preordered = 1;
			}



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

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolBarHelper::title(Text::_('COM_MYMUSE').' : '.Text::_('COM_MYMUSE_TITLE_ORDERS'), 'mymuse.png');

		/*if ($canDo->get('core.create'))
		{
			$toolbar->addNew('order.add');
		}
		*/
		if ($canDo->get('core.edit.state') || $canDo->get('core.admin'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if ($canDo->get('core.delete'))
			{
				$childBar->delete('orders.delete')
					->text('JTOOLBAR_DELETE')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			$toolbar->preferences('com_mymuse');
		}

		$toolbar->help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/orders-list?tmpl=component');

	}

}
