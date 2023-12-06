<?php 
/**
 * @version     $Id$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 */

namespace Joomla\Component\Mymuse\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

/**
 * Methods supporting reports.
 *
 * @since  5.0.0
 */

class ReportsModel extends ListModel
{

	/**
     * Store id
     *
     * int
     */
     var $_id = null;
     
 
    /**
     * Store lists array
     *
     * @array
     */
     var $_lists = array();
    
 	/**
  	 * Pagination object
	 *
	 * @$item->object
	 */
     var $_pagination = null;
  
 	/**
  	* stores total
  	*
  	* @var integer
  	*/
  	var $_total = null;
  	
    
	/**
	 * cats: the main and sublevel category objects
	 *
	 * array
	 */
	var $_cats = array();
    
    
    /**
	 * orders order objects
	 *
	 * string
	 */
	var $_orders = null;
    
    /**
	 * orderids: (1,2,3))
	 *
	 * string
	 */
	var $orderids = null;
    
    
    /**
	 * catid: the top level catid
	 *
	 * int
	 */
	var $_catid = null;
    

  
  	function __construct(){
  		
  		parent::__construct();
  	
  	}
  	
  	/**
  	 * getCats: fill $this->_catids with the main cat id and all subcat ids
  	 *
  	 * @param	bool	$recursive	True if you want to return children recursively.
  	 *
  	 * @return	mixed	An array of data items on success, false on failure.
  	 * @since	1.6
  	 */
  	public function getCats($catid, $recursive = false)
  	{

  		if (!count($this->_cats)) {

            $db  = Factory::getContainer()->get('DatabaseDriver');
            if($catid > 0){

                $query = "SELECT * FROM #__categories WHERE id='$catid'";
                $db->setQuery($query);
                $parent = $db->loadObject();
   
                $parent_id = $parent->id;
                $parent_left = $parent->lft;
                $parent_right = $parent->rgt;

            }else{
                $query = "SELECT * FROM #__categories WHERE extension='com_mymuse'
                ORDER BY id asc";
                $db->setQuery($query);
                $parent = $db->loadObject();
                $parent_id = $parent->id;
                $parent_left = $parent->lft;
                $parent_right = $parent->rgt;

            }
            

            $query = "SELECT id FROM #__categories WHERE lft > $parent_left
                 AND rgt < $parent_right";
            $db->setQuery($query);

            $this->_cats = $db->loadObjectList();
            return $this->_cats;

  		}
  	
  		return $this->_cats;
  	}
  	
    
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null)
    {
    	// Initialise variables.
    	$app = Factory::getApplication('administrator');
        $input = $app->input;
    	$db	 = Factory::getContainer()->get('DatabaseDriver');
    	
        // List state information.
    	parent::populateState('a.id', 'asc');
        $catid = $input->get('catid',0);
    	//$catid	= $app->getUserStateFromRequest( $this->context.'catid','catid','','int' );



    	$this->setState('filter.catid', $catid);
    	if($catid){
    		$this->getCats($catid, true);
    		//make sql bit for catids. get all subcats
    		$catids = '(';
    		$catids .= "$catid,";
    		 
    		if(is_array($this->_cats)){
    			foreach($this->_cats as $cat){
    				$catids .= $cat->id.",";
    			}
    		}
    		$catids = preg_replace("/,$/","",$catids);
    		$catids .= ")";
    	
    		$this->setState('list.catids', $catids);

            $prodids = '';
    		$this->setState('list.prodids', $prodids);
    	
    	}
    	
    	$order_status = $app->getUserStateFromRequest($this->context.'.filter.order_status', 'filter_order_status', '', 'string');
    	$this->setState('filter.order_status', $order_status);
    
    	$start_date = $app->getUserStateFromRequest($this->context.'.filter.start_date', 'filter_start_date', '', 'string');
    	$this->setState('filter.start_date', $start_date);
    	
    
    	$end_date = $app->getUserStateFromRequest($this->context.'.filter.end_date', 'filter_end_date', '', 'string');
    	$this->setState('filter.end_date', $end_date);

        $show_format = $app->getUserStateFromRequest($this->context.'.filter.show_format', 'filter_show_format', '', 'string');
        $this->setState('filter.show_format', $show_format);
        
        $this->setState('list.limit', 1000000000);



    	// Load the parameters.
    	$params = ComponentHelper::getParams('com_mymuse');
    	$this->setState('params', $params);
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
    	$id.= ':' . $this->getState('filter.catid');
    	$id.= ':' . $this->getState('filter.order_status');
    	$id.= ':' . $this->getState('filter.start_date');
    	$id.= ':' . $this->getState('filter.end_date');
    
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
    	$db		= Factory::getContainer()->get('DatabaseDriver');
    	$query	= $db->getQuery(true);
    
    	// Select the required fields from the table.
    	$query->select(
    			$this->getState(
    					'list.select',
    					'i.*'
    			)
    	);
    	$query->from('`#__mymuse_order_item` AS i');
    	$query->join('LEFT',  '#__mymuse_order as a ON i.order_id=a.id');
    
    	// Join over the order_status for the status name.
    	$query->select('os.name AS status_name');
    	$query->join('LEFT', '#__mymuse_order_status AS os ON os.code=a.order_status');

        $query->select('p.catid, p.artistid');
        $query->join('LEFT', '#__mymuse_product AS p ON p.id=i.product_id');
    
    
    	// Filter by order_status
    	$order_status = $this->getState('filter.order_status');
    	if (is_string($order_status) && $order_status != '0') {
    		$query->where('a.order_status = "'.$order_status.'"');
    	} else if ($order_status === '') {
    		//$query->where('(a.order_status IN (SELECT code from #__mymuse_order_status))');
    	}
    
    	//filter by date
    	$start_date = $this->getState('filter.start_date');
    	$end_date = $this->getState('filter.end_date');
    	$datenow = Factory::getDate();
    	$now = $datenow->format("%Y-%m-%d");
    
    	if($start_date== $now && $end_date == $now ){
    		$start_date = '';
    		$end_date = '';
    	}
    
    	$where = array();
    	if($start_date){
    		$query->where("a.created >= '$start_date 00:00:00'");
    	}
    	if($end_date){
    		$query->where("a.created <= '$end_date 23:59:59'");
    	}
    	
    	//filter by cat and product
    	$catid = $this->getState('filter.catid');
    	

        if($catid){
            $catids = $this->getState('list.catids');
            $query->where("(p.catid IN $catids OR p.artistid IN $catids)");
        }
    
    	// Filter by search in title
    	$search = $this->getState('filter.search');
    	if (!empty($search)) {
    		if (stripos($search, 'id:') === 0) {
    			$query->where('a.id = '.(int) substr($search, 3));
    		} else {
    			$search = $db->Quote('%'.$db->escape($search, true).'%');
    			$query->where("u.name LIKE $search");
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

    

    function _buildContentWhere()
    {

		$catid			= $this->getState('filter.catid');
		$start_date 	= $this->getState('filter.start_date');
		$end_date 		= $this->getState('filter.end_date');
		$order_status 	= $this->getState('filter.order_status');
	
		$datenow = Factory::getDate();
		$now = $datenow->format("%Y-%m-%d");
		if($start_date == $now && $end_date == $now ){
			$start_date = '';
			$end_date = '';
		}
		
    	$where = array();
    	$where[] = "1";
		if($start_date){
			$where[] = "c.created >= '$start_date 00:00:00'";
		}
		if($end_date){
			$where[] = "c.created <= '$end_date 23:59:59'";
		}
		if ( $order_status  ) {
			$where[] = "c.order_status = '$order_status '";
		}
		
		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

    	return $where;
    }

     
    function getOrderlinks()
    {
    	
    	$orderItems = $this->getItems();
    	$orderids = "";
        $seen = array();
    	if(is_array($orderItems ) && count($orderItems )){

    		$orderids = "(";
    		foreach($orderItems as $item){
                if(!in_array($item->order_id, $seen)){
                    $orderids .= '<a href="index.php?option=com_mymuse&view=order&layout=edit&id='.$item->order_id;
                    $orderids .= '">'.$item->order_id."</a>, ";
                    $seen[] = $item->order_id;
                }
    			
    		}
    		$orderids = preg_replace("/,$/","",$orderids);
    		$orderids .= ")";
    	}

    	return $orderids;
    }

  	/**
  	* Method to get summary of orders for a period, status, shopper
  	*
  	* @access public
  	* @return object
  	*/
  	function getOrderSummary()
  	{ 	

  	    // Get the WHERE clauses for the query
  		$where = $this->_buildContentWhere();
  		
  		$q = "SELECT * FROM #__mymuse_tax_rate WHERE published=1 ORDER BY ordering";
        $this->_db->setQuery($q);
        $tax_rates = $this->_db->loadObjectList();


        $tax_array = array();
  		
        $query = 'SELECT COUNT(*) as total_orders,  SUM(c.order_subtotal) as total_subtotal, 
		SUM(c.order_shipping) as total_shipping, 
		SUM(c.discount) as total_discount ,
		SUM(c.coupon_discount) as total_coupon_discount, 
		SUM(c.reservation_fee) as total_reservation_fee
		';
        foreach($tax_rates as $rate){
        	$name = trim($rate->tax_name);
        	$name = preg_replace("/['-\/\s\\\]/","_",$name);
        	$query .= ", SUM(c.$name) as $name ";
        	$tax_array[] = $name;
        }
        
        
		$query .= ' FROM #__mymuse_order AS c'
        . $where;


        $this->_db->setQuery( $query );
        $res = $this->_db->loadObject();
        $res->tax_array = $tax_array;
        
        
        //print_pre($res);
        return $res;
  	
  	}
  	
  	
  	/**
  	* Method to get summary of order items for a period, status, shopper
  	*
  	* @access public
  	* @return object
  	*/
  	function getItemsSummary()
  	{ 	

  		$query = $this->getItemsQuery();
  		$this->_db->setQuery( $query );
        $res = $this->_db->loadObjectList();

        return $res;
  	}
  	
  	/**
  	 * Method to get summary of order items for a period, status, shopper
  	 *
  	 * @access public
  	 * @return object
  	 */
  	protected function getItemsQuery()
  	{
  	
  		$catid	= $this->getState('filter.catid');
        $show_format = $this->getState('filter.show_format');
  		$where = $this->_buildContentWhere();

  		$orders = $this->getItems();
  		$order_ids = '';
  		if(is_array($orders ) && count($orders)){
  			$order_ids = "(";
  			foreach($orders as $order){
  				$order_ids .= $order->order_id.",";
  			}
  			$order_ids = preg_replace("/,$/","",$order_ids);
  			$order_ids .= ")";
  		}
  		$prodids   = $this->getState('list.prodids');

        $db     = Factory::getContainer()->get('DatabaseDriver');
        $query  = $db->getQuery(true);
    
        // Select the required fields from the table.
        $query->select('c.product_name, c.product_id,  a.title as artist_name, cat.title as cat_name, pp.title as parent_title,  sum(product_quantity) as quantity, c.product_item_price as price, sum(product_quantity * product_item_price) as total, p.parentid as parent_id
        ');
        

        $query->from('`#__mymuse_order_item` as c');
        $query->join('LEFT',  '`#__mymuse_product` as p ON c.product_id=p.id');
        $query->join('LEFT',  '`#__mymuse_product` as pp ON p.parentid=pp.id');
        
        $query->join('LEFT',  '`#__categories` as a ON p.artistid=a.id');
        $query->join('LEFT',  '`#__categories` as cat ON p.catid=cat.id');
        $query->where('1');

        if($show_format){
            $query->select('c.variation_id');
            $query->select('v.digital');
            $query->join('LEFT',  '`#__mymuse_product` as v ON c.variation_id=v.id');
            $query->group('variation_id');
        }


        if($order_ids){
            $query->where(" c.order_id IN $order_ids");
        }
        $catids     = $this->getState('list.catids');
        if($catids){
          $query->where(" (a.id IN ".$catids." OR cat.id IN ".$catids." )");
        }
        if($prodids){
            $query->where("  c.product_id IN $prodids");

        }
        $query->group('product_name, product_id, artist_name, cat_name, parent_title, price');

        $query->order('total DESC');

        //echo $db->replacePrefix((string) $query); exit;
 
  		return $query;
  	}

  
    /**
     * Method to set the store lists
     *
     * @access    public
     * @return    array
     */
    function getLists()
    {

    	// categories
    	$chosen_cat	= $this->getState('filter.catid');

  		$query = 'SELECT id, title ' .
  			' FROM #__categories' .
  			' WHERE extension ="com_mymuse" ';


  		$this->_db->setQuery($query);
  		$res = $this->_db->loadObjectList();
  		
  		$category[] = HTMLHelper::_('select.option', '0', Text::_( 'MYMUSE_SELECT_CATEGORY' ), 'id', 'title');
  		for($i=0;$i<count($res);$i++){
  			$category[] = $res[$i];
  		}
  		$lists['catid'] = HTMLHelper::_('select.genericlist',  $category, 'catid', 'class="inputbox"', 'id', 'title', $chosen_cat);
  		
  		return $lists;
    }
 
    /**
     * Method to get the record form.
     *
     * @param	array	$data		An optional array of data for the form to interogate.
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
     * @return	JForm	A JForm object on success, false on failure
     * @since	1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
    	// Initialise variables.
    	$app	= Factory::getApplication();
    	$start_date = $this->getState('filter.start_date');
    	$end_date = $this->getState('filter.end_date');
        $show_format = $this->getState('filter.show_format');
    	
    	// Get the form.
    	$form = $this->loadForm('com_mymuse.reports', 'reports', array());
    	if($start_date){
    		$form->setFieldAttribute('filter_start_date', 'default', $start_date);
    	}
    	if($end_date){
    		$form->setFieldAttribute('filter_end_date', 'default', $end_date);
    	}
        if($show_format){
            $form->setFieldAttribute('filter_show_format', 'default', $show_format);
        }
    	
    	if (empty($form)) {
    		return false;
    	}
    
    	return $form;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since	1.6
     */
    protected function loadFormData()
    {
    	// Check the session for previously entered form data.
    	$data = Factory::getApplication()->getUserState('com_mymuse.edit.reports.data', array());
    
    	if (empty($data)) {
    		$data = '';
    	}
    
    	return $data;
    }
    
    /**
     * Returns a reference to a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     * @return	Table	A database object
     * @since	1.6
     */
    public function getTable($type = 'Orderitem', $prefix = 'MymuseTable', $config = array())
    {
    	return Table::getInstance($type, $prefix, $config);
    }
    
    
    /*
     * get downloads query
     */
    protected function getDownloadsQuery()
    {
    	// Create a new query object.
    	$db		= Factory::getContainer()->get('DatabaseDriver');
    	$query	= $db->getQuery(true);
   	
    	// Select the required fields from the table.
    	$query->select('d.*, p.parentid as parentid, parent.title as parent, p.track_parentid as track_parentid, track_parent.title as track_parent');
    	$query->from('`#__mymuse_downloads` AS d');
        $query->join('LEFT',  '`#__mymuse_product` as p on p.id=d.product_id');
        $query->join('LEFT',  '`#__mymuse_product` as parent on parent.id=p.parentid');
        $query->join('LEFT',  '`#__mymuse_product` as track_parent on track_parent.id=p.track_parentid');

    	//filter by date
    	$start_date = $this->getState('filter.start_date');
    	$end_date = $this->getState('filter.end_date');
    	$datenow = Factory::getDate();
    	$now = $datenow->format("%Y-%m-%d");
    	
    	if($start_date== $now && $end_date == $now ){
    		$start_date = '';
    		$end_date = '';
    	}
    	
    	if($start_date){
    		$query->where("d.date >= '$start_date 00:00:00'");
    	}
    	if($end_date){
    		$query->where("d.date <= '$end_date 23:59:59'");
    	}
    	
    	//filter by cat 
    	$catid = $this->getState('filter.catid');
        $catids     = $this->getState('list.catids');
        if($catids){
            $query->join('LEFT',  '`#__categories` as a ON p.artistid=a.id');
            $query->join('LEFT',  '`#__categories` as cat ON p.catid=cat.id');
            $query->where("(a.id IN ".$catids." OR cat.id IN ".$catids." )");
        }

    	
    	$query->order($db->escape('d.id ASC'));
    	
    	return $query;
    }
    
    /*
     * get downloads table
     */
    public function getDownloads()
    {
    	$db    = Factory::getContainer()->get('DatabaseDriver');
    	$query = $this->getDownloadsQuery();
    	$db->setQuery($query);
    	$res   = $db->loadObjectList();
    	return $res;
    }
    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getOrderTableQuery()
    {
    	// Create a new query object.
    	$db		= Factory::getContainer()->get('DatabaseDriver');
    	$query	= $db->getQuery(true);

    	
    	// Select the required fields from the table.
    	$query->select('o.*, u.name, u.email');
    	$query->from('`#__mymuse_order` AS o');
    	$query->join('LEFT',  '#__users as u on u.id=o.user_id');
    
    	// Join over the order_status for the status name.
    	$query->select('os.name AS status_name');
    	$query->join('LEFT', '#__mymuse_order_status AS os ON os.code=o.order_status');
    
   
    	// Filter by order_status
    	$order_status = $this->getState('filter.order_status');
    	if (is_string($order_status) && $order_status != '0') {
    		$query->where('o.order_status = "'.$order_status.'"');
    	} else if ($order_status === '') {
    		//$query->where('(o.order_status IN (SELECT code from #__mymuse_order_status))');
    	}
    
    	//filter by date
    	$start_date = $this->getState('filter.start_date');
    	$end_date = $this->getState('filter.end_date');
    	$datenow = Factory::getDate();
    	$now = $datenow->format("%Y-%m-%d");
    
    	if($start_date== $now && $end_date == $now ){
    		$start_date = '';
    		$end_date = '';
    	}
    
    	if($start_date){
    		$query->where("o.created >= '$start_date 00:00:00'");
    	}
    	if($end_date){
    		$query->where("o.created <= '$end_date 23:59:59'");
    	}

    	$query->order($db->escape('o.id ASC'));
    	//echo $db->replacePrefix((string) $query); exit;
    	return $query;
    }
    
    /**
     * Build an SQL query to load the item table data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getItemTableQuery()
    {
    	// Create a new query object.
    	$db		= Factory::getContainer()->get('DatabaseDriver');
    	$query	= $db->getQuery(true);
    
    	// Select the required fields from the table.	
    	$query->select('i.id, i.order_id, i.product_id, i.variation_id, i.product_sku, c.title as category, 
    			pp.title as parent, i.product_name, i.file_name, i.product_quantity, i.product_item_price,
    			i.end_date, i.downloads, i.created, i.modified, i.product_in_stock, v.digital, os.name as status_name,
    			u.name, u.email');
    	$query->from('`#__mymuse_order_item` AS i');
    	$query->join('LEFT',  '#__mymuse_order as o ON i.order_id=o.id');
    	$query->join('LEFT',  '#__users as u on u.id=o.user_id');
    	$query->join('LEFT',  '#__mymuse_product as p on p.id=i.product_id');
    	$query->join('LEFT',  '#__categories as c on c.id=p.catid');
    	$query->join('LEFT',  '#__mymuse_product as pp on pp.id=p.parentid');
        $query->join('LEFT',  '#__mymuse_product as v on v.id=i.variation_id');
    	
    
    	// Join over the order_status for the status name.
    	$query->select('os.name AS status_name');
    	$query->join('LEFT', '#__mymuse_order_status AS os ON os.code=o.order_status');
    
    
    	// Filter by order_status
    	$order_status = $this->getState('filter.order_status');
    	if (is_string($order_status) && $order_status != '0') {
    		$query->where('o.order_status = "'.$order_status.'"');
    	} else if ($order_status === '') {
    		//$query->where('(a.order_status IN (SELECT code from #__mymuse_order_status))');
    	}
    
    	//filter by date
    	$start_date = $this->getState('filter.start_date');
    	$end_date = $this->getState('filter.end_date');
    	$datenow = Factory::getDate();
    	$now = $datenow->format("%Y-%m-%d");
    
    	if($start_date== $now && $end_date == $now ){
    		$start_date = '';
    		$end_date = '';
    	}
    
    	$where = array();
    	if($start_date){
    		$query->where("i.created >= '$start_date 00:00:00'");
    	}
    	if($end_date){
    		$query->where("i.created <= '$end_date 23:59:59'");
    	}
    	 
    	//filter by cat and product
    	$catid = $this->getState('filter.catid');
        $catids     = $this->getState('list.catids');
        if($catids){
 
            $query->join('LEFT',  '`#__categories` as a ON p.artistid=a.id');
            $query->join('LEFT',  '`#__categories` as cat ON p.catid=cat.id');
            $query->where("(a.id IN ".$catids." OR cat.id IN ".$catids." )");
        }

    	// Filter by search in title
    	$search = $this->getState('filter.search');
    	if (!empty($search)) {
    		if (stripos($search, 'id:') === 0) {
    			$query->where('i.id = '.(int) substr($search, 3));
    		} else {
    			$search = $db->Quote('%'.$db->escape($search, true).'%');
    			$query->where("u.name LIKE $search");
    		}
    	}
    
    	// Add the list ordering clause.
    	$query->order($db->escape('i.id ASC'));
    //echo "1. $query <br /><br />"; exit;
    	return $query;
    }
    
    
    
    /**
     * download as CSV
     * 
     */
     
    public function getCSV($table='report')
    {


     	$db = Factory::getContainer()->get('DatabaseDriver');
     	if($table == "mymuse_order"){
     		$query =  $this->getOrderTableQuery ();
     	}elseif($table == "mymuse_order_item"){
     		$query =  $this->getItemTableQuery ();
     	}elseif($table == "mymuse_downloads"){
     		$query =  $this->getDownloadsQuery();
     	}else{
 			  $this->populateState();
			 $query =  $this->getItemsQuery ();

     	}

     	$db->setQuery($query);

     	$items = $db->loadObjectList();

        if($items){

            return $items;
        }
		  
		return false;
     	
    }
  
    


}