<?php
/**
 * @version     $Id: $
 * @package     mymuse
 * @copyright   Copyright © 2021 - Arboreta Internet Services - All rights reserved.
 * @license     GNU/GPL
 * @author      Gordon Fisch
 * @author email    info@mymuse.ca
 * @website     http://www.mymuse.ca
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;

/**
* MyMuse Audio Amplitude plugin
*
* @package      MyMuse
* @subpackage   mymuse
*/
class plgMymuseAudio_amplitude extends CMSPlugin 
{

    /**
     * Database object
     *
     * @var    JDatabaseDriver
     * @since  4.4
     */
    protected $db;

    /**
     * Language
     *
     * @var    language
     * @since  4.4
     */
    protected $language;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.9.0
     */
    protected $autoloadLanguage = true;


    var $playlist = array();
    var $indexes = array();
    var $allcats = array();
    
    public $catalogs = array ();
    public $_playlist = null;
    

    function __construct(&$subject, $config)  {

        parent::__construct($subject, $config);

    }

    public function onGetPlaylist($load_js = true){
        $arr = $this->getPlaylist();
        return $arr[2];
    }
    /**
     * getPlaylist
     * Gets playlist for amplitute player and creates two arrays to do indexing and printing
     * loads javascript playlist for amplitude
     *
     * @return array
     */
    public function getPlaylist($load_js = true){
        if(!$this->_playlist){


            $db = Factory::getDBO();
            $mycategories           = $this->params->get('mycategories', array());

            foreach($mycategories as $key => $val){
                $alias = '';
                $query = "SELECT alias from #__categories WHERE id=$val";
                $db->setQuery($query);
                if($alias = $db->loadResult()){
                    $this->catalogs[$val] = $alias.'.js';
                }
            }
            $preview_path   = $this->params->get('preview_path', '/media/com_mymuse/previews/');
            $playlist_path  = $this->params->get('playlist_path', '/media/com_mymuse/playlists/');
            $site_url       = URI::root();
            $document       = Factory::getDocument();
            $app            = Factory::getApplication();
            $menu           = $app->getMenu();
            $front          = 0;
            $jinput         = $app->input;
            $option         = $jinput->get('option');

            if ($menu->getActive() == $menu->getDefault()) {
                $front = 1;
            }
        /*
            [language] => en-GB
            [option] => com_mymuse
            [view] => product
            [Itemid] => 132
            [id] => 1
            [catid] => 14
        */
            if($jinput->get('view') == "category" && null !== $jinput->get('id') && array_key_exists($jinput->get('id'),$this->catalogs)){
                $filename = $this->catalogs[$jinput->get('id')];
            }elseif($jinput->get('view') == "product" && null !== $jinput->get('id') && $jinput->get('catid')){
                $filename = $this->catalogs[$jinput->get('catid')];
            }elseif($front){
                $filename = "homepage.js";
            }else{
                $filename = "catalog.js";
            }

            if(!$filename){
                $filename = "catalog.js";
            }

        //echo "Using playlist: ".$filename. " view = ".$jinput->get('view'). " id = ".$jinput->get('id');
            $path = JPATH_ROOT . $playlist_path . $filename;
            $js_path = $site_url . $playlist_path . $filename;
            if (! file_exists ( $path )) {
                $path = JPATH_ROOT . $playlist_path."catalog.js";
                $js_path = $site_url . $playlist_path."catalog.js";
            }
         
            if($jinput->get('tmpl','') != "component" && $load_js){
               // HTMLHelper::_('script', $js_path, ['version' => 'auto', 'relative' => true], ['type' => 'module']);
            }
        //echo $path; exit;
            $playlist = file_get_contents ( $path );
            $playlist = preg_replace ( "~.*?Amplitude.init\(~", "", $playlist );
            $playlist = preg_replace ( "~\);$~", "", $playlist );
            $playlist = preg_replace ( "~//.*\\n~", "", $playlist );
            $playlist = preg_replace ( "~],~", "]", $playlist );
            
            $playarray = json_decode ( $playlist, true );
            if (! $playarray) {
                echo "<br /><br />".MymuseHelper::getJsonError()."<br /><br />";
                echo "<br /><br />".$playlist."<br /><br />";
            }
            
            $new_arr = array ();
            foreach ( $playarray ['songs'] as $index => $song ) {
                $new_arr [$song ['url']] = $index;
            }
            $arr[0] = $new_arr;
            $arr[1] = $playarray ['songs'];
            $arr[2] = $js_path;
            $this->_playlist = $arr;
        }

        return $this->_playlist;
    }

