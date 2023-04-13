<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2021 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;

/**
 * Mymuse Component Query Helper
 *
 * @since  1.5
 */
class QueryHelper
{
	/**
	 * Translate an order code to a field for primary category ordering.
	 *
	 * @param   string  $orderby  The ordering code.
	 *
	 * @return  string  The SQL field(s) to order by.
	 *
	 * @since   1.5
	 */
	public static function orderbyPrimary($orderby)
	{
		switch ($orderby)
		{
			case 'alpha':
				$orderby = 'c.path, ';
				break;

			case 'ralpha':
				$orderby = 'c.path DESC, ';
				break;

			case 'order':
				$orderby = 'c.lft, ';
				break;

			default:
				$orderby = '';
				break;
		}

		return $orderby;
	}

	/**
	 * Translate an order code to a field for secondary category ordering.
	 *
	 * @param   string             $orderby    The ordering code.
	 * @param   string             $orderDate  The ordering code for the date.
	 * @param   DatabaseInterface  $db         The database
	 *
	 * @return  string  The SQL field(s) to order by.
	 *
	 * @since   1.5
	 */
	public static function orderbySecondary($orderby, $orderDate = 'created', DatabaseInterface $db = null)
	{
		$db = $db ?: Factory::getDbo();

		$queryDate = self::getQueryDate($orderDate, $db);

		switch ($orderby)
		{
			case 'date':
				$orderby = $queryDate;
				break;

			case 'rdate':
				$orderby = $queryDate . ' DESC ';
				break;

			case 'alpha':
				$orderby = 'a.title';
				break;

			case 'ralpha':
				$orderby = 'a.title DESC';
				break;

			case 'hits':
				$orderby = 'a.hits DESC';
				break;

			case 'rhits':
				$orderby = 'a.hits';
				break;

			case 'rorder':
				$orderby = 'a.ordering DESC';
				break;

			case 'author':
				$orderby = 'author';
				break;

			case 'rauthor':
				$orderby = 'author DESC';
				break;

			case 'front':
				$orderby = 'a.featured DESC, fp.ordering, ' . $queryDate . ' DESC ';
				break;

			case 'random':
				$orderby = $db->getQuery(true)->rand();
				break;

			case 'vote':
				$orderby = 'a.id DESC ';

				if (PluginHelper::isEnabled('content', 'vote'))
				{
					$orderby = 'rating_count DESC ';
				}
				break;

			case 'rvote':
				$orderby = 'a.id ASC ';

				if (PluginHelper::isEnabled('content', 'vote'))
				{
					$orderby = 'rating_count ASC ';
				}
				break;

			case 'rank':
				$orderby = 'a.id DESC ';

				if (PluginHelper::isEnabled('content', 'vote'))
				{
					$orderby = 'rating DESC ';
				}
				break;

			case 'rrank':
				$orderby = 'a.id ASC ';

				if (PluginHelper::isEnabled('content', 'vote'))
				{
					$orderby = 'rating ASC ';
				}
				break;

			default:
				$orderby = 'a.ordering';
				break;
		}

		return $orderby;
	}

	/**
	 * Translate an order code to a field for product ordering.
	 *
	 * @param	string	$orderby	The ordering code.
	 * @param	string	$orderDate	The ordering code for the date.
	 *
	 * @return	string	The SQL field(s) to order by.
	 * @since	1.5
	 */
	public static function orderbyProduct($orderby, $orderDate = 'created')
	{
		$queryDate = self::getProductDate($orderDate);

		switch ($orderby)
		{
			case 'date' :
				$orderby = $queryDate;
				break;
	
			case 'rdate' :
				$orderby = $queryDate . ' DESC ';
				break;
	
			case 'alpha' :
				$orderby = 'p.title';
				break;
	
			case 'ralpha' :
				$orderby = 'p.title DESC';
				break;
	
			case 'hits' :
				$orderby = 'p.hits DESC';
				break;
	
			case 'rhits' :
				$orderby = 'p.hits';
				break;
	
			case 'order' :
				$orderby = 'p.ordering';
				break;
	
			case 'author' :
				$orderby = 'p.author';
				break;
	
			case 'rauthor' :
				$orderby = 'p.author DESC';
				break;
	
			case 'sales' :
				$orderby = 's.sales DESC';
				break;
	
			case 'discount' :
				$orderby = 'a.product_discount DESC';
				break;

			case 'rorder' :
				$orderby = 'a.ordering DESC';
				break;
	
			default :
				$orderby = 'a.ordering';
				break;
		}
	
		return $orderby;
	}

