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

// Protection against direct access
defined('_JEXEC') or die();

if(!defined('DS')){
  define('DS',DIRECTORY_SEPARATOR);
}

if(!defined('MYMUSE_CACERT_PEM')) {
	define('MYMUSE_CACERT_PEM', __DIR__.DS.'cacert.pem');
}

require_once(__DIR__.DS.'vendor'.DS.'autoload.php');

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Joomla\CMS\Object\CMSObject;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\CMS\Factory;

class MymuseStorage extends CMSObject
{

  /**
   * type of storge
   *
   * @var   string
   */
  public  $type = 'amazon';


	// ACL flags
	const ACL_PRIVATE = 'private';
	const ACL_PUBLIC_READ = 'public-read';
	const ACL_PUBLIC_READ_WRITE = 'public-read-write';
	const ACL_AUTHENTICATED_READ = 'authenticated-read';
	const ACL_BUCKET_OWNER_READ = 'bucket-owner-read';
	const ACL_BUCKET_OWNER_FULL_CONTROL = 'bucket-owner-full-control';

	public static $useSSL = true;

	private static $__accessKey; // AWS Access key
	private static $__secretKey; // AWS Secret key
	private static $__default_bucket = null;
	private static $__default_acl = 'private'; // Default ACLs to use: private
	private static $__default_time = 900; // Default timeout for signed URLs: 15 minutes
	private static $__default_region = 'us-west';

  private $_s3 = null;
  static $myparams = null;
  static $muse_params = null;
  static $static_s3 = null;
	

	/**
	 * Constructor
	 *
	 * @params   array   $config   An array that holds the plugin configuration
	 */
	public function __construct($params)
	{
    self::$myparams = $params;
    self::$muse_params = MymuseHelper::getParams();
		$this->_s3 = self::$static_s3 = $this->getInstance();

	}



	/**
	 * Singleton implemetation
	 */
	public static function getInstance($accessKey = null, $secretKey = null, $useSSL = true)
	{
		
		static $instance = null;
		

		if(!self::$myparams->get('my_s3region',0)){
			Factory::getApplication()->enqueueMessage(JText::_('MYMUSE_NO_S3_REGION'), 'error');
			return false;
		}
		$region = self::$myparams->get('my_s3region',0);
		
		
		if(!is_object($instance)) {
			
			if(empty($accessKey) && empty($secretKey)) {

					$accessKey	= self::$myparams->get('my_s3access','');
					$secretKey	= self::$myparams->get('my_s3secret','');
					$useSSL		= self::$myparams->get('my_s3ssl',true);
				
			}
			
			$instance = new Aws\S3\S3Client([
					'version' => '2006-03-01',
					'region'  => $region,
					'credentials' => [
        				'key'    => $accessKey,
        				'secret' => $secretKey
    				]
			]);
      if(self::$myparams->get('my_use_acceleration',0)){
      $instance = new Aws\S3\S3Client([
          'version' => '2006-03-01',
          'region'  => $region,
          'credentials' => [
                'key'    => $accessKey,
                'secret' => $secretKey
            ],
            'use_accelerate_endpoint' => True
      ]);
    }else{
      $instance = new Aws\S3\S3Client([
          'version' => '2006-03-01',
          'region'  => $region,
          'credentials' => [
                'key'    => $accessKey,
                'secret' => $secretKey
            ]
      ]);
    }

			self::$__default_bucket = self::$muse_params->get('my_download_dir', '');
			self::$__default_acl = self::$myparams->get('my_s3perms','private');
			self::$__default_time = self::$myparams->get('my_s3time', 900);
			self::$__default_region = $region;
			self::$__accessKey = $accessKey; 
			self::$__secretKey = $secretKey; 
			
		}

		return $instance;
	}


  /**
   * get Site URL
   * 
   *
   * @return  string URL
   */
  public function getSiteUrl()
  {
    return self::$myparams->get('my_s3web','');
  }

