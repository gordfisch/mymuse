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
 * Methods supporting a list of taxrate records.
 *
 * @since  5.0.0
 */
class ProductsModel extends ListModel
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
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'artistid', 'a.artistid',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'product_release_date', 'a.product_release_date',
				'modified', 'a.modified',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'author', 'a.author'
			);
			if (Associations::isEnabled())
			{
				$config['filter_fields'][] = 'association';
			}

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

		$params = ComponentHelper::getParams('com_mymuse');

		if (!$params->get('workflow_enabled'))
		{
			$form->removeField('stage', 'filter');
		}
		else
		{
			$ordering = $form->getField('fullordering', 'list');

			$ordering->addOption('JSTAGE_ASC', ['value' => 'ws.title ASC']);
			$ordering->addOption('JSTAGE_DESC', ['value' => 'ws.title DESC']);
		}
		$app = Factory::getApplication();
		$layout = $app->input->get('layout', '');

		if($layout == 'listtracks'){

			$form->removeField('fullordering', 'list');
			$form->setFieldAttribute('trackordering', 'name', 'fullordering');

		}else{
			
			$form->removeField('trackordering', 'list');
		}

		return $form;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
     *
     * @since 1.0
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
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');

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
		$query->from('`#__mymuse_product` AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		
		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.state = '.(int) $published);
		} else if ($published === '') {
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by featured.
		if ($featured = $this->getState('filter.featured')) {
			if($featured == "-1"){ $featured = 0;}
			$query->where("a.featured = '" . $featured."'");
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

		// Join over the categories.
		$query->select('c.title AS category_title');
		$query->join('LEFT', '#__categories AS c ON c.id = a.catid');
		
		// Join over the artist categories.
		$query->select('art.title AS artist_title');
		$query->join('LEFT', '#__categories AS art ON art.id = a.artistid');

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
                $query->where("title LIKE $search");
			}
		}

		// Filter by a single or group of categories.
		$baselevel = 1;
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId)) {
			$cat_tbl = Table::getInstance('Category', 'JTable');
			$cat_tbl->load($categoryId);
			$rgt = $cat_tbl->rgt;
			$lft = $cat_tbl->lft;
			$baselevel = (int) $cat_tbl->level;
			$query->where('c.lft >= '.(int) $lft);
			$query->where('c.rgt <= '.(int) $rgt);
		}
		elseif (is_array($categoryId)) {
			ArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			$query->where('a.catid IN ('.$categoryId.')');
		}

		// Filter by a single or group of artists.
		$baselevel = 1;
		$artistId = $this->getState('filter.artist_id');
		if (is_numeric($artistId)) {
			$art_tbl = Table::getInstance('Category', 'JTable');
			$art_tbl->load($categoryId);
			$rgt = $art_tbl->rgt;
			$lft = $art_tbl->lft;
			$baselevel = (int) $art_tbl->level;
			$query->where('art.lft >= '.(int) $lft);
			$query->where('art.rgt <= '.(int) $rgt);
		}
		elseif (is_array($artistId)) {
			ArrayHelper::toInteger($artistId);
			$artistId = implode(',', $artistId);
			$query->where('a.artistid IN ('.$artistId.')');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
		    $query->order($db->escape($orderCol.' '.$orderDirn));
		}

        //echo($query->__toString()); //exit;
		return $query;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   4.0.0
	 */
	public function publish(&$pks, $value = 1) {

		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->update('`#__mymuse_product`');
		$query->set('state = ' . $value);
		$query->where('id IN (' . implode(',', $pks). ')');
		$db->setQuery($query);
		$db->execute();
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

		$query->delete('`#__mymuse_product`');
		$query->where('id IN (' . implode(',', $pks). ')');
		$db->setQuery($query);
		$db->execute();

		//delete children
		$query = $db->getQuery(true);
		$query->delete('`#__mymuse_product`');
		$query->where('parentid IN (' . implode(',', $pks). ')');
		$db->setQuery($query);
		$db->execute();

	}
}