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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\Component\Mymuse\Administrator\Extension\MymuseComponent;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\IpHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Helper\ShopperHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Site\Helper\QueryHelper;
use Joomla\Component\Mymuse\Site\Service\Mymuse;


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
	 * Note. Calling Trac
	 State in this method will result in recursion.
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
		$params = MymuseHelper::getParams();
		$this->setState('params', $params);

		$user = Factory::getUser();

		// If $pk is set then authorise on complete asset, else on component only
		$asset = empty($pk) ? 'com_mymuse' : 'com_mymuse.product.' . $pk;

		if ((!$user->authorise('core.edit.state', $asset)) && (!$user->authorise('core.edit', $asset)))
		{
			$this->setState('filter.published', MymuseComponent::CONDITION_PUBLISHED);
			$this->setState('filter.archived', MymuseComponent::CONDITION_ARCHIVED);
		}
		$orderCol  = $app->getUserStateFromRequest('list.filter_order', 'filter_order', 'a.title', 'string');
		$orderDirn = $app->getUserStateFromRequest('list.filter_order_Dir', 'filter_order_Dir', 'asc', 'cmd');
		$this->setState('list.filter_order',$orderCol);
		$this->setState('list.filter_order_Dir',$orderDirn);
		$this->setState('list.ordering',$orderCol);
		$this->setState('list.direction',$orderDirn);


		$this->setState('filter.language', Multilanguage::isEnabled());


	}

	/**
	 * Method to get product data.
	 *
	 * @param   integer  $pk  The id of the product.
	 *
	 * @return  object|boolean  Menu item data object on success, boolean false
	 */
	public function getItem($pk = null, $variation=0)
	{
		$user = Factory::getUser();
		$params = MymuseHelper::getParams();
		$app = Factory::getApplication();
		$db = $this->getDbo();

		$pk = (int) ($pk ?: $this->getState('product.id'));

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				
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
							$db->quoteName('a.product_images'),
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
							$query->length($db->quoteName('a.fulltext')) . ' AS ' . $db->quoteName('readmore'),
							$db->quoteName('a.ordering'),
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
							//$db->quoteName('parent.path', 'parent_route'),
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
					->join('LEFT', $db->quoteName('#__mymuse_product', 'parent'), $db->quoteName('parent.id') . ' = ' . $db->quoteName('a.parentid'))
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
					//throw new \Exception($pk.Text::_('COM_MYMUSE_PRODUCT_NOT_FOUND'), 404);
					$app->enqueueMessage(Text::_('COM_MYMUSE_PRODUCT_NOT_FOUND').' '.$pk);
					return;
				}

				// Check for published state if filter set.
				if ((is_numeric($published) || is_numeric($archived)) && ($data->state != $published && $data->state != $archived))
				{
					//throw new \Exception(Text::_('COM_MYMUSE_PRODUCT_NOT_FOUND'), 404);
					$app->enqueueMessage(Text::_('COM_MYMUSE_PRODUCT_NOT_FOUND').' '.$pk);
					return;
				}

				// Convert parameter fields to objects.
				if(isset($data->attribs)){
					$registry = new Registry;
					$registry->loadString($data->attribs);
					$data->attribs = $registry;
				}
				
				if(isset($data->physical) && $data->physical){
					$registry = new Registry;
					$registry->loadString($data->physical);
					$data->physical = $registry;
				}

				if(isset($data->recording)){
					$registry = new Registry;
					$registry->loadString($data->recording);
					$data->recording = $registry;
				}

				$data->params = clone $this->getState('params');
				//$params->merge($registry);

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

				$data->price = $this->getPrice($data, $variation);
				if($data->params->get('my_add_taxes')){
					$data->price["product_price"] = MyMuseCheckout::addTax($data->price["product_price"]);
				}

				if($data->parentid > 0 and $data->product_downloadable == 1){
					$query = "SELECT special_status, product_release_date from #__mymuse_product 
					WHERE id =".$data->parentid;
					$db->setQuery($query);
					$res = $db->loadObject();
					$data->special_status = $res->special_status;
					$data->product_release_date = $res->product_release_date;

				}

				if($data->parentid > 0 and $data->product_physical == 1){
					$data->attributes = $this->getAttributes($data->id);
				}

				if($data->product_downloadable == 1 && $variation){
					$query = "SELECT digital from #__mymuse_product WHERE id=$variation";
					$db->setQuery($query);
					$data->digital = json_decode($db->loadResult());
					$data->digital->file_id = $variation;
				}

				$this->_item[$pk] = $data;

				//other cats
				$othercats = array();

				$query = "SELECT c.title FROM #__mymuse_product_category_xref as x
						LEFT JOIN #__categories as c ON c.id=x.catid
					WHERE product_id = '".$pk."' AND catid != ".$this->_item[$pk]->catid." 
							AND catid !=".$this->_item[$pk]->artistid;
				
				$db->setQuery($query);
				$res = $db->loadObjectList();
				if(count($res)){
					foreach($res as $r){
						$othercats[] = $r->title;
					}
				}
				$othercats = array_unique($othercats);
				$this->_item[$pk]->othercats = $othercats;


				/* get tracks for this product */
				if($this->_item[$pk]->parentid == 0){
					$this->_item[$pk]->tracks = $this->getTracks($pk);
				}


				

				/* get physical items for this product */
				$this->_item[$pk]->items = $this->getPhysicalItems($pk);			

				//get maincategory
				$query = "SELECT * from #__categories WHERE id='".$this->_item[$pk]->catid."'";
				$db->setQuery($query);
				$this->_item[$pk]->category = $db->loadObject();
				$this->_item[$pk]->category->link = RouteHelper::getCategoryRoute($this->_item[$pk]->catid);
				
				//get artist category
				$query = "SELECT c.*, p.title as parent_title from #__categories as c
						LEFT JOIN #__categories as p on p.id = c.parent_id

						WHERE c.id='".$this->_item[$pk]->artistid."'";
				$db->setQuery($query);
				$this->_item[$pk]->artist = $db->loadObject();
				$this->_item[$pk]->artist->link = RouteHelper::getCategoryRoute($this->_item[$pk]->artistid);
				

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



	/*
	* Method to get tracks for the product.
	*
	* @param   integer  $pk  The id of the product.
	*
	* @return  array|boolean  Tracks array on success, boolean false
	*/

	function getTracks($pk)
	{
		//may be coming from tracks model

		if ($this->_item === null)
		{
			$this->_item = array();
			$this->_item[$pk] = new CMSObject;
		}


		$params = MymuseHelper::getParams();
		$app = Factory::getApplication();
		$db = $this->getDbo();
		$alpha 		= $this->getState('list.alpha','');
		$searchword = $this->getState('list.searchword','');
		
		

		$jinput = Factory::getApplication ()->input;
		$after_sort = '';

		if($jinput->get('filter_order') != ''){
			$ordering 	= $this->getState('list.ordering', 'p.ordering');
			$ordering	.= ' '.$this->getState('list.direction', 'ASC');
			if(preg_match("/file_length|file_time|file_downloads/",$ordering)){
				//save it for after
				$after_sort = $ordering;
				$ordering = 'p.ordering';
			}
		}else{
			$active       = $app->getMenu()->getActive();
			$mparams = $active->getParams();
			$orderby_track = $mparams->get('orderby_track', 'alpha');
			$order_track_date = $mparams->get('order_track_date','');
			$ordering = QueryHelper::orderbyProduct($orderby_track, $order_track_date);
		}

		if(preg_match("/ASC|DESC/", strtoupper($ordering))){
			$listDirn = '';
		}

		$secondaryOrder = $this->getState('list.secondaryOrder', '');
		

		/** TRACK QUERY */
		$track_query = $db->getQuery(true);

		$track_query->select(
			$this->getState(
				'item.select',
				[
					$db->quoteName('p.id'),
					$db->quoteName('p.asset_id'),
					$db->quoteName('p.parentid'),
					$db->quoteName('p.track_parentid'),
					$db->quoteName('p.title'),
					$db->quoteName('p.product_sku'),
					$db->quoteName('p.alias'),
					$db->quoteName('p.title_alias'),
					$db->quoteName('p.introtext'),
					$db->quoteName('p.fulltext'),
					$db->quoteName('p.state'),
					$db->quoteName('p.price'),
					$db->quoteName('p.product_discount'),
					$db->quoteName('p.catid'),
					$db->quoteName('p.artistid'),
					$db->quoteName('p.created'),
					$db->quoteName('p.created_by'),
					$db->quoteName('p.created_by_alias'),
					$db->quoteName('p.modified'),
					$db->quoteName('p.modified_by'),
					$db->quoteName('p.checked_out'),
					$db->quoteName('p.checked_out_time'),
					$db->quoteName('p.publish_up'),
					$db->quoteName('p.publish_down'),
					$db->quoteName('p.list_image'),
					$db->quoteName('p.detail_image'),
					$db->quoteName('p.attribs'),
					$db->quoteName('p.physical'),
					$db->quoteName('p.digital'),
					$db->quoteName('p.version'),
					$db->quoteName('p.ordering'),
					$db->quoteName('p.metakey'),
					$db->quoteName('p.metadesc'),
					$db->quoteName('p.metadata'),
					$db->quoteName('p.access'),
					$db->quoteName('p.hits'),
					$db->quoteName('p.product_physical'),
					$db->quoteName('p.product_downloadable'),
					$db->quoteName('p.product_allfiles'),
					$db->quoteName('p.product_release_date'),
					$db->quoteName('p.file_preview'),
					$db->quoteName('p.special_status'),
					$db->quoteName('p.product_in_stock'),
					$db->quoteName('p.recording'),
					$db->quoteName('p.featured'),
					$db->quoteName('p.language'),
				]
			)
		)->from($db->quoteName('#__mymuse_product', 'p'));


		$track_query->where(
			[

				$db->quoteName('p.parentid') . ' = ' . $pk,
				$db->quoteName('p.product_downloadable') . ' = 1',
				$db->quoteName('p.state') . ' = 1',
				$db->quoteName('p.track_parentid') . ' = 0',
			]
		);


		$track_query->order($db->escape($ordering));

		

		//echo $track_query->__toString(); exit;

		$db->setQuery($track_query);
		
		try
		{
			$tracks = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			throw new \Exception($e->getMessage(), 'error');
			return;
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

						$db->quoteName('a.parentid') . ' = ' . $pk,
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

		}



		$site_url = MyMuseHelper::getSiteUrl($pk,'1');
		$site_path = MyMuseHelper::getSitePath($pk,'1');
					
		// set up previews and streams

		$this->_item[$pk]->flash = '';
		$this->_item[$pk]->flash_type = '';
		$preview_tracks = array();
		$parent_tracks = array();
		$t = 0;

		if(count($tracks)){

			$root = JPATH_ROOT.DIRECTORY_SEPARATOR;
			$file_downloads = 0;
			foreach ($tracks as $i => $track) {


				//set some defaults
				$this->_item[$pk]->flash_type = 'audio';
				if(isset($track->digital[0]->file_type)){
					$track->file_type = $track->digital[0]->file_type;
					$this->_item[$pk]->flash_type  = $track->digital[0]->file_type;
					$this->_item[$pk]->file_length = $track->digital[0]->file_length;
				}
				if(isset($track->digital[0]->file_length)){
					$track->file_length = $track->digital[0]->file_length;
				}
				if(isset($track->digital[0]->file_time)){
					$track->file_time = $track->digital[0]->file_time;
				}
				if(isset($track->digital[0]->file_downloads) && $track->digital[0]->file_downloads != ''){
					$file_downloads += $track->digital[0]->file_downloads;
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
				$tracks[$i]->price = $this->getPrice($track);
/*
				//Audio/Video or some horrid mix of both
				if($this->_item[$pk]->flash_type != "mix"){
					if($this->_item[$pk]->flash_type == "audio" && $track->file_type == "video"){
						//oh no it's a mix
						$this->_item[$pk]->flash_type = "mix";
						$track->flash_type = "mix";
					}elseif($this->_item[$pk]->flash_type == "video" && $track->file_type == "audio"){
						//oh no it's a mix
						$this->_item[$pk]->flash_type = "mix";
						$track->flash_type = "mix";
					}else{
						$this->_item[$pk]->flash_type = $track->file_type;
						$track->flash_type = $track->file_type;
					}
				}else{
					$track->flash_type = "mix";
				}
*/			
				$this->_item[$pk]->flash_type = 'audio';

				if($track->file_preview){
					$preview_tracks[] = $track;
				}else{
					$track->flash= '';
				}

				//$parent_tracks[] = $track;
				
			} // each track

			if(isset($track->digital[0])){
				$track->digital[0]->file_downloads = $file_downloads;
			}




			//$tracks = $parent_tracks;
			$params->set('product_player_type', "single");
			//$params->set('product_player_type', "single");

			if(count($preview_tracks) && ($params->get('product_player_type') == "each" || 
				$params->get('product_player_type') == "single")){
				
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
				//MymuseHelper::print_pre($track); exit;
				}//end for each preview track 
			} // if count previews for 'each'



			// some other sort
			if ($after_sort){
				if($listDirn == "desc"){
					usort($tracks, array($this,"cmp_desc_".$after_sort));
				}else{
					usort($tracks, array($this,"cmp_".$after_sort));
				}
				
			}
			

			//get player buttons to play previews
			if(count($preview_tracks) && $params->get('product_player_type') == "single"){
				// make a controller for the play/pause buttons
				$results = $app->triggerEvent('onPrepareMyMuseMp3PlayerControl',array(&$preview_tracks) );					
			
				//get the player itself
				reset($preview_tracks);
				$flash = '';
				$audio = 0;
				$video = 0;
				foreach($preview_tracks as $track){
					if($track->file_preview){
						$track->file_type = isset($track->digital[0]->file_type) ? $track->digital[0]->file_type : 'audio';
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
		}//isset tracks
			
		/*
		 * $track->download_path = MYmUseHelper::getDownloadPath($track->product_id, 1);
									if(1 == $params->get('my_download_dir_format',0)){ //downloads by format
										$track->download_path .= $format.DS;
									}
		 */
		
		//end of tracks

		return $tracks;

	}

	/*
	* Method to get physical items for the product.
	*
	* @param   integer  $pk  The id of the product.
	*
	* @return  array|boolean  Items array on success, boolean false

	*/

	function getPhysicalItems($pk)
	{

		$params = MymuseHelper::getParams();
		$app = Factory::getApplication();
		$db = $this->getDbo();

		/* PHYSICAL CHILD ITEMS  PHYSICAL CHILD ITEMS PHYSICAL CHILD ITEMS PHYSICAL CHILD ITEMS  */
		$query = "SELECT * FROM #__mymuse_product as p
		WHERE p.parentid='".$pk."'
		AND product_downloadable = 0
		and state=1
		ORDER BY ordering
		";
		$db->setQuery($query);
		$items = $db->loadObjectList();

		
		//attributes
		$query = 'SELECT * from #__mymuse_product_attribute_sku WHERE 
		product_parent_id = '.$this->_item[$pk]->id.'
		ORDER BY ordering';
		$db->setQuery($query);
		$this->_item[$pk]->attribute_sku = $db->loadObjectList();
		

		foreach ($items as $i => $item) {
			foreach($this->_item[$pk]->attribute_sku as $a_sku){
				$query = 'SELECT attribute_value from #__mymuse_product_attribute WHERE product_id='.$item->id.'
				AND product_attribute_sku_id='.$a_sku->id;
				$db->setQuery($query);
				$items[$i]->attributes[$a_sku->name] = $db->loadResult();
			}
		
				
			$query = 'SELECT a.*,b.name from #__mymuse_product_attribute as a 
			LEFT JOIN #__mymuse_product_attribute_sku as b on b.id=a.product_attribute_sku_id
			WHERE a.product_id='.$item->id;
			
			$db->setQuery($query);
			$tmp[$item->id] = $db->loadObjectList();
			foreach($tmp[$item->id] as $att){
				$attributes[$item->id][$att->product_attribute_sku_id] = $att;
			}

			$items[$i]->price = $this->getPrice($item);
			if($params->get('my_add_taxes')){
				$items[$i]->price["product_price"] = MyMuseCheckout::addTax($items[$i]->price["product_price"]);
			}
			

		}
							
		
		return $items;

	}


   /**
     * getPrice
     * 
     * @param object $product
     * @param integet $var
     * @return mixed Array or false: array [product_price] [special_shoppergroup] [product_discount] [product_shoppergroup_discount]
     */
	static function getPrice(&$product, $variation=0) {

		$params 		= MyMuseHelper::getParams();
		$shopper 		= Mymuse::getObject('Shopper','model')->getShopper();
		if(is_array($product->price)){
			//return ($product->price); 
		}
		$db	= Factory::getDBO();
		$shoppergroup_discount = 0;
		$discount = 0;
		$price_info = array();
		$price_info["item"]=false;
		$default_shoppergroup_id = $params->get("my_default_shoppergroup_id",1);
		$product_id = $product->id;
		$original_price = $product->price;
		// Get the product_parent_id for this product/item
		$product_parent_id = 0;
		if(isset($product->product_id)){
			$product_parent_id = $product->product_id;
			if($product_parent_id > 0){
				$price_info["item"]=true;
			}
		}  

		// Get the shopper group id for this shopper
		$shoppergroup_id = @$shopper->shoppergroup->id;
		if($shoppergroup_id == ""){
			$shoppergroup_id = $default_shoppergroup_id;
			$q = "SELECT * FROM #__mymuse_shopper_group WHERE  \n";
			$q .= "id='";
			$q .= $shoppergroup_id . "'";

			$db->setQuery($q);
			$shopper->shoppergroup = $db->loadObject();
		}
		
		$shoppergroup_discount = $shopper->shoppergroup->discount;
		
		// Get the product_parent_id for this product/item
		$product_parent_id = 0;

		$price_info ["product_price"] = $product->price;


		
		if (0 == $params->get ( 'my_price_by_product' )) {
			// price by track
			$price_info ["product_price"] = $product->price;
			
		} elseif (1 == $params->get ( 'my_price_by_product' )) {

			$id = $product->id;

			if (isset($product->parentid)) {
				$id = ($product->parentid > 0)? $product->parentid : $product->id;
			}
		
			$query = "SELECT attribs FROM #__mymuse_product WHERE id='" . $id . "'";
			$db->setQuery ( $query );
			if (! $product->attribs = $db->loadResult ()) {
				$price_info ["product_price"] = $product_price = $product->price;
			}
			
			$registry = new Registry ();
			$registry->loadString ( $product->attribs );
			$product->attribs = $registry;

			if($variation){
				$query = "SELECT digital FROM #__mymuse_product WHERE id='" . $variation . "'";
				$db->setQuery ( $query );
				if (! $product->digital = $db->loadResult ()) {
					$price_info ["product_price"] = $product_price = $product->price;
				}
				
				$product->digital = json_decode($product->digital);
				$product->digital->file_id = $variation;

			}

			if (isset($product->product_physical) && $product->product_physical) {
		
				$key = 'product_price_physical';
				$product->price = $product->attribs->get ( $key );
				if($product->price){
					$price_info ["product_price"] = $product->price;
				}
				//override if individueally priced
				if($original_price > 0){
					//$price_info ["product_price"] = $original_price;
				}
				
			} elseif (isset($product->product_allfiles) && $product->product_allfiles) {
		
				//$key = 'product_price_' . $product->ext . '_all';
				//$product->price = $product->attribs->get ( $key );
				//$price_info ["product_price"] = $product->price;
				$formats = array();
				$pformats = $params->get('my_formats', array());
				foreach($pformats as $i => $f){
					$formats[$f->ordering] = strtolower($f->format_key);
				}
				if(isset($product->digital->file_format)){
					$formats = array();
					$formats[0] = $product->digital->file_format;
				}

				if(isset($product->digital) && is_object($product->digital) ){


				}

				foreach($formats as $format) {
						
					$key = 'product_price_' . $format . '_all';
				
					$price_info [$format]["product_price"] = $product->attribs->get ( $key );
					$product_price = $price_info [$format]["product_price"];
					$price_info[$format]["product_original_price"] = round ( $price_info [$format]["product_price"], 2);
						
					$price_info [$format]["product_shoppergroup_discount"] = $shoppergroup_discount;
					$price_info [$format]["product_shoppergroup_discount_amount"] = $product_price * $shoppergroup_discount / 100;
					$price_info [$format]["product_shoppergroup_discount_amount"] = round ( $price_info [$format] ["product_shoppergroup_discount_amount"], 2 );
						
					$discount = isset($product->product_discount)? $product->product_discount : '';
					$price_info[$format]["product_discount"] = $discount;
						
					$price_info[$format]["product_price"] = $price_info [$format]["product_price"] - ($product_price * $shoppergroup_discount/100) - $discount;
					$price_info [$format]["product_price"] = round ( $price_info [$format]["product_price"], 2 );
				}
				if(isset($product->digital->file_format)){
					$price_info = $price_info [$format];
				}
				
				//$price_info = $price_info [$format];

				return $price_info;
				
			} elseif(isset($product->digital) && is_array($product->digital)
				&& is_countable($product->digital) && count($product->digital) ){
	

				$formats = array();
				$pformats = $params->get('my_formats', array());
				foreach($pformats as $i => $f){
					$formats[$f->ordering] = strtolower($f->format_key);
				}
				foreach($formats as $format) {
			
					$key = 'product_price_' . $format;
	
					$price_info [$format]["product_price"] = $product->attribs->get ( $key );
					$product_price = $price_info [$format]["product_price"];
					$price_info[$format]["product_original_price"] = round ( $price_info [$format]["product_price"], 2);
			
					$price_info [$format]["product_shoppergroup_discount"] = $shoppergroup_discount;
					$price_info [$format]["product_shoppergroup_discount_amount"] = $product_price * $shoppergroup_discount / 100;
					$price_info [$format]["product_shoppergroup_discount_amount"] = round ( $price_info [$format] ["product_shoppergroup_discount_amount"], 2 );
			
					$discount = isset($product->product_discount)? $product->product_discount : '';
					$price_info[$format]["product_discount"] = $discount;
			
					$price_info[$format]["product_price"] = $price_info [$format]["product_price"] - ($product_price * $shoppergroup_discount/100) - $discount;
					$price_info [$format]["product_price"] = round ( $price_info [$format]["product_price"], 2 );	

	
				}

				return $price_info;

			}elseif(isset($product->digital) && is_object($product->digital) ){


				$format = strtolower($product->digital->file_format);
				$key = 'product_price_' . $format;
				
				$price_info [$format]["product_price"] = $product->attribs->get ( $key );
				$product_price = $price_info [$format]["product_price"];
				$price_info[$format]["product_original_price"] = round ( $price_info [$format]["product_price"], 2);
				
				$price_info [$format]["product_shoppergroup_discount"] = $shoppergroup_discount;
				$price_info [$format]["product_shoppergroup_discount_amount"] = $product_price * $shoppergroup_discount / 100;
				$price_info [$format]["product_shoppergroup_discount_amount"] = round ( $price_info [$format] ["product_shoppergroup_discount_amount"], 2 );
				
				$discount = isset($product->product_discount)? $product->product_discount : '';
				$price_info[$format]["product_discount"] = $discount;
				
				$price_info[$format]["product_price"] = $price_info [$format]["product_price"] - ($product_price * $shoppergroup_discount/100) - $discount;
				$price_info [$format]["product_price"] = round ( $price_info [$format]["product_price"], 2 );	
				$price_info = $price_info [$format];

				return $price_info;


			} else {
				$price_info ["product_price"] = $product->price;
			}
			$product_price = $price_info ["product_price"];

			$price_info["product_original_price"] = round ( $price_info ["product_price"], 2);

			$price_info ["product_shoppergroup_discount"] = $shoppergroup_discount;

			if(is_numeric($product_price) ){
				$price_info ["product_shoppergroup_discount_amount"] = (float) $product_price * $shoppergroup_discount / 100;
				$price_info ["product_shoppergroup_discount_amount"] = round ( $price_info ["product_shoppergroup_discount_amount"], 2 );

			}else{
				$price_info ["product_shoppergroup_discount_amount"] = 0.00;
			}
			
			
			$discount = $product->product_discount;
			$price_info["product_discount"] = $discount;
			if(is_numeric($product_price) ){
				$price_info["product_price"]= $price_info ["product_price"] - ( (float) $product_price * $shoppergroup_discount / 100) - $discount;
				$price_info ["product_price"] = round ( $price_info ["product_price"], 2 );	
			}else{
				$price_info ["product_price"] = 0.00;
			}
			
			return $price_info;
			
		} elseif (2 == $params->get ( 'my_price_by_product' ) && 1 != $product->product_physical) {
			// price by licence
			
			$session = Factory::getSession ();
			$jinput = Factory::getApplication ()->input;
			$my_licence = $jinput->get ( 'my_licence', $session->get ( "my_licence", 0 ) );
			if (! $session->get ( "cart", 0 )) {
				self::$cart = array ();
				self::$cart ["idx"] = 0;
			} else {
				self::$cart = $session->get ( "cart" );
			}
			
			$session = Factory::getSession ();
			$my_licence = $jinput->get ( 'my_licence', $session->get ( "my_licence", 0 ) );
			
			$price_info ["product_price"] = $params->get ( 'my_license_' . $my_licence . '_price' );
			for($i = 0; $i < 5; $i ++) {
				if (null != $params->get ( 'my_license_' . $i . '_name' ) && null != $params->get ( 'my_license_' . $i . '_price' )) {
					$price_info ['licence'] [$i] ['name'] = $params->get ( 'my_license_' . $i . '_name' );
					$price_info ['licence'] [$i] ['price'] = $params->get ( 'my_license_' . $i . '_price' );
				}
			}
			$product->price = $price_info ["product_price"];
			$product_price = $product->price;
			$price_info ["product_shoppergroup_discount"] = $shoppergroup_discount;
			$price_info ["product_shoppergroup_discount_amount"] = (float) $product_price * $shoppergroup_discount / 100;
			
			$price_info ["product_price"] = round ( $price_info ["product_price"], 2 );
			$price_info ["product_shoppergroup_discount_amount"] = round ( $price_info ["product_shoppergroup_discount_amount"], 2 );
			
			// DISCOUNTS FROM PLUGINS
			PluginHelper::importPlugin ( 'mymuse' );
			$dispatcher = JDispatcher::getInstance ();
			$result = $dispatcher->trigger ( 'onCalculatePrice', array (
					&$price_info,
					self::$cart 
			) );
			if (count ( $result )) {
				// MymuseHelper::print_pre($price_info);
			}
		
			return $price_info;
		}
			
		//}

		$product_price = (float) $product->price;
		
		// see if this product has a discount
		$discount = $product->product_discount;
		
		// IT'S FOR A SPECIAL SHOPPER GROUP?
		if($shoppergroup_id != $default_shoppergroup_id){

			if ($product_price && $product_price > 0) {
				$price_info["special_shoppergroup"] = True;
				$price_info["product_original_price"] = $product_price;
				$price_info["product_price"] = $product_price - ($product_price*$shoppergroup_discount/100)-$discount;
				$price_info["product_price"] = round($price_info["product_price"],2);
				$price_info["product_discount"] = $discount;
				$price_info["product_shoppergroup_discount"] = $shoppergroup_discount;
				$price_info["product_shoppergroup_discount_amount"] = $product_price*$shoppergroup_discount/100;
				
				$price_info["product_price"] = round($price_info["product_price"],2);
				$price_info["product_shoppergroup_discount_amount"] = round($price_info["product_shoppergroup_discount_amount"],2);
				
				return $price_info;
			}
		}


		// MAYBE IT'S AN ITEM, FIRST SEE IF IT HAS ITS OWN PRICE
		if(isset($product_parent_id) && $product_parent_id > 0){

			//if (isset($product_price) && $product_price > 0) {
			if (isset($product_price)) {
				$price_info["product_original_price"] = $product_price;
				$price_info["product_price"]=$product_price - ($product_price*$shoppergroup_discount/100)-$discount;
				$price_info["product_discount"] = $discount;
				$price_info["product_shoppergroup_discount"] = $shoppergroup_discount;
				$price_info["product_shoppergroup_discount_amount"] = sprintf("%.2f",$product_price*$shoppergroup_discount/100);
				//$price_info["product_price"] = sprintf("%.2f",$price_info["product_price"]);
				if($price_info["product_price"] == 0.00){
					$price_info["product_price"] = 0;
				}
				$price_info["product_price"] = round($price_info["product_price"],2);
				$price_info["product_shoppergroup_discount_amount"] = round($price_info["product_shoppergroup_discount_amount"],2);
				//MymuseHelper::print_pre($price_info);
				return $price_info;
			}
		}


		// Get default price
		if ($product_price && $product_price > 0) {

			$price_info["default"] = True;
			$price_info["product_original_price"] = $product_price;
			$price_info["product_price"]= $product_price - ($product_price * $shoppergroup_discount/100) - $discount;
			$price_info["product_discount"] = $discount;
			$price_info["product_shoppergroup_discount"] = $shoppergroup_discount;
			$price_info["product_shoppergroup_discount_amount"] = sprintf("%.2f",$product_price*$shoppergroup_discount/100);
			
			$price_info["product_price"] = round($price_info["product_price"],2);
			$price_info["product_shoppergroup_discount_amount"] = round($price_info["product_shoppergroup_discount_amount"],2);

			//MymuseHelper::print_pre($price_info); 
			return $price_info;
		}


		
		// No price found, must be FREE
		$price_info["default"] = True;
		$price_info["product_original_price"] = $product_price;
		$price_info["product_price"]=$product_price - ($product_price*$shoppergroup_discount/100)-$discount;
		$price_info["product_discount"] = $discount;
		$price_info["product_shoppergroup_discount"] = $shoppergroup_discount;
		$price_info["product_shoppergroup_discount_amount"] = sprintf("%.2f",$product_price*$shoppergroup_discount/100);
		
		$price_info["product_price"] = round($price_info["product_price"],2);
		$price_info["product_shoppergroup_discount_amount"] = round($price_info["product_shoppergroup_discount_amount"],2);
		
		return $price_info;
		
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

	/**
     * getAttributes
     * 
     * @param int $item_id
     * @param int $product_id
     * @param string $attribute_name
     * @return object
     */
	function getAttributes($item_id="",$product_id="",$attribute_name="") {

	  	$db = Factory::getDBO();
	    if ($item_id and $product_id) {
	      $q  = "SELECT * FROM #__mymuse_product_attribute as pa, #__mymuse_product_attribute_sku as pas  \n";
	      $q .= "WHERE pa.product_id = '$item_id'  \n";
	      $q .= "AND pas.product_parent_id =$product_id  \n";
	      if ($attribute_name) {
	        $q .= "AND pa.attribute_name"." = '$attribute_name'  \n";
	      }
	      $q .= "AND pa.product_attribute_sku_id=pas.id  \n";
	      $q .= "ORDER BY ordering, pa.attribute_name  \n";
	    } elseif ($item_id) {
	      $q  = "SELECT * FROM #__mymuse_product_attribute  \n";
	      $q .= "WHERE product_id = $item_id ";
	      if ($attribute_name) {
	        $q .= "AND attribute_name = '$attribute_name'  \n";
	      }
	    } elseif ($product_id) {
	      $q  = "SELECT * FROM #__mymuse_product_attribute_sku  \n";
	      $q .= "WHERE product_parent_id =$product_id  \n";
	      if ($attribute_name) {
	        $q .= "AND #__mymuse_product_attribute.attribute_name = '$attribute_name'  \n";
	      }
	      $q .= " ORDER BY ordering,attribute_name \n";
	      //$q .= " ORDER BY attribute_list ";
	    } else {
	      $this->error = Text::_("MYMUSE_ERROR_GET_ATTRIBUTE");
	      return false;
	    }

		$db->setQuery($q);
		$res = $db->loadObjectList();
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
	  //file_time|file_downloads

	/**
	    * getRecommended
	  	*
	  	* For individual product (not for cart)
	*/
	function getRecommended()
	{
		$db 		= Factory::getDBO();
		$params 	= MyMuseHelper::getParams();
		$prods 		= array();
		$recommends = array();
		$productid 	= $this->getState('product.id');
		$product 	= $this->_item[$productid];
		$cats[]		= $product->catid;
		$prods 		= array();

		if(!$params->get('show_recommends', 0)){
			return $recommends;
		}

		$query = "SELECT * FROM #__mymuse_product_recommend_xref
				WHERE product_id = '".$productid."'";

		$db->setQuery($query);
		$res = $db->loadObjectList();
		if(count($res)){
			foreach($res as $r){
				$prods[] = $r->recommend_id;
			}
		}
	
		$prods = array_unique($prods);
		$prodsin = implode(",",$prods);

		if($prodsin){
	  		//get the products
	  		$query = "SELECT id, title, catid, artistid, list_image, product_release_date FROM #__mymuse_product
			WHERE id IN ($prodsin) 
	  		AND id != $productid
	  		AND parentid = 0
	  		ORDER BY  product_release_date DESC 
	  		LIMIT ".$params->get('my_max_recommended', 4);


	  		$db->setQuery($query);
	  		$recommends = $db->loadObjectList();

	  		$cat = $params->get('my_product_link', 'catid');

	  		for($i = 0; $i<count($recommends); $i++){
	  			$recommends[$i]->url = RouteHelper::getProductRoute ( $recommends[$i]->id, $recommends[$i]->$cat);
	  			$recommends[$i]->cat_url = RouteHelper::getCategoryRoute ( $recommends[$i]->$cat  );
	  		}
		}

		if(!count($recommends) && $params->get('show_category_recommends',0)){
	  		//other cats
	  		$query = "SELECT * FROM #__mymuse_product_category_xref
					WHERE product_id = '".$productid."'";
	  		$db->setQuery($query);
	  		$res = $db->loadObjectList();
	  		if(count($res)){
	  			foreach($res as $r){
	  				$cats[] = $r->catid;
	  			}
	  		}
	  		$cats = array_unique($cats);
	  		$catsin = implode(",",$cats);

		  		$query = "SELECT * FROM #__mymuse_product_category_xref
						WHERE catid IN ($catsin)
						AND product_id != $productid";
	
		  		$db->setQuery($query);
		  		$res = $db->loadObjectList();
		  		if(count($res)){
		  			foreach($res as $r){
		  				$prods[] = $r->product_id;
		  			}
		  			$prods = array_unique($prods);
					$prodsin = implode(",",$prods);
		  		}else{
		  			$prodsin = $productid;
		  		}

	  		if($catsin){
	  			//get the products
		  		$query = "SELECT id, title, catid, list_image, product_release_date FROM #__mymuse_product
				WHERE ( catid IN ($catsin) OR id IN ($prodsin) )
				AND product_downloadable != 1
		  		AND id != $productid
		  		AND state = 1
		  		AND parentid = 0
		  		ORDER BY FIELD(catid, $catsin), product_release_date DESC 
		  		LIMIT ".$params->get('my_max_recommended', 4);
	
		  		$db->setQuery($query);
		  		$recommends = $db->loadObjectList();

		  		for($i = 0; $i<count($recommends); $i++){
	  				$recommends[$i]->url = RouteHelper::getProductRoute ( $recommends[$i]->id, $recommends[$i]->catid );
	  				$recommends[$i]->cat_url = RouteHelper::getCategoryRoute ( $recommends[$i]->catid  );
	  			}
	  		}
		}
		return $recommends;
	}
}
