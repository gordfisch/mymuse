<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\Helpers\Sidebar;


define('TAX_REGEX',"[\'-\/\s\\\]");


/**
 * MyMuse component helper.
 *
 * @since  5.0.0
 */
class MymuseHelper extends ContentHelper
{

	
	/**
	 * params from store and component
	 *
	 * @var		object
	 * @since   5.0.0
	 */
	public static $_params = null;

	/**
	 * version
	 *
	 * @var		string
	 * @since   5.0.0
	 */
	public static $_version = null;
	
	/**
	 * extarray extentions to mime type
	 *
	 * @var		array
	 * @since   5.0.0
	 *
	 */
	public $extarray = array(
			'mp3' => 'audio/mpeg',
			'm4a' => 'audio/mp4',
			'ogg' => 'application/ogg',
			'oga' => 'application/ogg',
			'webma' => 'audio/webm',
			'wav' => 'audio/wav',
			'm4v' => 'video/m4v',
			'ogv' => 'video/ogv',
			'webm' => 'video/webmv',
			'webmv' => 'video/webmv',
			'wmv' => 'video/webmv'
	
	);

	/**
	 * Get current version
	 * 
	 * @return string
	 * 
	 * @since 5.0
	 */
	public static function getVersion() {

		if (preg_match('/<version>([0-9.]+)<\/version>/s', file_get_contents(JPATH_ADMINISTRATOR.'/components/com_mymuse/mymuse.xml'), $match)) {
			return $match[1];
		}else{
			return"unknown";
		}

	}

	/**
	 * For debugging
	 * 
	 * @param $var
	 * @return string
	 * 
	 * @since 3.0
	 */
	public static function print_pre($var){
		echo "<pre>";
		print_r($var);
		echo "</pre>";
		return true;
	}



	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   5.0.0
	 */
	public static function addSubmenu($vName)
	{
		
		\JHtmlSidebar::addEntry(
			Text::_('COM_MYMUSE'),
			'index.php?option=com_mymuse',
			$vName == 'welcome'
		);

		\JHtmlSidebar::addEntry(
			Text::_('COM_MYMUSE_TITLE_ORDERS'),
			'index.php?option=com_mymuse&view=orders',
			$vName == 'orders'
		);

		\JHtmlSidebar::addEntry(
			Text::_('COM_MYMUSE_TITLE_STORE'),
			'index.php?option=com_mymuse&view=store&task=store.edit&id=1',
			$vName == 'stores'
		);
		
		\JHtmlSidebar::addEntry(
			Text::_('COM_MYMUSE_TITLE_CATEGORIES'),
			'index.php?option=com_categories&extension=com_mymuse',
			$vName == 'categories'
		);
		
		\JHtmlSidebar::addEntry(
			Text::_('COM_MYMUSE_TITLE_PRODUCTS'),
			'index.php?option=com_mymuse&view=products',
			$vName == 'products'
		);

		
		
		
		\JHtmlSidebar::addEntry(
			Text::_('COM_MYMUSE_TITLE_TAXRATES'),
			'index.php?option=com_mymuse&view=taxrates',
			$vName == 'taxrates'
		);

		\JHtmlSidebar::addEntry(
			Text::_('COM_MYMUSE_TITLE_COUPONS'),
			'index.php?option=com_mymuse&view=coupons',
			$vName == 'coupons'
		);
		
		\JHtmlSidebar::addEntry(
			Text::_('COM_MYMUSE_TITLE_SHOPPERGROUPS'),
			'index.php?option=com_mymuse&view=shoppergroups',
			$vName == 'shoppergroups'
		);

		\JHtmlSidebar::addEntry(
			Text::_('MYMUSE_MYMUSE_REPORTS'),
			'index.php?option=com_mymuse&view=reports',
			$vName == 'reports'
		);
		
		\JHtmlSidebar::addEntry(
			Text::_('MYMUSE_PLUGINS'),
			'index.php?option=com_plugins&view=plugins&filter_folder=mymuse',
			$vName == 'plugins'
		);
		
		\JHtmlSidebar::addEntry(
			Text::_('MYMUSE_PLUGINS_USER'),
			'index.php?option=com_plugins&view=plugins&filter_folder=user',
			$vName == 'plugins'
		);

		if (ComponentHelper::isEnabled('com_fields') && ComponentHelper::getParams('com_mymuse')->get('custom_fields_enable', '1'))
		{
			\JHtmlSidebar::addEntry(
				Text::_('JGLOBAL_FIELDIRECTORY_SEPARATOR'),
				'index.php?option=com_fields&context=com_mymuse.product',
				$vName == 'fields.fields'
			);
			\JHtmlSidebar::addEntry(
				Text::_('JGLOBAL_FIELD_GROUPS'),
				'index.php?option=com_fields&view=groups&context=com_mymuse.product',
				$vName == 'fields.groups'
			);
		}
	}