    /**
     * onPrepareMyMuseMp3Player
     * 
     */
    function onPrepareMyMuseMp3Player(&$track, $type='single', $height=0, $width=0, $index=0, $count=0)
    {

        $arr            = $this->getPlaylist();
        $this->indexes  = $arr[0];
        $this->playlist = $arr[1];

        $document       = Factory::getDocument();
        $match          = 0;
        $site_url       = rtrim( JURI::root(), '/');
        $params         = MyMuseHelper::getParams();
        $preview_path   = $this->params->get('preview_path', 'media/com_mymuse/previews/');

        if($type == 'singleplayer' || $type == 'single'){
            $id = 1;
        }else{
            $id = $index + 1;
        }

        if($type == 'singleplayer'){
            return '';
        }

        //SINGLE PLAYER MAKE PLAY BUTTONS//
        if($type=='single'){
            //get index
            $one_dir = $params->get('my_previews_in_one_dir',1);
            if(!$one_dir){
                $artist_alias = MymuseHelper::getArtistAlias($track->id);
                $album_alias = MymuseHelper::getAlbumAlias($track->id);
                $preview = $site_url.$preview_path.$artist_alias.'/'.$album_alias.'/'.$track->file_preview;
            }else{
                $preview = $site_url.$preview_path.$track->file_preview;
            }

            if(isset($this->indexes[$preview])){
                $index = $this->indexes[$preview];
            }else{
                $index = '0';
            }
            

            $html = '
<div class="amplitude-song-container">

    <div class="amplitude-song-container amplitude-play-pause" data-amplitude-song-index="'.$index.'">
        <div class="play-pause" amplitude-main-play-pause="true"></div>
        <div class="playlist-meta">
            <div class="now-playing-title" style="display:none;">'.$this->playlist[$index]['name'].'</div>
            <div class="album-information" style="display:none;">'.$this->playlist[$index]['artist'].'</div>
        </div>
    </div>
</div>
';

            return $html;
            
            
        }
        $document->addScript( $js_path );
        
        if($type == 'playlist'){
            return '';
        }
        
    }
    
    function onPrepareMyMuseMp3PlayerControl(&$tracks)
    {

        return true;
    }
    
