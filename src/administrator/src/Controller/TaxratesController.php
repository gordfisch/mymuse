<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Mymuse\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

/**
 * Taxrates list controller class.
 *
 * @since  5.0.0
 */
class TaxratesController extends AdminController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);


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
	public function getModel($name = 'taxrates', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to get the number of published articles for quickicons
	 *
	 * @return  string  The JSON-encoded amount of published articles
	 *
	 * @since   4.0
	 */
	public function getQuickiconContent()
	{
		$model = $this->getModel('taxrates');

		$model->setState('filter.published', 1);

		$amount = (int) $model->getTotal();

		$result = [];

		$result['amount'] = $amount;
		$result['sronly'] = Text::plural('COM_CONTENT_N_QUICKICON_SRONLY', $amount);
		$result['name'] = Text::plural('COM_CONTENT_N_QUICKICON', $amount);

		echo new JsonResponse($result);
	}

	/**
	 * Method to add Euro taxes in one click
	 *
	 * @return  boolean
	 *
	 * @since   4.0
	 */

	function addEuroTax()
	{

		$msg = '';
		$db = Factory::getContainer()->get('DatabaseDriver');
		$tax_names = array('VAT__AT_','VAT__BE_','VAT__BG_','VAT__CY_','VAT__CZ_','VAT__HR_',
		'VAT__DK_','VAT__EE_','VAT__FI_','VAT__FR_','VAT__DE_','VAT__GR_','VAT__HU_','VAT__IE_',
		'VAT__IT_','VAT__LT_','VAT__LU_','VAT__MT_','VAT__NL_','VAT__PL_','VAT__PT_','VAT__RO_',
		'VAT__SK_','VAT__SI_','VAT__ES_','VAT__SE_','VAT__GB_','VAT_Exempt');
		
		foreach($tax_names as $name){
			$query = "ALTER TABLE `#__mymuse_order` ADD `$name` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00';";
	
			$db->setQuery($query);
			if($db->execute()){
				$msg = "Added Euro Zone Taxes to Order Table. <br />";
			}else{
				$msg = "Error Adding Euro Zone Taxes to Order Table";
				$this->setRedirect( 'index.php?option=com_mymuse&view=taxrates', $msg);
				return false;
			}
		}
		
		
		$query = "
INSERT INTO `#__mymuse_tax_rate` ( `published`, `province`, `country`, `tax_rate`, `tax_applies_to`, `tax_name`, `tax_format`, `compounded`, `ordering`, `checked_out`, `checked_out_time`) VALUES
(1, '', 'AUT', 0.2000, 'C', 'VAT (AT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'BEL', 0.2100, 'C', 'VAT (BE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'BGR', 0.2000, 'C', 'VAT (BG)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'CYP', 0.1900, 'C', 'VAT (CY)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'CZE', 0.2100, 'C', 'VAT (CZ)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'HRV', 0.2500, 'C', 'VAT (HR)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'DNK', 0.2500, 'C', 'VAT (DK)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'EST', 0.2000, 'C', 'VAT (EE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'FIN', 0.2400, 'C', 'VAT (FI)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'FRA', 0.2000, 'C', 'VAT (FR)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'DEU', 0.1900, 'C', 'VAT (DE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'GRC', 0.2300, 'C', 'VAT (GR)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'HUN', 0.2700, 'C', 'VAT (HU)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'IRL', 0.2300, 'C', 'VAT (IE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'ITA', 0.2200, 'C', 'VAT (IT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'LTU', 0.2100, 'C', 'VAT (LT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'LUX', 0.1700, 'C', 'VAT (LU)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'MLT', 0.1800, 'C', 'VAT (MT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'NLD', 0.2100, 'C', 'VAT (NL)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'POL', 0.2300, 'C', 'VAT (PL)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'PRT', 0.2300, 'C', 'VAT (PT)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'ROM', 0.2400, 'C', 'VAT (RO)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'SVK', 0.2000, 'C', 'VAT (SK)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'SVN', 0.2200, 'C', 'VAT (SI)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'ESP', 0.2100, 'C', 'VAT (ES)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'SWE', 0.2500, 'C', 'VAT (SE)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'GBR', 0.2000, 'C', 'VAT (GB)', 'RATE', '0', 1, 0, '0000-00-00 00:00:00'),
(1, '', 'EUU', 0.0000, 'C', 'VAT Exempt', 'RATE', '0', 1, 0, '0000-00-00 00:00:00');
	 		";

		
		$db->setQuery($query);
		if($db->execute()){
			$msg .= "Added Euro Zone Taxes to Tax Rate Table";
		}else{
			$msg .= "Error Adding Euro Zone Taxes to Tax Rate Table";
		}


		$this->setRedirect( 'index.php?option=com_mymuse&view=taxrates', $msg);
		return true;
	}
}