	/**
	 * getParams
	 * Gets and sets params by mixing store params and component params
	 * Expands currency settings and gets version from #__extensions
	 * 
	 * @param object  The Store Object
	 * 
	 * @return mixed Registry object or false
	 *
	 * @since   5.0.0
	 */
	public static function getParams($store=0, $new = 0)
	{
		if(!self::$_params || $new){
			$db = Factory::getDbo();

			if(!$store){
				$query = "SELECT * from `#__mymuse_store` WHERE id='1'";
				$db->setQuery($query);
				$store = $db->loadObject();
			}
			
			//store params
			$params = new Registry($store->params);
			
			//get component params
			$cparams = ComponentHelper::getParams( 'com_mymuse' );
			$params->merge( $cparams );
			$my_formats = $params->get('my_formats');

			if(isset($my_formats[0])){
				$f_array = [];
				foreach($my_formats as $f){
					$f_array[] = $f;
				}
				$IN = implode(",",$f_array);
				$query = "SELECT * from `#__mymuse_format` WHERE id IN (".$IN.") 
				OR format_key IN (".$IN.")";
				$db->setQuery($query);
				$formats = $db->loadObjectList();
				$params->set('my_formats', $formats) ;

			}else{
				$p[0] = new CMSObject;
				$p[0]->id = 1;
				$p[0]->format_key = 'mp3';
				$p[0]->format_value = 'MP3';
				$p[0]->ordering = 1;

				$params->set('my_formats', $p) ;
			}

			//merge app params includes menu
			$app            = Factory::getApplication();
			if($app->isClient('site')){
				$app_params      = $app->getParams();
				$params->merge( $app_params );
			}

			$params->set('my_currency',$params->get('currency'));

			$currency = MyMuseHelper::getCurrency($params->get('currency'));
			$params->set('my_currency_code',$currency['currency_code']);
			$params->set('my_currency_symbol',$currency['symbol']);
			$params->set('my_currency_id',$currency['id']);
			
			$version = (self::$_version)? self::$_version : self::getVersion();
			$params->set('my_version',$version);
	
			self::$_params = $params;
		}
		return self::$_params;

	}

	/**
	 * setParams
	 * 
	 * @param string  The key
	 * @param mixed  The value
	 * 
	 * @return null
	 *
	 * @since   5.0.0
	 */
	public static function setParam($key, $val){
		if(!$key || !$val){
			return;
		}
        if(!self::$_params){
            self::getParams();

        }
		self::$_params->set($key, $val);
	}
	
