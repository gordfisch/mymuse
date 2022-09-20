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
 * coupon Table class.
 *
 * @since  1.0
 */
class CouponTable extends Table implements VersionableTableInterface
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
		$this->typeAlias = 'com_mymuse.coupon';

		parent::__construct('#__mymuse_coupon', 'id', $db);

		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Stores a coupon.
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

		$date				= Factory::getDate();
		$this->modified		= $date->toSQL();
		if (!intval($this->created)) {
			$this->created = $date->toSQL();
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
		if (trim($this->title) == '')
		{
			$this->setError(Text::_('COM_MYMUSE_COUPON_TITLE_REQUIRED'));

			return false;
		}
		// Check for valid code
		if (trim($this->code) == '')
		{
			$this->setError(Text::_('COM_MYMUSE_COUPON_CODE_REQUIRED'));

			return false;
		}
		// Check for product
		if (trim($this->product_id) == '' && trim($this->coupon_type) == 1)
		{
			$this->setError(Text::_('COM_MYMUSE_COUPON_PRODUCT_REQUIRED'));

			return false;
		}
		//make sure code is unique
		if(!$this->id){
		    $query = "SELECT code from #__mymuse_coupon WHERE code='".$this->code."'";
		    $this->_db->setQuery($query);
		    if($this->_db->loadResult()){
		        $this->setError(JText::_('COM_MYMUSE_COUPON_CODE_MUST_BE_UNIQUE'));
		        return false;
		    }
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
		return 'com_mymuse.coupon';
	}
}
