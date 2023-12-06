<?php
/**
 * @version		$Id: helper.php 1574 2015-10-19 00:02:44Z gfisch $
 * @package		mymuse
 * @copyright	Copyright © 2011 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

namespace Joomla\Module\MymuseLatest\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Site\Service\Mymuse;
use Joomla\Component\Mymuse\Site\Helper\QueryHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Administrator\Model\ProductsModel;


// no direct access
defined('_JEXEC') or die('Restricted access');

class MymuseLatestHelper
{

	static function getResults($params)
	{
		$ordering = 'a.id'; 
		$direction = 'desc';
		$order_date = $params->get('order_date');
		$orderby_sec= $params->get('orderby_sec');

		$product_ids = $params->get('product_ids', 0);

		if($product_ids){
        	$ordering = " FIELD(a.id, $product_ids)";
        	$direction = " ";
        	$filter_subcats = '';
        	$max_category_levels = 1;
		}else{
			$ordering = QueryHelper::orderbySecondary($orderby_sec, $order_date );
			$direction = " ";
			$filter_subcats = 'true';
			$max_category_levels = 10;
		}
		$limit = $params->get('display_num',5);

		$cat_id = $params->get('id', 0);


		//echo "ordering = $ordering <br />direction = $direction<br />";
		$model = ListModel::getInstance('Products', 'Mymuse', array('ignore_request' => true));
		/*$form = $model->getFilterForm(array(), true);
		$form->removeField('stage', 'filter');
		$form->removeField('category_id', 'filter');
		$form->removeField('artist_id', 'filter');
		$form->removeField('language', 'filter');
		$form->removeField('author_id', 'filter');
		$this->filterForm = $form;
		*/
		$model->setState('filter.published', 1);
		if($cat_id){
			$model->setState('filter.category_id', $cat_id);
		}
		if($product_ids){
			$model->setState('filter.product_ids', $product_ids);
		}
   		//$model->setState('filter.access', $this->getState('filter.access'));
   		//$model->setState('filter.language', $this->getState('filter.language'));
   		$model->setState('list.ordering', $ordering);
   		//$model->setState('list.start', $this->getState('list.start'));
   		$model->setState('list.limit', $limit);
   		$model->setState('list.direction', $direction);
   		//$model->setState('list.filter', $this->getState('list.filter'));
   		$model->setState('filter.subcategories', $filter_subcats);
   		$model->setState('filter.max_category_levels', $max_category_levels);
   		//$model->setState('list.links', $this->getState('list.links'));
   		
   		//$model->setState('filter.parentid', $id);
		$model->setState('filter.downloadable', 0);
		$model->setState('params', $params);

		if ($limit >= 0) {
			$results= $model->getItems();

			if ($results  === false) {
				echo $model->getError(); exit;
			}
		}
		else {
			$results =array();
		}
	
		