    function onMyMuseAfterSave()
    {

        jimport('joomla.filesystem.file');
 
        if(!$mycategories           = $this->params->get('mycategories', array())){
            $text  = "Please set your categories in the Plugin Audio Amplitude";
            return $text;
        }

        $path_to_previews       = $this->params->get('preview_path', false);
        $first_album_art_path   = $this->params->get('first_album_art_path', '/media/com_mymuse/images/mymuse-180x180.png ');
        $playlist_path          = $this->params->get('playlist_path', '/media/com_mymuse/playlists/');
        $preview_path           = $this->params->get('preview_path', '/media/com_mymuse/previews/');

        $mymuseparams = MymuseHelper::getParams();
        $onedir = $mymuseparams->get('my_previews_in_one_dir',0);



        $text       = '';
        $db         = Factory::getDBO();
        $top_cat    = $mycategories[0];
        $query      = "SELECT id, alias from #__categories WHERE parent_id=$top_cat";


        $db->setQuery($query);
        $res        = $db->loadObjectList();
        $root_uri = URI::root();
        $root_uri = rtrim($root_uri,'/');
        
        $first                  = new \StdClass;
        $first->name            = " ";
        $first->artist          = "PLAYER";
        $first->album           = "READY";
        $first->url             = $root_uri.$path_to_previews."00_-_silence.mp3";
        $first->cover_art_url   = $root_uri.$first_album_art_path;

        $all                    = array();
        $all['songs'][]         = $first;
        $allcats                = array();
        
        foreach($res as $r){
            $filename = $r->alias.".js";
            $text .= "Making list for $filename <br />";
            $arr = array();
            $arr['songs'][] = $first;
            //see if they have children
            $catin = array();
            $catin[] = $r->id;
            $allcats[] = $r->id;
            $child_query = "SELECT id, alias from #__categories WHERE parent_id=".$r->id;
            $db->setQuery($child_query);
            if($childres = $db->loadObjectList()){
                foreach($childres as $child){
                    $catin[] = $child->id;
                    $allcats[] = $child->id;
                }
            }

            $track_query = $this->_getQuery($catin);
            //echo $track_query; exit;
            $db->setQuery($track_query);
    
            if($tracks = $db->loadObjectList()){
                $i = 0;
                foreach ($tracks as $track){
                    if(!$onedir){
                        $path = JPATH_ROOT.$preview_path.$track->artist_alias.'/'.$track->album_alias.'/'.$track->url;
                        $track->url = $root_uri.$preview_path.$track->artist_alias.'/'.$track->album_alias.'/'.$track->url;
                    }else{
                        $path = JPATH_ROOT.$preview_path.'/'.$track->url;
                        $track->url = $root_uri.$preview_path.$track->url;
                    }
                    unset($track->track_parentid);
                    unset($track->album_alias);
                    unset($track->artist_alias);
            
                    if(!file_exists($path)){
                        //echo "$i ".$path."<br />\n";
                        $i++;
                    }
                    $arr['songs'][] = $track;
                }
    
                $jstring = "Amplitude.init(".json_encode($arr).");";
                $jstring = preg_replace("~,~",",\n",$jstring);
                $jstring = preg_replace("~\[~","[\n",$jstring);
                $jstring = preg_replace("~\{~","{\n",$jstring);
                $jstring = preg_replace("~\},~","\n},",$jstring);
                $jstring = preg_replace("~\}\]\}\)~","\n}\n]\n})",$jstring);
    
                if($fh = fopen(JPATH_ROOT.$playlist_path.$filename, "w")){
                    fwrite($fh,$jstring);
                    fclose($fh);
                }
                //print_pre($jstring);
                //echo "<br />";
            }else{
                $text .= "No tracks for $filename <br />";
            //echo $query;
            }
        }
        if(count($allcats) == 0){
            $text  = "Please check your categories in the Plugin Audio Amplitude";
            return $text;
        }
        
        $this->allcats = $allcats;
        //one for all

        $track_query = $this->_getQuery($allcats);
        $db->setQuery($track_query);
        if($tracks = $db->loadObjectList()){
            $i = 0;
            
            foreach ($tracks as $track){
                if(!$onedir){
                    $track->url = $root_uri.$preview_path.$track->artist_alias.'/'.$track->album_alias.'/'.$track->url;
                }else{
                    $track->url = $root_uri.$preview_path.$track->url;
                }
                $all['songs'][] = $track;
            }
        }
        $text .= "Making list for catalog.js <br />";
        $jstring = "Amplitude.init(".json_encode($all).");";
        $jstring = preg_replace("~,~",",\n",$jstring);
        $jstring = preg_replace("~\[~","[\n",$jstring);
        $jstring = preg_replace("~\{~","{\n",$jstring);
        $jstring = preg_replace("~\},~","\n},",$jstring);
        $jstring = preg_replace("~\}\]\}\)~","\n}\n]\n})",$jstring);
    
        if($fh = fopen(JPATH_ROOT.$playlist_path.'catalog.js', "w")){
            fwrite($fh,$jstring);
            fclose($fh);
        }

        //for homepage
        $all = array();
        $all['songs'][] = $first;
        $track_query = $this->_getQueryHome();
//$text .= $track_query;
        $db->setQuery($track_query);
        if($tracks = $db->loadObjectList()){
            $i = 0;
            
            foreach ($tracks as $track){
                if(!$onedir){
                    $track->url = $root_uri.$preview_path.$track->artist_alias.'/'.$track->album_alias.'/'.$track->url;
                }else{
                    $track->url = $root_uri.$preview_path.$track->url;
                }
                $all['songs'][] = $track;
            }
        }
        $text .= "Making list for homepage.js <br />";
        $jstring = "Amplitude.init(".json_encode($all).");";
        $jstring = preg_replace("~,~",",\n",$jstring);
        $jstring = preg_replace("~\[~","[\n",$jstring);
        $jstring = preg_replace("~\{~","{\n",$jstring);
        $jstring = preg_replace("~\},~","\n},",$jstring);
        $jstring = preg_replace("~\}\]\}\)~","\n}\n]\n})",$jstring);
    
        if($fh = fopen(JPATH_ROOT.$playlist_path.'homepage.js', "w")){
            fwrite($fh,$jstring);
            fclose($fh);
        } 
        return $text;
        
    }
    
    function _getQuery($catin)
    {

        $root_uri = Uri::root();
        $root_uri = rtrim($root_uri,'/');

        $preview_path   = $root_uri.$this->params->get('preview_path', '/media/com_mymuse/previews/');
        $catin          = implode(",",$catin);


        $track_query = "SELECT p.track_parentid, p.title as name, parent.title as album,
            a.title as artist,
            p.file_preview as url,
            CONCAT('".$root_uri."/"."',parent.list_image) as cover_art_url,
            parent.alias as album_alias,
            a.alias as artist_alias
            FROM #__mymuse_product as p
            LEFT JOIN #__mymuse_product as parent on p.parentid=parent.id
            LEFT JOIN #__categories as a on parent.artistid=a.id
            WHERE p.parentid > 0 
            AND ( parent.catid IN (".$catin.") OR parent.artistid IN (".$catin.") )
            AND p.product_allfiles=0
            AND p.product_physical=0
            AND p.state > 0 AND parent.state > 0
            AND p.track_parentid = 0
            ORDER BY parent.product_release_date DESC, parent.created DESC, p.product_sku"; 

        return $track_query;
    }

