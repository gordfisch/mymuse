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

use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Store Table class.
 *
 * @since  1.0
 */
class StoreTable extends Table implements VersionableTableInterface
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
	protected $_jsonEncode = array('params', 'metadata');

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   1.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_mymuse.store';

		parent::__construct('#__mymuse_store', 'id', $db);

	}

	/*
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param	array		Named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	1.5
	 */
	public function bind($array, $ignore = '')
	{
		
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new Registry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
			
		}else{
			$registry = new Registry();
			$registry->loadString($array['params']);
			$array['params'] = (string)$registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string)$registry;
		}elseif (isset($array['metadata'])) {
			$registry = new Registry();
			$registry->loadString($array['metadata']);
			$array['metadata'] = (string)$registry;
		}
		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     \JTable::check
	 * @since   1.5
	 */
	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Check for valid name
		if (trim($this->title) == '')
		{
			$this->setError(Text::_('MYMUSE_STORE_MUST_HAVE_A_TITLE'));

			return false;
		}


		return true;
	}


	/**
	 * Stores a store.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function store($updateNulls = true)
	{
		if (is_null($this->checked_out))
		{
			$this->checked_out = 0;
		}
    	$app = Factory::getApplication();
    	
    	$myparams = MymuseHelper::getParams();
    	$jinput = Factory::getApplication()->input;
    	$form 	= $jinput->get('jform',array(), 'ARRAY');

    	
    	//$user_plugin = JPluginHelper::getPlugin('user', 'mymuse');
    	//$noreg_plugin = JPluginHelper::getPlugin('user', 'mymusenoreg');
    	
    	//if registration type has changed
    	//joomla, full, jossocial, no_reg, full_guest
    	if($form['params']['my_registration'] !== $myparams->get('my_registration')){
    		if ($form ['params'] ['my_registration'] == 'joomla') {
    			if (! $this->enablePlugin ( 'mymuse', 0 ) || ! $this->enablePlugin ( 'mymusenoreg', 0 )) {
    				$app->enqueueMessage ( Text::_ ( 'MYMUSE_ENABLE_PLUGIN_FAILED' ) . ' check user_mymuse and user_mymusenoreg', 'notice' );
    				return false;
    			} else {
    				$app->enqueueMessage ( Text::_ ( 'MYMUSE_DISABLE_PLUGIN' ) . ' user_mymuse and user_mymusenoreg', 'notice' );
    			}
    		}
			if ($form ['params'] ['my_registration'] == 'full_guest') {
				if (! $this->enablePlugin ( 'mymuse', 1 ) || ! $this->enablePlugin ( 'mymusenoreg', 1 )) {
					$app->enqueueMessage ( Text::_ ( 'MYMUSE_ENABLE_PLUGIN_FAILED' ) . ' check user_mymuse and user_mymusenoreg', 'notice' );
					return false;
				} else {
					$app->enqueueMessage ( Text::_ ( 'MYMUSE_ENABLE_PLUGIN' ) . ' user_mymuse and user_mymusenoreg', 'notice' );
				}
			}
			if ($form ['params'] ['my_registration'] == 'full') {
				if (! $this->enablePlugin ( 'mymuse', 1 ) || ! $this->enablePlugin ( 'mymusenoreg', 0 )) {
					$app->enqueueMessage ( Text::_ ( 'MYMUSE_ENABLE_PLUGIN_FAILED' ) . ' check user_mymuse and user_mymusenoreg', 'notice' );
					return false;
				} else {
					$app->enqueueMessage ( Text::_ ( 'MYMUSE_ENABLE_PLUGIN' ) . ' user_mymuse,' . Text::_ ( 'MYMUSE_DISABLE_PLUGIN' ) . ' user_mymusenoreg', 'notice' );
				}
			}
			if ($form ['params'] ['my_registration'] == 'no_reg') {
				if (! $this->enablePlugin ( 'mymuse', 0 ) || ! $this->enablePlugin ( 'mymusenoreg', 1 )) {
					$app->enqueueMessage ( Text::_ ( 'MYMUSE_ENABLE_PLUGIN_FAILED' ) . ' check user_mymuse and user_mymusenoreg', 'notice' );
					return false;
				} else {
					$app->enqueueMessage ( Text::_ ( 'MYMUSE_ENABLE_PLUGIN' ) . ' user_mymusenoreg,' . Text::_ ( 'MYMUSE_DISABLE_PLUGIN' ) . ' user_mymuse', 'notice' );
				}
			}
    	}

    	//save the css file
    	$mymuse_css = $jinput->get('com_mymuse_css','', 'RAW');

    	if($mymuse_css){
    		$myFile = JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_mymuse'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'mymuse.css';
    		if(!FILE::write($myFile, $mymuse_css)){
    			$app->enqueueMessage(Text::_('MYMUSE_COULD_NOT_OPEN_CSS_FILE').' '.$myFile, 'notice');
    		}
    	}

    	//if my_noreg_password has changed
    	$my_noreg_password = $form['params']['my_noreg_password'];

    	if($my_noreg_password !== $myparams->get('my_noreg_password')){
    		
    		$user	= Factory::getUser('buyer');
            if(!$user){
                $Uparams = JComponentHelper::getParams('com_users');
        		$data = array('name' => 'Guest Buyer',
        				'password'=>  $my_noreg_password,
        				'email' => 'guest@joomlamymuse.com',
    					'username' => 'buyer',
    					'password1' => $my_noreg_password,
    					'password2' => $my_noreg_password, 
    					'email1' => 'guest@joomlamymuse.com',
    					'email2' => 'guest@joomlamymuse.com' 
     			);
                $system = $uparams->get('new_usertype', 2);
                $data['groups'][] = $system;
        		// Bind the data.
        		if (!$user->bind($data))
        		{
        			$this->setError(Text::sprintf('COM_USERS_PROFILE_BIND_FAILED', $user->getError()));
        			return false;
        		}
        		$user->groups = null;
        		// Store the data.
                PluginHelper::importPlugin('user');
                
                // Store the data.
                if (!$user->save()) {
                    $this->setError(Text::sprintf('MYMUSE_REGISTRATION_SAVE_FAILED', $user->getError()));
                    return false;
                }
            }
    	}
    	
        //if encode filenames has changed
        if($form['params']['my_encode_filenames'] !== $myparams->get('my_encode_filenames')){
           //$form['params']['my_encode_filenames'] = $form['params']['my_encode_filenames'];
           //$jinput->set('jform', $form);
           if($form['params']['my_encode_filenames'] === '0'){
                $this->change_encoding(0);
           }
        }

        $this->checked_out_time = $this->_db->getNullDate();
        $this->checkin();

		return parent::store($updateNulls);
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
		return 'com_mymuse.store';
	}

	/**
	 * enablePlugin.
	 *
	 * @param   string  $name  Name of plugin.
	 *
	 * @param   boolean  $enable Enable or disable.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function enablePlugin($name, $enable = 1)
	{
		// Enable plugin
		$db  = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__extensions');
		$query->set($db->quoteName('enabled') . " = $enable");
		$query->where($db->quoteName('element') . ' = ' . $db->quote($name));
		$query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
		$db->setQuery($query);
		try {
			$db->execute();
		}
		catch (Exception $e){
			$this->setError(Text::_('MYMUSE_ENABLE_PLUGIN_FAILED', $e->getMessage()));
			return false;
		}
		return true;
		
		
	}

    /**
     * Change encoding of files
     * 
     * @param    integer The new state of encode filenames.
     * @return    boolean    True on success.
     */
    function change_encoding($my_encode_filenames = 0)
    {
    
    	$db = Factory::getDBO();
    	$params = MyMuseHelper::getParams();
    	$app = Factory::getApplication();
  
    	if($my_encode_filenames) // we want to encode them
    	{
    		$app->enqueueMessage(JText::_("MYMUSE_ENCODING_FILENAMES_NO_LONGER_SUPPORTED"), 'error');
    		return false;
    	}else{ // we want to change back to regular names
    		$query = "SELECT p.id, p.title, p.alias, p.title_alias, p.file_name
			FROM `#__mymuse_product` AS p
			WHERE p.product_downloadable =1
    		AND	p.title_alias != ''
			AND p.product_allfiles = 0
			ORDER BY p.title ";
    		
    		$db->setQuery($query);
    		$products = $db->loadObjectList();
    		foreach($products as $product){
    			$path = MyMuseHelper::getDownloadPath($product->id);
 
    			$old_file = $path.$product->title_alias;
    			
    			$current_files = json_decode($product->file_name);
    		
    			if(is_array($current_files) && $current_files[0]->file_name){
    				$new_file = $path.$current_files[0]->file_name;
    			}else{
    				$new_file = $path.$product->file_name;
    			}
			
    			if($new_file){
					if (! File::copy ( "$old_file", "$new_file" )) {
						$this->setError ( JText::_ ( "MYMUSE_COULD_NOT_MOVE_FILE" ) . ": " . $old_file . " " . $new_file );
						$app->enqueueMessage ( JText::_ ( "MYMUSE_COULD_NOT_MOVE_FILE" ) . ": " . $old_file . " " . $new_file, 'error' );
					}
					//if (! JFile::delete ( "$old_file" )) {
					//	$this->setError ( JText::_ ( "MYMUSE_COULD_NOT_DELETE_FILE" ) . ": " . $old_file );
					//	$app->enqueueMessage ( JText::_ ( "MYMUSE_COULD_NOT_DELETE_FILE" ) . ": " . $old_file, 'error' );
					//}
    			}
    		} 
    		
    	}
    	return true;
    }
}