//MymuseHelper::print_pre($results); exit;

		$db 			= Factory::getContainer()->get('DatabaseDriver');
		$jnow			= Factory::getDate();
		$now			= $jnow->toSql();
		$nullDate		= $db->getNullDate();
		$MyMuseStore	=& MyMuse::getObject('store','model');
		$player 		=  $params->get('track_player');
		$root 			= JPATH_ROOT;
		$MMHelper 		= new MymuseHelper;
		$document 		= Factory::getDocument();

		$type = $params->get('type_shown','tracks');
		
		$maximum_shown = $params->get('maximum_shown',5);
		$datenow = Factory::getDate();
		
		$my_artistid = $params->get('my_artistid');
				
	
		for($i=0; $i < count($results); $i++){
			$id = ($results[$i]->parentid)? $results[$i]->parentid : $results[$i]->id;
			$results[$i]->product_link = RouteHelper::getProductRoute($id,$results[$i]->catid );
			$results[$i]->artist_link = RouteHelper::getCategoryRoute($results[$i]->catid);
			$results[$i]->flash = '';
			
            if($params->get('type_shown') == "albums"){
                //get first track to play
                $query = "SELECT file_preview FROM #__mymuse_product WHERE parentid =".$results[$i]->id." ORDER BY ordering ASC";
                $db->setQuery($query);
                if(!$results[$i]->preview = $db->loadResult()){
                    $results[$i]->preview = '';
                }
            } 
			if($params->get('type_shown') == "tracks" && $results[$i]->file_preview){
				$trs = array();
				
				$artist_alias = MymuseHelper::getArtistAlias($results[$i]->parentid,'1');
				$album_alias = MymuseHelper::getAlbumAlias($results[$i]->parentid);
				
					
				$site_url = $params->get('my_use_s3')? $params->get('my_s3web') : preg_replace("#administrator/#","",JURI::base());
				$site_url .= $params->get('my_use_s3')? '' :  $params->get('my_preview_dir');
				$site_url .=  DS.$artist_alias.DS.$album_alias.DS;

				$results[$i]->path = $site_url.$results[$i]->file_preview;
				$results[$i]->real_path = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$results[$i]->file_preview;
				
				if($results[$i]->file_preview_2){
					$results[$i]->path_2 = $site_url.$results[$i]->file_preview_2;
					$results[$i]->real_path_2 = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$results[$i]->file_preview_2;
				}
				if($results[$i]->file_preview_3){
					$results[$i]->path_3 = $site_url.$results[$i]->file_preview_3;
					$results[$i]->real_path_3 = JPATH_ROOT.DS.$params->get('my_preview_dir').DS.$artist_alias.DS.$album_alias.DS.$results[$i]->file_preview_3;
				}

							
				
				$id = $results[$i]->id;

				$results[$i]->flash .= 	 '<ul>
				
				<li id="mod_track_pause_li_'.$id.'" class="jp-pause ui-state-default ui-corner-all"><a id="mod_track_pause_'.$id.'" href="javascript:;" class="jp-pause ui-icon ui-icon-pause" tabindex="1" title="pause">'.Text::_('COM_MYMUSE_PAUSE').'</a></li>
				<li id="mod_track_play_li_'.$id.'" class="jp-play ui-state-default ui-corner-all"><a id="mod_track_play_'.$id.'" href="javascript:;" class="jp-play ui-icon ui-icon-play" tabindex="1" title="play">'.Text::_('COM_MYMUSE_PLAY').'</a></li>
						</ul>';

				$supplied = array();
				$trs[0]['src'] = $results[$i]->path;
				$ext = pathinfo($results[$i]->path, PATHINFO_EXTENSION);
				if($ext == "ogg"){
					$ext = "oga";
				}
				if(!isset($MMHelper->extarray[$ext])){
					continue;
				}
				$trs[0]['type'] = @$MMHelper->extarray[$ext];
				$trs[0]['ext'] = $ext;
					
				$supplied[] = $ext;
					

				if(isset($results[$i]->file_preview_2) && $results[$i]->file_preview_2 != ''){
					$trs[1]['src'] = $results[$i]->path_2;
					$ext = pathinfo($results[$i]->path_2, PATHINFO_EXTENSION);
					if($ext == "ogg"){
						$ext = "oga";
					}
					$trs[1]['type'] = $MMHelper->extarray[$ext];
					$trs[1]['ext'] = $ext;

					if(!in_array($ext,$supplied)){
						$supplied[] = $ext;
					}
				}
				if(isset($results[$i]->file_preview_3) && $results[$i]->file_preview_3 != ''){
					$trs[2]['src'] = $results[$i]->path_3;
					$ext = MymuseHelper::getExt($results[$i]->path_3);
					if($ext == "ogg"){
						$ext = "oga";
					}
					$trs[2]['type'] = $MMHelper->extarray[$ext];
					$trs[2]['ext'] = $ext;

					if(!in_array($ext,$supplied)){
						$supplied[] = $ext;
					}
				}
				$supplied = implode(", ",$supplied);
					
				$media = '';
				foreach($trs as $source){
					$media .= $source['ext'].': "'.$source['src'].'",'."\n";

				}
					
				$media = preg_replace("/,\\n$/","",$media);
				//$media .= ', title: "'.$results[$i]->title.'"';
					
				$results[$i]->title = preg_replace("/\"/","",$results[$i]->title);
				$js = '';


				$js .= '
			jQuery(document).ready(function(){ 
				jQuery("#mod_track_play_'.$results[$i]->id.'").click( function(e) {
					var title = "'.$results[$i]->title.'";
					jQuery("#jp-title-li").html(title);

					jQuery("#mod_track_play_'.$results[$i]->id.'").css("display","none");
					jQuery("#mod_track_play_li_'.$results[$i]->id.'").css("display","none");
					jQuery("#mod_track_pause_'.$results[$i]->id.'").css("display","block");
					jQuery("#mod_track_pause_li_'.$results[$i]->id.'").css("display","block");
					myCirclePlayer.player.jPlayer("setMedia",{ '.$media.' });
					myCirclePlayer.player.jPlayer("play");

					return false;
				});

				jQuery("#mod_track_pause_'.$results[$i]->id.'").click( function(e) {

					jQuery("#mod_track_play_'.$results[$i]->id.'").css("display","block");
					jQuery("#mod_track_play_li_'.$results[$i]->id.'").css("display","block");
					jQuery("#mod_track_pause_'.$results[$i]->id.'").css("display","none");
					jQuery("#mod_track_pause_li_'.$results[$i]->id.'").css("display","none");
					myCirclePlayer.player.jPlayer("stop");
					return false;
				})

			});
				';
				$document->addScriptDeclaration($js);
				$results[$i]->flash .= '<!-- End Player -->';
			}
		}

		return $results;
	}


}
?>