    function _getQueryHome()
    {
        $root_uri = Uri::root();
        $root_uri = rtrim($root_uri,'/');
        $preview_path   = $root_uri . $this->params->get('preview_path', '/media/com_mymuse/previews/');

        $db = Factory::getDBO();
        //$db->setQuery("SELECT params FROM #__modules WHERE title='Latest Releases (MyMuse)' AND module='mod_mymuse_latest_nexgen'");
        $db->setQuery("SELECT params FROM `#__modules` WHERE `params` LIKE '%\"homepage\":\"1\"%' ");

        $product_ids  = '';
        $search = 'p.created';
        $homepage  = 0;
        $my_artistid = 0;


        if($params_string = $db->loadResult()){
            $moduleParams = new JRegistry();
            $moduleParams->loadString($params_string);
            $product_ids    = $moduleParams->get('product_ids','');
            $homepage       = $moduleParams->get('homepage',0);
            $search         = $moduleParams->get('type_search');
            $my_artistid    = $moduleParams->get('my_artistid',0);

        }



        if($search == 'p.product_made_date'){
            $search = 'parent.product_made_date DESC';
        }elseif($search == 'order'){
            $search = 'p.ordering ASC';
        }elseif($search == 'rorder'){
            $search = 'p.ordering DESC';
        }elseif($search == 'p.hits'){
            $search = 'parent.hits DESC';
        }else{
            $search .= ' DESC';
        }

        if($my_artistid){
            //get children
            $options = array();
            $options['countItems'] = 1;
            
            $categories = JCategories::getInstance('Mymuse', $options);

            $this_cat = $categories->get($my_artistid);

            if (is_object($this_cat)) {
                
                $child_cats = $this_cat->getChildren(true);
            }
            else {
                $child_cats = false;
            }
            if($child_cats){
                $cats[] = $my_artistid;
                foreach($child_cats as $cat){
                    $cats[] = $cat->id;
                }
                $my_artistid = implode(",",$cats);
            }
        }

        if($product_ids && $homepage){
            $search = "FIELD(parent.id, $product_ids)";
        }

        $track_query = "SELECT p.track_parentid, p.title as name, parent.title as album,
            a.title as artist, p.hits,
            p.file_preview as url,
            CONCAT('".$root_uri."/"."',parent.list_image) as cover_art_url,
            parent.alias as album_alias,
            a.alias as artist_alias
            FROM #__mymuse_product as p
            LEFT JOIN #__mymuse_product as parent on p.parentid=parent.id
            LEFT JOIN #__categories as a on parent.artistid=a.id 
            WHERE p.parentid > 0  
            ";
            
            if(!$homepage){
                if($my_artistid && !$product_ids){
                    $track_query .=  ' AND parent.artistid IN('.$my_artistid.')';
                }
                if($product_ids && !$my_artistid){
                    $track_query .=  ' AND parent.id IN('.$product_ids.')';
                }
                if($product_ids && $my_artistid){
                    $track_query .=  ' AND ( parent.id IN('.$product_ids.') OR parent.catid IN('.$my_artistid.') )';
                }
            }else{
                if($my_artistid && !$product_ids){
                    $track_query .=  ' AND artistid IN('.$my_artistid.')';
                }
                if($product_ids && !$my_artistid){
                    $track_query .=  ' AND ( parent.id IN('.$product_ids.') )';
                }
                if($product_ids && $my_artistid){
                    $track_query .=  ' AND ( parent.id IN('.$product_ids.') OR parent.catid IN('.$my_artistid.') )';
                }
            }

            $track_query .= "
            AND p.product_allfiles=0
            AND p.state > 0 AND parent.state > 0
            AND p.track_parentid = 0
            AND p.product_physical=0
            ORDER BY ".$search.", p.product_sku"; 




   //echo 'home query '.$db->replacePrefix((string) $track_query)." \n\n"; exit;
        return $track_query;
    }
}
/*
            LEFT JOIN (SELECT sum(quantity) as sales, x.product_name, x.product_id FROM
            (SELECT sum(i.product_quantity) as quantity, i.product_id, p.parentid,
            i.product_name, CASE WHEN parentid > 0 THEN parentid ELSE product_id END as all_id
            FROM #__mymuse_order_item as i
            LEFT JOIN #__mymuse_product as p ON i.product_id=p.id
            GROUP BY i.product_id )
            as x GROUP BY x.all_id) as s ON s.product_id = p.id

*/