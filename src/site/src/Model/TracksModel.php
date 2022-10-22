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
use Joomla\Component\Mymuse\Site\Model\ProductModel;


/**
 * This model supports retrieving lists of tracks.
 *
 * @package		Joomla.Site
 * @subpackage	com_mymuse
 * @since		1.6
 */
class TracksModel extends ProductsModel
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

	protected $pModel = null;

	public $after_sort = '';

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{

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


		// Load the parameters.
		$params = MymuseHelper::getParams();
		$this->setState('params', $params);

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

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter-search');
		

		$alpha = $this->getUserStateFromRequest($this->context . '.filter.alpha', 'filter_alpha');
		if($alpha){
			$this->setState('filter.search', '');
		}else{
			$this->setState('filter.search', $search);
		}
		$this->setState('filter.alpha', $alpha);

		$featured = $this->getUserStateFromRequest($this->context . '.filter.featured', 'filter_featured', '');
		$this->setState('filter.featured', $featured);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);


		$this->setState('filter.downloadable', "1");

        $parentid = $this->getUserStateFromRequest($this->context . '.filter.parentid', 'filter_parentid');
        $this->setState('filter.parentid', $parentid);

        $trackparentid = $this->getUserStateFromRequest($this->context . '.filter.trackparentid', 'filter_trackparentid');
        $this->setState('filter.trackparentid', $trackparentid);

		$formSubmited = $app->input->post->get('form_submited');

		$access     = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$artistId 	= $this->getUserStateFromRequest($this->context . '.filter.artist_id', 'filter_artist_id');


		$categoryId = $app->input->get('id');
		$this->setState('filter.category_id', $categoryId);

		if ($formSubmited)
		{
			$access = $app->input->post->get('access');
			$this->setState('filter.access', $access);

			$categoryId = $app->input->post->get('category_id');
			$this->setState('filter.category_id', $categoryId);

			$artistId = $app->input->post->get('artist_id');
			$this->setState('filter.artist_id', $artistId);

		}

		// Filter.order
		$orderCol = $app->getUserStateFromRequest($this->context .'.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.title';
		}

		$this->setState('list.ordering', $orderCol);


		$listOrder = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));

		// Set limit for query. If list, use parameter.

		$limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', $params->get('display_num'), 'uint');


		$this->setState('list.limit', $limit);



		// Load the parameters.
		$params = MymuseHelper::getParams();
		$this->setState('params', $params);

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
	 *
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		//$id .= ':'.$this->getState('filter.published');
		$id .= ':tracks';

		return parent::getStoreId($id);
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

		$tracks  = parent::getItems();
		$db 	 = $this->getDbo();
		$params 	= MymuseHelper::getParams();
		$app 		= Factory::getApplication();
		$ordering 	= $this->getState('list.ordering', 'a.title');
		$listDirn	= $this->getState('list.direction', 'ASC');
		$after_sort = '';

		if($ordering == 'file_length'){
			$after_sort = 'file_length';
		}


		// Child tracks formats
		foreach($tracks as $i => $track){
			$format_query = $db->getQuery(true);

			$format_query->select(
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
						$db->quoteName('a.price'),
						$db->quoteName('a.product_discount'),
						$db->quoteName('a.digital'),
						$db->quoteName('a.access'),
						$db->quoteName('a.hits'),
						$db->quoteName('a.product_allfiles'),
					]
				)
			)->from($db->quoteName('#__mymuse_product', 'a'));


				$format_query->where(
					[

						$db->quoteName('a.parentid') . ' = ' . $track->parentid,
						$db->quoteName('a.product_downloadable') . ' = 1',
						$db->quoteName('a.state') . ' = 1',
						$db->quoteName('a.track_parentid') . ' = '.$track->id,
					]
				);
			$db->setQuery($format_query);

			try
			{
				$format_tracks = $db->loadObjectList();
			}
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 'error');
				return;

			}

			
			$track->formats = array();
			$track->digital= array();
			foreach($format_tracks as $ft){
				$ft->digital = json_decode($ft->digital);
				$ft->digital->file_id = $ft->id;
				$track->formats[] = $ft;
				$track->digital[] = $ft->digital;
			}

		} //for each track

		
		// set up previews and streams


		$preview_tracks = array();
		$parent_tracks = array();
		$t = 0;


		if(is_countable($tracks) && count($tracks)){

			$root = JPATH_ROOT.DIRECTORY_SEPARATOR;
			foreach ($tracks as $i => $track) {
				$site_url = MyMuseHelper::getSiteUrl($track->parentid,'1');
				$site_path = MyMuseHelper::getSitePath($track->parentid,'1');

				//set some defaults
				
				if(isset($track->digital[0]->file_type)){
					$track->file_type = $track->digital[0]->file_type;
				}
				if(isset($track->digital[0]->file_length)){
					$track->file_length = $track->digital[0]->file_length;
				}
				if(isset($track->digital[0]->file_time)){
					$track->file_time = $track->digital[0]->file_time;
				}
				if(isset($track->digital[0]->file_downloads)){
					$track->file_downloads = $track->digital[0]->file_downloads;
				}
				
				if(!isset($track->sales) || $track->sales == ''){
					$track->sales = 0;
				}
				//other cats
				$track->othercats = '';
				$othercats = array();
				$query = "SELECT c.id, c.title FROM #__mymuse_product_category_xref as x
				LEFT JOIN #__categories as c ON c.id=x.catid
				WHERE product_id = '".$track->id."' 
				AND catid != ".$track->catid."
				AND catid != ".$track->artistid;
				$db->setQuery($query);
				if($res = $db->loadObjectList()){
					foreach($res as $r){
						$othercats[$r->id] = $r->title;
					}
				}else{
					
				}
				$track->othercats = implode(", ", array_unique($othercats));
				$tracks[$i]->price = ProductModel::getPrice($track);


				if($track->file_preview){
					$preview_tracks[] = $track;
				}else{
					$track->flash= '';
				}

				//$parent_tracks[] = $track;
				
			} // each track


			// some other sort
			if ($after_sort){
				echo "run after sort $after_sort";
				if($listDirn == "desc"){
					usort($tracks, array($this,"cmp_desc_".$after_sort));
				}else{
					usort($tracks, array($this,"cmp_".$after_sort));
				}
				
			}
			$params->set('product_player_type', "single");

			if(count($preview_tracks) && ($params->get('product_player_type') == "each" || 
				$params->get('product_player_type') == "single")){
				$site_url = MyMuseHelper::getSiteUrl($track->parentid,'1');
				$site_path = MyMuseHelper::getSitePath($track->parentid,'1');
				
				reset($preview_tracks);
				$count = count($preview_tracks);
				foreach ($preview_tracks as $i=> $track) {
				
					$flash = '';
					$track->purchased = 0;
					if($track->file_preview){

						$track->path = $site_url.$track->file_preview;
						$track->real_path = $site_path.$track->file_preview;
			
						//should we use the real download file? Not available in AmazonS3
						if( $params->get('storage', 'regular') == 'regular' ){
							$track->download_real_path = MyMuseHelper::getDownloadPath($track->parentid, 1);
							
							if(1 == $params->get('my_download_dir_format',0)){ 
								//downloads by format and we don't know the format
								//$track->download_real_path .= $format.DS;
							}
							if($params->get('my_play_downloads', 0) && in_array($track->id, $myOrders)){
								$track->path = isset($track->download_path)? $track->download_path : '';
								$track->real_path = isset($track->download_real_path)? $track->download_real_path : '';
								$track->purchased = 1;
							}
							if($params->get('my_play_downloads', 0) &&
									(!$track->price["product_price"] || $track->price["product_price"] == "FREE")){
								$track->path = isset($track->download_path)? $track->download_path : '';
								$track->real_path = isset($track->download_real_path)? $track->download_real_path : '';
								$track->purchased = 1;
							}
						}
						//audio or video?
						PluginHelper::importPlugin('mymuse');
						$ext = MyMuseHelper::getExt($track->file_preview);
						$flash = '<!-- Begin Play -->';
						if(isset($track->file_type) && substr_count($track->file_type,"video")){
							//movie
							
							$results = $app->triggerEvent('onPrepareMyMuseVidPlayer',array(&$track,$params->get('product_player_type'),0,0,$i, $count) );

							if(is_array($results) && isset($results[0]) && $results[0] != ''){
								$flash .= $results[0];
							}
							
						}else{
							//audio
							
							$results = $app->triggerEvent('onPrepareMyMuseMp3Player',array(&$track,$params->get('product_player_type'), 0, 0, $i, $count));
							if(is_array($results) && isset($results[0]) && $results[0] != ''){
								$flash .= $results[0];
							}

							
						}
						$flash .= '<!-- End Play -->';

					}else{
						$flash = '';
					}
	
					$track->flash = $flash;
				
				}//end for each preview track 
			} // if count previews for 'each'



			//get player buttons to play previews
			if(count($preview_tracks) && $params->get('product_player_type') == "single"){
				// make a controller for the play/pause buttons
				$results = $app->triggerEvent('onPrepareMyMuseMp3PlayerControl',array(&$preview_tracks) );					
			
				//get the player itself
				/*
				reset($preview_tracks);
				$flash = '';
				$audio = 0;
				$video = 0;
				foreach($preview_tracks as $track){
					if($track->file_preview){
						
						if(substr_count($track->file_type,"video") && !$video){
							//movie

							$results = $app->triggerEvent('onPrepareMyMuseVidPlayer',array(&$track,'singleplayer') );
						
							$video = 1;
								
						}elseif(substr_count($track->file_type,"audio") && !$audio){
							//audio
				
							$results = $app->triggerEvent('onPrepareMyMuseMp3Player',array(&$track,'singleplayer') );
							$audio = 1;
					
						}

						if(is_array($results) && isset($results[0]) && $results[0] != ''){
							$flash .= '<!-- Begin Player -->';
							$flash .= $results[0];
							
							$flash .= '<!-- End Player -->';

						}
						
						$this->_item[$pk]->flash = $flash;
						$this->_item[$pk]->flash_id = $track->id;
						if($this->_item[$pk]->flash_type != "mix"){
							break;
						}elseif($audio && $video){
							break;
						}
						
					}
				}//end for each preview track 
				*/
			}// if count previews for single
			
			
			if(count($preview_tracks) && $params->get('product_player_type') == "playlist"){
				//get the main flash for the product
		
				reset($preview_tracks);
				$this->_item[$pk]->previews = array();
				$audio = 0;
				
				$i = 0;
				$type = "";
				foreach($preview_tracks as $track){
					if($track->file_preview){
						$track->path = $site_url.$track->file_preview;
					}

					$this->_item[$pk]->previews[] = $track;
					if(preg_match("/video/",$track->type)){
						$type = "video";
					}
					if(preg_match("/audio/",$track->type)){
						$type = "audio";
					}
					
				}//end for each preview track 
				
				if($type == "video"){
					// movie
					$flash = '<!-- Begin Player -->';
					$results = $dispatcher->trigger('onPrepareMyMuseVidPlayer',array(&$this->_item[$pk],'playlist') );
					if(isset($results[0]) && $results[0] != ''){
						$flash .= $results[0];
					}
					$flash .= '<!-- End Player -->';
						
				}elseif($type == "audio"){
					
					$flash = '<!-- Begin Player -->';
					$results = $dispatcher->trigger('onPrepareMyMuseMp3Player',array(&$this->_item[$pk],'playlist') );

					if(isset($results[0]) && $results[0] != ''){
						$flash .= $results[0];
					}
					$flash .= '<!-- End Player -->';
				}
				$this->_item[$pk]->flash = $flash;
				$this->_item[$pk]->flash_id = $pk;

			}// if count previews for playlist
			
		}else{
			return array();
		}// if count tracks
		
		
		// free downloads if price = free. NOTE NOT available while using Amazon s3
		if(isset($tracks) && $params->get('my_free_downloads') && $params->get('storage', 'regular') == 'regular' ){
			
			reset($tracks);
			foreach($tracks as $track){
				if($track->product_allfiles){
					continue;
				}
				
				if(count($params->get('my_formats'))) {
					foreach($params->get('my_formats') as $format){
						//make a link for download if we need it
						$track->free_download_link[$format] = "index.php?option=com_mymuse&view=store&task=downloadit&id=".$track->id."&format=".$format->format_value;
						if(1 == $params->get('my_price_by_product', 0) && isset($track->price[$format->format_value]) ){
							$price = $track->price[$format->format_value];
						}else{
							$price = $track->price;
						}
						
						if(!isset($price['product_price']) ||
								$price['product_price'] == "FREE" ||
								!$price['product_price']) {
									foreach($track->digital as $file){
				
										if(isset($file->file_ext) && strtolower($format->format_value) == $file->file_ext){
											
											$track->free_download_link[$file->file_ext] = "index.php?option=com_mymuse&view=store&task=downloadit&id=".$track->id."&format=".$format;
											if($track->access > 1 && !$user->get('id')){
												$view = $params->get('my_registration_redirect', 'login');
												$track->free_download_link = "index.php?option=com_users&view=$view";
												
											}
											$track->free_download = 1;
										}
									}
										
								}
					}
				}else{
					if(!isset($track->price['product_price']) ||
							$track->price['product_price'] == "FREE" ||
							!$track->price['product_price']) {
								$track->free_download_link = "index.php?option=com_mymuse&view=store&task=downloadit&id=".$track->id;
								if($track->access > 1 && !$user->get('id')){
									$view = $params->get('my_registration_redirect', 'login');
									$track->free_download_link = "index.php?option=com_users&view=$view";
								}
								$track->free_download = 1;
							}
								
				}//end formats
					
			}//foreach track
		}//if free downloads
	
			
        $res[0] = $tracks;
        $res[1] = $this->_category;
        $res[2] = $this->getPagination();

		return $res;
	}




	function cmp_file_length($a, $b)
	{
	    if ($a->file_length == $b->file_length) {
	        return 0;
	    }
	    return ($a < $b) ? -1 : 1;
	}
	function cmp_desc_file_length($a, $b)
	{
	    if ($a->file_length == $b->file_length) {
	        return 0;
	    }
	    return ($a > $b) ? -1 : 1;
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

			$this->_category= $categories->get($this->getState('filter.category_id', 'root'));
			$registry = new Registry;
			$registry->loadObject(json_decode($this->_category->params));
			$this->_category->params = $registry;
          
		}

		return $this->_category;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$params 	= MymuseHelper::getParams();
		$app 		= Factory::getApplication();
		$db 		= $this->getDbo();
		$searchword = $this->getState('filter.search','');
		$listDirn	= $this->getState('list.direction', 'ASC');
		$ordering 	= $this->getState('list.ordering', 'a.title');

		if($ordering == 'file_length'){
			//save it for after
			$ordering = 'a.title';
			$this->after_sort = 'file_length';
		}
		if(preg_match("/ASC|DESC/", strtoupper($ordering))){
			$listDirn = '';
		}

		$secondaryOrder = $this->getState('list.secondaryOrder', '');
		

		/** TRACK QUERY */
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
		)->from($db->quoteName('#__mymuse_product', 'a'));

		
		// Join over the product.
		$query->select('p.title AS product_title');
		$query->select('p.alias AS product_alias');
		$query->select('p.hits AS hits');
		$query->join('LEFT', '`#__mymuse_product` AS p ON p.id = a.parentid');


		// Join over the categories.
		$query->select('c.title AS category_title');
		$query->select('c.alias AS category_alias');
		$query->select('c.id AS catid');
		$query->select($db->quoteName('c.language', 'category_language'));
		$query->join('LEFT', '`#__categories` AS c ON c.id = p.catid');
		
		// Join over the artist categories.
		$query->select('art.title AS artist_title');
		$query->select('art.alias AS artist_alias');
		$query->select('art.id AS artistid');
		$query->join('LEFT', '`#__categories` AS art ON art.id = p.artistid');


		// Filter by alpha start title
		$alpha = $this->getState('filter.alpha');

		if (!empty($alpha) && $alpha != "ALL") {
			$alpha = $db->Quote($db->escape($alpha, true).'%');
               $query->where("art.title LIKE $alpha");
		}
		if (empty($alpha)){
			// Filter by search in title
			$search = $this->getState('filter.search');
			if (!empty($search)) {		
				$search = $db->Quote('%'.$db->escape($search, true).'%');
	            $query->where("a.title LIKE $search");
			}
		}

		$query->where(
			[
				$db->quoteName('a.state') . ' = 1',
				$db->quoteName('a.product_downloadable') . ' = 1',
				$db->quoteName('p.state') . ' = 1',
				$db->quoteName('c.published') . ' = 1',
				$db->quoteName('art.published') . ' = 1',
				$db->quoteName('a.track_parentid') . ' = 0'

			]
		);
		//ORDERING
		$orderCol  = $app->getUserStateFromRequest('list.filter_order', 'filter_order', 'a.title', 'string');
		$orderDirn = $app->getUserStateFromRequest('list.filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
		if($orderCol == 'file_length' ){
			$orderCol = '';
		}

		if ($orderCol && $orderDirn) {
		    $query->order($db->escape($orderCol.' '.$orderDirn));
		}
		elseif ($orderCol) {
		    $query->order($db->escape($orderCol));
		}
		

		//echo $db->replacePrefix(($query->__toString())); //exit;

		return $query;

	}

}
