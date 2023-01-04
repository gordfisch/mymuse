<?php
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */

namespace Joomla\Component\Mymuse\Administrator\View\Reports;
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
 * View class for a list of reports.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	
	function display($tpl = null){

		$app 		= Factory::getApplication();
		$option 	= 'com_mymuse';
		$params 	= MymuseHelper::getParams();
		$jinput 	= Factory::getApplication()->input;
		$task 		= $jinput->get('task');
		$view 		= $jinput->get('view');

		if($task == 'downloadCSV'){
			$this->get('DownloadCSV');
		}
		
		// Get data from the model
		$this->state	= $this->get('State');
		$this->lists  	= $this->get( 'Lists');
		$this->form		= $this->get('Form');
		$this->addToolbar();

		if($task == 'downloads'){

			$this->downloads = $this->get('Downloads');
			parent::display('downloads');
			return true;
		}

		if(!$this->state->get('filter.start_date')
				&& !$this->state->get('filter.end_date')
				&& !$this->state->get('filter.order_status')
				&& !$this->state->get('filter.catid') ){
			 
			parent::display($tpl);
			return true;
		}
		
		$this->items			= $this->get('Items');
		$this->orderlinks 		= $this->get('Orderlinks');
		$this->pagination		= $this->get('Pagination');

		$this->orders_summary 	= $this->get( 'OrderSummary');
		$this->items_summary  	= $this->get( 'ItemsSummary');
		$this->catid			= $app->getUserStateFromRequest( 'filter.catid','catid','','int' );
		$this->show_format 		= $this->state->get('filter.show_format');
		

		


		
		
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			Error::raiseError(500, implode("\n", $errors));
			return false;
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
		
		ToolBarHelper::title(Text::_('COM_MYMUSE').' : '. Text::_( 'COM_MYMUSE_MYMUSE_REPORTS' ), 'COM_MYMUSE.png');
		ToolBarHelper::addNew('reports','COM_MYMUSE_CREATE_REPORT');
		ToolBarHelper::custom('reports.downloadCSV','publish.png', 'publish_f2.png','COM_MYMUSE_DOWNLOAD_CSV_CURRENT_REPORT',false);
		ToolBarHelper::custom('reports.downloadOrder','publish.png', 'publish_f2.png','COM_MYMUSE_DOWNLOAD_CSV_ORDER_TABLE',false);
		ToolBarHelper::custom('reports.downloadItem','publish.png', 'publish_f2.png','COM_MYMUSE_DOWNLOAD_CSV_ITEM_TABLE',false);
		
		ToolBarHelper::spacer('10px');
		
		ToolBarHelper::addNew('reports.downloads','COM_MYMUSE_DOWNLOADS_REPORT');
		ToolBarHelper::custom('reports.downloadDownload','publish.png', 'publish_f2.png','COM_MYMUSE_DOWNLOAD_CSV_DOWNLOADS',false);
		
		
		ToolBarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/reports?tmpl=component');
		


	}

}
