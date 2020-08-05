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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Registry\Registry;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;


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
	 */
	protected $batch_copymove = false;

	/**
	 * Allowed batch commands
	 *
	 * @var  array
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
	 */
	protected $filter_fields = null;
	
	/**
	 * @var		array
	 */
	protected $_attribute_skus = null;
	
	/**
	 * @var		object
	 */
	protected $_params = null;
	
	/**
	 * @var		array
	 */
	protected $_previews = null;
	
	/**
	 * @var		object
	 */
	protected $storage = null;
	
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
		$this->event_before_change_featured = $this->event_before_change_featured ?? 'onContentBeforeChangeFeatured';

		$this->event_after_change_featured  = $config['event_after_change_featured'] ?? $this->event_after_change_featured;
		$this->event_after_change_featured  = $this->event_after_change_featured ?? 'onContentAfterChangeFeatured';


		$this->event_before_delete = $config['event_before_delete'] ?? $this->event_before_delete;
		$this->event_before_delete = $this->event_before_delete ?? 'onMymuseBeforeDelete';

		$this->event_before_save = $config['event_before_save'] ?? $this->event_before_save;
		$this->event_before_save = $this->event_before_save ?? 'onMymuseBeforeSave';

		$this->event_after_delete = $config['event_after_delete'] ?? $this->event_after_delete;
		$this->event_after_delete = $this->event_after_delete ?? 'onMymuseAfterDelete';

		$this->event_after_save = $config['event_after_save'] ?? $this->event_after_save;
		$this->event_after_save = $this->event_after_save ?? 'onMymuseAfterSave';


		$this->_params 		= MyMuseHelper::getParams();
		$this->storage 		= $GLOBALS['mymuseStorage'];



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
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   Table  $table  A Table object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select('MAX(' . $db->quoteName('ordering') . ')')
					->from($db->quoteName('#__mymuse_product'));

				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
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
				$input = JFactory::getApplication()->input;
				$task = $input->get('task','');
				$parentid= $input->get('parentid','');
				$id = $input->get('id','');
				
				if($task == "addfile" || $task == "additem" || $task == "new_allfiles"){
					$pk = 0;
					$input->set('id',0);
				}
				
				if ($item = parent::getItem($pk)) {
					//print_pre($item); exit; 
					// Convert the attribs field to an array.
					$registry = new JRegistry;
					$registry->loadString($item->attribs);
					$item->attribs = $registry->toArray();

					// Convert the metadata field to an array.
					$registry = new JRegistry;
					$registry->loadString($item->metadata);
					$item->metadata = $registry->toArray();

					$item->articletext = trim($item->fulltext) != '' ? $item->introtext . "<hr id=\"system-readmore\" />" . $item->fulltext : $item->introtext;
						
					if($parentid && $parentid != $id){
						$item->parentid = $parentid;
					}
						
					if($task == "new_allfiles"){
						$item->product_allfiles = 1;
					}
					if($item->parentid){
						$q = "SELECT * FROM #__mymuse_product WHERE id='".$item->parentid."'";
						$this->_db->setQuery($q);
						$this->_parent = $this->_db->loadObject();
						$item->parent = $this->_parent;
						$item->catid = $item->parent->catid;
					}else{
						//set the parent id for the tracks and items
						$mainframe = JFactory::getApplication();
						$parentid= $mainframe->getUserStateFromRequest( "com_mymuse.parentid", 'id', 0 );
					}
					$item->flash_type = '';
					
					$jason = json_decode($item->file_name);
					if(is_array($jason)){
						$item->file_name = $jason;
					}elseif($item->file_name != ''){
						$jason = (object) array('file_name' => $item->file_name);
						$item->file_name = array();
						$item->file_name[] = $jason;
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

	    	$app 				= JFactory::getApplication();
	    	$input 				= $app->input;
	    	$option 			= $input->get('option','com_mymuse');
	    	$filter_order 		= $app->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'a.ordering', 'cmd' );
	    	$filter_order_Dir 	= $app->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );
	    	$this->setState('file.ordering', $filter_order);
	    	$this->setState('file.direction', $filter_order_Dir);
	    	$this->setState('list.ordering', $filter_order);
	    	$this->setState('list.direction', $filter_order_Dir);
	    	$table = $this->getTable('product','MymuseTable');

	    	$limit 				= $this->getState('list.limit');
	    	$id 				= $input->get('id');
	    	

	    	$root = JPATH_ROOT;
	  
	    	if ($this->_tracks === null && $product = $this->getItem()) {
	    		JLoader::import( 'products', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_mymuse' . DS . 'models' );
	    		$model = JModelLegacy::getInstance('Products', 'MyMuseModel', array('ignore_request' => true));

	    		//$model->setState('filter.category_id', $category->id);
	    		$model->setState('filter.published', $this->getState('filter.published'));
	    		$model->setState('filter.access', $this->getState('filter.access'));
	    		$model->setState('filter.language', $this->getState('filter.language'));
	    		$model->setState('list.ordering', $this->getState('file.ordering'));
	    		$model->setState('list.start', $this->getState('list.start'));
	    		$model->setState('list.limit', $limit);
	    		$model->setState('list.direction', $this->getState('file.direction'));
	    		$model->setState('list.filter', $this->getState('list.filter'));
	    		// filter.subcategories indicates whether to include articles from subcategories in the list or blog
	    		$model->setState('filter.subcategories', $this->getState('filter.subcategories'));
	    		$model->setState('filter.max_category_levels', $this->setState('filter.max_category_levels'));
	    		$model->setState('list.links', $this->getState('list.links'));
	    
	    		$model->setState('filter.downloadable', 1);
	    		$model->setState('filter.parentid', $product->id);


	    		if ($limit >= 0) {
	    			$this->_tracks = $model->getItems();

	    			if ($this->_tracks  === false) {
	    				$this->setError($model->getError());
	    			}
	    		}
	    		else {
	    			$this->_track =array();
	    		}
	    
	    		$this->_trackPagination = $model->getPagination();
	    	}

	  
	    	return $this->_tracks;
	    }


      /**
        * Get the items for the product
        *
        * @return	mixed	An array of products or false if an error occurs.
        * @since	1.5
        */
       function getItems()
       {
       	
       	$app = JFactory::getApplication();
       	$input = $app->input;
       	$option = 'com_mymuse';

       	$this->_params = $this->getState()->get('params');
       	$limit = $this->getState('list.limit');
       	$id = $input->get('id');

       	$root = JPATH_ROOT.DS;
       
       	if ($this->_items === null && $product = $this->getItem()) {
       
       		$model = JModelLegacy::getInstance('Products', 'MyMuseModel', array('ignore_request' => true));
       
       		//$model->setState('filter.category_id', $category->id);
       		$model->setState('filter.published', $this->getState('filter.published'));
       		$model->setState('filter.access', $this->getState('filter.access'));
       		$model->setState('filter.language', $this->getState('filter.language'));
       		$model->setState('list.ordering', $this->getState('item.ordering'));
       		$model->setState('list.start', $this->getState('list.start'));
       		$model->setState('list.limit', $limit);
       		$model->setState('list.direction', $this->getState('item.direction'));
       		$model->setState('list.filter', $this->getState('list.filter'));
       		// filter.subcategories indicates whether to include articles from subcategories in the list or blog
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
       		$db = JFactory::getDBO();
       		for($i = 0; $i<count($this->_items); $i++){
       		
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
     * Method to set the product lists
     *
     * @access    public
     * @return    array
     */
    function getLists()
    {
    	global $option;
    	$app 				= JFactory::getApplication();
    	$input 				= $app->input;
    	$id 				= $input->get('id', 0);

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
		$subtype				= $input->get('subtype', '');
		if($this->_item->parentid){
			//we want the parentid
			$pid = $this->_item->parentid;
		}else{
			$pid = $id;
		}
		$query = 'SELECT * from #__mymuse_product_attribute_sku WHERE
			product_parent_id='.$pid.'
			ORDER BY ordering';

		$this->_db->setQuery($query);
		$lists['attribute_sku'] = $this->_db->loadObjectList();

		// items
		$query = "SELECT a.* from #__mymuse_product as a WHERE parentid=".$pid."
			AND product_downloadable=0 ";
		if($filter_item_order){
			$query .= "ORDER BY $filter_item_order ";
		}
		if($filter_item_order && $filter_item_order_Dir){
			$query .= "$filter_item_order_Dir";
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

		return $lists;
    }
    
     /**
     * Method to get the file lists.
     *
     * @access    public
     * @return    array
     */
    public function getFileLists()
    {
    	$input = JFactory::getApplication()->input;
    	$parentid = $this->_item->parentid;
    	jimport('joomla.filesystem.file');

 		// file lists for albums
 		$artist_alias = MyMuseHelper::getArtistAlias($parentid,1);
		$album_alias = MyMuseHelper::getAlbumAlias($parentid,1);

		$site_url = MyMuseHelper::getSiteUrl($parentid,1);
		$site_path = MyMuseHelper::getSitePath($parentid,1);
		$download_path = MyMuseHelper::getdownloadPath($parentid,1);
		$application = JFactory::getApplication();

		$files = array();

		$files = $this->storage->listFilesPreviews($site_path);

		
		$previews 	= array(  JHTML::_('select.option',  '', '- '. JText::_( 'MYMUSE_SELECT_FILE' ) .' -' ) );
		foreach ( $files as $file ) {
				$previews[] = JHTML::_('select.option',  $file );
		}
		$lists['previews'] = JHTML::_('select.genericlist',  $previews, 'file_preview', 'class="inputbox" size="1" ', 'value', 'text', $this->_item->file_preview );

		
		
		// get the download tracks lists
		$files = array();
		$directory = rtrim(MyMuseHelper::getDownloadPath($parentid,'1'), '/');
		$files = $this->storage->listFilesDownloads($directory);
		
		$myfiles = array(  JHTML::_('select.option',  '', '- '. JText::_( 'MYMUSE_SELECT_FILE' ) .' -' ) );
		foreach($files as $file){
				$myfiles[] = JHTML::_('select.option',  $file, stripslashes($file) );
		}
		
		$current = $this->_item->file_name;

		$i = 0;
		if($current){
			for($i = 0; $i < count($current); $i++){
				$lists['select_file'][$i] = JHTML::_('select.genericlist',  $myfiles, "select_file[$i]", 'class="inputbox" size="1" ', 'value', 'text', $current[$i]->file_name);
			}
		}else{
			$lists['select_file'][0] = JHTML::_('select.genericlist',  $myfiles, "select_file[0]", 'class="inputbox" size="1" ', 'value', 'text','');
		}
		for($i = $i++; $i < 9; $i++){
			$lists['select_file'][$i] = JHTML::_('select.genericlist',  $myfiles, "select_file[$i]", 'class="inputbox" size="1" ', 'value', 'text','');
		}
		
		// for display purposes
		$lists['preview_dir'] = $site_path;
		if($this->_params->get('my_download_dir_format')){
            //by format
            $lists['download_dir'] = '';
            foreach($this->_params->get('my_formats') as $format){
            	$lists['download_dir'] .= $download_path.DS.$format."<br />";
            }
        }else{
        	$lists['download_dir'] = $download_path;
        }
		

		return $lists;
    }
    


}
