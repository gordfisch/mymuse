<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mymuse
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Mymuse\Administrator\Extension\MymuseComponent;
use Joomla\Component\Mymuse\Site\Helper\AssociationHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;



/**
 * This models supports retrieving lists of products.
 *
 * @since  1.6
 */
class ProductsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'state', 'a.state',
				'price', 'a.price',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'file_length', 'a.file_length',
				'product_title','p.title',
				'category_title','c.title',
				'artist_title', 'art.title',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'images', 'a.images',
				'urls', 'a.urls',
				'filter_tag',
				'sales','s.sales' 
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 */
	protected function populateState($ordering = 'a.id', $direction = 'desc')
	{
		$app = Factory::getApplication();

		$forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			//$this->context .= '.' . $layout;
		}

		// Adjust the context to support forced languages.
		if ($forcedLanguage)
		{
			$this->context .= '.' . $forcedLanguage;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$alpha= $this->getUserStateFromRequest($this->context . '.filter.alpha', 'filter_alpha');
		$this->setState('filter.alpha', $alpha);

		$featured = $this->getUserStateFromRequest($this->context . '.filter.featured', 'filter_featured', '');
		$this->setState('filter.featured', $featured);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$downloadable = $this->getUserStateFromRequest($this->context . '.filter.downloadable', 'filter_downloadable');
		$this->setState('filter.downloadable', $downloadable);

        $parentid = $this->getUserStateFromRequest($this->context . '.filter.parentid', 'filter_parentid');
        $this->setState('filter.parentid', $parentid);

        $trackparentid = $this->getUserStateFromRequest($this->context . '.filter.trackparentid', 'filter_trackparentid');
        $this->setState('filter.trackparentid', $trackparentid);

		$formSubmited = $app->input->post->get('form_submited');

		$access     = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$artistId 	= $this->getUserStateFromRequest($this->context . '.filter.artist_id', 'filter_artist_id');

		if ($formSubmited)
		{
			$access = $app->input->post->get('access');
			$this->setState('filter.access', $access);

			$categoryId = $app->input->post->get('category_id');
			$this->setState('filter.category_id', $categoryId);

			$artistId = $app->input->post->get('artist_id');
			$this->setState('filter.artist_id', $artistId);

		}

		// List state information.
		parent::populateState($ordering, $direction);

		// Force a language
		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.featured');
		$id .= ':' . serialize($this->getState('filter.product_id'));
		$id .= ':' . $this->getState('filter.product_id.include');
		$id .= ':' . serialize($this->getState('filter.category_id'));
		$id .= ':' . $this->getState('filter.category_id.include');
		$id .= ':' . serialize($this->getState('filter.author_id'));
		$id .= ':' . $this->getState('filter.author_id.include');
		$id .= ':' . serialize($this->getState('filter.author_alias'));
		$id .= ':' . $this->getState('filter.author_alias.include');
		$id .= ':' . $this->getState('filter.date_filtering');
		$id .= ':' . $this->getState('filter.date_field');
		$id .= ':' . $this->getState('filter.start_date_range');
		$id .= ':' . $this->getState('filter.end_date_range');
		$id .= ':' . $this->getState('filter.relative_date');
		$id .= ':' . serialize($this->getState('filter.tag'));

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
		$params      = $this->getState('params');

		$conditionArchived    = MymuseComponent::CONDITION_ARCHIVED;
        $conditionUnpublished = MymuseComponent::CONDITION_UNPUBLISHED;

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__mymuse_product` AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '`#__users` AS uc ON uc.id=a.checked_out');

		
		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published)) {
			$query->where('a.state = '.(int) $published);
		} else if ($published === '') {
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by featured state
		$featured = $this->getState('filter.featured');

		switch ($featured) {
		    case 'hide':
		        $query->where($db->quoteName('a.featured') . ' = 0');
		        break;

		    case 'only':
		        $query->where($db->quoteName('a.featured') . ' = 1');
		        break;

		    case 'show':
		    default:
		        // Normally we do not discriminate between featured/unfeatured items.
		        break;
		}

		// Filter by parentid. Default is parentid = 0
		if ( $parentid = $this->getState('filter.parentid', 'default') ) {
			if($parentid == 'default'){ $parentid = 0; }
			$query->where("a.parentid = '" . $parentid."'");
		}

		// Filter by trackparentid. Default is trackparentid = 0
		if ( $trackparentid = $this->getState('filter.trackparentid', 'default') ) {
			if($trackparentid == 'default'){ $trackparentid = 0; }
			$query->where("a.track_parentid = '" . $trackparentid."'");
		}

		//readmore

		$query->select($query->length($db->quoteName('a.fulltext')) . ' AS ' . $db->quoteName('readmore'),
					$db->quoteName('a.ordering'));

		// Join over the categories.
		$query->select('c.title AS category_title');
		$query->select('c.alias AS category_alias');
		$query->select($db->quoteName('c.language', 'category_language'));
		$query->join('LEFT', '`#__categories` AS c ON c.id = a.catid');
		
		// Join over the artist categories.
		$query->select('art.title AS artist_title');
		$query->select('art.alias AS artist_alias');
		$query->join('LEFT', '`#__categories` AS art ON art.id = a.artistid');

		$query->select( [
			$db->quoteName('parent.title', 'parent_title'),
					$db->quoteName('parent.id', 'parent_id'),
					$db->quoteName('parent.path', 'parent_route'),
					$db->quoteName('parent.alias', 'parent_alias'),
					$db->quoteName('parent.language', 'parent_language'),
				]
			)
		->join('LEFT', $db->quoteName('#__categories', 'parent'), $db->quoteName('parent.id') . ' = ' . $db->quoteName('c.parent_id'));

		// Join over the associations.
		if (Associations::isEnabled())
		{
			$subQuery = $db->getQuery(true)
				->select('COUNT(' . $db->quoteName('asso1.id') . ') > 1')
				->from($db->quoteName('#__associations', 'asso1'))
				->join('INNER', $db->quoteName('#__associations', 'asso2'), $db->quoteName('asso1.key') . ' = ' . $db->quoteName('asso2.key'))
				->where(
					[
						$db->quoteName('asso1.id') . ' = ' . $db->quoteName('a.id'),
						$db->quoteName('asso1.context') . ' = ' . $db->quote('com_content.item'),
					]
				);

			$query->select('(' . $subQuery . ') AS ' . $db->quoteName('association'));
		}

		

		$query->select($db->quoteName('ag.title', 'access_level'));
		$query->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'));

		//downloadable??
		if ($downloadable = $this->getState('filter.downloadable')) {
			$query->where('a.product_downloadable = 1');
		}
            

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where("art.title LIKE $search");
			}
		}
		// Filter by alpha start title
		$alpha = $this->getState('filter.alpha');

		if (!empty($alpha)) {
			$alpha = $db->Quote($db->escape($alpha, true).'%');
               $query->where("art.title LIKE $alpha");
		}

		// Process the filter for list views with user-entered filters
		if (is_object($params) && ($params->get('filter_field') !== 'hide') && ($filter = $this->getState('list.filter'))) {
		    // Clean filter variable
		    $filter      = StringHelper::strtolower($filter);
		    $monthFilter = $filter;
		    $hitsFilter  = (int) $filter;
		    $textFilter  = '%' . $filter . '%';

		    switch ($params->get('filter_field')) {
		        case 'author':
		            $query->where(
		                'LOWER(CASE WHEN ' . $db->quoteName('a.created_by_alias') . ' > ' . $db->quote(' ')
		                . ' THEN ' . $db->quoteName('a.created_by_alias') . ' ELSE ' . $db->quoteName('ua.name') . ' END) LIKE :search'
		            )
		                ->bind(':search', $textFilter);
		            break;

		        case 'hits':
		            $query->where($db->quoteName('a.hits') . ' >= :hits')
		                ->bind(':hits', $hitsFilter, ParameterType::INTEGER);
		            break;

		        case 'month':
		            if ($monthFilter != '') {
		                $monthStart = date("Y-m-d", strtotime($monthFilter)) . ' 00:00:00';
		                $monthEnd   = date("Y-m-t", strtotime($monthFilter)) . ' 23:59:59';

		                $query->where(
		                    [
		                        ':monthStart <= CASE WHEN a.publish_up IS NULL THEN a.created ELSE a.publish_up END',
		                        ':monthEnd >= CASE WHEN a.publish_up IS NULL THEN a.created ELSE a.publish_up END',
		                    ]
		                )
		                    ->bind(':monthStart', $monthStart)
		                    ->bind(':monthEnd', $monthEnd);
		            }
		            break;

		        case 'title':
		        default:
		            // Default to 'title' if parameter is not valid
		            $query->where('LOWER(' . $db->quoteName('a.title') . ') LIKE :search')
		                ->bind(':search', $textFilter);
		            break;
		    }
		}



		// Filter by a single or group of categories.
		$cat_query = '';
		$baselevel = 1;
		$categoryId = $this->getState('filter.category_id');
		$artistId = $this->getState('filter.artist_id');
		$cat_query_left_right = '';
		$art_query_left_right = '';

		if (is_numeric($artistId)) {

			$cat_tbl = Table::getInstance('Category', 'JTable');
			$cat_tbl->load($artistId);
			$rgt = $cat_tbl->rgt;
			$lft = $cat_tbl->lft;
			$baselevel = (int) $cat_tbl->level;
			$art_query_left_right =  ' OR ( art.lft >= '.(int) $lft.' AND art.rgt <= '.(int) $rgt.' )';
		}

		if (is_numeric($categoryId)) {

			$cat_tbl = Table::getInstance('Category', 'JTable');
			$cat_tbl->load($categoryId);
			$rgt = $cat_tbl->rgt;
			$lft = $cat_tbl->lft;
			$baselevel = (int) $cat_tbl->level;
			$cat_query_left_right =  ' OR ( c.lft >= '.(int) $lft.' AND c.rgt <= '.(int) $rgt.' )';

			// Add subcategory check
            $includeSubcategories = $this->getState('filter.subcategories', false);
            $type = $this->getState('filter.category_id.include', true) ? ' = ' : ' <> ';
            $subcats = array($categoryId);

            if ($includeSubcategories) {
                $categoryId = (int) $categoryId;
                $levels     = (int) $this->getState('filter.max_category_levels', 1);

                // Create a subquery for the subcategory list
                $subQuery = $db->getQuery(true)
                    ->select($db->quoteName('sub.id'))
                    ->from($db->quoteName('#__categories', 'sub'))
                    ->join(
                        'INNER',
                        $db->quoteName('#__categories', 'this'),
                        $db->quoteName('sub.lft') . ' > ' . $db->quoteName('this.lft')
                            . ' AND ' . $db->quoteName('sub.rgt') . ' < ' . $db->quoteName('this.rgt')
                    )
                    ->where($db->quoteName('this.id') . ' = '.$categoryId);

                if ($levels >= 0) {
                    $subQuery->where($db->quoteName('sub.level') . ' <= ' . $db->quoteName('this.level') . ' + $levels');
         
                }

               // echo $db->replacePrefix(($subQuery->__toString())); 
                $db->setQuery($subQuery);
                $res = $db->loadColumn();
                
                foreach($res as $r){
                	$subcats[] = $r;
                }


                //$query->bind(':categoryId', $categoryId, ParameterType::INTEGER);
            }

            //add other xref check

            $prodsIN = array();
            $prod_query = '';

            $catIN = $subcats;
            ArrayHelper::toInteger($catIN);
            $catIN = implode(',', $catIN);
            $q = "SELECT product_id from #__mymuse_product_category_xref
			WHERE catid IN (".$catIN.")";
			//echo $q."<br />";
			$db->setQuery($q);
			$prods = $db->loadObjectList();

			if(count($prods)){
				foreach($prods as $p){
					$prodsIN[] = $p->product_id;
				}

				ArrayHelper::toInteger($prodsIN);
				$prodsIN = implode(',', $prodsIN);

				$prod_query = "OR (a.id IN ($prodsIN))";
			}
			$categoryId = $subcats; 

		}
		
		if (is_array($categoryId)) {
			ArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			$query->where('( a.catid IN ('.$categoryId.') OR a.artistid IN ('.$categoryId.') '.$prod_query. ' '.$cat_query_left_right.' '.$art_query_left_right.')' );
		}

		
		// add sales figures
