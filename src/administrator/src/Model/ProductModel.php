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

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\UCM\UCMType;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Administrator\Helper\Mp3fileHelper;
use Joomla\Component\Mymuse\Administrator\Model\ProductsModel;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseStorage;


/**
 * Product model.
 *
 * @since  1.6
 */
class ProductModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_MYMUSE_PRODUCT';

	/**
	 * The type alias for this content type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = 'com_mymuse.product';

	/**
	 * Batch copy/move command. If set to false, the batch copy/move command is not supported
	 *
	 * @var  string
     * @since  5.0
	 */
	protected $batch_copymove = true;

	/**
	 * Allowed batch commands
	 *
	 * @var  array
     * @since  5.0
	 */
	protected $batch_commands = array(
		'language_id' => 'batchLanguage'
	);

	/**
	 * @var		object	The parent object
	 * @since	1.6
	 */
	protected $_parent = null;
	
	/**
	 * @var		product(item) object
	 * @since	1.6
	 */
	protected $_item = null;
	
	/**
	 * @var		array of product(track) objects
	 * @since	1.6
	 */
	protected $_tracks = null;

	/**
	 * @var		array of product(format) objects
	 * @since	1.6
	 */
	protected $_formats = array();
	
	/**
	 * @var		array of product(item) objects
	 * @since	1.6
	 */
	protected $_items = null;
	
	/**
	 * @var		object
	 * @since	1.6
	 */
	protected $_trackPaginition = null;
	
	/**
	 * @var		object
	 * @since	1.6
	 */
	protected $_itemPaginition = null;
	
	/**
	 * @var		array
     * @since  4.0
	 */
	protected $filter_fields = null;
	
	/**
	 * @var		array
     * @since  4.0
	 */
	protected $_attribute_skus = null;
	
	/**
	 * @var		object
     * @since  4.0
	 */
	protected $_params = null;
	
	/**
	 * @var		array
     * @since  4.0
	 */
	protected $_previews = null;
	
	/**
	 * @var		object
     * @since  4.0
	 */
	protected $storage = null;
 

	/**
	 * The event to trigger before changing featured status one or more items.
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $event_before_change_featured = null;

	/**
	 * The event to trigger after changing featured status one or more items.
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $event_after_change_featured = null;

	/**
	 * The event to trigger before changing featured status one or more items.
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $event_before_delete = null;

	/**
	 * The event to trigger after changing featured status one or more items.
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $event_before_save = null;

	/**
	 * The event to trigger before changing featured status one or more items.
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $event_after_delete = null;

	/**
	 * The event to trigger after changing featured status one or more items.
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $event_after_save = null;

	
	/**
	 * Constructor.
	 *
	 * @param   array                 $config       An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface   $factory      The factory.
	 * @param   FormFactoryInterface  $formFactory  The form factory.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{

		parent::__construct($config, $factory, $formFactory);

		// Set the featured status change events
		$this->event_before_change_featured = $config['event_before_change_featured'] ?? $this->event_before_change_featured;
		$this->event_before_change_featured = $this->event_before_change_featured ?? 'onMymuseBeforeChangeFeatured';

		$this->event_after_change_featured  = $config['event_after_change_featured'] ?? $this->event_after_change_featured;
		$this->event_after_change_featured  = $this->event_after_change_featured ?? 'onMymuseAfterChangeFeatured';


		$this->event_before_delete = $config['event_before_delete'] ?? $this->event_before_delete;
		$this->event_before_delete = $this->event_before_delete ?? 'onMymuseBeforeDelete';

		$this->event_before_save = $config['event_before_save'] ?? $this->event_before_save;
		$this->event_before_save = $this->event_before_save ?? 'onMymuseBeforeSave';

		$this->event_after_delete = $config['event_after_delete'] ?? $this->event_after_delete;
		$this->event_after_delete = $this->event_after_delete ?? 'onMymuseAfterDelete';

		$this->event_after_save = $config['event_after_save'] ?? $this->event_after_save;
		$this->event_after_save = $this->event_after_save ?? 'onMymuseAfterSave';


		$this->_params 		= MyMuseHelper::getParams();
		//$this->storage 		= $GLOBALS['mymuseStorage'];



	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form. [optional]
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not. [optional]
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_mymuse.product', 'product', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_mymuse.edit.product.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('product.id') == 0)
			{
				$filters     = (array) $app->getUserState('com_mymuse.product.filter');
			}
		}

		$this->preprocessData('com_mymuse.product', $data);

		return $data;
	}


	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   Table  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since   1.6
	 */
	protected function getReorderConditions($table)
	{
		return [
			$this->_db->quoteName('published') . ' >= 0',
		];
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   \Joomla\CMS\Table\Table  $table  A Table object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		

		// Increment the content version number.
		$table->version++;

		// Reorder the articles within the category so the new article is first
		if (empty($table->id))
		{
			$table->reorder('catid = ' . (int) $table->catid . ' AND state >= 0');
		}
		if ($table->state == 1 && (int) $table->publish_up == 0)
		{
			$table->publish_up = Factory::getDate()->toSql();
		}

		if ($table->state == 1 && intval($table->publish_down) == 0)
		{
			$table->publish_down = NULL;
		}

	}


	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
    public function getItem($pk = null)
	{
		if(!$this->_item){
			$input = Factory::getApplication()->input;
			$task = $input->get('task','');
			$parentid = $input->get('parentid',NULL);
			$id = $input->get('id','');

			if($task == "addfile" || $task == "additem" || $task == "new_allfiles"){
				$pk = 0;
        $id = 0;
				$input->set('id',0);
				
			}


			if ($item = parent::getItem($pk)) {
				$item->parentid = $parentid ?? $item->parentid;

				if($item->parentid){
					$this->_parent = parent::getItem($item->parentid);
					$item->parent = $this->_parent;
					$item->catid = $item->parent->catid;
					$item->artistid = $item->parent->artistid;
				}


				// Convert the attribs field to an array.
				$registry = new Registry($item->attribs);
				$item->attribs = $registry->toArray();

				// Convert the metadata field to an array.
				$registry = new Registry($item->metadata);
				$item->metadata = $registry->toArray();

				// Convert the physical field to an array.
				$registry = new Registry($item->physical);
				$item->physical = $registry->toArray();

				// Convert the recording field to an array.
				$registry = new Registry($item->recording);
				$item->recording = $registry->toArray();

				// Convert the digital field to an array.
				$registry = new Registry($item->digital);
				$item->digital = $registry->toArray();


				if($task == "additem"){

					$registry = new Registry($item->parent->physical);
					$item->physical = $registry->toArray();

					//price if item && my_price_by_product
					if($this->_params->get('my_price_by_product',0)) {
						$registry = new Registry($item->parent->attribs);
						$attribs = $registry->toArray();
						$item->price = $attribs['product_price_physical'];
						$item->attribs = $item->parent->attribs;
					}
					$item->list_image = $item->parent->list_image;
					$item->detail_image = $item->parent->detail_image;
					$item->product_physical = 1;

				}

				$item->articletext = $item->fulltext != '' ? trim($item->introtext) . "<hr id=\"system-readmore\" />" . $item->fulltext : $item->introtext;


        $item->tags = new TagsHelper();
        $item->tags->getTagIds($item->id, 'com_content.article');
				

				
				$item->flash_type = '';

				if($task == "addfile" || $task == "new_allfiles"){
					$item->downloadable = 1;
				}
				if($task == "new_allfiles"){
					$item->product_allfiles = "1";
				}
				
				
			}
		
			$this->_item = $item;

		}
		return $this->_item;
	}


  /**
   * Get the tracks for the product
   *
   * @return	mixed	An array of products or false if an error occurs.
   * @since	1.5
  */
  function getTracks()
  {

    	$app 				= Factory::getApplication();
    	$input 				= $app->input;
    	$option 			= $input->get('option','com_mymuse');
    	$filter_order 		= $app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'a.ordering', 'cmd' );
    	$filter_order_Dir 	= $app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
    	$this->setState('file.ordering', $filter_order);
    	$this->setState('file.direction', $filter_order_Dir);
    	$this->setState('list.ordering', $filter_order);
    	$this->setState('list.direction', $filter_order_Dir);

    	$limit 				= $this->getState('list.limit');
    	$id 				= $input->get('id');

    	if ($this->_tracks === null && $product = $this->getItem()) {

	      	$model = ListModel::getInstance('Products', 'MyMuse', array('ignore_request' => true));
	      	$form = $model->getFilterForm(array(), true);
	      	$form->removeField('stage', 'filter');
	      	$form->removeField('category_id', 'filter');
	      	$form->removeField('artist_id', 'filter');
	      	$form->removeField('language', 'filter');
	      	$form->removeField('author_id', 'filter');
	      	$this->filterForm = $form;

				$model->setState('filter.published', $this->getState('filter.published'));
	   		$model->setState('filter.access', $this->getState('filter.access'));
	   		$model->setState('filter.language', $this->getState('filter.language'));
	   		$model->setState('list.ordering', $this->getState('item.ordering'));
	   		$model->setState('list.start', $this->getState('list.start'));
	   		$model->setState('list.limit', $limit);
	   		$model->setState('list.direction', $this->getState('item.direction'));
	   		$model->setState('list.filter', $this->getState('list.filter'));
	   		$model->setState('filter.subcategories', $this->getState('filter.subcategories'));
	   		$model->setState('filter.max_category_levels', $this->setState('filter.max_category_levels'));
	   		$model->setState('list.links', $this->getState('list.links'));
	   		
	   		$model->setState('filter.parentid', $id);
    		$model->setState('filter.downloadable', 1);

    		if ($limit >= 0) {
    			$this->_tracks = $model->getItems();

    			if ($this->_tracks  === false) {
    				$this->setError($model->getError());
    			}
    		}
    		else {
    			$this->_tracks =array();
    		}
    		foreach($this->_tracks as $track){
    			$track->formats = $this->getFormats($track->id);
    		}
    		$this->_trackPagination = $model->getPagination();
    	}


    	return $this->_tracks;
    }

 /**
   * Get the formats for the track
   *
   * @return	mixed	An array of formats or false if an error occurs.
   * @since	1.5
  */
  function getFormats($id)
  {

    	$app 				= Factory::getApplication();
    	$input 				= $app->input;
    	if(isset($this->_parent->id)){
    		$parentid = $this->_parent->id;
    	}else{
    		$parentid = $this->_item->id;
    	}
    	
    	$option 			= $input->get('option','com_mymuse');
    	$filter_order 		= $app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'a.ordering', 'cmd' );
    	$filter_order_Dir 	= $app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
    	$this->setState('file.ordering', $filter_order);
    	$this->setState('file.direction', $filter_order_Dir);
    	$this->setState('list.ordering', $filter_order);
    	$this->setState('list.direction', $filter_order_Dir);

    	$limit 				= $this->getState('list.limit');
  
    	if (!isset($this->_formats[$id]) || $this->_formats[$id] === null) {

	      	$model = ListModel::getInstance('Products', 'MyMuse', array('ignore_request' => true));
	      	$form = $model->getFilterForm(array(), true);
	      	$form->removeField('stage', 'filter');
	      	$form->removeField('category_id', 'filter');
	      	$form->removeField('artist_id', 'filter');
	      	$form->removeField('language', 'filter');
	      	$form->removeField('author_id', 'filter');
	      	$this->filterForm = $form;

				$model->setState('filter.published', $this->getState('filter.published'));
	   		$model->setState('filter.access', $this->getState('filter.access'));
	   		$model->setState('filter.language', $this->getState('filter.language'));
	   		$model->setState('list.ordering', $this->getState('item.ordering'));
	   		$model->setState('list.start', $this->getState('list.start'));
	   		$model->setState('list.limit', $limit);
	   		$model->setState('list.direction', $this->getState('item.direction'));
	   		$model->setState('list.filter', $this->getState('list.filter'));
	   		$model->setState('filter.subcategories', $this->getState('filter.subcategories'));
	   		$model->setState('filter.max_category_levels', $this->setState('filter.max_category_levels'));
	   		$model->setState('list.links', $this->getState('list.links'));
				$model->setState('filter.parentid', $parentid);
	   		$model->setState('filter.trackparentid', $id);
    		$model->setState('filter.downloadable', 1);

    		if ($limit >= 0) {
    			$this->_formats[$id] = $model->getItems();

    			if ($this->_formats[$id]  === false) {
    				$this->setError($model->getError());
    			}
    			foreach($this->_formats[$id] as $f){
    				$f->file_data = json_decode($f->digital);
    			}
    		}
    		else {
    			$this->_formats[$id] =array();
    		}
    
    	}

    	return $this->_formats[$id];
    }
    /**
    * Get the items for the product
    *
    * @return	mixed	An array of products or false if an error occurs.
    * @since	1.5
    */
   function getItems()
   {
   	
   	$app = Factory::getApplication();
   	$input = $app->input;
   	$option = 'com_mymuse';

   	//$this->_params = $this->getState()->get('params');
   	$limit = $this->getState('list.limit');
   	$id = $input->get('id');
   
   	if ($this->_items === null && $product = $this->getItem()) {
   
   		$model = ListModel::getInstance('Products', 'MyMuse', array('ignore_request' => true));

   		//$model->setState('filter.category_id', $category->id);
   		$model->setState('filter.published', $this->getState('filter.published'));
   		$model->setState('filter.access', $this->getState('filter.access'));
   		$model->setState('filter.language', $this->getState('filter.language'));
   		$model->setState('list.ordering', $this->getState('item.ordering'));
   		$model->setState('list.start', $this->getState('list.start'));
   		$model->setState('list.limit', $limit);
   		$model->setState('list.direction', $this->getState('item.direction'));
   		$model->setState('list.filter', $this->getState('list.filter'));
   		$model->setState('filter.subcategories', $this->getState('filter.subcategories'));
   		$model->setState('filter.max_category_levels', $this->setState('filter.max_category_levels'));
   		$model->setState('list.links', $this->getState('list.links'));
   
   		$model->setState('filter.downloadable', 0);
   		$model->setState('filter.physical', 1);
   		$model->setState('filter.parentid', $product->id);

   
   		if ($limit >= 0) {
   			$this->_items = $model->getItems();
   
   			if ($this->_items  === false) {
   				$this->setError($model->getError());
   			}
   		}
   		else {
   			$this->_items =array();
   		}
   
   		$this->_itemPagination = $model->getPagination();
   		
   		//get attributes
   		$db = Factory::getDBO();
   		for($i = 0; $i<count($this->_items); $i++){

   			// Convert the attribs field to an array.
   			$registry = new Registry($this->_items[$i]->attribs);
   			$this->_items[$i]->attribs = $registry->toArray();

   			// Convert the metadata field to an array.
   			$registry = new Registry($this->_items[$i]->metadata);
   			$this->_items[$i]->metadata = $registry->toArray();

   			// Convert the physical field to an array.
   			$registry = new Registry($this->_items[$i]->physical);
   			$this->_items[$i]->physical = $registry->toArray();

   		
   			if(!$this->_attribute_skus && $product->id){
   				$query = 'SELECT * from #__mymuse_product_attribute_sku WHERE product_parent_id='.$product->id;
					$db->setQuery($query);
					$this->_attribute_skus = $db->loadObjectList();
   			}
   			$id = $this->_items[$i]->id;
   			if($this->_attribute_skus ){
   				foreach($this->_attribute_skus as $a_sku){
   					$query = 'SELECT attribute_value from #__mymuse_product_attribute WHERE product_id='.$id.'
   					AND product_attribute_sku_id='.$a_sku->id;
   					
   					$db->setQuery($query);
   					$this->_items[$i]->attributes[$a_sku->name] = $db->loadResult();
   				}
   			}
   		}
   	}

   	return $this->_items;
   }
   

   
   function getItemPagination()
   {
   	if (empty($this->_itemPagination)) {
   		return null;
   	}
   	return $this->_itemPagination;
   }
	       
	/**
     * Method to get the product lists
     *
     * @return    array
     *
     * @since 3.0
   */
   public function getLists()
   {
    	global $option;
    	$app 				= Factory::getApplication();
    	$input 				= $app->input;
    	$id 				= $input->get('id', 0);
    	$subtype			= $input->get('type', '');

    	$filter_state 		= $app->getUserStateFromRequest( $option.'filter_state', 'filter_state', '', 'word' );
			$filter_catid 		= $app->getUserStateFromRequest( $option.'filter_catid', 'filter_catid', 0, 'int' );
			$filter_artistid 	= $app->getUserStateFromRequest( $option.'filter_artistid', 'filter_artistid', 0, 'int' );
			$filter_order 		= $app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'a.ordering', 'cmd' );
			$filter_order_Dir 	= $app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );

			$filter_item_order 		= $app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'a.ordering', 'cmd' );
			$filter_item_order_Dir 	= $app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
			
			$this->setState('file.ordering', $filter_order);
			$this->setState('file.direction', $filter_order_Dir);
			
			$this->setState('item.ordering', $filter_order);
			$this->setState('item.direction', $filter_item_order_Dir);
			
			$lists['order'] = $filter_order;
			$lists['order_Dir'] = $filter_order_Dir;
			$edit = $input->get('edit', 0);

			//other categories
			$selectedCats = array();
			if($id){
				$query = "SELECT * FROM #__mymuse_product_category_xref WHERE product_id=".$id;
				$this->_db->setQuery($query);
				$cats =  $this->_db->loadObjectList();
				if($cats){
					foreach($cats as $cat){
						$selectedCats[] = $cat->catid;
					}
				}
			}	

			$query = "SELECT id,title FROM #__categories WHERE extension='com_mymuse'";
			$this->_db->setQuery($query);
			$lists['other_cats'] = $this->_db->loadObjectList();
			
			
			// Items, Attributes, Files
			$lists['attributes'] 	= array();
			$lists['attribute_sku'] = array();
			$lists['items'] 		= array();
			$lists['files'] 		= array();
		
			//attributes
			if($this->_item->parentid > 0){
				//we want the parentid
				$pid = $this->_item->parentid;
			}else{
				$pid = $id;
			}
			if($pid) {
				$query = "SELECT * from #__mymuse_product_attribute_sku WHERE
					product_parent_id=$pid
					ORDER BY ordering";


				$this->_db->setQuery($query);
				$lists['attribute_sku'] = $this->_db->loadObjectList();

		
			

				// items
				$query = "SELECT a.* from #__mymuse_product as a WHERE parentid=".$pid."
					AND product_downloadable='1' ";


				if($filter_item_order){
					$query .= " ORDER BY $filter_item_order ";
				}
				if($filter_item_order && $filter_item_order_Dir){
					$query .= " $filter_item_order_Dir ";
				}
					
				$this->_db->setQuery($query);

				if($lists['items'] = $this->_db->loadObjectList()){

					foreach($lists['items'] as $item){
						foreach($lists['attribute_sku'] as $a_sku){
							$query = 'SELECT attribute_value from #__mymuse_product_attribute WHERE product_id='.$item->id.'
								AND product_attribute_sku_id='.$a_sku->id;
						
							$this->_db->setQuery($query);
							$item->attributes[$a_sku->name] = $this->_db->loadResult();
						}
						$query = 'SELECT * from #__mymuse_product_attribute WHERE product_id='.$item->id;
					
						$this->_db->setQuery($query);
						$lists['attributes'][$item->id] = $this->_db->loadObjectList();

					}
				}
			}
			return $lists;
    }
    
    /**
    * Method to get the file lists.
    *
    * @return    array
    *
    * @since 3.0
    */
    public function getFileLists()
    {

    	$input 	= Factory::getApplication()->input;
    	$task 	= $input->get('task');
    	$id 		= $input->get('id');
    	$layout = $input->get('layout');

      $parentid= $input->get('parentid','');
      if(!$parentid){
      	if($layout == "listtracks"){
      		$parentid = $id;
      	}else{
        	$parentid = $this->_item->parentid;
  
      	}
      }

	 		// file lists for albums
	 		$artist_alias = MyMuseHelper::getArtistAlias($parentid,1);
			$album_alias = MyMuseHelper::getAlbumAlias($parentid,1);

			$site_url = MyMuseHelper::getSiteUrl($parentid,1);
			$site_path = MyMuseHelper::getSitePath($parentid,1);
			$download_path = MyMuseHelper::getdownloadPath($parentid,1);
			$application = Factory::getApplication();

			//get previews
			$files = array();
			$files = MymuseStorage::listFilesPreviews($site_path);
			$previews 	= array(  HTMLHelper::_('select.option',  '', '- '. Text::_( 'COM_MYMUSE_SELECT_FILE' ) .' -' ) );
			foreach ( $files as $file ) {
					$previews[] = HTMLHelper::_('select.option',  $file );
			}
	        $this->_item->file_preview  = isset($this->_item->file_preview) ? $this->_item->file_preview : '';
			$lists['previews'] = HTMLHelper::_('select.genericlist',  $previews, 'file_preview', 'class="inputbox" size="1" ', 'value', 'text', $this->_item->file_preview );

			
			
			// get the download tracks lists
			$directory = rtrim(MyMuseHelper::getDownloadPath($parentid,'1'), '/');
			$files = MymuseStorage::listFilesDownloads($directory);

			$myfiles = array();

			if($files){
				$myfiles = array(  HTMLHelper::_('select.option',  '', '- '. Text::_( 'COM_MYMUSE_SELECT_FILE' ) .' -' ) );
				foreach($files as $file){
					$myfiles[] = HTMLHelper::_('select.option',  $file, stripslashes($file) );
				}
			}

			$current = array();
			if($task == "editfile"){
				

				$formats = $this->getFormats($id);
				
				foreach($formats as $key => $f){
					$current[] = json_decode($f->digital);
				}


			}
		

			$i = 0;
			if(count($current) > 0){
				for($i = 0; $i < count($current); $i++){
					$lists['select_file'][$i] = HTMLHelper::_('select.genericlist',  $myfiles, "select_file[$i]", 'class="inputbox" size="1" ', 'value', 'text', $current[$i]->file_name);
				}
			}else{
				$lists['select_file'][0] = HTMLHelper::_('select.genericlist',  $myfiles, "select_file[0]", 'class="inputbox" size="1" ', 'value', 'text','');
			}
			for($i = $i++; $i < 9; $i++){
				$lists['select_file'][$i] = HTMLHelper::_('select.genericlist',  $myfiles, "select_file[$i]", 'class="inputbox" size="1" ', 'value', 'text','');
			}

			//for formats
			$all_formats = $this->_params->get('my_formats');

			if(count($all_formats) == 1){
				$lists['formats'][0] = $all_formats[0]->format_key;
			}else{

				for($i = 0; $i < count($all_formats); $i++){ 

					$formats 	= array(  HTMLHelper::_('select.option',  '', '- '. Text::_( 'COM_MYMUSE_SELECT_FORMAT' ) .' -' ) );
					foreach ( $all_formats as $format ) {
							$formats[] = HTMLHelper::_('select.option',  $format->format_key, $format->format_value);
					}
					$ff = isset($current[$i]->file_format)? $current[$i]->file_format : '';
					$lists['formats'][$i] = HTMLHelper::_('select.genericlist',  $formats, "formats[$i]", 'class="inputbox" size="1" ', 'value', 'text', $ff);
				}
			}
			
			// for display purposes
			$lists['preview_dir'] = $site_path;
			if($this->_params->get('my_download_dir_format')){
	            //by format
	            $lists['download_dir'] = '';
	            foreach($this->_params->get('my_formats') as $format){
	            	$lists['download_dir'] .= $download_path.DIRECTORY_SEPARATOR.$format."<br />";
	            }
	        }else{
	        	$lists['download_dir'] = $download_path;
	        }
			

			return $lists;
    }
    




	/**
	 * getAttributes
	 * 
	 * @return array
	 */
	function getAttributes(){

		$db = Factory::getDBO();

		$this->_attribute_skus = $this->getAttributeskus();

		$this->_item->attributes = array();
		$app 		= Factory::getApplication();
		$input 	= $app->input;
		$id 		= $input->get('id',0);
		if($id){
			foreach($this->_attribute_skus as $a_sku){
				$query = 'SELECT attribute_value from #__mymuse_product_attribute WHERE product_id='.$id.'
				AND product_attribute_sku_id='.$a_sku->id;
				$db->setQuery($query);
				$this->_item->attributes[$a_sku->name] = $db->loadResult();
			}
		
			return $this->_item->attributes;
		}
		
		return array();
		
	}

	/**
	 * getAttributeskus
	 *
	 * @return array
	 */
	function getAttributeskus(){
		$db = Factory::getDBO();
		$app 	= Factory::getApplication();
		$input 	= $app->input;
		$pid	= $input->get( 'parentid', null );

		if(!$pid){
			//see if we can get the parent from the item id
			$item = $this->getItem();

			if(isset($item->id)){
				$query = "SELECT parentid from #__mymuse_product WHERE id=".$item->id;
				$this->_db->setQuery($query);
				$pid = $this->_db->loadResult();
			}else{
				$this->setError(Text::_('COM_MYMUSE_COULD_NOT_FIND_PARENT'));
            	return false;
			}
		}
		$query = 'SELECT * from #__mymuse_product_attribute_sku WHERE product_parent_id='.$pid;
		$db->setQuery($query);
		$this->_attribute_skus = $db->loadObjectList();
		
		return $this->_attribute_skus;

	}
	
	/**
	 * updateAttributes
	 *
	 * @return boolean
	 */
	public function updateAttributes()
	{
		// Attributes
		$input 				= Factory::getApplication()->input;
		$post 				= $input->post->getArray();
		$itemid				= $input->get('itemid','');
		$db = Factory::getDBO();

		$attribute_values		= $input->getVar('attribute_value', array());
		$attribute_names		= $input->getVar('attribute_name', array());

		if(!count($attribute_values) || !count($attribute_names) || !isset($itemid)){
			return;
		}

		
		$query = "DELETE FROM #__mymuse_product_attribute
			WHERE product_id='".$itemid."'";
		//echo $query;
		$db->setQuery($query);
		$db->execute();

		foreach( $attribute_values as $key => $val) {

			$query = "INSERT INTO #__mymuse_product_attribute
				(`product_id`,`product_attribute_sku_id`,`attribute_name`,`attribute_value`)
				VALUES 
				(".$itemid.",".$key.",'".$attribute_names[$key]."','".$val."')";
	
			$db->setQuery($query);
			$db->execute();

		}
		return true;

	}


	function save2copy($oldid, $item=0){

			$this->table = $this->getTable();
			$this->table->reset();

			// Check that the row actually exists
			if (!$this->table->load($oldid))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					return false;
				}
			}
			
			$categoryId = $this->table->catid;
			$this->table->id 										= 0;
			$input      												= Factory::getApplication()->input;
			$this->table->parentid 							= $oldid;
			$data = $this->generateNewTitle($categoryId, $this->table->alias, $this->table->title);

			//is it an item?
			if($item){

				// Reset the ID because we are making a copy
				
				$this->table->product_physical 			= 1;
				$this->table->product_downloadable 	= 0;
				$this->table->product_sku 					= $this->table->product_sku . $input->get('item_title');
				$this->table->title 								= $this->table->title . $input->get('item_title');
				$old_alias													= $this->table->alias;
				$this->table->alias 								= ApplicationHelper::stringURLSafe($this->table->title);
				$this->table->state 								= 1;
				//price if item && my_price_by_product
				if($this->_params->get('my_price_by_product',0)) {
					$registry = new Registry($this->table->attribs);
					$attribs = $registry->toArray();
					$this->table->price = $attribs['product_price_physical'];
				}
				if(isset($this->table->product_images) && $this->table->product_images != '' && $input->get('color',0)){
					$detail_image = "images/".$this->table->product_images.'/'.$old_alias.'-'.$input->get('color',0).'.png';
					if(file_exists(JPATH_ROOT.'/'.$detail_image)){
						$this->table->detail_image = $detail_image;
					}
					$list_image = "images/".$this->table->product_images.'/'.$old_alias.'-'.$input->get('color',0).'.png';
					if(file_exists(JPATH_ROOT.'/'.$list_image)){
						$this->table->list_image = $list_image;
					}
					//echo $detail_image."<br />";
				}
			}else{
				// Alter the title & alias
				
				$this->table->title = $data['0'];
				$this->table->alias = $data['1'];
				// Unpublish because we are making a copy
				$this->table->state = 0;
			}
			//make new sku
			list($newSku, $alias) = $this->generateNewTitle($categoryId, $this->table->product_sku, $this->table->product_sku);
			$this->table->product_sku = $newSku;
	
			// Reset hits because we are making a copy
			$this->table->hits = 0;



			// New category ID
			$this->table->catid = $categoryId;
			$this->table->asset_id = '';

			// TODO: Deal with ordering?
			// $table->ordering	= 1;

			// Get the featured state
			$featured = $this->table->featured;

			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($this->table->getError());

				return false;
			}


			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());

				return false;
			}

			// Get the new item ID
			$newId = $this->table->get('id');

			return $newId;

	}


    /**
     * Method override to check-in a record or an array of record
     *
     * @param   mixed  $pks  The ID of the primary key or an array of IDs
     *
     * @return  integer|boolean  Boolean false if there is an error, otherwise the count of records checked in.
     *
     * @since   1.6
     *
     */

    public function checkin($pks = array()) {
        $table = $this->getTable('Product');
        if(is_string($pks)) {
        	$arr[] = $pks;
        	$pks = $arr;
        }
 
        foreach($pks as $pk){
            $table->checkin($pk);
        }
        return true;

    }

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array    $pks    An array of primary key ids.
	 * @param   integer  $order  +1 or -1
	 *
	 * @return  mixed
	 *
	 * @since   12.2
	 */
	public function saveorder($pks = null, $order = null)
	{
	
		MyMuseHelper::logMessage("here in model product\n");
		$table = $this->getTable('product','MymuseTable');
		$conditions = array();
	
		if (empty($pks))
		{
			return JError::raiseWarning(500, JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
		}
	
		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load((int) $pk);
	
			// Access checks.
			if (!$this->canEditState($table))
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
			}
			elseif ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];
	
	
				if (!$table->store())
				{
					$this->setError($table->getError());
					return false;
				}
	
				// Remember to reorder within position and client_id
				$condition = $this->getReorderConditions($table);
				$found = false;
				MyMuseHelper::logMessage("$condition\n");
	
				foreach ($conditions as $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}
	
				if (!$found)
				{
					$key = $table->getKeyName();
					$conditions[] = array($table->$key, $condition);
				}
			}
		}
	
	
		// Clear the component's cache
		$this->cleanCache();
	
		return true;
	}


	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$input  		= Factory::getApplication()->input;
		$filter 		= \JFilterInput::getInstance();
		$db     		= $this->getDbo();
		$user				= Factory::getUser();


		if (isset($data['metadata']) && isset($data['metadata']['author'])){
			$data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');
		}

		if (isset($data['created_by_alias'])){
			$data['created_by_alias'] = $filter->clean($data['created_by_alias'], 'TRIM');
		}

		

		// Automatic handling of alias for empty fields
		if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (!isset($data['id']) || (int) $data['id'] == 0))
		{
			if ($data['alias'] == null)
			{
				if (Factory::getApplication()->get('unicodeslugs') == 1){
					$data['alias'] = \JFilterOutput::stringURLUnicodeSlug($data['title']);
				}else{
					$data['alias'] = \JFilterOutput::stringURLSafe($data['title']);
				}

				$table = Table::getInstance('ProductTable', 'Joomla\\Component\\Mymuse\\Administrator\\Table\\');

				if ($table->load(array('alias' => $data['alias'], 'catid' => $data['catid']))){
					$msg = Text::_('COM_MYMUSE_SAVE_WARNING');
				}

				list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
				$data['alias'] = $alias;

				if (isset($msg)){
					Factory::getApplication()->enqueueMessage($msg, 'warning');
				}
			}
		}

