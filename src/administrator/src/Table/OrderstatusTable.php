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
 * Orderitem Table class.
 *
 * @since  1.0
 */
class OrderstatusTable extends Table implements VersionableTableInterface
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;


	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   1.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_mymuse.orderstatus';

		parent::__construct('#__mymuse_order_status', 'id', $db);

	}

	/**
	 * Stores a orderstatus.
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

		return parent::store($updateNulls);
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
		return 'com_mymuse.orderstatus';
	}
}
