<?php
/**
 * @version     $Id: mymuseStorage.php 1987 2019-04-23 23:18:50Z gfisch $
 * @package     com_mymuse
 * @copyright   Copyright (C) 2019. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 *
 * Amazon S3 is a trademark of Amazon.com, Inc. or its affiliates.
 */
namespace Joomla\Component\Mymuse\Administrator\Helper;
// Protection against direct access
defined('_JEXEC') or die();

use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Object as CMSObject;
use Joomla\CMS\Language\Text;




class MymuseStorage
{

	/**
	 * type of storage
	 *
	 * @var		string
     *
     * @since 4.2
	 */
	public  $type = 'regular';

    /**
     * my_download_dir_format
     *
     * @var     int
     *
     * @since 4.2
     */
    private  $_my_download_dir_format = null;

    /**
     * my_formats
     *
     * @var     array
     *
     * @since 4.2
     */
    private  $_my_formats = null;

	/**
	 * Constructor
	 *
	 * @param   array   $params   An array that holds the plugin configuration
     *
     * @since 4.2
	 */
	public function __construct()
	{
        $params = MyMuseHelper::getParams();
        $this->_my_download_dir_format = $params->get('my_download_dir_format');
        $this->_my_formats = $params->get('my_formats');
	}


    /**
     * List Files
     * 
     * @param   string folder name
     *
     * @return  array
     *
     * @since 4.2
     */
    public static function listFilesPreviews($dir)
    {

        if(!Folder::exists($dir)){
            Folder::create($dir);
        }
        $files = Folder::files( $dir );

        return $files;

    }

    /**
     * List Files Downloads
     * 
     * @param   string folder name
     *
     * @return  array
     *
     * @since 4.2
     */
    public static function listFilesDownloads($dir)
    {
        $params = MyMuseHelper::getParams();
        $_my_download_dir_format = $params->get('my_download_dir_format');
        $_my_formats = $params->get('my_formats');
        if($_my_download_dir_format){
            //by format
            $files = array();
            foreach($_my_formats as $format){
                if(!Folder::exists( $dir.DIRECTORY_SEPARATOR.$format )){
                    Folder::create( $dir.DIRECTORY_SEPARATOR.$format );
                }
                $arr = Folder::files( $dir.DIRECTORY_SEPARATOR.$format );
                if(is_array($arr)){
                    $files = array_merge($files,$arr);
                }
            }
        }else{
            if(!Folder::exists( $dir )){
                Folder::create( $dir );
            }
            $files = Folder::files( $dir );
        }


        return $files;

    }

    /**
     * Create a new folder
     * 
     * @param   string folder name
     *
     * @return  boolean  True on success.
     *
     * @since 4.2
     */
    public function folderNew($dir)
    {
    	

		$status = Folder::create($dir);
		if(!$status){
			$this->setError( "Could not create $dir");
			$application = Factory::getApplication();
			$application->enqueueMessage("Could not create $dir" , 'error');
			return false;
		}
		if(!File::copy(JPATH_ROOT.DIRECTORY_SEPARATOR."administrator".DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_mymuse".DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."index.html",
				$dir.DIRECTORY_SEPARATOR."index.html")){
			$this->setError(Text::_("MYMUSE_COULD_NOT_COPY_INDEX").": ".$dir);
		    $application = Factory::getApplication();
			$application->enqueueMessage(Text::_("MYMUSE_COULD_NOT_COPY_INDEX").": ".$dir, 'error');
			return false;
		}
    	 
    	return true;
    }
    
    
    /**
     * Delete a file
     * 
     * @param   string file name
     *
     * @return  boolean  True on success.
     *
     * @since 4.2
     */
    public function fileDelete($file)
    {
    	
		if(!File::delete($file)){
			$this->setError(Text::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$file);
			$application = Factory::getApplication();
			$application->enqueueMessage(Text::_("MYMUSE_COULD_NOT_DELETE_FILE").": ".$file , 'error');
            return false;
		}
    	
    	return true;
    }
    
    /**
     * Upload a file
     * 
     * @param   string file to 
     * @param   string file name moving to
     *
     * @return  boolean  True on success.
     *
     * @since 4.2
     */
    public function fileUpload($tmpName, $new_file)
    {
    	if(!file_exists($tmpName)){
    		$this->setError(Text::_("MYMUSE_FILE_DOES_NOT_EXIST").": ".$tmpName);
    		return false;
    	}
    	$application = Factory::getApplication();

		if(!File::upload($tmpName, $new_file)){
			$this->setError(Text::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$tmpName." ".$new_file);
			$application = Factory::getApplication();
			$application->enqueueMessage(Text::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$tmpName." ".$new_file , 'error');
			return false;
		}
    	
    	return true;
    	 
    }
    
    /**
     * Copy a file
     * 
     * @param   string file to copy
     * @param   string file name copy to
     *
     * @return  boolean  True on success.
     *
     * @since 4.2
     */
    public function fileCopy($src, $dest)
    {
    	
		if(!File::copy("$src", "$dest")){
			$this->setError(Text::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$src." ".$dest);
			$application = Factory::getApplication();
			$application->enqueueMessage(Text::_("MYMUSE_COULD_NOT_MOVE_FILE").": ".$src." ".$dest , 'error');
			return false;
		}
	
    	return true;
    
    }
    
    /**
     * File Exists?
     * 
     * @param   string file name
     *
     * @return  boolean  True on success.
     *
     * @since 4.2
     */
    public function fileExists($file)
    {
    	return file_exists($file);
    }
    

    /**
     * folder Move
     * 
     * @param   string $src
     * @param   string $dest
     *
     * @return  boolean  True on success.
     *
     * @since 4.2
     */
    public function folderMove($src, $dest)
    {

		if ( !Folder::create($dest) ) {
            //Throw error message and stop script
            $this->setError("Could not create $dest");
            $application = Factory::getApplication();
			$application->enqueueMessage(Text::_("Could not create $dest") , 'error');
            return false;
        }
        $files = Folder::files($src);
        foreach ($files as $file) {
            File::move($src. DIRECTORY_SEPARATOR . $file, $dest . DIRECTORY_SEPARATOR. $file);
        }
    	
    	return true;

    }
    

    /**
     * file File Size
     * 
     * @param   string $src
     *
     * @return  integer file size
     *
     * @since 4.2
     */
    public function fileFilesize($src)
    {
    		return filesize($src);
    }
}