	/**
	 * Log a message. Should only be done when SHOP_TEST is on
	 * 
	 * @param $message
	 * @return boolean
	 */
	static function logMessage($message){
		jimport('joomla.filesystem.file');

		$date = date('Y-m-d h:i:s');
		$message = "################### \n$date\n".$message;

		if($fh = fopen(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_mymuse'.DIRECTORY_SEPARATOR.'log.txt', "a")){
			fwrite($fh,$message."\n");
			fclose($fh);
		}
		return true;
	}

	
	/**
	 * getStore
	 * 
	 * @param int  id
	 * 
	 * @return object
	 *
	 * @since   5.0.0
	 */
	static function getStore($id=1)
	{
		$db = Factory::getDbo();
		$query = "SELECT * from `#__mymuse_store` WHERE id='$id'";
		$db->setQuery($query);
		$store = $db->loadObject();
		
		$params = MyMuseHelper::getParams();
		$store->currency = $params->get('currency');
		
		return $store;
	}

	/**
	 * returnURL. build a return URL
	 *
	 * @return string
	 *
	 * @since   5.0.0
	 */
	static function returnURL()
	{
		$input 		= Factory::getApplication()->input;
		$url 		= URI::base(true);
		$option     = $input->get( 'option', '' );
		$task       = $input->get( 'task', '' );
		$Itemid     = $input->get( 'Itemid', '' );
		$id 		= $input->get( 'id','');	
		$controller = $input->get( 'controller','');
		$view		= $input->get( 'view','');
		$layout		= $input->get( 'layout','');

		$url .= "/index.php?option=$option";
		if($id){
			$url .= "&id=".$id;
		}
		if($task){
			$url .= "&task=".$task;
		}
		if($view){
			$url .= "&view=".$view;
		}
		if($layout){
			$url .= "&layout=".$layout;
		}
		if($controller){
			$url .= "&controller=".$controller;
		}
		if($Itemid){
			$url .= "&Itemid=".$Itemid;
		}

		$url = base64_encode($url);

		return $url;
	}

	/**
 	 * Get Currency
 	 * 
 	 * @param string code
 	 *
 	 * @return array
 	 *
	 * @since   5.0.0
 	 */
	public static function getCurrency($code=0)
	{

		if(!$code){
			jimport( 'joomla.html.parameter' );
			$db = Factory::getDbo();
			$query = "SELECT * from `#__mymuse_store` WHERE id='1'";
			$db->setQuery($query);
			$store = $db->loadObject();
			$params = new Registry($store->params);
			$code = $params->get('currency');

		}
		$db = Factory::getDbo();
		$query = "SELECT * from #__mymuse_currency WHERE currency_code = '".$code."'";

		$db->setQuery($query);
		$curr = $db->loadObject();

		$currency = array(
			'id'			=> $curr->id,
			'currency_name'	=> $curr->currency_name,
			'currency_code' => $curr->currency_code,
			'symbol'		=> $curr->symbol
		);
		return $currency;
	}

	 /**
 	 * printMoney
 	 * format a money string
 	 * 
 	 * @param float amount
 	 *
 	 * @return string
 	 *
	 * @since   5.0.0
 	 */
	static function printMoney($amount){
		$params = MyMuseHelper::getParams();

		if(!is_numeric($amount)){
			return $amount;
		}

		$str = "";
		if($amount == 0.00 || $amount == 0){
			$str = '0.00';
			return $str;
		}
		if($params->get('my_currency_separator') == "" || $params->get('my_currency_separator') == "space" ){
			$my_currency_separator = " ";
		}else{
			$my_currency_separator = $params->get('my_currency_separator');
		}
		
		if($params->get('my_currency_position') == 0){
			$str .= $params->get('my_currency_symbol');
		}
		
		$str .= number_format($amount, 2, $params->get('my_currency_dec_point'), $my_currency_separator);
		if($params->get('my_currency_position') == 1){
			$str .= " ".$params->get('my_currency_symbol');
		}
		
		return $str;
	}
	
     /**
 	 * printMoneyPublic
 	 * format a money string with possible discount
 	 * 
 	 * @param array price
 	 *
 	 * @return string
 	 *
	 * @since   5.0.0
 	 */
	static function printMoneyPublic($price=array()){
		$params = MyMuseHelper::getParams();
		$string = '';
		/**
		 * $price[item] = 0/1
		 * $price[product_original_price]
		 * $price[product_price]
		 * $price[product_discount]
		 * $price[product_shopper_group_discount]
		 * $price[product_shopper_group_discount_amount]
		 */
		if($params->get('my_show_original_price')
			&& ($price['product_discount'] > 0.00 
			|| $price['product_shopper_group_discount_amount'] > 0.00)){
				$string .= '<span class="discount">';
				$string .= MyMuseHelper::printMoney($price['product_original_price']);
				$string .= '</span> ';
		}
		if(isset($price['product_price'])){
			$string .= MyMuseHelper::printMoney($price['product_price']);
		}
		return $string;
	}

	/**
	 * logPayment
	 * 
	 * @param $payment array
	 *
	 * @return string
	 *
	 * @since   5.0.0
	 */
	
	public function logPayment($payment){
		$db		= Factory::getDbo();
		include_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_mymuse".DIRECTORY_SEPARATOR."tables".DIRECTORY_SEPARATOR."orderpayment.php");
		$table = new MymuseTableorderpayment($db);
		
		if (!$table->bind($payment)) {
            $this->setError($db->getErrorMsg());
            return false;
        }
        
		// Make sure the item payment is valid
        if (!$table->check()) {
            $this->setError($table->getError());
            return false;
        }
        
		// Store the payment table to the database
        if (!$table->store()) {
            $this->setError($db->getErrorMsg());
            return false;
        }
        return true;
		
	}
	/**
	 * getArtistAlias. alias from artist record
	 * 
	 * @param $id int
	 * @param $parent boolean // is this the parent?
	 *
	 * @return string
	 *
	 * @since   5.0.0
	 */
	
	static function getArtistAlias($id,$parent=0){
		
		$db	= Factory::getDbo();
		if(!$parent){ //not the parent, find the parent
			
			$query = "SELECT parentid from #__mymuse_product
			WHERE id ='$id'";
			$db->setQuery($query);
			$parentid = $db->loadResult();
			if(!$parentid){ // I guess that was the parent
				$parentid = $id;
			}
			$query = "SELECT artistid from #__mymuse_product
			WHERE id ='$parentid'";
			$db->setQuery($query);
			$artistid = $db->loadResult();

		}else{
			
			$query = "SELECT artistid from #__mymuse_product
			WHERE id ='$id'";
			//echo $query;
			$db->setQuery($query);
   			$artistid = $db->loadResult();
		}

		$query = "SELECT alias FROM #__categories 
   		WHERE id='".$artistid."'";

   		$db->setQuery($query);
   		$alias = $db->loadResult();

		return $alias;
		
	}
	
	/**
	 * getArtistId. get main artistid from product record
	 * 
	 * @param $id int
	 * @param $parent bool
	 *
	 * @return string
	 *
	 * @since   5.0.0
	 */
	
	function getArtistId($id,$parent=0){

		
		$db	= Factory::getDbo();
		if(!$parent){ //not the parent, find the parent
			$query = "SELECT parentid from #__mymuse_product
			WHERE id ='$id'";
			$db->setQuery($query);
			$parentid = $db->loadResult();
			if(!$parentid){ // I guess that was the parent
				$parentid = $id;
			}
			$query = "SELECT artistid from #__mymuse_product
			WHERE id ='$parentid'";
			$db->setQuery($query);
			$artistid = $db->loadResult();
			
		}else{
			$query = "SELECT artistid from #__mymuse_product
			WHERE id ='$id'";
			$db->setQuery($query);
			$artistid = $db->loadResult();
		}

		return $artistid;
		
	}
	
	/**
	 * getAlbumAlias get alias from parent
	 * 
	 * @param $id int
	 *
	 * @return string
	 *
	 * @since   5.0.0
	 */
	
	static function getAlbumAlias($id,$parent=0){
		
		$db	= Factory::getDbo();
		if(!$parent){ //not the parent, find the parent
			$query = "SELECT parentid from #__mymuse_product
			WHERE id ='$id'";
			$db->setQuery($query);
			$id = $db->loadResult();
		}
		$query = "SELECT alias FROM #__mymuse_product 
   			WHERE id='".$id."'";

   		$db->setQuery($query);
   		$alias = $db->loadResult();
   
		return $alias;
		
	}
	
	/**
	 * getSiteUrl get url for previews
	 *
	 * @param $id int
	 * @param $parent boolean // is this the parent?
	 *
	 * @return string
	 *
	 * @since   5.0.0
	 */
	static function getSiteUrl($id, $parent=0)
	{
		$params = self::$_params;

		

		if(1 == $params->get('my_previews_in_one_dir')){
			if( $params->get('storage', 'regular') == 'regular' ) {
				$site_url = preg_replace("#administrator/#","",URI::root(true)).DIRECTORY_SEPARATOR.$params->get('my_preview_dir').DIRECTORY_SEPARATOR;
			}else{
				$site_url = $GLOBALS['mymuseStorage']->getSiteUrl();
			}


		}else{
			$artist_alias = MyMuseHelper::getArtistAlias($id,$parent);
			$album_alias = MyMuseHelper::getAlbumAlias($id,$parent);	
			$path_url = $artist_alias.DIRECTORY_SEPARATOR.$album_alias.DIRECTORY_SEPARATOR;
			if( $params->get('storage', 'regular') == 'regular' ) {
				$site_url = URI::root(true).DIRECTORY_SEPARATOR.$params->get('my_preview_dir').DIRECTORY_SEPARATOR.$path_url;
			}else{
				$site_url = $GLOBALS['mymuseStorage']->getSiteUrl().DIRECTORY_SEPARATOR.$path_url;
			}
		}
		return $site_url;
	}
	
	/**
	 * getSitePath get path for previews
	 *
	 * @param $id int
	 * @param $parent boolean // is this the parent?
	 *
	 * @return string
	 *
	 * @since   5.0.0
	 */
	static function getSitePath($id, $parent=0)
	{
		$params = self::$_params;

		if(1 == $params->get('my_previews_in_one_dir')){
			$site_path = $params->get('my_preview_dir').DIRECTORY_SEPARATOR;
			if( $params->get('storage', 'regular') == 'regular' ) {
				$site_path = JPATH_ROOT.DIRECTORY_SEPARATOR.$site_path;
			}else{
				$site_path = '';
			}
		}else{
			$artist_alias = MyMuseHelper::getArtistAlias($id,$parent);
			$album_alias = MyMuseHelper::getAlbumAlias($id,$parent);	
			$site_path = $params->get('my_preview_dir').DIRECTORY_SEPARATOR.$artist_alias.DIRECTORY_SEPARATOR.$album_alias.DIRECTORY_SEPARATOR;
			if( $params->get('storage', 'regular') == 'regular' ) {
				$site_path = JPATH_ROOT.DIRECTORY_SEPARATOR.$site_path;
			}
		}
		return $site_path;
	}
	
	/**
	 * getDownloadPath get path for downloads
	 *
	 * @param $id int
	 *
	 * @return string
	 *
	 * @since   5.0.0
	 */
	static function getDownloadPath($id, $parent=0)
	{
		$params = self::$_params;

		if($params->get('my_download_dir_format') == 1 ){
			$site_path = rtrim($params->get('my_download_dir'), '/').DIRECTORY_SEPARATOR;
		}else{
			$artist_alias = MyMuseHelper::getArtistAlias($id,$parent);
			$album_alias = MyMuseHelper::getAlbumAlias($id,$parent);
			$site_path = rtrim($params->get('my_download_dir'), '/').DIRECTORY_SEPARATOR.$artist_alias.DIRECTORY_SEPARATOR.$album_alias.DIRECTORY_SEPARATOR;
		}

		return $site_path;
	}

	/**
	 * findExt get file extension
	 * 
	 * @param $string filename
	 *
	 * @return string
	 *
	 * @since   5.0.0
	 */
	static function getExt($filename) { 
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		return $ext; 
	}
	
	/**
	 * Get status name from one letter code
	 * 
	 * @param string $code
	 *
	 * @since   5.0.0
	 */
	static function getStatusName($code){
		
		$db	= Factory::getDbo();
		$q = "SELECT name FROM #__mymuse_order_status WHERE ";
		$q .= "code = '".$code."' ";
		$db->setQuery($q);
		$status = $db->loadResult();	
		return $status;
		
	}

	/**
	 * Get fields for registration
	 *
	 * @return array
	 *
	 * @since   5.0.0
	 */
	static function getRegFields()
	{
		$fields = array(
				'address1',
				'address2',
				'city',
				'region',
				'country',
				'postal_code',
				'vat_number',
				'phone',
				'mobile',
				'fax',
				'shopper_group',
				'category_owner',
				'tos',
				'shipping_add_address',
				'shipping_address1',
				'shipping_address2',
				'shipping_city',
				'shipping_region',
				'shipping_region_name',
				'shipping_country',
				'shipping_postal_code'
		);
	
		return $fields;
	}
	
	/**
	 * Get fields for no registration
	 * 
	 * @return array
	 *
	 * @since   5.0.0
	 */
	static function getNoRegFields()
	{
		$fields = array(
				'name',
				'first_name',
				'last_name',
				'email',
				'address1',
				'address2',
				'city',
				'region',
				'region_name',
				'country',
				'postal_code',
				'vat_number',
				'phone',
				'mobile',
				'tos',
				'shipping_first_name',
				'shipping_last_name',
				'shipping_add_address',
				'shipping_address1',
				'shipping_address2',
				'shipping_city',
				'shipping_region',
				'shipping_region_name',
				'shipping_country',
				'shipping_postal_code'
		);

		
		return $fields;
	}
	
	/**
	 * Method to update stock
	 * 
	 * @param int $id product id
	 * @param int $quantity the amount of stock just sold
	 *
	 * @return boolean
	 *
	 * @since   5.0.0
	 */
	function updateStock($id, $quantity=0)
	{
		if(!$quantity){
			return false;
		}
		if(!$id){
			return false;
		}
		$db = Factory::getDbo();

		$q = "SELECT product_in_stock from #__mymuse_product WHERE id=$id";
		$db->setQuery($q);
		$current_stock = $db->loadResult();
		if($current_stock < $quantity){
			$this->error = Text::_('MYMUSE_STOCK_LESS_THAN_QUANTITY');
			return false;
		}

		$q = "UPDATE #__mymuse_product SET product_in_stock = product_in_stock - $quantity
		WHERE id=$id";
		$db->setQuery($q);
		if(!$db->query()){
			$this->error = $db->getErrorMsg();
			return false;
		}
		return true;
	}

	/**
	 * Convert Weight Unit
	 *
	 * @param float $value
	 * @param string $from
	 * @param string $to
	 *
	 * @return float
	 *
	 * @since   5.0.0
	 */
	static function convertWeightUnit ($value, $from, $to) {

		$from = strtoupper($from);
		$to = strtoupper($to);
		$value = str_replace (',', '.', $value);
		if ($from === $to) {
			return $value;
		}

		$g = (float)$value;

		switch ($from) {
			case 'KG':
				$g = (float)(1000 * $value);
			break;
			case 'MG':
				$g = (float)($value / 1000);
			break;
			case 'LB':
				$g = (float)(453.59237 * $value);
			break;
			case 'OZ':
				$g = (float)(28.3495 * $value);
			break;
		}
		switch ($to) {
			case 'KG' :
				$value = (float)($g / 1000);
				break;
			case 'G' :
				$value = $g;
				break;
			case 'MG' :
				$value = (float)(1000 * $g);
				break;
			case 'LB' :
				$value = (float)($g / 453.59237);
				break;
			case 'OZ' :
				$value = (float)($g / 28.3495);
				break;
		}
		return $value;
	}

	/**
	 * Convert Metric Unit
	 *
	 * @param float $value
	 * @param string $from
	 * @param string $to
	 *
	 * @return float
	 *
	 * @since   5.0.0
	 */
	static function convertDimensionUnit ($value, $from, $to) {

		$from = strtoupper($from);
		$to = strtoupper($to);
		$value = (float)str_replace (',', '.', $value);
		if ($from === $to) {
			return $value;
		}
		$meter = (float)$value;

		// transform $value in meters
		switch ($from) {
			case 'CM':
				$meter = (float)(0.01 * $value);
				break;
			case 'MM':
				$meter = (float)(0.001 * $value);
				break;
			case 'YD' :
				$meter =(float) (0.9144 * $value);
				break;
			case 'FT' :
				$meter = (float)(0.3048 * $value);
				break;
			case 'IN' :
				$meter = (float)(0.0254 * $value);
				break;
		}
		switch ($to) {
			case 'M' :
				$value = $meter;
				break;
			case 'CM':
				$value = (float)($meter / 0.01);
				break;
			case 'MM':
				$value = (float)($meter / 0.001);
				break;
			case 'YD' :
				$value =(float) ($meter / 0.9144);
				break;
			case 'FT' :
				$value = (float)($meter / 0.3048);
				break;
			case 'IN' :
				$value = (float)($meter / 0.0254);
				break;
		}
		return $value;
	}

	/**
	 * Return the countryname or code of a given countryID
	 *
	 * @param int $id Country ID
	 * @param char $fld Field to return: country_name (default), country_2_code or country_3_code.
	 *
	 * @return string Country name or code
	 *
	 * @since   5.0.0
	 */
	static public function getCountryByID ($id, $fld = 'country_name') {

		if (empty($id)) {
			return '';
		}

		$id = (int)$id;
		$db = Factory::getDbo ();

		$q = 'SELECT `' . $db->escape ($fld) . '` AS fld FROM `#__mymuse_country` WHERE id = ' . (int)$id;
		$db->setQuery ($q);
		$r = $db->loadObject ();
		return $r->fld;
	}

	/**
	 * Return the id of a given country name
	 *
	 * @param string $name Country name (can be country_name or country_3_code  or country_2_code )
	 *
	 * @return int id
	 *
	 * @since   5.0.0
	 */
	static public function getCountryIDByName ($name) {

		if (empty($name)) {
			return 0;
		}
		if (strlen ($name) === 2) {
			$fieldname = 'country_2_code';
		} else {
			if (strlen ($name) === 3) {
				$fieldname = 'country_3_code';
			} else {
				$fieldname = 'country_name';
			}
		}
		$db = Factory::getDbo ();
		$q = 'SELECT `id` FROM `#__mymuse_country` WHERE `' . $fieldname . '` = "' . $db->escape ($name) . '"';

		$db->setQuery ($q);
		return $db->loadResult ();
	}

	/**
	 * Return the statename or code of a given id
	 *
	 * @param int $id State ID
	 * @param char $fld Field to return: state_name (default), state_2_code or state_3_code.
	 *
	 * @return string state name or code
	 *
	 * @since   5.0.0
	 */
	static public function getStateByID ($id, $fld = 'state_name') {

		if (empty($id)) {
			return '';
		}
		$db = Factory::getDbo ();
		$q = 'SELECT ' . $db->escape ($fld) . ' AS fld FROM `#__mymuse_state` WHERE id = "' . (int)$id . '"';
		$db->setQuery ($q);
		$r = $db->loadObject ();
		return $r->fld;
	}

	/**
	 * Return the stateID of a given state name
	 *
	 * @param string $name Country name
	 *
	 * @return int virtuemart_state_id
	 *
	 * @since   5.0.0
	 */
	static public function getStateIDByName ($name) {

		if (empty($name)) {
			return 0;
		}
		$db = Factory::getDbo ();
		if (strlen ($name) === 2) {
			$fieldname = 'state_2_code';
		} else {
			if (strlen ($name) === 3) {
				$fieldname = 'state_3_code';
			} else {
				$fieldname = 'state_name';
			}
		}
		$q = 'SELECT `id` FROM `#__mymuse_state` WHERE `' . $fieldname . '` = "' . $db->escape ($name) . '"';
		$db->setQuery ($q);
		$r = $db->loadResult ();
		return $r;
	}

	/**
     * ByteSize
     * 
     * @param int $bytes
     *
     * @return string
     *
	 * @since   5.0.0
     */
	static function ByteSize($bytes) 
	{
    	$size = (int) $bytes / 1024;
    	if($size < 1024)
    	{
    		$size = number_format($size, 2);
    		$size .= ' KB';
    	}
    	else
    	{
    		if($size / 1024 < 1024)
    		{
    			$size = number_format($size / 1024, 2);
    			$size .= ' MB';
    		}
    		else if ($size / 1024 / 1024 < 1024)
    		{
    			$size = number_format($size / 1024 / 1024, 2);
    			$size .= ' GB';
    		}
    	}
    	return $size;
    } 


	/**
     * getCouponDiscount
     * 
     * @param object $order
     *
     * @return boolean
     *
	 * @since   5.0.0
     */
	static function getCouponDiscount(&$order){

		if(!isset($order->coupon->id)){
			return ;
		}

		if($order->coupon->coupon_type == 1){
			//it is for a product
			$found = 0;
			for($i=0;$i<count($order->items); $i++){
				if($order->coupon->product_id == $order->items[$i]->product_id){
					$order->coupon->discount_item_price = $order->items[$i]->product_item_price;
					$found = 1;
				}
				
			}
			if($found){
				if($order->coupon->coupon_value_type == 0){
					//it is a flat rate
					$order->coupon->discount = $order->coupon->coupon_value;
				
				}else{
					//it is a percent discount
					$order->coupon->discount = $order->coupon->coupon_value * $order->coupon->discount_item_price/100;
				}
			}else{
				$order->coupon->discount = 0;
			}
		}else{
			//it is for the whole order
			if($order->coupon->coupon_value_type == 0){
				//it is a flat rate
				$order->coupon->discount = $order->coupon->coupon_value;

			}else{
				//it is a percent discount
				$order->coupon->discount = $order->coupon->coupon_value * $order->order_subtotal/100;
			}

		}
			
		
		return true;
		
	}
	
	/**
	 * Return the last json_decode error
	 *
	 * @return  string
	 *
	 * @since   5.0.0
	 */
	
	static function getJsonError()
	{
		$message = '';
		if(function_exists('json_last_error')){
			switch (json_last_error()) {
				case JSON_ERROR_NONE:
					$message = 'JSON - No errors';
					$message = '';
					break;
				case JSON_ERROR_DEPTH:
					$message = 'JSON - Maximum stack depth exceeded';
					break;
				case JSON_ERROR_STATE_MISMATCH:
					$message = 'JSON - Underflow or the modes mismatch';
					break;
				case JSON_ERROR_CTRL_CHAR:
					$message = 'JSON - Unexpected control character found';
					break;
				case JSON_ERROR_SYNTAX:
					$message = 'JSON - Syntax error, malformed JSON';
					break;
				case JSON_ERROR_UTF8:
					$message = 'JSON - Malformed UTF-8 characters, possibly incorrectly encoded';
					break;
				default:
					$message = 'JSON - Unknown error';
					break;
			}
		}
		return $message;
	}

}