  /**
   * list files Previews
   * 
   * @param   string directory name
   *
   * @return  array
   */
  public static function listFilesPreviews($dir)
  {

    // first section is bucket name
    $files = array();
    $parts = explode(DS,$dir);
    $bucket = array_shift($parts);
    $bucket = trim($bucket, DS);
    $uri = implode(DS, $parts);
    $uri = trim($uri,DS).DS;

    try{
        $result = self::$static_s3->listObjects([
          'Bucket' => $bucket, 
          'Prefix' => $uri
        ]);
      } catch (S3Exception $e) {

        self::setError( 'S3 Error: '.$e->getMessage() );
        $application = Factory::getApplication();
        $application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
        return false;
      }
      //print_pre($result);
      $everything = $result['Contents'];
      $folder = trim($uri,'/');
      $dirLength = strlen($folder);
      if(is_countable($everything) && count($everything)) {
        foreach($everything as $info) {
            // print_pre($info); exit;
          if (array_key_exists ( 'Size', $info ) && (substr ( $info ['Key'], - 1 ) != '/')) {
            $path = $info ['Key'];
            if (substr ( $info ['Key'], 0, $dirLength ) == $folder) {
              $path = substr ( $info ['Key'], $dirLength );
            }
            $path = trim ( $path, '/' );
            $files [] = $path;
          }
        }
      }

      if(1 == self::$muse_params->get('my_previews_in_one_dir')){
        $new_files = array();
        foreach($files as $file){
          $pos = strpos($file, '/');
          if($pos === false){
            $new_files[] = $file;
          }
        }
        $files = $new_files;
      }
      return $files;

  }

  /**
   * list files Downloads
   * 
   * @param   string directory name
   *
   * @return  array
   */
  public static function listFilesDownloads($dir)
  {
    $files = array();
      if(1 == self::$muse_params->get('my_download_dir_format')){ //downloads by format
        $result = array();
        $dir = trim($dir, '/');
        //get main folder, might be some 'other' files
        $format_result = array();
        try{
          $format_result = self::$static_s3->listObjects([
              'Bucket' => $dir,
              'Prefix' => ''
          ]);
        } catch (S3Exception $e) {
            
          self::setError( 'S3 Error: '.$e->getMessage() );
          $application = Factory::getApplication();
          $application->enqueueMessage('S3 Error: '. $e->getMessage() , 'error');
          return false;
        }
        $result = array_merge($result, $format_result['Contents']);

        $everything = $result;

      }else{

        $start = strlen(self::$muse_params->get('my_download_dir'));
        $prefix = substr($dir, $start);
        $prefix= trim($prefix, "/");
        $result = self::$static_s3->listObjects([
          'Bucket' => self::$muse_params->get('my_download_dir'), // REQUIRED
          'Prefix' => $prefix
        ]);
        $everything = $result['Contents'];

      }

      //echo "download path = $dir";
      //echo " prefix = $prefix";
      $folder = trim($dir,'/');
      $dirLength = strlen($prefix);
      if(is_countable($everything) && count($everything)) {
        foreach($everything as $info) {
          if(array_key_exists('Size', $info) && (substr($info['Key'], -1) != '/')) {
            $path = $info['Key'];
            if(substr($info['Key'], 0, $dirLength) == $prefix) {
              $path = substr($info['Key'], $dirLength);
            }
            $path = trim($path,'/');
            $files[] = $path;
          }
        }
      }

      return $files;
  }

