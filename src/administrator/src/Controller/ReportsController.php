<?php 
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 */

namespace Joomla\Component\Mymuse\Administrator\Controller;

\defined('_JEXEC') or die;


use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Session\Session;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;


class ReportsController extends AdminController
{
	/**
	 * Custom Constructor
	 */

	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);
		$input = Factory::getApplication()->input;
		$input->set('view','reports');


	}
	
	/**
	 * display the report
	 * @return void
	 */
	function report()
	{
		$input 	= Factory::getApplication()->input;
		$input->set( 'layout', 'report');

		parent::display();
	}
	
	/**
	 * display the dowmloads report
	 * @return void
	 */
	function downloads()
	{
		parent::display();
	}
	
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'reports', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
	
	
	/**
	 * download the order table as csv
	 * 
	 */
	public function downloadOrder()
	{
		$this->downloadCSV('mymuse_order');
	}
	
	/**
	 * download the item table as csv
	 *
	 */
	public function downloadItem()
	{
		$this->downloadCSV('mymuse_order_item');
	}
	
	/**
	 * download the download table as csv
	 *
	 */
	public function downloadDownload()
	{
		$this->downloadCSV('mymuse_downloads');
	}
	
	
	public function downloadCSV($report='report')
	{
		$model = $this->getModel('reports');

		$show_format = $model->getState('filter.show_format');

		$items = $model->getCsv ($report);
		//MymuseHelper::print_pre($items); exit;
		if($items){
			$delimiter = ",";
			$f = fopen('php://memory', 'w');
			$fields = array_keys ( (array)$items[0]) ;
			fputcsv($f, $fields, $delimiter);
			foreach ( $items as $line ) {
			    fputcsv($f, (array) $line, $delimiter);
			}
			fseek($f, 0);

			$date = $date = Factory::getDate()->format('Y-m-d');
			if($show_format){
				$filename = $report.'_by_format_'.$date.'.csv';
			}else{
				$filename = $report.'_'.$date.'.csv';
			}
			
			
			header ( "Content-type: text/csv" );
			header ( "Content-Disposition: attachment; filename=$filename" );
			header ( "Pragma: no-cache" );
			header ( "Expires: 0" );
			
			fpassthru($f);

			exit();
		}else{
			echo "error"; exit;
		}
		
	}
}
?>
