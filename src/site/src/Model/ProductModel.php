<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mymuse
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\Table;
use Joomla\Component\Mymuse\Administrator\Extension\MymuseComponent;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\IpHelper;

/**
 * Mymuse Component Product Model
 *
 * @since  1.5
 */
class ProductModel extends ItemModel
{
	/**
	 * Model context string.
	 *
	 * @var        string
	 */
	protected $_context = 'com_mymuse.product';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 *
	 * @return void
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('product.id', $pk);

		$offset = $app->input->getUint('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$user = Factory::getUser();

		// If $pk is set then authorise on complete asset, else on component only
		$asset = empty($pk) ? 'com_mymuse' : 'com_mymuse.product.' . $pk;

		if ((!$user->authorise('core.edit.state', $asset)) && (!$user->authorise('core.edit', $asset)))
		{
			$this->setState('filter.published', MymuseComponent::CONDITION_PUBLISHED);
			$this->setState('filter.archived', MymuseComponent::CONDITION_ARCHIVED);
		}

		$this->setState('filter.language', Multilanguage::isEnabled());
	}

	/**
	 * Method to get product data.
	 *
	 * @param   integer  $pk  The id of the product.
	 *
	 * @return  object|boolean  Menu item data object on success, boolean false
	 */
	public function getItem($pk = null)
	{
		$user = Factory::getUser();

		$pk = (int) ($pk ?: $this->getState('product.id'));

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true);

				$query->select(
					$this->getState(
						'item.select',
						[
							$db->quoteName('a.id'),
							$db->quoteName('a.asset_id'),
							$db->quoteName('a.parentid'),
							$db->quoteName('a.track_parentid'),
							$db->quoteName('a.title'),
							$db->quoteName('a.product_sku'),
							$db->quoteName('a.alias'),
							$db->quoteName('a.title_alias'),
							$db->quoteName('a.introtext'),
							$db->quoteName('a.fulltext'),
							$db->quoteName('a.state'),
							$db->quoteName('a.price'),
							$db->quoteName('a.product_discount'),
							$db->quoteName('a.catid'),
							$db->quoteName('a.artistid'),
							$db->quoteName('a.created'),
							$db->quoteName('a.created_by'),
							$db->quoteName('a.created_by_alias'),
							$db->quoteName('a.modified'),
							$db->quoteName('a.modified_by'),
							$db->quoteName('a.checked_out'),
							$db->quoteName('a.checked_out_time'),
							$db->quoteName('a.publish_up'),
							$db->quoteName('a.publish_down'),
							$db->quoteName('a.list_image'),
							$db->quoteName('a.detail_image'),
							$db->quoteName('a.attribs'),
							$db->quoteName('a.physical'),
							$db->quoteName('a.digital'),
							$db->quoteName('a.version'),
							$db->quoteName('a.ordering'),
							$db->quoteName('a.metakey'),
							$db->quoteName('a.metadesc'),
							$db->quoteName('a.metadata'),
							$db->quoteName('a.access'),
							$db->quoteName('a.hits'),
							$db->quoteName('a.product_physical'),
							$db->quoteName('a.product_downloadable'),
							$db->quoteName('a.product_allfiles'),
							$db->quoteName('a.product_release_date'),
							$db->quoteName('a.file_preview'),
							$db->quoteName('a.special_status'),
							$db->quoteName('a.product_in_stock'),
							$db->quoteName('a.recording'),
							$db->quoteName('a.featured'),
							$db->quoteName('a.language'),
						]
					)
				)
					->select(
						[

							$db->quoteName('c.title', 'category_title'),
							$db->quoteName('c.alias', 'category_alias'),
							$db->quoteName('c.access', 'category_access'),
							$db->quoteName('c.language', 'category_language'),
							$db->quoteName('u.name', 'author'),
							$db->quoteName('parent.title', 'parent_title'),
							$db->quoteName('parent.id', 'parent_id'),
							$db->quoteName('parent.path', 'parent_route'),
							$db->quoteName('parent.alias', 'parent_alias'),
							$db->quoteName('parent.language', 'parent_language'),
							'ROUND(' . $db->quoteName('v.rating_sum') . ' / ' . $db->quoteName('v.rating_count') . ', 1) AS '
								. $db->quoteName('rating'),
							$db->quoteName('v.rating_count', 'rating_count'),
						]
					)
					->from($db->quoteName('#__mymuse_product', 'a'))
					->join(
						'INNER',
						$db->quoteName('#__categories', 'c'),
						$db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid')
					)
					->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'))
					->join('LEFT', $db->quoteName('#__categories', 'parent'), $db->quoteName('parent.id') . ' = ' . $db->quoteName('c.parent_id'))
					->join('LEFT', $db->quoteName('#__content_rating', 'v'), $db->quoteName('a.id') . ' = ' . $db->quoteName('v.content_id'))
					->select('art.title AS artist_title, art.alias AS artist_alias, art.access AS artist_access')
					->join('LEFT', '#__categories AS art on art.id = a.artistid')

