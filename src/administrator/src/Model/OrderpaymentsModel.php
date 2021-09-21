<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

/**
 * Methods supporting a list of orderitem records.
 *
 * @since  5.0.0
 */
class OrderitemsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \JControllerLegacy
	 *
	 * @since   5.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'order_id', 'a.order_id',
                'product_id', 'a.product_id',
                'product_sku', 'a.product_sku',
                'product_name', 'a.product_name',

            );
        }

		parent::__construct($config);
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  Form|null  The \JForm object or null if the form can't be found
	 *
	 * @since   3.2
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);

		return $form;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState($ordering = 'a.id', $direction = 'desc')
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context.'.filter.orderid', 'filter_orderid', '', 'string');
		$this->setState('filter.orderid', $orderid);

		// Load the parameters.
		$params = ComponentHelper::getParams('com_mymuse');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}



	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.orderid');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__mymuse_order_payments` AS a');


		// Join over the orders.
		$query->select('o.*');
		$query->join('LEFT', '#__mymuse_order AS o ON o.id=a.order_id');

		// Filter by orderid
		$orderid = (string) $this->getState('filter.orderid');

		if (is_numeric($orderid))
		{
			$query->where($db->quoteName('o.id') . ' = :orderid')
				->bind(':orderid', $orderid, ParameterType::INTEGER);
		}
		

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(
						a.plugin LIKE '.$search.' 
						OR a.transaction_id LIKE '.$search.'
						)');
			}
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
		    $query->order($db->escape($orderCol.' '.$orderDirn));
		}
		//echo($query->__toString()); exit;
		return $query;
	}



		/**
		 * Method to delete of one or more records.
		 *
		 * @param   array    &$pks   A list of the primary keys to change.
		 *
		 * @return  boolean  True on success.
		 *
		 * @since   4.0.0
		 */
		public function delete(&$pks) {

			$db = $this->getDbo();
			$query = $db->getQuery(true);

			$query->delete('`#__mymuse_order_payment`');
			$query->where('id IN (' . implode(',', $pks). ')');
			$db->setQuery($query);
			$db->execute();
		}
}