//MymuseHelper::print_pre($data); exit;


		if (parent::save($data)){
			return true;
		}

		return false;
	}




	/**
	 * Update DB from MyMuse4 to MyMuse5
	 *
	 * @param   int limit
	 * @param   int limitstart
	 *
	 * @return  mixed
	 *
	 * @since   5.0
	 */

   function updateDB($limit=50, $limitstart=0, $type='physical')
   {

   		$process 				= 0;
   		$input      		= Factory::getApplication()->input;
	   	$db 						= Factory::getDBO();
	   	$this->table 		= $this->getTable();
	   	$formats 				= array();
	   	$my_return			= array();
	   	$my_return[0]		= '';
	   	$my_return[1]		= '';

	   	$query = "SELECT * FROM #__mymuse_format";
	   	$db->setQuery($query);
	   	$all_formats = $db->loadObjectList();
	   	foreach($all_formats as $f){
	   		$formats[$f->format_value] = $f->id;
	   	}

	   	if($type == "physical"){
		   	//physical parents, CD's. Gather physical info and put it in 'physical' field
		   	$query 					= "SELECT * FROM #__mymuse_product 
		   	WHERE product_physical=1 AND 
		   	product_downloadable= 0 AND
		   	track_parentid = 0
		   	ORDER BY id ASC
		   	LIMIT $limitstart, $limit";

		   	echo $query. "<br />";

		   	$db->setQuery($query);
		   	if($res = $db->loadObjectList())
		   	{
		   		if(count($res)){
		   			$process = 1;
		   		}
		   		
		   	}else{
		   		$my_return[0] = 'physical-done';
		   		return $my_return;
		   	}
		   	if($process)
		   	{
		   		foreach($res as $r){

		   				//{"prducoduct_weight":".2","product_weight_uom":"LBS","product_length":".6","product_width":"6","product_height":".5","product_lwh_uom":"IN"}
		   				$physical = array();

							$this->table->reset();
							$this->table->load($r->id);

		   				$physical['product_weight'] =  $r->product_weight;
		   				$physical['product_weight_uom'] =  $r->product_weight_uom;
		   				$physical['product_length'] =  $r->product_length;
		   				$physical['product_width'] =  $r->product_width;
		   				$physical['product_height'] =  $r->product_height;
		   				$physical['product_lwh_uom'] =  $r->product_lwh_uom;

		   				$registry = new Registry;
							$registry->loadArray($physical);
							$this->table->physical = (string)$registry;

							if(!$result = $this->table->updateDB()){
								$app->enqueueMessage(Text::_('COM_MYMUSE_COULD_NOT_SAVE_PHYSICAL').' '.$this->table->getError(), 'error');
								return false;
							}
							$my_return[1] .= 'created '.$this->table->id.' FROM '.$r->id.' : '.$r->title.'<br />';
		   		
		   			
		   		}
		   		$my_return[0] = 'physical-continue';
		   		return $my_return;
	
		   	}
	    }
	    if($type == "tracks"){

		   	//tracks. Save a child track for each file format
		   	$process = 0;
		   	$query 					= "SELECT * FROM #__mymuse_product 
		   	WHERE parentid > 0 AND 
		   	product_physical=0 AND 
		   	product_downloadable= 1 AND
		   	track_parentid = 0
		   	ORDER BY id ASC
		   	LIMIT $limitstart, $limit";

		   	echo $query. "<br />";

		   	$db->setQuery($query);
		   	if($res = $db->loadObjectList())
		   	{
		   		if(count($res)){
		   			$process = 1;
		   		}

		   	}else{
		   		$my_return[0] = "tracks-done";
		   		return $my_return;
		   	}
		   	if($process)
		   	{
		   		foreach($res as $r){
		   			if(isset($r->file_name) && $r->file_name != '')
		   			{
		   				$files_array = json_decode($r->file_name);


		   				foreach($files_array as $file){
		   						$file->file_format = isset($formats[$file->file_ext])? $formats[$file->file_ext] : '';

									$this->table->reset();
									$this->table->load($r->id);
									$this->table->id 										= 0;
									$this->table->product_sku 					= $r->product_sku.'-'.$file->file_ext;
									$this->table->title_alias						= '';
									$this->table->parentid 							= $r->parentid;
									$this->table->track_parentid				= $r->id;
									$this->table->digital								= json_encode($file);
									$this->table->filter_name						= '';

									if(!$result = $this->table->updateDB()){
										$app->enqueueMessage(Text::_('COM_MYMUSE_COULD_NOT_SAVE_CHILD_TRACK').' '.$this->table->getError(), 'error');
										return false;
									}
									$my_return[1] .= 'created '.$this->table->id.' FROM '.$r->id.' : '.$r->title.'<br />';
		   				}
		   			}
		   		}
		   		$my_return[0] = 'tracks-continue';
		   		return $my_return;

		   	}
		  }
	   	return true;


   }


}
