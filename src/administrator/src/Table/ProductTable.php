<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\Table;

\defined('_JEXEC') or die;


use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Administrator\Helper\Mp3fileHelper as Mp3fileHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseStorage;

/**
 * Product Table class.
 *
 * @since  1.0
 */
class ProductTable extends Table implements VersionableTableInterface
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;

	/**
	 * Ensure the params and metadata in json encoded in the bind method
	 *
	 * @var    array
	 * @since  3.3
	 */
    protected $_jsonEncode = array('attribs', 'metadata', 'physical', 'digital', 'recording');

	/**
	 * Hold product_data table
	 *
	 * @var    array
	 * @since  5.0
	 */
	protected $_product_data = null;

	/**
	 * Hold product_physical table
	 *
	 * @var    array
	 * @since  5.0
	 */
	protected $_product_physical = null;

	/**
	 * Hold product_digital table
	 *
	 * @var    array
	 * @since  5.0
	 */
	protected $_product_digital = null;

    /**
     * parentid
     *
     * @var    int
     * @since  5.0
     */
    public $parentid = 0;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   1.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_mymuse.product';
		$this->setColumnAlias('published', 'state');
        $this->storage = new MymuseStorage();
        /*

        
        $this->$_product_data		= Table::getInstance('Productdata', 'MymuseTable', array());
        $this->$_product_physical	= Table::getInstance('Productphysical', 'MymuseTable', array());
        $this->$_product_digital	= Table::getInstance('Productdigital', 'MymuseTable', array());
        */
		parent::__construct('#__mymuse_product', 'id', $db);


	}

	public function bind($array, $ignore = '')
	{

		$input 	= Factory::getApplication()->input;
		$form 	= $input->get('jform', array(), 'ARRAY'); 
		$date 	= Factory::getDate();

		if(isset($form['product_release_date']) && $form['product_release_date'] == ''){
			$array['product_release_date'] = NULL;
		}
		if(isset($form['articletext'])){
			$array['articletext'] = $form['articletext'];
		}
		// Search for the {readmore} tag and split the text up accordingly.
		if (isset($array['articletext'])) {
			$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
			$tagPos	= preg_match($pattern, $array['articletext']);

			if ($tagPos == 0) {
				$this->introtext	= $array['articletext'];
				$this->fulltext         = '';
			} else {
				list($this->introtext, $this->fulltext) = preg_split($pattern, $array['articletext'], 2);
			}

		}
        if(!isset($array['product_downloadable'])){
            $array['product_downloadable'] = 0;
        }
        if(!isset($array['product_allfiles'])){
            $array['product_allfiles'] = 0;
        }

        if (isset($array['attribs']) && is_array($array['attribs'])) {
			$registry = new Registry;
			$registry->loadArray($array['attribs']);
			$array['attribs'] = (string)$registry;
		}else{
            $array['attribs'] = '';
        }
        if(!isset($array['metadesc'])){
            $array['metadesc'] = '';
        }

		if (isset($array['metadata']) && is_array($array['metadata'])) {
			$registry = new Registry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string)$registry;
		}


		if (isset($array['physical']) && is_array($array['physical'])) {
			$registry = new Registry;
			$registry->loadArray($array['physical']);
			$array['physical'] = (string)$registry;
		}
		if (isset($array['digital']) && is_array($array['digital'])) {
			$registry = new Registry;
			$registry->loadArray($array['digital']);
			$array['digital'] = (string)$registry;
		}
		if (isset($array['recording']) && is_array($array['recording'])) {
			$registry = new Registry;
			$registry->loadArray($array['recording']);
			$array['recording'] = (string)$registry;
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules'])) {
			$rules = new JRules($array['rules']);
			$this->setRules($rules);
		}

		if (!isset($array['alias']) || trim($array['alias']) == '') {
			$array['alias'] = $array['title'];
		}
		if (!isset($array['hits']) || trim($array['hits']) == '') {
			$array['hits'] = 0;
		}
		if (!isset($array['modified']) || trim($array['modified']) == '') {
			$array['modified'] = $date->toSQL();
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     JTable::check
	 * @since   11.1
	 */
	public function check()
	{
		$app = Factory::getApplication();

		if (is_null($this->checked_out))
		{
			$this->checked_out = NULL;
		}

		$this->title = trim($this->title);

		if ($this->title == '') {
            Factory::getApplication ()->enqueueMessage ( Text::_('COM_MYMUSE_FILE_MUST_HAVE_A_TITLE'), 'error' );
			return false;
		}

		if (trim($this->alias) == '') {
			$this->alias = $this->title;
		}

		$this->alias =  ApplicationHelper::stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		if (trim(str_replace('&nbsp;', '', $this->fulltext)) == '') {
			$this->fulltext = '';
		}

		// Check the publish down date is not earlier than publish up.
		if ($this->publish_down > $this->_db->getNullDate() && $this->publish_down < $this->publish_up) {
			$temp = $this->publish_up;
			$this->publish_up = $this->publish_down;
			$this->publish_down = $temp;
		}
		
		//set a default publish up
		if(!isset($this->publish_up) || $this->publish_up == "" || $this->publish_up == "0000-00-00 00:00:00"){
			$this->publish_up = Factory::getDate()->format('Y-m-d H:i:s');
		}
		
		if($this->publish_down == "1970-01-01 00:00:01" || $this->publish_down == $this->publish_up){
			$this->publish_down = '';
		}
		
		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey)) {
			// Only process if not empty
			$bad_characters = array("\n", "\r", "\"", "<", ">"); // array of characters to remove
			$after_clean = StringHelper::str_ireplace($bad_characters, "", $this->metakey); // remove bad characters
			$keys = explode(',', $after_clean); // create array using commas as delimiter
			$clean_keys = array();

			foreach($keys as $key) {
				if (trim($key)) {
					// Ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}
			$this->metakey = implode(", ", $clean_keys); // put array back together delimited by ", "
		}
		if(!isset($this->metadata)){
		    $this->metadata = '';
        }
        if(!isset($this->product_physical)){
            $this->product_physical = '0';
        }
        if (empty($this->product_coming_soon)) {
            $this->product_coming_soon = 0;
        }
        if (empty($this->product_preorder)) {
            $this->product_preorder = 0;
        }
        if (empty($this->product_in_stock)) {
            $this->product_in_stock = 1;
        }
        if (empty($this->digital)) {
            $this->digital = '';
        }
        if (empty($this->physical)) {
            $this->physical= '';
        }
        if (empty($this->recording)) {
            $this->recording= '';
        }
        if (empty($this->isrc_code)) {
            $this->isrc_code= '';
        }
		
		//If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0) {
			$this->ordering = self::getNextOrder();
		}

		return true;
	}

	/*
	 * Stores a product.
	 *
	 * @param   boolean  $updateNulls  True to update fielDIRECTORY_SEPARATOR even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function store($updateNulls = true)
	{
		

		$params 		= MyMuseHelper::getParams();
		$app 			= Factory::getApplication();
		$input 			= $app->input;
		$subtype		= $input->get('subtype');
		$task 			= $input->get('task');
		$form 			= $input->get('jform', array(), 'array'); 
		$select_files 	= $input->get('select_file', '' ,'array');
		$formats 		= $input->get('formats', '' ,'array');
		$preview 		= $input->getString('file_preview', '');
		$current_preview = $input->get('current_preview', $this->file_preview ?? '');
		$remove_preview = $input->get('remove_preview', '');
		$date			= Factory::getDate();
		$user			= Factory::getUser();
		$db 			= Factory::getDBO();


		if ($this->id) {
			// Existing item
			$this->modified		= $date->toSQL();
			$this->modified_by	= $user->get('id');
			$isNew = 0;

		} else {
			// New product.
			if (!intval($this->created)) {
				$this->created = $date->toSQL();
			}

			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
            if (empty($this->modified_by)) {
                $this->modified_by = $user->get('id');
            }
			if(!isset($this->parentid) || !$this->parentid ){
				$this->parentid 	= isset($form['parentid'])? $form['parentid'] : '0' ;
			}
			$isNew = 1;
		}
        

        // for tracks with parentids
        if($this->parentid){
        	$download_path = MyMuseHelper::getdownloadPath($this->parentid,1);
        }   

 		$done = 0;

		//removing one of the variations
		if($task == 'deletevariation'){
			$variationid = $input->get('variation','');
			
			$new_current = array();
			$new_select = array();
					
			for( $i = 0; $i < count($select_files); $i++ ){
				if($i != $variationid && isset($current_files[$i]) && $select_files[$i]){
					$new_select[] = $select_files[$i];
					$new_current[] = $current_files[$i];
				}
			}
			$select_files = $new_select;
			$current_files = $new_current;
			$this->digital= json_encode($current_files);
			$done = 1;
			
		}

		if($subtype == 'file'){

			if(!isset($form['file_type']) || $form['file_type'] == ''){
				$form['file_type'] = 'audio';
			}

			// Preview from select box
			if(isset($preview) && $preview && $preview != $current_preview){
				if($this->parentid){
					$path = MyMuseHelper::getSitePath($this->parentid, 1);
				}elseif ($this->id){
					$path = MyMuseHelper::getSitePath($this->id, 0);
				}

				$new = 1;

				$ext = MyMuseHelper::getExt($preview);
				$name = preg_replace("/\.$ext$/","",$preview);
				if($params->get('my_use_string_url_safe')){
					$file_preview_name = ApplicationHelper::stringURLSafe($name).'.'.$ext;
				}else{
					$file_preview_name = $preview;
				}


				$old_file = $path.$preview;
				$new_file = $path.$file_preview_name;

				if($old_file != $new_file){

					if($this->storage->fileExists($old_file)){
						if(!$this->storage->fileCopy($old_file, $new_file)){
							return false;
						}
						if(!$this->storage->fileDelete($old_file)){
							return false;
						}

					}
				}
				$this->file_preview = $file_preview_name;
			}else{
				$this->file_preview = isset($current_preview)? $current_preview : '';
			}//if preview

			if($remove_preview){
				$this->file_preview = '';
			}

			//if select_files
			if(is_array( $select_files ) && !$done){
				
				for( $i = 0; $i < count($select_files); $i++ ){
					//rename if necessary
					$select_file = $select_files[$i];
					if($select_file &&  $select_file != @$current_file[$i]->file_name){
						
						// tidy up name and copy it to the download dir
						$ext = MyMuseHelper::getExt($select_file);
						$name = preg_replace("/$ext$/","",$select_file);
						if($params->get('my_use_string_url_safe')){
							$file_name = ApplicationHelper::stringURLSafe($name).'.'.$ext;
						}else{
							$file_name = $select_file;
						}
					
						$my_download_path = $download_path;

						if($params->get('my_download_dir_format', 0)){
							$my_download_path .= $ext.DIRECTORY_SEPARATOR;
						}

						$new_file = $my_download_path.$file_name;
						$old_file = $my_download_path.$select_file;

						if($old_file != $new_file){
							//echo "old = $old_file new = $new_file <br />"; 
							if(!$this->storage->fileCopy($old_file, $new_file)){
								return false;
							}
							if(!$this->storage->fileDelete($old_file)){
								return false;
							}
						}
						$file_length = $this->storage->fileFilesize($new_file);


						if(!isset($track_time)){
							$track_time = (isset($form['file_time']))? $form['file_time']: '';
						}
						


						if(isset($new_file) && is_file($new_file)
								&& strtolower(pathinfo($new_file, PATHINFO_EXTENSION)) == "mp3"
								&& !$track_time){
							$m = new Mp3fileHelper($new_file);

							$a = $m->get_metadata();
							if ($a['Encoding']=='VBR' || $a['Encoding']=='CBR'){
								$track_time = $a["Length mm:ss"];
							}
						}

						$file_downloads = isset($current[$i]->file_downloads)? $current[$i]->file_downloads: "0";

						
						//  save this to the digital field
						$digital = array(
							'file_name' => $file_name,
							'file_length' => $file_length,
							'file_ext' => $ext,
							'file_downloads'=> $file_downloads,
							'file_time' => $track_time,
							'file_type' => $form['file_type'],
							'file_format' => $formats[$i]
						);
						$registry = new Registry($digital );
						$this->digital = (string) $registry;
						if($i == 0){
							//make this the parent track
							$this->track_parentid = 0;
							$result = parent::store($updateNulls);
							if(!$result){
								$this->setError('Could not save parent track');
				                $app->enqueueMessage(Text::_('COM_MYMUSE_COULD_NOT_SAVE_PARENT_TRACK' ), 'error');
								return false;
							}
							$track_parent_id = $this->id;
						}else{
							unset($this->id);
							$this->featured = 0;
							$this->track_parentid = $track_parentid;
							$result = parent::store($updateNulls);
							if(!$result){
								$this->setError('Could not save child track');
				                $app->enqueueMessage(Text::_('COM_MYMUSE_COULD_NOT_SAVE_CHILD_TRACK' ), 'error');
								return false;
							}
						}
					}//if not = current
				}//for each select_files
			}//if select files
			$this->id = $track_parentid;
			return true;
		} //if subtype = file

		//all files
		if(isset($form['product_allfiles']) && $form['product_allfiles']){

			$this->product_allfiles = 1;
			for($p = 0; $p < count($params->get('my_formats')); $p++){
				$file_name = ApplicationHelper::stringURLSafe($this->alias."-full-release-". $params->get('my_formats')[$p]);
				$current_files[$p] = array(
							'file_name' => $file_name,
							'file_length' => '',
							'file_ext' => $params->get('my_formats')[$p],
							'file_downloads'=> '0',
							'file_time' => ''
					);

			}
			$this->digital= json_encode($current_files);
			$this->file_type = "audio";
		}





		//$this->checkin();

		$result = parent::store($updateNulls);

		if($result){

			// clear product_category_xref
			$query = "DELETE FROM #__mymuse_product_category_xref WHERE product_id=".$this->id;

			$db->setQuery($query);
			$db->execute();

			// other categories
			if(isset($form['othercats'])  && count($form['othercats']) && $form['othercats'][0] != ''&& $this->id){

				if(in_array($form['catid'], $form['othercats']) ){
					unset($form['othercats'][$form['catid']]);
				}
				foreach($form['othercats'] as $catid){
					$query = "INSERT INTO #__mymuse_product_category_xref
        			(catid,product_id) VALUES (".$catid.",".$this->id.")";
        			echo $query; exit;
					$db->setQuery($query);

					if(!$db->execute()){
						$this->setError(JText::_('MYMUSE_COULD_NOT_SAVE_PRODUCTCAT_XREF').$db->getErrorMsg());
						return false;
					}

				}
			}



			// recommends
			// clear product_recommend_xref
			$query = "DELETE FROM #__mymuse_product_recommend_xref WHERE product_id=".$this->id;
			$db->setQuery($query);
			$db->execute();

			//now add
			if(isset($form['recommended']) && $this->id){

				foreach($form['recommended'] as $recommend_id){
					$query = "INSERT INTO #__mymuse_product_recommend_xref
        			(product_id, recommend_id) VALUES (".$this->id.",".$recommend_id.")";
					$db->setQuery($query);

					if(!$db->execute()){
						$this->setError(JText::_('MYMUSE_COULD_NOT_SAVE_PRODUCT_RECOMMEND_XREF').$db->getErrorMsg());
						return false;
					}

				}
			}

			//what about the sku?
			if(!isset($this->product_sku) || $this->product_sku == ''){
				$this->product_sku = $this->alias.'-'.$this->id;
			}

			$result = parent::store($updateNulls);




			// if it is the parent. Parentid will be 0
	        //make dirs if necessary

	        if((!isset($this->parentid) || !$this->parentid) && $this->id){
				//must be  a parent
	        	$preview_path = MyMuseHelper::getSitePath($this->id,1);
				$download_path = MyMuseHelper::getdownloadPath($this->id,1);
				$artist_alias = MyMuseHelper::getArtistAlias($this->id,1);
				$album_alias = MyMuseHelper::getAlbumAlias($this->id,1);
				//$download_path .= $artist_alias.DIRECTORY_SEPARATOR.$album_alias;

				// only create dirs if my_download_dir_format is default 0
	        	if(!$params->get('my_download_dir_format', 0)){

					// create new dirs if needed
					if (! $this->storage->fileExists ( $download_path )) {
						if (! $this->storage->folderNew ( $download_path )) {
							$this->setError ( JText::_ ( "MYMUSE_COULD_NOT_MAKE_DIR" ) . $download_path );
							return false;
						}
						if ($this->storage->type == 'regular') {
							if (! $this->storage->fileCopy ( JPATH_ROOT . DIRECTORY_SEPARATOR . "administrator" . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_mymuse" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "index.html", $download_path . DIRECTORY_SEPARATOR . "index.html" )) {
								$this->setError ( JText::_ ( "MYMUSE_COULD_NOT_COPY_INDEX" ) . $download_path );
							}
						}
					}
				}


				//create preview dirs if needed
	        	// only create dirs if my_previews_in_one_dir is 0
	        	if(!$params->get('my_previews_in_one_dir')){

					if (! $this->storage->fileExists ( $preview_path )) {
						if (! $this->storage->folderNew ( $preview_path )) {
							$this->setError ( JText::_ ( "MYMUSE_COULD_NOT_MAKE_DIR" ) . ' ' . $preview_path );
							return false;
						}
						if ($this->storage->type == 'regular') {
							if (! $this->storage->fileCopy ( JPATH_ROOT . DIRECTORY_SEPARATOR . "administrator" . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_mymuse" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "index.html", $preview_path . DIRECTORY_SEPARATOR . "index.html" )) {
									$this->setError ( JText::_ ( "MYMUSE_COULD_NOT_COPY_INDEX" ) . ' ' . $preview_path );
							}
						}
					}
				}

				// what if directory names have changed?
				$old_alias = $input->get( 'old_alias', '' );
				$old_artistid = $input->get( 'old_artistid', '' );

				if (($old_alias && $old_alias != $this->alias) || ($old_artistid && $old_artistid != $this->artistid)) {
					$changed = 0;
					// for the source
					if ($old_alias && $old_alias != $this->alias) {
						$src_alias = $old_alias;
					} else {
						$src_alias = $this->alias;
					}

					if ($old_artistid && $old_artistid != $this->artistid) {
						$query = "SELECT alias FROM #__categories
	   					WHERE id='".$old_artistid."'";

						$db->setQuery($query);
						$src_artist_alias = $db->loadResult();
					} else {
						$src_artist_alias = $artist_alias;
					}

					// for the main product dir
					if($params->get('my_download_dir_format', 0) == 0 ){
						$query = "SELECT alias FROM #__categories
	   					WHERE id='".$this->artistid."'";

						$db->setQuery($query);
						$dest_artist_alias = $db->loadResult();


						$src = $params->get ( 'my_download_dir' ) . DIRECTORY_SEPARATOR . $src_artist_alias . DIRECTORY_SEPARATOR . $src_alias;
						$dest = $params->get ( 'my_download_dir' ) . DIRECTORY_SEPARATOR . $dest_artist_alias . DIRECTORY_SEPARATOR . $this->alias;

						if (! $this->storage->folderMove ( $src, $dest )) {
							$this->setError ( JText::_ ( "MYMUSE_COULD_NOT_MOVE_DIR" ) . $src . " " . $dest );
							return false;
						}
						$changed = 1;
					}

					// for the preview dir
					if(!$params->get('my_previews_in_one_dir', 0)){
						$src = ($this->storage->type != 'regular' ? '' : JPATH_ROOT . DIRECTORY_SEPARATOR) . $params->get ( 'my_preview_dir' ) . DIRECTORY_SEPARATOR . $src_artist_alias . DIRECTORY_SEPARATOR . $src_alias;
						$dest = ($this->storage->type != 'regular' ? '' : JPATH_ROOT . DIRECTORY_SEPARATOR) . $params->get ( 'my_preview_dir' ) . DIRECTORY_SEPARATOR . $dest_artist_alias . DIRECTORY_SEPARATOR . $this->alias;
						//echo "ready for foldermove $src $dest"; exit;
						if (! $this->storage->folderMove ( $src, $dest )) {
							$this->setError ( Text::_ ( "MYMUSE_COULD_NOT_MOVE_DIR" ) . $src . " " . $dest );
							return false;
						}
						$changed = 1;
					}
					if($changed){
						$msg = Text::_("COM_MYMUSE_PRODUCT_CHANGED_CATEGORY_SUCCESS");
						$msg .= ' src: '.$src.' dest:'.$dest ;
						Factory::getApplication ()->enqueueMessage ( $msg, 'notice' );
					}
					
				}

	        } // end of if parent


			// onMymuseAfterSave  onFinderAfterSave

			Factory::getApplication()->triggerEvent('onFinderAfterSave', array('com_mymuse.product', &$this, false, $isNew));
			$res = Factory::getApplication()->triggerEvent('onMyMuseAfterSave', array('com_mymuse.product', &$this, false, $isNew));
			if(isset($res[0])){
				$app->enqueueMessage($res[0], 'Notice');
			}

			/*
			$dispatcher = EventDispatcher::getInstance();
			JPluginHelper::importPlugin('finder');

			$res = $dispatcher->trigger('onFinderAfterSave', array('com_mymuse.product', $this, $isNew));
			JPluginHelper::importPlugin('mymuse');

			$res = $dispatcher->trigger('onMyMuseAfterSave', array('com_mymuse.product', $this, $isNew));
			if(isset($res[0])){
				$app->enqueueMessage($res[0], 'Notice');
			}
			*/
			$this->checkin($this->id);
			if($this->parentid > 0){
				$this->checkin($this->parentid);
			}
			
		}

		return $result; 
	}




	/**
	 * Get the type alias for the history table
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   4.0.0
	 */
	public function getTypeAlias()
	{
		return 'com_mymuse.product';
	}
}
