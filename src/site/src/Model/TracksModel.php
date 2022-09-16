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
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Helper\QueryHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * This model supports retrieving lists of tracks.
 *
 * @package		Joomla.Site
 * @subpackage	com_mymuse
 * @since		1.6
 */
class TracksModel extends ListModel
{
	/**
	 * Model context string.
	 *
	 * @var        string
	 */
	protected $_context = 'com_mymuse.track';

    protected $_item = null;
    
	protected $_products = null;

	protected $_category = null;
	
	protected $_tracks = null;

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'catid', 'a.catid', 'category_title',
				'artistid','ar.artistid','artist_name','product_title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'p.created',
				'created_by', 'p.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'price','a.price',
				'file_downloads', 'a.file_downloads',
				'product_discount','a.product_discount',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
                'category_name', 'c.title',
				'sales', 's.sales',
				'created','p.created',
				'modified','p.modified',
				'product_made_date', 'p.product_made_date',
				'c.lft'
			);
		}

		parent::__construct($config);
		$this->getCategory();
		
	}

   
    


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState($ordering = 'category_name', $direction = 'ASC')
	{
		$app 	= Factory::getApplication();
		$jinput = $app->input;

		$params = MymuseHelper::getParams();

		// List state information
        $pk		= $jinput->get('id',0,'INT');
        $this->setState('category.id', $pk);
        $this->setState('list.alpha', $jinput->get('filter_alpha', '', 'STRING'));
        $this->setState('list.prods', $jinput->get('products', '', 'STRING'));
        $this->setState('list.searchword', $jinput->get('searchword','', 'STRING'));
        
		//$value = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$value = $jinput->get('limit', $params->get('display_num',$app->getCfg('list_limit', 0)));
		$this->setState('list.limit', $value);

		//$value = $app->getUserStateFromRequest($this->context.'.limitstart', 'limitstart', 0);
		$value = $jinput->get('limitstart', 0);
		$this->setState('list.start', $value);
		
		//listOrder
		$this->setState('list.ordering', '');
		$this->setState('list.secondaryOrder','');
		
		//direction
		$listOrder	=  $jinput->get('filter_order_Dir', 'ASC');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);
		
		//primary ordering
		if($params->get('orderby_pri') != 'none'){
			$orderCol = QueryHelper::orderbyPrimary($params->get('orderby_pri'));
			$this->setState('list.ordering', $orderCol);
		}

		//over ride primary if we have filter request
		$filter_order	= $jinput->get('filter_order');

		if ($filter_order){
			if(!in_array($filter_order, $this->filter_fields)) {
				$filter_order = 'category_name';
			}
			$this->setState('list.ordering', $filter_order);
		}
		
		//secondary ordering
		if(!$filter_order){
			$secondaryOrder = QueryHelper::orderbyProduct($params->get('orderby_sec','alpha'),$params->get('order_date',''));
			$this->setState('list.secondaryOrder',$secondaryOrder);
		}
		
		//if order by track, override ordering
		$orderby_track = $params->get('orderby_track','');

		if(!$filter_order && $orderby_track){
			$primaryOrder = QueryHelper::orderbySecondary($orderby_track,$params->get('order_date',''));
			$this->setState('list.secondaryOrder','');
			$this->setState('list.direction', '');
			$this->setState('list.ordering', $primaryOrder );
				
		}
		
		// just featured tracks?
		$featured = $params->get('featured','0');
		$this->setState('list.featured', $featured);

		$this->setState('params', $params);
		$user		= Factory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_content')) &&  (!$user->authorise('core.edit', 'com_content'))){
			// filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.published', 1);
		}

		$this->setState('filter.language', $app->getLanguageFilter());

		// process show_noauth parameter
		if (!$params->get('show_noauth')) {
			$this->setState('filter.access', true);
		}
		else {
			$this->setState('filter.access', false);
		}
        
        // set the depth of the category query based on parameter
		$showSubcategories = $params->get('show_subcategory_content', '0');
		if ($showSubcategories) {
			$this->setState('filter.max_category_levels', $params->get('show_subcategory_content', '1'));
			$this->setState('filter.subcategories', true);
		}

		$this->setState('layout', $jinput->get('layout'));
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		//$id .= ':'.$this->getState('filter.published');
		$id .= ':'.$this->getState('filter.access');
		$id .= ':'.$this->getState('filter.featured');
		$id .= ':'.$this->getState('filter.product_id');
		$id .= ':'.$this->getState('filter.product_id.include');
		$id .= ':'.$this->getState('filter.category_id');
		$id .= ':'.$this->getState('filter.category_id.include');
		$id .= ':'.$this->getState('filter.author_id');
		$id .= ':'.$this->getState('filter.author_id.include');
		$id .= ':'.$this->getState('filter.author_alias');
		$id .= ':'.$this->getState('filter.author_alias.include');
		$id .= ':'.$this->getState('filter.date_filtering');
		$id .= ':'.$this->getState('filter.date_field');
		$id .= ':'.$this->getState('filter.start_date_range');
		$id .= ':'.$this->getState('filter.end_date_range');
		$id .= ':'.$this->getState('filter.relative_date');

		return parent::getStoreId($id);
	}

	/**
	 * Get the master query for retrieving a list of products subject to the model state.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuerySave()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$params 	= MymuseHelper::getParams();

        $alpha 		= $this->getState('list.alpha','');
        $searchword = $this->getState('list.searchword','');
        $IN 		= $this->getState('list.prods','');
        $listDirn	= $this->getState('list.direction', 'ASC');
        $ordering 	= $this->getState('list.ordering', 'category_name');
        $featured 	= $this->getState('list.featured',0);
        
        if(preg_match("/ASC|DESC/",strtoupper($ordering)) || !$ordering){
        	$listDirn	= '';
        }
        
        $app 	= Factory::getApplication();
        $jinput = $app->input;
        $categoryId = $this->getState('category.id',$jinput->get('id',0,'INT'));
   
        $secondaryOrder = $this->getState('list.secondaryOrder', '');

       // TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS 
        // get child tracks with prices
        $query = "SELECT a.id as my_track_id,a.id, a.title, a.title_alias,a.alias, a.introtext, a.fulltext, a.parentid, a.product_physical, 
        a.product_downloadable, a.product_allfiles, a.product_sku, a.hits,
        a.price, a.featured, a.product_discount,
        a.file_name,a.file_preview,a.file_preview_2, a.file_preview_3,a.file_type, a.detail_image,
        p.id as parentid, p.title as product_title, p.alias as parent_alias, p.catid, p.artistid as artistid, 
        p.product_made_date as product_made_date,
        p.created as created, p.publish_up as publish_up, p.modified as modified,
        c.title as category_name, c.alias as category_alias, ar.title as artist_name, ar.alias as artist_alias, s.sales,
        ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count as rating_count,
        CASE WHEN p.created_by_alias > ' ' THEN p.created_by_alias ELSE ua.name END AS author
        
        FROM #__mymuse_product as a
        LEFT JOIN #__mymuse_product as p ON a.parentid = p.id
        LEFT JOIN #__categories as c ON a.catid = c.id
        LEFT JOIN #__categories as ar ON a.artistid =ar.id
        LEFT JOIN #__mymuse_product_rating AS v ON a.id = v.product_id
        LEFT JOIN (SELECT sum(quantity) as sales, x.product_name, x.product_id FROM
        		(SELECT sum(i.product_quantity) as quantity, i.product_id, p.parentid,
        		i.product_name, product_id as all_id
        		FROM #__mymuse_order_item as i
        		LEFT JOIN #__mymuse_product as p ON i.product_id=p.id
        		GROUP BY i.product_id, i.product_name )
        		as x GROUP BY x.all_id, x.product_name) as s ON s.product_id = a.id
        LEFT JOIN #__users AS ua ON ua.id = p.created_by
        ";
    
        if($params->get('category_match_level') == "track"){
        	
        	if($this->getState('filter.subcategories')){
        		$cats = array();
        		$cats[] = $categoryId;
        		$category = $this->getCategory();
        		$category->children = $category->getChildren();
        		foreach($category->children as $child){
        			$cats[] = $child->id;
        		}
        		$CATIN = implode(",",$cats);
        		$query .= "RIGHT JOIN #__mymuse_product_category_xref as xref ON xref.product_id = a.id
        		WHERE xref.catid IN ($CATIN) ";
        	}else{
        		$query .= "RIGHT JOIN #__mymuse_product_category_xref as xref ON xref.product_id = a.id
        		WHERE xref.catid = $categoryId ";
        	}
        		
		}else{			
        		
        	$query .= " WHERE a.parentid IN ".$IN ." ";
		}	
        		
        $query .= " AND a.product_downloadable = 1
        AND a.state=1
        		AND a.product_allfiles = 0
        ";
        if($alpha != ''){
            $query .= "AND ar.title LIKE '$alpha%' ";
        }
        if($searchword != ''){
        	$query .= "AND (
        	a.title LIKE ".$db->quote('%'.$searchword.'%')."
        	OR a.introtext LIKE ".$db->quote('%'.$searchword.'%')."
        	OR a.file_name LIKE ".$db->quote('%'.$searchword.'%')."
        	OR p.title LIKE ".$db->quote('%'.$searchword.'%')."
        	OR ar.title LIKE ".$db->quote('%'.$searchword.'%')."
        	)";
        }
        
        if($featured){
        	$query .= " AND a.featured=1
        	";
        }
        $query .=  " GROUP BY a.id, s.sales ";
        if($params->get('group_by',0)){
        	$query .= " ".$params->get('group_by',0)."
        	";
        }

        $orderby = "ORDER BY $ordering $listDirn
        ";
        if($secondaryOrder){
        	$orderby .= ", $secondaryOrder ";
        }
        $query .= $orderby;

		return $query;
	}

	/**
	 * Get the master query for retrieving a list of products subject to the model state.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$params 	= MymuseHelper::getParams();

        $alpha 		= $this->getState('list.alpha','');
        $searchword = $this->getState('list.searchword','');
        $IN 		= $this->getState('list.prods','');
        $listDirn	= $this->getState('list.direction', 'ASC');
        $ordering 	= $this->getState('list.ordering', 'category_name');
        $featured 	= $this->getState('list.featured',0);
        
        if(preg_match("/ASC|DESC/",strtoupper($ordering)) || !$ordering){
        	$listDirn	= '';
        }
        
        $app 	= Factory::getApplication();
        $jinput = $app->input;
        $categoryId = $this->getState('category.id',$jinput->get('id',0,'INT'));
   
        $secondaryOrder = $this->getState('list.secondaryOrder', '');

       // TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS 
        // get child tracks with prices
        $query = "SELECT a.id as my_track_id,a.id, a.title, a.title_alias,a.alias, a.introtext, a.fulltext, a.parentid, a.product_physical, 
        a.product_downloadable, a.product_allfiles, a.product_sku, a.hits,
        a.price, a.featured, a.product_discount,
        a.file_preview, a.detail_image,
        p.id as parentid, p.title as product_title, p.alias as parent_alias, p.catid, p.artistid as artistid, 
        p.product_release_date,
        p.created as created, p.publish_up as publish_up, p.modified as modified,
        c.title as category_name, c.alias as category_alias,
         ar.title as artist_name, ar.alias as artist_alias
        FROM #__mymuse_product as a, #__mymuse_product as p, #__categories as c, #__categories as ar
		WHERE p.id  = a.parentid
		AND c.id = p.catid
		AND ar.id = p.artistid
		AND a.product_downloadable = 1 
		AND a.state=1 
		AND a.product_allfiles = 0
		";

       
        if (is_numeric($categoryId)) {
			$type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';

			// Add subcategory check
			$includeSubcategories = $this->getState('filter.subcategories', false);
	
			$categoryEquals = 'a.catid '.$type.(int) $categoryId;
			$artistEquals = 'a.artistid '.$type.(int) $categoryId;
        	//ARBORETA
			//get other sub cats
            $in = array();
            
            // get prodids from xref for the top level cat
			$subQuery = "SELECT product_id FROM #__mymuse_product_category_xref WHERE catid = ".$categoryId;
			$db->setQuery($subQuery);
			$pids = $db->loadObjectList();
			foreach($pids as $pid){
				$in[] = $pid->product_id;
			}
            
			if ($includeSubcategories) {
				
				$subQuery = "SELECT sub.id FROM #__categories as sub
				INNER JOIN #__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt
				WHERE this.id = $categoryId";
					
				$db->setQuery($subQuery);
				$subCats = $db->loadObjectList();
               
                $cats_in[] = $categoryId;
				foreach($subCats as $subCat){
                    $cats_in[] = $subCat->id;
                }
                $cats_in = "(".implode(",",$cats_in).")";   
               
                    
                    
                $subQuery = "SELECT product_id FROM #__mymuse_product_category_xref WHERE catid IN ".$cats_in;
               echo "<!-- SubQuery: $subQuery -->";
                $db->setQuery($subQuery);
                $pids = $db->loadObjectList();
						
                foreach($pids as $pid){
                    $in[] = $pid->product_id;
                }
			}
            
			if(!count($in)){
				$in[] = 0;
			}
			$IN = "(".implode(",",$in).")";
		

			if ($includeSubcategories) {
				
				$levels = (int) $this->getState('filter.max_category_levels', '1');
				// Create a subquery for the subcategory list
				$subQuery = $db->getQuery(true);
				$subQuery->select('sub.id');
				$subQuery->from('#__categories as sub');
				$subQuery->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt');
				$subQuery->where('this.id = '.(int) $categoryId);
				if ($levels >= 0) {
					$subQuery->where('sub.level <= this.level + '.$levels);
				}

				// Add the subquery to the main query
				//$query->where('('.$categoryEquals.' OR a.catid IN ('.$subQuery->__toString().') OR a.id IN '.$IN.')');
				$query .= ' AND ('.$categoryEquals.' OR a.catid IN ('.$subQuery->__toString().') OR a.id IN '.$IN.'  ';
				$query .= ' OR '.$artistEquals.' OR a.artistid IN ('.$subQuery->__toString().') OR a.id IN '.$IN.')  ';
			}
			else {
				//$query->where('('.$categoryEquals.' OR a.id IN '.$IN.')');
				$query .= ' AND ('.$categoryEquals.' OR a.id IN '.$IN.') ';
			}
			
			
			
			
			
		}
		elseif (is_array($categoryId) && (count($categoryId) > 0)) {
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			if (!empty($categoryId)) {
				$type = $this->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
				//$query->where('a.catid '.$type.' ('.$categoryId.')');
				$query .= ' AND a.catid '.$type.' ('.$categoryId.') ';
			}
		}

        if($alpha != ''){
            $query .= " AND ( c.title LIKE '$alpha%' OR ar.title LIKE '$alpha%' ) ";
        }
        if($searchword != ''){
        	$query .= "AND (
        	a.title LIKE ".$db->quote('%'.$searchword.'%')."
        	OR a.file_name LIKE ".$db->quote('%'.$searchword.'%')."
        	OR p.title LIKE ".$db->quote('%'.$searchword.'%')."
        	OR c.title LIKE ".$db->quote('%'.$searchword.'%')."
        	)";
        }
        
        $query .=  " GROUP BY a.id ";
        if($params->get('group_by',0)){
        	$query .= " ".$params->get('group_by',0)."
        	";
        }

        $orderby = "ORDER BY $ordering $listDirn
        ";
        if($secondaryOrder){
        	if($ordering){
        		$orderby .= ", $secondaryOrder ";
        	}else{
        		$orderby .= " $secondaryOrder ";
        	}
        	
        }
        $query .= $orderby;
	echo "<br />Track Query ".$this->_db->replacePrefix((string) $query); exit;
        //print_pre($params); exit;
		return $query;
	}

    	/**
	 * Get the products in the category
	 *
	 * @return	mixed	An array of products or false if an error occurs.
	 * @since	1.5
	 */
	public function getProducts()
	{
		$params = $this->getState()->get('params');
		$limit = $this->getState('list.limit');
		// what should the limit be? 
        $limit = 10000;

		if ($this->_products === null && $category = $this->getCategory()) {

			
			$model = JModelList::getInstance('Products', 'MyMuseModel', array('ignore_request' => true));
			$model->setState('params', Factory::getApplication()->getParams());
			$model->setState('filter.category_id', $category->id);
			$model->setState('filter.published', '1');
			$model->setState('filter.access', $this->getState('filter.access'));
			$model->setState('filter.language', $this->getState('filter.language'));

			$model->setState('list.ordering', 'a.id');


			$model->setState('list.start', 0);
			$model->setState('list.limit', $limit);
			$model->setState('list.direction', 'ASC');
			$model->setState('list.filter', $this->getState('list.filter'));
			// filter.subcategories indicates whether to include products from subcategories in the list or blog
			$model->setState('filter.subcategories', $this->getState('filter.subcategories'));	
			$model->setState('filter.max_category_levels', $this->getState('filter.max_category_levels'));
			$model->setState('list.links', $this->getState('list.links'));

         
			if ($limit >= 0) {
				$this->_products = $model->getItems();

				if ($this->_products  === false) {
					$this->setError($model->getError());
				}
			}
			else {
				$this->_products =array();
			}
			/**
			
			$db = Factory::getDbo();
			$query = "SELECT id from #__mymuse_product WHERE parentid=0";
			$db->setQuery($query);
			$this->_products = $db->loadObjectList();
			*/

		}

		$IN = '(';
		foreach($this->_products as $p){
			$IN .= $p->id.',';
		}

		$IN = preg_replace("/,$/",'',$IN);
		$IN .= ')';
        $this->setState('list.prods',$IN);
        
		return $this->_products;
	}
    
    
    


	/**
	 * Method to get category data for the current category
	 *
	 * @param	int		An optional ID
	 *
	 * @return	object
	 * @since	1.5
	 */
	public function getCategory()
	{

		if (!is_object($this->_category)) {

            if( isset( $this->state->params ) ) {
				$params = $this->state->params;
				$options = array();
				$options['countItems'] = $params->get('show_cat_num_articles', 1) || !$params->get('show_empty_categories_cat', 0);
			}
			else {
				$options['countItems'] = 0;
			}
 

            $categories = Categories::getInstance('Mymuse', $options);

			$this->_category= $categories->get($this->getState('category.id', 'root'));
			$registry = new Registry;
			$registry->loadObject(json_decode($this->_category->params));
			$this->_category->params = $registry;
          
		}

		return $this->_category;
	}



	/**
	 * Method to get a list of tracks.
	 *
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 * @since	1.6
	 */
	public function getItems()
	{
		$db = $this->getDbo();
		$app 	= Factory::getApplication();

		if($this->getState('list.prods','') == "()"){
			return array();
		}
		
		$params = MymuseHelper::getParams();
		$tracks	= parent::getItems();
MymuseHelper::print_pre($tracks);
		$site_url = preg_replace("#administrator/#","",URI::base());
		if($params->get('storage', 'regular') != 'regular' ){
			$site_url = $GLOBALS['mymuseStorage']->getSiteUrl();
		}

		$globalParams = ComponentHelper::getParams('com_mymuse', true);
        $root = JPATH_ROOT.DIRECTORY_SEPARATOR;
        $top_cat = $this->getState('filter.category_id');
   
        $this->_category->flash_type = '';
        $this->_category->flash = '';
        
        // Compute ordered products.
        $user	= Factory::getUser();
        $myOrders = array();
        if (!$user->get('guest') && $user->get('username') != 'buyer' && $params->get('my_play_downloads')) {
        	$userId	= $user->get('id');
        	$my_download_enable_status = $params->get('my_download_enable_status','C');
        	$query = "SELECT i.product_id from #__mymuse_order_item as i, #__mymuse_order as o
        	WHERE o.id=i.order_id
        	AND o.order_status='$my_download_enable_status'
        	AND o.user_id = $userId";
        
        	$db->setQuery($query);
        	if($res = $db->loadAssocList()){
        		foreach($res as $r){
        			if(!in_array($r['product_id'], $myOrders)){
        				$myOrders[] = $r['product_id'];
        			}
        		}
        	}
        }
     
        $preview_tracks = array();
		//check date, prices add flash

		if($tracks && count($tracks)){
	
			foreach($tracks as $i => $track){
				// get display date
				switch ($params->get ( 'order_date' )) {
					case 'product_made_date' :
						$tracks [$i]->displayDate = $tracks [$i]->product_made_date;
						break;
					
					case 'modified' :
						$tracks [$i]->displayDate = $tracks [$i]->modified;
						break;
					
					case 'published' :
						$tracks [$i]->displayDate = ($tracks [$i]->publish_up == 0) ? $tracks [$i]->created : $tracks [$i]->publish_up;
						break;
					
					default :
					case 'created' :
						$tracks [$i]->displayDate = $tracks [$i]->created;
						break;
				}
				$tracks [$i]->flash = '';
				$tracks [$i]->flash_type = '';
				$product_model = JModelLegacy::getInstance ( 'Product', 'MyMuseModel', array (
						'ignore_request' => true 
				) );
				$tracks [$i]->price = $product_model->getPrice ( $track );
				if ($params->get ( 'my_add_taxes' )) {
					if(count($params->get('my_formats') > 1) && $params->get('my_price_by_product')){
						foreach($params->get('my_formats') as $format){
							$tracks [$i]->price [$format]["product_price"] = MyMuseCheckout::addTax ( $tracks [$i]->price [$format]["product_price"] );
						}
					}else{
						$tracks [$i]->price ["product_price"] = MyMuseCheckout::addTax ( $tracks [$i]->price ["product_price"] );
					}
				}
				$track->params = clone $this->getState ( 'params' );
				
				
				// get download path for first variation for free download
				// not available for multiple formats
				$tracks [$i]->download_path = MymuseHelper::getDownloadPath($tracks [$i]->parentid, 1);
				
				$tracks [$i]->file_name = json_decode($tracks [$i]->file_name);
				if(isset($tracks [$i]->file_name[0]->file_length)){
					$tracks [$i]->file_length = $tracks [$i]->file_name[0]->file_length;
				}
				if(isset($tracks [$i]->file_name[0]->file_time)){
					$tracks [$i]->file_time = $tracks [$i]->file_name[0]->file_time;
				}
				if(isset($tracks [$i]->file_name[0]->file_downloads)){
					$tracks [$i]->file_downloads = $tracks [$i]->file_name[0]->file_downloads;
				}
				if(!isset($tracks [$i]->sales) || $tracks [$i]->sales == ''){
					$tracks [$i]->sales = 0;
				}
				if(1 == $params->get('my_price_by_product')){
					$tracks [$i]->first_price = $tracks [$i]->price [$tracks [$i]->file_name[0]->file_ext];
				}else{
					$tracks [$i]->first_price = $tracks [$i]->price;
				}
				/**
				$jason = json_decode($tracks [$i]->file_name);
				if(is_array($jason)){
					$tracks [$i]->file_name = $jason[0]->file_name;
					$tracks [$i]->file_alias = isset($jason[0]->file_alias)? $jason[0]->file_alias : '';
					$tracks [$i]->file_ext = isset($jason[0]->file_ext)? $jason[0]->file_ext : '';
					$tracks [$i]->file_length = isset($jason[0]->file_length)? $jason[0]->file_length : '';
					$tracks [$i]->file_downloads = isset($jason[0]->file_downloads)? $jason[0]->file_downloads : '';
					$tracks [$i]->file_time = isset($jason[0]->file_time)? $jason[0]->file_time : '';
					if(1 == $params->get('my_price_by_product')){
						$tracks [$i]->first_price = $tracks [$i]->price [$tracks [$i]->file_ext];
					}else{
						$tracks [$i]->first_price = $tracks [$i]->price;
					}
				}else{
					$tracks [$i]->first_price = $tracks [$i]->price;
				}
				*/
				

				$tracks [$i]->download_name = $tracks [$i]->file_name[0]->file_name;

				
				
				if(1 == $params->get('my_download_dir_format')){ //downloads by format
					//$tracks [$i]->download_path .= DS.$tracks [$i]->first_file_ext;
				}
				//print_pre($tracks [$i]); exit;
				$tracks [$i]->download_real_path = $tracks [$i]->download_path . $tracks [$i]->download_name;	
				
				
				
				if ((! $tracks [$i]->first_price || $tracks [$i]->first_price == "FREE") && $params->get ( 'my_play_downloads' )) {
					$tracks [$i]->file_preview = 1;
				}
				
				
				// Audio/Video or some horrid mix of both
				if ($this->_category->flash_type != "mix") {
					if ($this->_category->flash_type == "audio" && $tracks [$i]->file_type == "video") {
						// oh christ it's a mix
						$this->_category->flash_type = "mix";
						$tracks [$i]->flash_type = "mix";
					} elseif ($this->_category->flash_type == "video" && $tracks [$i]->file_type == "audio") {
						// oh christ it's a mix
						$this->_category->flash_type = "mix";
						$tracks [$i]->flash_type = "mix";
					} else {
						$this->_category->flash_type = $tracks [$i]->file_type;
						$tracks [$i]->flash_type = $tracks [$i]->file_type;
					}
				} else {
					$tracks [$i]->flash_type = "mix";
				}
				
				if ($tracks [$i]->file_preview) {
					$preview_tracks [] = $tracks [$i];
				} else {
					$tracks [$i]->flash = '';
				}
				
			}
		}
		

        if(count($preview_tracks) && 
        		($params->get('product_player_type') == "each" || 
            	$params->get('product_player_type') == "single" ||
        		$params->get('product_player_type') == "module"
        		)
        		
        	){
            reset($preview_tracks);
            $count = count($tracks);
   
            foreach($preview_tracks as $i => $track){
                if($track->product_allfiles == 1){
                    continue;
                }
                
                $flash = '';
                $track->purchased = 0;
                //echo "artist alias $album_alias $artist_alias";
                if($track->file_preview){
                	$prev_url 		= MymuseHelper::getSiteUrl($track->parentid, 1);
                	$prev_path 		= MymuseHelper::getSitePath($track->parentid, 1);
                    $track->path = $prev_url.$track->file_preview;
                    $track->real_path = $prev_path .$track->file_preview;
                    if($track->file_preview_2){
                        $track->path_2 = $prev_url.$track->file_preview_2;
                        $track->real_path_2 = $track->real_path.$track->file_preview_2;
                    }
                    if($track->file_preview_3){
                        $track->path_3 = $prev_url.$track->file_preview_3;
                        $track->real_path_3 = $track->real_path.$track->file_preview_3;
                    }
                    
                    
                    //should we use the real download file?
                    if($params->get('storage', 'regular') == 'regular'  && $params->get('my_play_downloads', 0) && in_array($track->id, $myOrders)){
                        $track->path = $track->download_path;
                        $track->real_path = $track->download_real_path;
                        $track->purchased = 1;
                    }
                    if($params->get('storage', 'regular') == 'regular'  && $params->get('my_play_downloads', 0) && 
                            (!$track->price["product_price"] || $track->price["product_price"] == "FREE")){
                        $track->path = $track->download_path;
                        $track->real_path = $track->download_real_path;
                        $track->purchased = 1;
                    }
                    
                    //audio or video?
                    $ext = MymuseHelper::getExt($track->file_preview);

                    if($track->file_type == "video"){
                        //movie
                        $flash = '<!-- Begin Player -->';
                        $results = $app->triggerEvent('onPrepareMyMuseVidPlayer',array(&$track,$params->get('product_player_type'),0,0,$i, $count) );
                        if(is_array($results) && isset($results[0]) && $results[0] != ''){
                            $flash .= $results[0];
                        }
                        $flash .= '<!-- End Player -->';
                    }elseif($track->file_type == "audio"){
                        //audio
                        $flash = '<!-- Begin Player -->';
                        $results = $app->triggerEvent('onPrepareMyMuseMp3Player',array(&$track,$params->get('product_player_type'),0,0,$i, $count));
                        if(is_array($results) && isset($results[0]) && $results[0] != ''){
                            $flash .= $results[0];
                        }
                        $flash .= '<!-- End Player -->';
                    }

                }else{
                    $flash = '';
                }
                $track->flash = $flash;


            }//end for each track
        }
        
        if(count($preview_tracks) 
        		&& ( $params->get('product_player_type') == "single"
        		|| $params->get('product_player_type') == "module" )
        		){
        	
        
        	// make a controller for the play/pause buttons
        	$results = $app->triggerEvent('onPrepareMyMuseMp3PlayerControl',array(&$preview_tracks) );
            
        	//get the player itself
            reset($preview_tracks);
            $flash = '';
            $audio = 0;
            $video = 0;
            foreach($preview_tracks as $track){
            	
                if($track->file_preview){
                    if($track->file_type == "video" && !$video){
                        //movie
                        $flash .= '<!-- Begin VIDEO Player -->';
                        $results = $app->triggerEvent('onPrepareMyMuseVidPlayer',array(&$track,'singleplayer') );
                        if(is_array($results) && $results[0] != ''){
                            $flash .= $results[0];
                        }
                        $flash .= '<!-- End Player -->';
                        $video = 1;
                            
                    }elseif($track->file_type == "audio" && !$audio){
                        //audio
                        $flash .= '<!-- Begin AUDIO Player -->';
                        $results = $app->triggerEvent('onPrepareMyMuseMp3Player',array(&$track,'singleplayer') );

                        if(is_array($results) && isset($results[0]) && $results[0] != ''){
                            $flash .= $results[0];
                        }
                        $flash .= '<!-- End Player -->';
                        $audio = 1;
                    }
                    
                    $this->_category->flash = $flash;
                    $this->_category->flash_id = $track->id;
                    if($this->_category->flash_type != "mix"){
                        break;
                    }elseif($audio && $video){
                        break;
                    }
                }
            }
        }
 
        if(count($preview_tracks) && $params->get('product_player_type') == "playlist"){
            //get the main flash for the product
    
            reset($preview_tracks);
            $this->_category->previews = array();
            $audio = 0;
     
            $i = 0;
            $type = "";
            foreach($preview_tracks as $track){
                if($track->file_preview){
                    $track->path .= $prev_dir.'/'.$artist_alias.'/'.$album_alias.'/'.$track->file_preview;
                }

                if($track->file_preview_2){
                    $track->path_2 = $prev_dir.'/'.$artist_alias.'/'.$album_alias.'/'.$track->file_preview_2;
                }
                if($track->file_preview_3){
                    $track->path_3 = $prev_dir.'/'.$artist_alias.'/'.$album_alias.'/'.$track->file_preview_3;
                }
                $this->_category->previews[] = $track;
                if(preg_match("/video/",$track->file_type)){
                    $type = "video";
                }
                if(preg_match("/audio/",$track->file_type)){
                    $type = "audio";
                }
                
            }
 
            if($type == "video"){
                // movie
                $flash = '<!-- Begin Player -->';
                $results = $app->triggerEvent('onPrepareMyMuseVidPlayer',array(&$this->_category,'playlist') );
                if(isset($results[0]) && $results[0] != ''){
                    $flash .= $results[0];
                }
                $flash .= '<!-- End Player -->';
                    
            }elseif($type == "audio"){
                
                $flash = '<!-- Begin Player -->';
                $results = $app->triggerEvent('onPrepareMyMuseMp3Player',array(&$this->_category,'playlist') );

                if(isset($results[0]) && $results[0] != ''){
                    $flash .= $results[0];
                }
                $flash .= '<!-- End Player -->';
            }
            $this->_category->flash = $flash;
            $this->_category->flash_id = $top_cat;

        }
        
        // free downloads
        if($params->get('storage', 'regular') == 'regular'  && $params->get('my_free_downloads')
        	 && is_array($tracks) && count($tracks)
    	){
            reset($tracks);
            foreach($tracks as $track){
                if(
                        (!$track->price["product_price"] || $track->price["product_price"] == "FREE")
                        || ($params->get('my_play_downloads') && in_array($track->id, $myOrders))
                        
                    ){
                    $track->free_download = 1;
                    $track->free_download_link = $track->download_path;
                    $track->free_download_link = "index.php?option=com_mymuse&view=store&task=downloadit&id=".$track->id;
                }else{
                    $track->free_download = 0;
                }
            }
        }elseif(isset($track)){
            $track->free_download = 0;
        }

        $res[0] = $tracks;
        $res[1] = $this->_category;
       $res[2] = $this->getPagination();
        //$res[2] = array();
		return $res;
	}
    /*
	public function getPagination()
	{
		$app 	= Factory::getApplication();
		$jinput = $app->input;
		// Get a storage key.
		$store = $this->getStoreId('getPagination');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Create the pagination object.
		jimport('joomla.html.pagination');
		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new JPagination($this->getTotal(), $this->getStart(), $limit);

        $page->setAdditionalUrlParam('option', 'com_mymuse');
        $page->setAdditionalUrlParam('view', 'tracks');
        $page->setAdditionalUrlParam('layout', 'alphatunes');
        $page->setAdditionalUrlParam('id', $this->getState('category.id'));
	    if($this->getState('list.alpha','')){
            $page->setAdditionalUrlParam('filter_alpha', $this->getState('list.alpha'));
        }
        
        if($this->getState('list.ordering','')){
        	$page->setAdditionalUrlParam('filter_order', $this->getState('list.ordering'));
        }
        if($this->getState('list.direction','')){
        	$page->setAdditionalUrlParam('filter_order_Dir', $this->getState('list.direction'));
        }

        if($this->getState('list.searchword','')){
        	$page->setAdditionalUrlParam('searchword', $this->getState('list.searchword'));
        }
        $Itemid           = $jinput->get('Itemid');
        if($Itemid){
        	$page->setAdditionalUrlParam('Itemid',$Itemid);
        }

        if($this->getState('list.limit')){
            $page->setAdditionalUrlParam('limit', $this->getState('list.limit'));
        }
		// Add the object to the internal cache.
		$this->cache[$store] = $page;

		return $this->cache[$store];
	}
 */   
	public function getStart()
	{
		return $this->getState('list.start');
	}
}