	/**
	 * Translate an order code to a field for primary category ordering.
	 *
	 * @param   string             $orderDate  The ordering code.
	 * @param   DatabaseInterface  $db         The database
	 *
	 * @return  string  The SQL field(s) to order by.
	 *
	 * @since   1.6
	 */
	public static function getQueryDate($orderDate, DatabaseInterface $db = null)
	{
		$db = $db ?: Factory::getDbo();

		switch ($orderDate)
		{
			case 'modified' :
				$queryDate = ' CASE WHEN a.modified IS NULL THEN a.created ELSE a.modified END';
				break;

			// Use created if publish_up is not set
			case 'published' :
				$queryDate = ' CASE WHEN a.publish_up IS NULL THEN a.created ELSE a.publish_up END ';
				break;

			case 'unpublished' :
				$queryDate = ' CASE WHEN a.publish_down IS NULL THEN a.created ELSE a.publish_down END ';
				break;
			case 'product_release_date' :
				$queryDate = ' CASE WHEN a.product_release_date IS NULL THEN a.created ELSE a.product_release_date END ';
				break;
			case 'created' :
			default :
				$queryDate = ' a.created ';
				break;
		}

		return $queryDate;
	}

	/**
	 * Translate an order code to a field for primary product ordering.
	 *
	 * @param   string             $orderDate  The ordering code.
	 * @param   DatabaseInterface  $db         The database
	 *
	 * @return  string  The SQL field(s) to order by.
	 *
	 * @since   1.6
	 */
	public static function getProductDate($orderDate, DatabaseInterface $db = null)
	{
		$db = $db ?: Factory::getDbo();

		switch ($orderDate)
		{
			case 'modified' :
				$queryDate = ' CASE WHEN p.modified IS NULL THEN p.created ELSE p.modified END';
				break;

			// Use created if publish_up is not set
			case 'published' :
				$queryDate = ' CASE WHEN p.publish_up IS NULL THEN p.created ELSE p.publish_up END ';
				break;

			case 'unpublished' :
				$queryDate = ' CASE WHEN p.publish_down IS NULL THEN p.created ELSE p.publish_down END ';
				break;
			case 'product_release_date' :
				$queryDate = ' CASE WHEN p.product_release_date IS NULL THEN p.created ELSE p.product_release_date END ';
				break;
			case 'created' :
			default :
				$queryDate = ' p.created ';
				break;
		}

		return $queryDate;
	}
	/**
	 * Get join information for the voting query.
	 *
	 * @param   \Joomla\Registry\Registry  $params  An options object for the article.
	 *
	 * @return  array  A named array with "select" and "join" keys.
	 *
	 * @since   1.5
	 *
	 * @deprecated  5.0  Deprecated without replacement, not used in core
	 */
	public static function buildVotingQuery($params = null)
	{
		if (!$params)
		{
			$params = ComponentHelper::getParams('com_content');
		}

		$voting = $params->get('show_vote');

		if ($voting)
		{
			// Calculate voting count
			$select = ' , ROUND(v.rating_sum / v.rating_count) AS rating, v.rating_count';
			$join = ' LEFT JOIN #__content_rating AS v ON a.id = v.content_id';
		}
		else
		{
			$select = '';
			$join = '';
		}

		return array('select' => $select, 'join' => $join);
	}

	/**
	 * Method to order the intro articles array for ordering
	 * down the columns instead of across.
	 * The layout always lays the introtext articles out across columns.
	 * Array is reordered so that, when articles are displayed in index order
	 * across columns in the layout, the result is that the
	 * desired article ordering is achieved down the columns.
	 *
	 * @param	array	$articles	Array of intro text articles
	 * @param	integer	$numColumns	Number of columns in the layout
	 *
	 * @return	array	Reordered array to achieve desired ordering down columns
	 * @since	1.6
	 */
	public static function orderDownColumns(&$articles, $numColumns = 1)
	{
		$count = count($articles);

		// just return the same array if there is nothing to change
		if ($numColumns == 1 || !is_array($articles) || $count <= $numColumns) {
			$return = $articles;
		}
		// we need to re-order the intro articles array
		else {
			// we need to preserve the original array keys
			$keys = array_keys($articles);

			$maxRows = ceil($count / $numColumns);
			$numCells = $maxRows * $numColumns;
			$numEmpty = $numCells - $count;
			$index = array();

			// calculate number of empty cells in the array


			// fill in all cells of the array
			// put -1 in empty cells so we can skip later

			for ($row = 1, $i = 1; $row <= $maxRows; $row++)
			{
				for ($col = 1; $col <= $numColumns; $col++)
				{
					if ($numEmpty > ($numCells - $i)) {
						// put -1 in empty cells
						$index[$row][$col] = -1;
					}
					else {
						// put in zero as placeholder
						$index[$row][$col] = 0;
					}
					$i++;
				}
			}

			// layout the articles in column order, skipping empty cells
			$i = 0;
			for ($col = 1; ($col <= $numColumns) && ($i < $count); $col++)
			{
				for ($row = 1; ($row <= $maxRows) && ($i < $count); $row++)
				{
					if ($index[$row][$col] != - 1) {
						$index[$row][$col] = $keys[$i];
						$i++;
					}
				}
			}

			// now read the $index back row by row to get articles in right row/col
			// so that they will actually be ordered down the columns (when read by row in the layout)
			$return = array();
			$i = 0;
			for ($row = 1; ($row <= $maxRows) && ($i < $count); $row++)
			{
				for ($col = 1; ($col <= $numColumns) && ($i < $count); $col++)
				{
					$return[$keys[$i]] = $articles[$index[$row][$col]];
					$i++;
				}
			}
		}
		return $return;
	}
}
