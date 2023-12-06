<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Taxrate Table class.
 *
 * @since  1.0
 */
class TaxrateTable extends Table implements VersionableTableInterface
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;

	/**
	 * Ensure the params and metadata in json encoded in the bind method
	 *
	 * @var    array
	 * @since  3.3
	 */
	protected $_jsonEncode = array('params', 'metadata');

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   1.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_mymuse.taxrate';

		parent::__construct('#__mymuse_tax_rate', 'id', $db);

		$this->setColumnAlias('title', 'tax_name');
	}

	/**
	 * Stores a taxrate.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function store($updateNulls = true)
	{
		if (is_null($this->checked_out))
		{
			$this->checked_out = 0;
		}
		$input  = Factory::getApplication()->input;
		$form   = $input->get('jform', array(), 'ARRAY');
		$db = Factory::getContainer()->get('DatabaseDriver');


		$regex = TAX_REGEX;
		$name = preg_replace("/$regex/","_",$form['tax_name']);
		preg_match_all("/[^a-zA-Z_]/",$name,$m);

		if (@$m[0]){
			$this->setError(JText::_( 'MYMUSE_ERROR_SAVING_TAX_RATE' ).$m[0][0]);
			return false;
		}elseif($form["old_tax_name"] == ""){
			$query = "ALTER TABLE `#__mymuse_order` ADD `$name` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00'";
			$db->setQuery($query);
			if(!$db->execute()){
				$this->setError("Error with saving tax : ".$db->getErrorMsg());
				return false;
			}
		}
		if($form["old_tax_name"] != '' && $form["old_tax_name"] != $form["tax_name"]){
			$name = preg_replace("/$regex/","_",$form['old_tax_name']);
			$query = "ALTER TABLE `#__mymuse_order` DROP `".$name."` ";
			$db->setQuery($query);
			if(!$db->execute()){
				$this->setError("Error removing old tax : ".$db->getErrorMsg());
				return false;
			}
			$name = preg_replace("/$regex/","_",$form['tax_name']);
			$query = "ALTER TABLE `#__mymuse_order` ADD `$name` DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00'";
			$db->setQuery($query);
			if(!$db->execute()){
				$this->setError("Error with saving tax : ".$db->getErrorMsg());
				return false;
			}
		}
		

		return parent::store($updateNulls);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     \JTable::check
	 * @since   1.5
	 */
	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Check for valid name
		if (trim($this->tax_name) == '')
		{
			$this->setError(Text::_('MYMUSE_TAX_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}


		return true;
	}


	/**
	 * Get the type alias for the history table
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   4.0.0
	 */
	public function getTypeAlias()
	{
		return 'com_mymuse.taxrate';
	}
}