/*
		$query->select('s.sales');

		$query->join('LEFT', "(SELECT sum(quantity) as sales, x.product_id FROM 
		(SELECT sum(i.product_quantity) as quantity, i.product_id, p.parentid, CASE WHEN parentid > 0 THEN parentid ELSE product_id END as all_id
		FROM #__mymuse_order_item as i
		LEFT JOIN #__mymuse_product as p ON i.product_id=p.id
		GROUP BY i.product_id ) 
		as x GROUP BY x.all_id, x.product_id) as s ON s.product_id = a.id");
*/

		// Join over the users for the author and modified_by names.
		$query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author");
		$query->select("ua.email AS author_email");

		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

	

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');


        if ($orderCol && $orderDirn) {
		    $query->order($db->escape($orderCol.' '.$orderDirn));
		}
		elseif ($orderCol) {
		    $query->order($db->escape($orderCol));
		}

 		//echo $db->replacePrefix(($query->__toString())); //exit;
		return $query;
	}


	/**
	 * Method to get a list of products.
	 *
	 * Overridden to inject convert the attribs field into a Registry object.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		$items  = parent::getItems();
		if(!is_countable($items)){
			return array();
		}
		$user   = Factory::getUser();
		$userId = $user->get('id');
		$guest  = $user->get('guest');
		$groups = $user->getAuthorisedViewLevels();
		$input  = Factory::getApplication()->input;

		// Get the global params
		$globalParams = ComponentHelper::getParams('com_mymuse', true);

		// Convert the parameter fields into objects.

		foreach ($items as &$item)
		{
			$productParams = new Registry($item->attribs);

			// Unpack readmore and layout params
			$item->alternative_readmore = $productParams->get('alternative_readmore');
			$item->layout               = $productParams->get('layout');

			$item->params = clone $this->getState('params');

			/**
			 * For blogs, product params override menu item params only if menu param = 'use_product'
			 * Otherwise, menu item params control the layout
			 * If menu item is 'use_product' and there is no product param, use global
			 */
			if (($input->getString('layout') === 'blog') || ($input->getString('view') === 'featured')
				|| ($this->getState('params')->get('layout_type') === 'blog'))
			{
				// Create an array of just the params set to 'use_product'
				$menuParamsArray = $this->getState('params')->toArray();
				$productArray    = array();

				foreach ($menuParamsArray as $key => $value)
				{
					if ($value === 'use_product')
					{
						// If the product has a value, use it
						if ($productParams->get($key) != '')
						{
							// Get the value from the product
							$productArray[$key] = $productParams->get($key);
						}
						else
						{
							// Otherwise, use the global value
							$productArray[$key] = $globalParams->get($key);
						}
					}
				}

				// Merge the selected product params
				if (count($productArray) > 0)
				{
					$productParams = new Registry($productArray);
					$item->params->merge($productParams);
				}
			}
			else
			{
				// For non-blog layouts, merge all of the product params
				$item->params->merge($productParams);
			}

			// Get display date
			switch ($item->params->get('list_show_date'))
			{
				case 'modified':
					$item->displayDate = $item->modified;
					break;

				case 'published':
					$item->displayDate = ($item->publish_up == 0) ? $item->created : $item->publish_up;
					break;

				default:
				case 'created':
					$item->displayDate = $item->created;
					break;
			}

			/**
			 * Compute the asset access permissions.
			 * Technically guest could edit an product, but lets not check that to improve performance a little.
			 */
			if (!$guest)
			{
				$asset = 'com_mymuse.product.' . $item->id;

				// Check general edit permission first.
				if ($user->authorise('core.edit', $asset))
				{
					$item->params->set('access-edit', true);
				}

				// Now check if edit.own is available.
				elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
				{
					// Check for a valid user and that they are the owner.
					if ($userId == $item->created_by)
					{
						$item->params->set('access-edit', true);
					}
				}
			}

			$access = $this->getState('filter.access');

			if ($access)
			{
				// If the access filter has been set, we already have only the products this user can view.
				$item->params->set('access-view', true);
			}
			else
			{
				// If no access filter is set, the layout takes some responsibility for display of limited information.
				if ($item->catid == 0 || !isset($item->category_access ) || $item->category_access === null)
				{
					$item->params->set('access-view', in_array($item->access, $groups));
				}
				else
				{
					$item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
				}
			}

			// Some contexts may not use tags data at all, so we allow callers to disable loading tag data
			if ($this->getState('load_tags', $item->params->get('show_tags', '1')))
			{
				$item->tags = new TagsHelper;
				$item->tags->getItemTags('com_mymuse.product', $item->id);
			}

			if (Associations::isEnabled() && $item->params->get('show_associations'))
			{
				$item->associations = AssociationHelper::displayAssociations($item->id);
			}

			//echo "list -image ".$item->list_image."<br />";
		}

		return $items;

	}

	/**
	 * Method to get the starting number of items for the data set.
	 *
	 * @return  integer  The starting number of items available in the data set.
	 *
	 * @since   3.0.1
	 */
	public function getStart()
	{
		return $this->getState('list.start');
	}

	/**
	 * Count Items by Month
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 * @since   3.9.0
	 */
	public function countItemsByMonth()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Get the list query.
		$listQuery = $this->getListQuery();
		$bounded   = $listQuery->getBounded();

		// Bind list query variables to our new query.
		$keys      = array_keys($bounded);
		$values    = array_column($bounded, 'value');
		$dataTypes = array_column($bounded, 'dataType');

		$query->bind($keys, $values, $dataTypes);

		$query
			->select(
				'DATE(' .
				$query->concatenate(
					array(
						$query->year($db->quoteName('publish_up')),
						$db->quote('-'),
						$query->month($db->quoteName('publish_up')),
						$db->quote('-01')
					)
				) . ') AS ' . $db->quoteName('d')
			)
			->select('COUNT(*) AS ' . $db->quoteName('c'))
			->from('(' . $this->getListQuery() . ') AS ' . $db->quoteName('b'))
			->group($db->quoteName('d'))
			->order($db->quoteName('d') . ' DESC');

		return $db->setQuery($query)->loadObjectList();
	}
}