					->where(
						[
							$db->quoteName('a.id') . ' = :pk',
							$db->quoteName('c.published') . ' > 0',
						]
					)
					->bind(':pk', $pk, ParameterType::INTEGER);

				// Filter by language
				if ($this->getState('filter.language'))
				{
					$query->whereIn($db->quoteName('a.language'), [Factory::getLanguage()->getTag(), '*'], ParameterType::STRING);
				}

				if (!$user->authorise('core.edit.state', 'com_mymuse.product.' . $pk)
					&& !$user->authorise('core.edit', 'com_mymuse.product.' . $pk)
				)
				{
					// Filter by start and end dates.
					$nowDate = Factory::getDate()->toSql();

					$query->extendWhere(
						'AND',
						[
							$db->quoteName('a.publish_up') . ' IS NULL',
							$db->quoteName('a.publish_up') . ' <= :publishUp',
						],
						'OR'
					)
						->extendWhere(
							'AND',
							[
								$db->quoteName('a.publish_down') . ' IS NULL',
								$db->quoteName('a.publish_down') . ' >= :publishDown',
							],
							'OR'
						)
						->bind([':publishUp', ':publishDown'], $nowDate);
				}

				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');

				if (is_numeric($published))
				{
					$query->whereIn($db->quoteName('a.state'), [(int) $published, (int) $archived]);
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if (empty($data))
				{
					throw new \Exception(Text::_('COM_MYMUSE_PRODUCT_NOT_FOUND'), 404);
				}

				// Check for published state if filter set.
				if ((is_numeric($published) || is_numeric($archived)) && ($data->state != $published && $data->state != $archived))
				{
					throw new \Exception(Text::_('COM_MYMUSE_PRODUCT_NOT_FOUND'), 404);
				}

				// Convert parameter fields to objects.
				$registry = new Registry;
				$registry->loadString($data->attribs);
				$data->attribs = $registry;

				$registry = new Registry;
				$registry->loadString($data->physical);
				$data->physical = $registry;

				$registry = new Registry;
				$registry->loadString($data->recording);
				$data->recording = $registry;

				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				$data->metadata = new Registry($data->metadata);

				// Technically guest could edit an product, but lets not check that to improve performance a little.
				if (!$user->get('guest'))
				{
					$userId = $user->get('id');
					$asset = 'com_mymuse.product.' . $data->id;

					// Check general edit permission first.
					if ($user->authorise('core.edit', $asset))
					{
						$data->params->set('access-edit', true);
					}

					// Now check if edit.own is available.
					elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
					{
						// Check for a valid user and that they are the owner.
						if ($userId == $data->created_by)
						{
							$data->params->set('access-edit', true);
						}
					}
				}

				// Compute view access permissions.
				if ($access = $this->getState('filter.access'))
				{
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else
				{
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user = Factory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					if ($data->catid == 0 || $data->category_access === null)
					{
						$data->params->set('access-view', in_array($data->access, $groups));
					}
					else
					{
						$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
					}
				}

				$this->_item[$pk] = $data;
			}
			catch (\Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go through the error handler to allow Redirect to work.
					throw $e;
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Increment the hit counter for the product.
	 *
	 * @param   integer  $pk  Optional primary key of the product to increment.
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 */
	public function hit($pk = 0)
	{
		$input = Factory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount)
		{
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('product.id');

			$table = Table::getInstance('ProductTable', 'Joomla\\Component\\Mymuse\\Administrator\\Table\\');
			$table->hit($pk);
		}

		return true;
	}

	/**
	 * Save user vote on product
	 *
	 * @param   integer  $pk    Joomla Product Id
	 * @param   integer  $rate  Voting rate
	 *
	 * @return  boolean          Return true on success
	 */
	public function storeVote($pk = 0, $rate = 0)
	{
		$pk   = (int) $pk;
		$rate = (int) $rate;

		if ($rate >= 1 && $rate <= 5 && $pk > 0)
		{
			$userIP = IpHelper::getIp();

			// Initialize variables.
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($db->quoteName('#__content_rating'))
				->where($db->quoteName('content_id') . ' = :pk')
				->bind(':pk', $pk, ParameterType::INTEGER);

			// Set the query and load the result.
			$db->setQuery($query);

			// Check for a database error.
			try
			{
				$rating = $db->loadObject();
			}
			catch (\RuntimeException $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			// There are no ratings yet, so lets insert our rating
			if (!$rating)
			{
				$query = $db->getQuery(true);

				// Create the base insert statement.
				$query->insert($db->quoteName('#__content_rating'))
					->columns(
						[
							$db->quoteName('content_id'),
							$db->quoteName('lastip'),
							$db->quoteName('rating_sum'),
							$db->quoteName('rating_count'),
						]
					)
					->values(':pk, :ip, :rate, 1')
					->bind(':pk', $pk, ParameterType::INTEGER)
					->bind(':ip', $userIP)
					->bind(':rate', $rate, ParameterType::INTEGER);

				// Set the query and execute the insert.
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (\RuntimeException $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

					return false;
				}
			}
			else
			{
				if ($userIP != $rating->lastip)
				{
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__content_rating'))
						->set(
							[
								$db->quoteName('rating_count') . ' = ' . $db->quoteName('rating_count') . ' + 1',
								$db->quoteName('rating_sum') . ' = ' . $db->quoteName('rating_sum') . ' + :rate',
								$db->quoteName('lastip') . ' = :ip',
							]
						)
						->where($db->quoteName('content_id') . ' = :pk')
						->bind(':rate', $rate, ParameterType::INTEGER)
						->bind(':ip', $userIP)
						->bind(':pk', $pk, ParameterType::INTEGER);

					// Set the query and execute the update.
					$db->setQuery($query);

					try
					{
						$db->execute();
					}
					catch (\RuntimeException $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

						return false;
					}
				}
				else
				{
					return false;
				}
			}

			$this->cleanCache();

			return true;
		}

		Factory::getApplication()->enqueueMessage(Text::sprintf('COM_CONTENT_INVALID_RATING', $rate), 'error');

		return false;
	}

	/**
	 * Cleans the cache of com_mymuse and content modules
	 *
	 * @param   string   $group     The cache group
	 * @param   integer  $clientId  @deprecated   5.0   No longer used.
	 *
	 * @return  void
	 *
	 * @since   3.9.9
	 */
	protected function cleanCache($group = null, $clientId = 0)
	{
		parent::cleanCache('com_mymuse');
		parent::cleanCache('mod_products_archive');
		parent::cleanCache('mod_products_categories');
		parent::cleanCache('mod_products_category');
		parent::cleanCache('mod_products_latest');
		parent::cleanCache('mod_products_news');
		parent::cleanCache('mod_products_popular');
	}
}