  /**
   * Create a new folder
   * 
   * @param   string folder name
   *
   * @return  boolean  True on success.
   */
  public function folderNew($dir)
  {
    //echo "folderNew dir = $dir <br />";
		// first section is bucket name
		$parts = explode(DS,$dir);
		$bucket = array_shift($parts);
		$bucket = trim($bucket, DS);
		$uri = implode(DS, $parts);
		$uri = trim($uri,DS).DS;

		try{
			$result = $this->_s3->putObject([
					'Bucket'     => $bucket,
					'Key'        => $uri,
					'Body' => '',
			]);
		} catch (S3Exception $e) {
			$application = Factory::getApplication();
      echo $e->getMessage();
			$this->setError( 'S3 Error: '.$e->getMessage() );
			$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
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
   */
  public function fileDelete($file)
  {
      //echo "file Delete $file"; 
  		// first section is bucket name
  		$parts = explode(DS,$file);
  		$bucket = array_shift($parts);
  		$bucket = trim($bucket, DS);
  		$uri = implode(DS, $parts);
  		$uri = trim($uri,DS);

  		try{
  			$result = $this->_s3->deleteObject([
  					'Bucket'     => $bucket,
  					'Key'        => $uri
  			]);
  		} catch (S3Exception $e) {
  			$application = Factory::getApplication();
  			$this->setError( 'S3 Error: '.$e->getMessage() );
  			$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
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
   */
  public function fileUpload($tmpName, $new_file)
  {
   // echo "tmpName = $tmpName new_file = $new_file <br />";
  	if(!file_exists($tmpName)){
  		//$this->setError(JText::_("COM_MYMUSE_FILE_DOES_NOT_EXIST").": ".$tmpName);
      $application = Factory::getApplication();
      $application->enqueueMessage('S3 Error: '.JText::_("COM_MYMUSE_FILE_DOES_NOT_EXIST").": ".$tmpName , 'error');
  		return false;
  	}

  		// first section is bucket name
  		$parts = explode(DS,$new_file);
  		$bucket = array_shift($parts);
  		$bucket = trim($bucket, DS);
  		$uri = implode(DS, $parts);
  		$uri = trim($uri,DS);
  		try{
  			$result = $this->_s3->putObject([
  					'Bucket'     => $bucket,
  					'Key'        => $uri,
  					'SourceFile' => $tmpName,
  			]);
  		} catch (S3Exception $e) {
  			$application = Factory::getApplication();
  			$this->setError( 'S3 Error: '.$e->getMessage());
  			$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
  			echo $this->_s3->getError(); exit;
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
   */
  public function fileCopy($src, $dest)
  {
      //echo "fileCopy src $src dest $dest <br />"; 
  		//they are both on s3. Must download one 
  		$oldParts = explode(DS,$src);
  		
  		$oldBucket = array_shift($oldParts);
  		$oldBucket = trim($oldBucket, DS);
  		$uri = implode(DS, $oldParts);
  		
  		$jconfig = Factory::getConfig();
  		$tmpName = $jconfig->get('tmp_path','').DS.array_pop($oldParts );
  		
  		$parts = explode(DS,$dest);
  		$newbucket = array_shift($parts);
  		$newbucket = trim($newbucket, DS);
  		$newname = implode(DS, $parts);
  		

  		try{
  			$result = $this->_s3->copyObject([
  				'Bucket' => $newbucket,
  				'CopySource' => $src,
  				'Key' => $newname,
  			]);
  		} catch (S3Exception $e) {
  			//echo $e->getMessage() . "\n";
  			$this->setError( 'S3 Error: '.$e->getMessage() );
        $application = Factory::getApplication();
  			$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
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
   */
  public function fileExists($file)
  {

  		$parts = explode(DS,$file);
  		$bucket = array_shift($parts);
  		$bucket = trim($bucket, DS);
  		$uri = implode(DS, $parts);
  		$uri = trim($uri,DS);
  		$file = array_pop($parts);
  		$prefix = implode(DS, $parts);
		
  		
  		try{
  			$objects = $this->_s3->getIterator('ListObjects', array('Bucket' => $bucket, 'Prefix' => $prefix));
  			foreach ($objects as $object) {
          //echo "$uri -- ".$object['Key']."<br />";
  				if($object['Key'] == $uri){
  					return true;
  				}
			  }
  		} catch (S3Exception $e) {

  			$this->setError( 'S3 Error: '.$e->getMessage() );
        $application = Factory::getApplication();
  			$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
  			return false;
  		}
  		
  		return false;
  		

  }
  
  public function folderMove($src, $dest)
  {
  //echo "folderMove src $src dest $dest <br />";
  		if(!$this->folderNew($dest)){
  			return false;
  		}
  		$old_files = array();
  		$new_files = array();
  		// first section is bucket name
  		$parts = explode(DS,$src);
  		$srcBucket = array_shift($parts);
  		$srcBucket = trim($srcBucket, DS);
  		$uri = implode(DS, $parts);
  		$uri = trim($uri,DS);
  		$uri = $uri.'/';
  		
  		$parts = explode(DS,$dest);
  		$targetBucket = array_shift($parts);
  		$targetBucket = trim($targetBucket, DS);
  		$targetUri = implode(DS, $parts);
  		$targetUri = trim($targetUri,DS);

			try{
				$objects = $this->_s3->getIterator('ListObjects', array('Bucket' => $srcBucket));
				foreach ($objects as $object) {
					$pos = strpos($object['Key'], $uri);
					
					if($pos !== false){
						
						$old_files[] = $object['Key'];
						
						$parts = explode(DS,$object['Key']);
						$key = array_pop($parts);	
						//copy the file
						$targetKey = $targetUri.'/'.$key;
						$copySource = $srcBucket.'/'.$object['Key'];
						$new_files[] = $targetBucket.'/'.$targetKey;
						//echo "object['Key'] = ".$object['Key'].' uri '.$uri.' MATCH<br /><br />';
						//echo "Bucket = $targetBucket : Key = $targetKey : copySource = ".$copySource." <br /><br />";
						if (! $this->fileExists ( $targetBucket.'/'.$targetKey )) {
						try {
							$this->_s3->copyObject ( array (
									'Bucket' => $targetBucket,
									'Key' => $targetKey,
									'CopySource' => $copySource 
							) );
						} catch ( S3Exception $e ) {
							// echo $e->getMessage() . "\n";
							$this->setError ( 'S3 Error: ' . $e->getMessage () );
              $application = Factory::getApplication();
							$application->enqueueMessage ( 'S3 Error: ' . $e->getMessage (), 'error' );
							return false;
						}
						// delete the old
						$result = $this->_s3->deleteObject ( array (
								'Bucket' => $srcBucket,
								'Key' => $object ['Key'] 
						) );
						}
					
						
					}
					//echo "object['Key'] ".$object['Key'].' NO MATCH <br />';
				}

			} catch (S3Exception $e) {

				$this->setError( 'S3 Error: '.$e->getMessage() );
        $application = Factory::getApplication();
				$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
				return false;
			}
  		
  	return true;

  }
  
  public function fileFilesize($src)
  {
  	

  		// first section is bucket name
  		$parts = explode(DS,$src);
  		$bucket = array_shift($parts);
  		$bucket = trim($bucket, DS);
  		$uri = implode(DS, $parts);
  		$uri = trim($uri,DS);
  		
  		try{
  			// HEAD object
  			$result= $this->_s3->headObject(array(
  					'Bucket' => $bucket,
  					'Key' => $uri
  			));
  		} catch (S3Exception $e) {
  			//echo $e->getMessage() . "\n";
  			$this->setError( 'S3 Error: '.$e->getMessage() );
        $application = Factory::getApplication();
  			$application->enqueueMessage('S3 Error: '.$e->getMessage() , 'error');
  			return false;
  		}
  		$arr = $result->toArray();
  		return $arr['ContentLength'];

  }

  public function getSignedUrl($file)
  {
      //echo "getSignedUrl $file <br />";


      // first section is bucket name
      $parts = explode(DS,$file);
      $bucket = array_shift($parts);
      $bucket = trim($bucket, DS);
      $uri = implode(DS, $parts);
      $uri = trim($uri,DS);

            
      $lifetime = self::$myparams->get('my_s3time', 900);
     
      $expires = time() + $lifetime;
      $minutes = $lifetime / 60 - 1;
     
      $cmd = $this->_s3->getCommand('GetObject', [
        'Bucket' => $bucket,
        'Key'    => $uri,
        'ResponseContentDisposition' => 'attachment;'
      ]);
    
      $request = $this->_s3->createPresignedRequest($cmd, '+'.$minutes.' minutes');
      $s3URL = (string) $request->getUri();

      return $s3URL;
  }

}