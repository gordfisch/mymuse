<?php
/**
 * @version   $Id$
 * @package   mymuse
 * @copyright Copyright © 2021 - Arboreta Internet Services - All rights reserved.
 * @license   GNU/GPL
 * @author    Gordon Fisch
 * @author mail info@joomlamymuse.com
 * @website   http://www.joomlamymuse.com
 */

namespace Joomla\Component\Mymuse\Site\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Dispatcher\Dispatcher;
use Joomla\Component\Mymuse\Site\Helper\CheckoutHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Site\Model\ProductModel;
use Joomla\Component\Mymuse\Administrator\Table\ProductTable;
use Joomla\Component\Categories\Administrator\Table\CategoryTable;
use Joomla\Registry\Registry;
use Joomla\Component\Mymuse\Site\Service\Mymuse;


class CartHelper
{

  /**
   * @var cart
   *
   * array
   */
  var $cart = null;
  
  /**
   * order object
   *
   * @var    order
   */
  var $order = null;

  /**
   * product model class
   *
   * @var    MyMuseProduct
   */
  var $MyMuseProduct = null;

  
  /**
  * @var error
  */
  var $error = null;
  
  /**
   * Constructor
   *
   */
  function __construct()
  {
    // Register the cart
    $session = Factory::getSession();
    if (!$session->get("cart",0)) {
      $this->cart = array();
      $this->cart["idx"] = 0;
    }else{
      $this->cart = $session->get("cart");
    }

    $this->MyMuseProduct = Mymuse::getObject('Product','model');
  }


  function getError(){
    return $this->error;
  }


  public function getCart(){
    return self::cart;
  }

  public function setCart(){
    $session = Factory::getSession();
    $session->set("cart",$this->cart);

  }

  
  /**
   * addToCart
   * 
   * @param array $cart The session cart
   * @param array $catid The category ids of the product
   * @param array $productid The product ids
   * @param array $quantity The quantities of the products
   * @param int $parentid Possible parentid
   * @return bool
   */
  
  public function addToCart() {

    // Process the cart preparation plugins
    $app            = Factory::getApplication();
    PluginHelper::importPlugin('system');
    $results        = $app->triggerEvent('onBeforeAddToCart', array (&$_POST, &$this->cart ));

    
    $jinput         = $app->input;
    $params         = MyMuseHelper::getParams();

    $catid          = $jinput->get('catid',  0, 'INT');
    $parentid       = $jinput->get('parentid',  0, 'INT');
    $productid      = $jinput->get('productid',array(), 'ARRAY');
    $quantity       = $jinput->get('quantity',array(), 'ARRAY');
    $variation      = $jinput->get('variation',array(), 'ARRAY');
    $item_quantity  = $jinput->get('item_quantity',array(), 'ARRAY');

    $db = Factory::getDBO();   

    if(!$productid){
        $this->error = Text::_("COM_MYMUSE_PLEASE_SELECT_PRODUCT");
        return false;
    }

    if (!is_array($productid)) {
        $prod_id = $productid;
        $productid = array();
        $productid["0"] = $prod_id;
    }

    if(!$quantity){
        foreach($productid as $pid){
          $quantity[$pid] = 1;
        }
    }
    
    if (!is_array($quantity)) {
        $quant = $quantity;
        $quantity = array();
        $quantity[$productid["0"]] = $quant;
    }

    if ($catid && !is_array($catid)) {
      $c = $catid;
      $catid = array();
      foreach($productid as $pid){
          $catid[$pid] = $c;
        }
    }
    reset($productid);

    // FOR EACH PRODUCT IN ARRAY

    foreach($productid as $key => $val) {
      if(!$val){ continue; }
      $product_id = $val;
      
      if(!isset($quantity[$val])){
          $quantity[$val] =1;
      }
      
      $quant = $quantity[$val];
      
      // Check for negative quantity
      if ($quant < 0) {
            $this->error = Text::_('COM_MYMUSE_NEGATIVE_QUANTITY');
            return False;
      }

      if (!is_numeric($quant)) {
            $this->error = Text::_('COM_MYMUSE_INVALID_QUANTITY');
            return False; 
      }
      $v = 0;
      if(isset($variation[$val])){
        $v = $variation[$val];
      }

      $res = $this->getProduct($product_id, $v);
      $category_id = $res->catid;
      $product_physical = $res->product_physical;

      // Check to see if checking stock quantity
      $backordered = 0;
      if ($params->get('my_check_stock',0)) {
  
        if($res->product_physical){


          if(isset($res->special_status)  && 
              ( $res->special_status == "COM_MYMUSE_COMING_SOON" || 
                $res->special_status == "COM_MYMUSE_OUT_OF_STOCK" || 
                $res->special_status == "COM_MYMUSE_NO_LONGER_AVAILABLE" 
              )
            ){
              $this->error = Text::_($res->special_status);
              return false;
          }
          
          //not enough stock or pre-order
          if ( $quant > $res->product_in_stock || $res->special_status == "COM_MYMUSE_PREORDER"){
            
            if($res->special_status == "COM_MYMUSE_PREORDER"){
                $backordered = 1;
                $msg = $res->title;
                $msg .= ' '. Text::_('COM_MYMUSE_BACKORDERED');
                Factory::getApplication()->enqueueMessage($msg , 'notice');

            }elseif($params->get('my_add_stock_zero',0)) {
               // $quantity[$val]  = $quant = 0;
                $backordered = 1;
                $msg = $res->title.' '.Text::_('COM_MYMUSE_EXCEEDS_AVAILABLE_STOCK').' ';
                $msg .= Text::_('COM_MYMUSE_AVAILABLE_STOCK')." ".$res->product_in_stock;
                $msg .= ' '. Text::_('COM_MYMUSE_BACKORDERED');
                Factory::getApplication()->enqueueMessage($msg , 'notice');

            }else{
                $this->error = $res->title.' '.Text::_('COM_MYMUSE_EXCEEDS_AVAILABLE_STOCK')." ";
                $this->error .= Text::_('COM_MYMUSE_AVAILABLE_STOCK')." ".$res->product_in_stock;
                return false;
            }
          }
          
        } // end physical check
      } // end check stock


      // Tracks: MYMUSE_COMING_SOON, preorder, release date
      if($res->product_downloadable){

          if(isset($res->special_status) && $res->special_status == "COM_MYMUSE_NO_LONGER_AVAILABLE" ){
              $this->error = Text::_($res->special_status);
              return false;
            }
            $q = "SELECT 
            CASE
            WHEN (product_release_date = '0000-00-00') THEN '0'
            WHEN (product_release_date > CURDATE()) THEN 'future' 
            WHEN (product_release_date <= CURDATE()) THEN 'good'
            ELSE 0
            END as product_release_date ";
            $q .= "FROM #__mymuse_product ";
            $q .= "WHERE id = $res->parentid ";

            $db->setQuery($q);
            $product_release_date = $db->loadResult();
    
            if(isset($res->special_status) && $res->special_status == "COM_MYMUSE_PREORDER" && 
              ($product_release_date == 'future' || $product_release_date == '0') ){
              $backordered = 1;
              $msg = Text::_('COM_MYMUSE_PREORDERED').' ';
              Factory::getApplication()->enqueueMessage($msg);
            }
      }



      $updated = 0;
      // Check for duplicate and add to current quantity
      for ($i=0;$i<$this->cart["idx"];$i++) {
            if (@$this->cart[$i]["product_id"] == $product_id && 
                @$variation[$product_id] == @$this->cart[$i]["variation"]) {
                  $updated = 1;
                  $this->cart[$i]["quantity"] += $quantity[$val];
                  $this->cart[$i]["backordered"] = $backordered;
            }
      }
  
      // If we did not update then add the item
      if (!$updated) {
          $this->cart[$this->cart["idx"]]["quantity"] = $quant;
          $this->cart[$this->cart["idx"]]["product_id"] = $product_id;
          $this->cart[$this->cart["idx"]]["catid"] = $category_id;
          $this->cart[$this->cart["idx"]]["product_physical"] = $product_physical;
          $this->cart[$this->cart["idx"]]["variation"] = (isset($variation[$product_id]))? $variation[$product_id] : '';
          $this->cart[$this->cart["idx"]]["backordered"] = $backordered;
          
          $this->cart["idx"]++;
      }
    } // end of while loop

    //move coupons to the end
    $j = 0;
    $coupon_id = 0;
    $fixed = array();
    $fixed['idx'] = 0;
    for ($i=0;$i<$this->cart["idx"];$i++) {
            if(isset($this->cart[$i]["coupon_id"])){
                    $coupon_id = $this->cart[$i]["coupon_id"];
                    unset($this->cart[$i]["coupon_id"]);
                    continue;
            }
            $fixed[$j]["quantity"] = $this->cart[$i]["quantity"];
            $fixed[$j]["product_id"] = $this->cart[$i]["product_id"];
            $fixed[$j]["catid"] = $this->cart[$i]["catid"];
            $fixed[$j]["product_physical"] = @$this->cart[$i]["product_physical"];
            $fixed[$j]["variation"] = $this->cart[$i]["variation"];
            $fixed[$j]["backordered"] = $this->cart[$i]["backordered"];
            $j++;
            $fixed['idx']++;
    }
    if($coupon_id){
        $fixed[$j]["coupon_id"] = $coupon_id;
        $fixed['idx']++;
    }
    if(isset($this->cart['extra'])){
      $fixed['extra'] = $this->cart['extra'];
    }
    $this->cart = $fixed;

    $this->buildOrder(1,1);

    return true; 
  }


    /**
     * updateCart
     * 
     * @param array $cart The session cart
     * @param array $productid The product ids
     * @param array $quantity The quantities of the products
     * @return bool
     */ 
    public function updateCart( ) 
    {

      $app    = Factory::getApplication();
      $jinput   = $app->input;
      $params   = MyMuseHelper::getParams();
    
      $catid    = $jinput->get('catid',  0, 'INT');
      $parentid   = $jinput->get('parentid',  0, 'INT');
      $productid  = $jinput->get('productid',array(), 'ARRAY');
      $quantity   = $jinput->get('quantity',array(), 'ARRAY');
      $variation  = $jinput->get('variation',array(), 'ARRAY');
      $Itemid   = $jinput->get('Itemid',  0, 'INT');
      $licence  = $jinput->get('licence',  0, 'INT');
      $notes    = $jinput->get('notes', '', 'RAW');

 
      $db  = Factory::getDBO();;
        if(!@$productid){
            $this->error = Text::_('COM_MYMUSE_CANT_UPDATE_CART');
            return false;
        }

        if (!is_array(@$productid)) {
            $prod_id = $productid;
            $productid = array();
            $productid["0"] = $prod_id;
        }
    
        if (!is_array(@$quantity)) {
            $quant = $quantity;
            $quantity = array();
            $quantity[$productid["0"]] = $quant;
        }

        reset($productid);
    
        // FOR EACH PRODUCT  IN ARRAY
    
 
        foreach($productid as $key => $val) {
            $product_id = $val;
            $quant = (isset($quantity[$val]))? $quantity[$val] : 1;
    
            // Check for negative quantity
            if ($quant < 0) {
                $this->error = Text::_('COM_MYMUSE_NEGATIVE_QUANTITY');
                return False;
            }
    
            if (!preg_match("/^[0-9]*$/", $quant)) {
                $this->error = Text::_('COM_MYMUSE_INVALID QUANTITY');
                return False;
            }
            $backordered = 0;
          
            // Check to see if checking stock quantity
            if ($params->get('my_check_stock',0)) {

              $q = "SELECT product_in_stock, title ";
              $q .= "FROM #__mymuse_product ";
              $q .= "WHERE id= $product_id ";

              $db->setQuery($q);
              $res = $db->loadObject();


              $product_in_stock = $res->product_in_stock;

              if ($quant > $product_in_stock) {
                    if($params->get('my_add_stock_zero',0)) {
                        //$quant = 0;
                        $msg = $res->title.' '.Text::_('COM_MYMUSE_EXCEEDS_AVAILABLE_STOCK').' ';
                        $msg .= Text::_('COM_MYMUSE_AVAILABLE_STOCK')." ".$product_in_stock;
                        Factory::getApplication()->enqueueMessage($msg , 'warning');
                        $backordered = 1;
                    }else{
                        $this->error = $res->title.' '.Text::_('COM_MYMUSE_EXCEEDS_AVAILABLE_STOCK').' ';
                        $this->error .= Text::_('COM_MYMUSE_AVAILABLE_STOCK')." ".$product_in_stock;
                        return False;
                    }
              }else{
                $backordered = 0;
              }
            }
    
            if ($quant == 0) {
              if(!$params->get('my_add_stock_zero',1)) {
                    $this->delete($product_id);
                }

            }else{
    
                for ($i=0;$i<$this->cart["idx"];$i++) {
                    if (isset($this->cart[$i]["product_id"]) && $this->cart[$i]["product_id"] == $product_id) {
                        $this->cart[$i]["quantity"] = $quant;
                        $this->cart[$i]["variation"] = isset($variation[$product_id])? $variation[$product_id] : 0;
                        $this->cart[$i]["backordered"] = $backordered;
                    }
                }
            }
        }
        //move coupons to the end
        $j = 0;
        $coupon_id = 0;
        $fixed = array();
        $fixed['idx'] = 0;
        for ($i=0;$i<$this->cart["idx"];$i++) {
          if(isset($this->cart[$i]["coupon_id"])){
            $coupon_id = $this->cart[$i]["coupon_id"];
            unset($this->cart[$i]["coupon_id"]);
            continue;
          }

          $fixed[$j]["quantity"] = $this->cart[$i]["quantity"];
          $fixed[$j]["product_id"] = $this->cart[$i]["product_id"];
          $fixed[$j]["catid"] = $this->cart[$i]["catid"];
          $fixed[$j]["product_physical"] = $this->cart[$i]["product_physical"];
          $fixed[$j]["variation"] = $this->cart[$i]["variation"];
          $fixed[$j]["backordered"] = $this->cart[$i]["backordered"];
          $j++;
          $fixed['idx']++;
        }
        if($coupon_id){
          $fixed[$j]["coupon_id"] = $coupon_id;
          $fixed['idx']++;
        }
        if(isset($this->cart['extra'])){
          $fixed['extra'] = $this->cart['extra'];
        }
        $this->cart = $fixed;
        
        if($licence){
          $this->cart['licence'] = $licence;
        }
        if($notes){
          $this->cart['notes'] = $notes;
        }
        $this->setCart();
        $this->buildOrder(1,1);
        return True;
    }
  
    /**
     * delete remove an item from the cart
     * 
     * @return bool
     */    
    public function delete($product_id) {
      $jinput = Factory::getApplication()->input;
      $variationid  = $jinput->get('variationid', '', 'int');
      $temp = array();

      $j = 0;
      for ($i=0; $i < $this->cart["idx"]; $i++) {

        if (isset($this->cart[$i]['product_id']) && $this->cart[$i]['product_id'] != $product_id){
          if(isset($this->cart[$i]['variationid']) && $this->cart[$i]['variationid'] != $variationid){
            $temp[$j++] = $this->cart[$i];
          }elseif(!isset($this->cart[$i]['variationid']) || !$variationid){
            $temp[$j++] = $this->cart[$i];
          }
        }
      }
      $temp["idx"] = $j;
      $this->cart = $temp;
      $this->setCart();
      $this->buildOrder(1,1);
      return True;
    }


    /**
     * reset
     * clear the cart
     *
     * @return bool
     */   
    function reset() { 
      $this->cart = array();
      $this->cart["idx"]=0;
      $session = Factory::getSession();
      $session->set("cart",$this->cart);
      return True;
    }
    
    
    /**
     * shipping_needed
     * chick if shipping_ is needed
     *
     * @return bool
     */   
    function shipping_needed() { 

      $shipping_needed = false;
      for ($i=0; $i < $this->cart["idx"]; $i++) {
        if(!isset($this->cart[$i]["product_id"]) ){ continue;}
        if($this->cart[$i]['product_physical']){
          $shipping_needed = true;
        }
      }
      return $shipping_needed;
    }

    
    public function couponadd() {
      $db       =  Factory::getDBO();
      $user     =  Factory::getApplication()->getIdentity();
      $user_id  = $user->get('id');
      $app      = Factory::getApplication();
      $jinput   = $app->input;
      $params   = MyMuseHelper::getParams();
      
      $coupon_value = $jinput->get('coupon', '');
      $query = "SELECT * FROM #__mymuse_coupon WHERE code='$coupon_value'
      AND state='1'";
      $db->setQuery($query);
      $coupon = $db->loadObject();
      if(!isset($coupon->id)){
        $this->error = Text::_("COM_MYMUSE_COUPON_COULD_NOT_FIND");
        return false;
      }

      //see if it has maxed out uses
      if($coupon->coupon_max_uses > 0 && $coupon->coupon_uses >= $coupon->coupon_max_uses){
        $this->error = Text::_("COM_MYMUSE_COUPON_USE_EXCEEDS_MAX_USE");
        return false;
      }
      
      //see if it has maxed out uses by user
      if($params->get('my_registration') != "no_reg"){
        $query = "SELECT count(*) as num FROM #__mymuse_order
        WHERE user_id='$user_id' AND coupon_id='".$coupon->id."'";
        $db->setQuery($query);
        $num = $db->loadResult();
        if($coupon->coupon_max_uses_per_user > 0 && $num >= $coupon->coupon_max_uses_per_user){
          $this->error = Text::_("COM_MYMUSE_COUPON_USE_EXCEEDS_MAX_USE_BY_USER");
          return false;
        }
      }
      
      
      $date = Factory::getDate();
      $now = $date->toSQL();

      //see if it is expired
      $query = "SELECT id FROM #__mymuse_coupon WHERE code='$coupon_value'
      AND expiration_date >= '$now' OR expiration_date ='0000-00-00 00:00:00'";
      $db->setQuery($query);
      if(!$db->loadResult()){
        $this->error = Text::_("COM_MYMUSE_COUPON_EXPIRED");
        return false;
      }
      
      //see if it is started
      $query = "SELECT id FROM #__mymuse_coupon WHERE code='$coupon_value'
      AND start_date <= '$now' OR start_date ='0000-00-00 00:00:00'";
      $db->setQuery($query);
      if(!$db->loadResult()){
        $this->error = Text::_("COM_MYMUSE_COUPON_NOT_YET_VALID");
        return false;
      }
      
      
      
      // See if it is for a product in the cart
      if($coupon->coupon_type == 1 && $coupon->product_id > 0){
        $good = 0;
        for ($i=0;$i<$this->cart["idx"];$i++) {
                if ($this->cart[$i]["product_id"] == $coupon->product_id) {
                    $good = 1;
                }
            }
            if(!$good){
              $this->error = Text::_("COM_MYMUSE_COUPON_NO_MATCHING_PRODUCT");
              return false;
            }
      }
      
      // put it in the cart 
      $this->cart[$this->cart["idx"]]["coupon_id"] = $coupon->id;
      $this->cart["idx"]++;
      $this->setCart();
      $this->buildOrder(1,1);
      return true;
    }
    
    
    /**
     * Get a order object.
     *
     * Returns the global order object, only creating it if it doesn't already exist.
     *
     * @return  order object
     *
     */
    public  function buildOrder($edit = true, $new = false)
    {
      if (!$this->order || $new)
      {
        $this->order = $this->_buildOrder($edit);
      }
    
      return $this->order;
    }
    
    protected function _buildOrder($edit =  true )
    {

      $app            = Factory::getApplication();
      $jinput         = $app->input;
      $params         = MyMuseHelper::getParams();
      $MyMuseCheckout = new CheckoutHelper;
      $MyMuseProduct  = MyMuse::getObject('Product','Model');

      $user           = Factory::getApplication()->getIdentity();
      $preview_tracks = array();
      $Itemid         = $jinput->get('Itemid', '');
      $db             = Factory::getDBO();
  

      // just check that there is an order_item
      if ($this->cart["idx"] == 0) {
        $this->error = Text::_("COM_MYMUSE_YOUR_CART_IS_EMPTY");
        return false;
      }
      
        // FOR THE ORDER
      $order = new \stdClass();
      $order->order_subtotal          = 0.00;
      $order->order_subtotal_physical = 0.00;
      $order->must_pay_now            = 0.00;
      $order->tax_total               = 0.00;
      $order->reservation_fee         = 0.00;
      $order->reservation_fees        = array();
      $order->non_res_total           = 0.00;
      $order->order_shipping          = '';
      $order->need_shipping           = 0;
      $order->order_total             = 0.00;
      $order->discount                = 0.00;
      $order->shopper_group_discount  = 0.00;
      

      $order->idx       = $this->cart["idx"];
      $order->update_form   = '
              <!-- update form -->' .
  //         ' <form action="'.JURI::base().'" method="post" name="update">'
            '<input type="hidden" name="option" value="com_mymuse"/>
            <input type="hidden" name="task" value="updatecart"/>
            <input type="hidden" name="Itemid" value="'.$Itemid.'"/>
            <input type="hidden" name="ship_to_info_id" value="'.@$ship_to_info_id.'" />
            ';
      
      // FOR EACH CART ITEM
      for ($i=0;$i<$this->cart["idx"];$i++) {
        if(isset($this->cart[$i]["coupon_id"])){
          $coupon_id = $this->cart[$i]["coupon_id"];
          continue;
        }
        if(isset($this->cart[$i]["discount"])){
          $order->discount = $this->cart[$i]["discount"];
          continue;
        }
        if(!$this->cart[$i]["variation"]){
          $this->cart[$i]["variation"] = 0;
        }

        //get product
        $order->items[$i] = $this->getProduct($this->cart[$i]['product_id'], $this->cart[$i]["variation"] );

        if($order->items[$i]->product_downloadable){
          $preview_tracks[$i] = $order->items[$i];
        }
        $ext = '';

        $jason = $order->items[$i]->digital;
        if(is_array($jason)){
          
          $order->items[$i]->variation_select = '<select name="variation['.$this->cart[$i]['product_id'].']"
            id = "variationid_'.$this->cart[$i]['product_id'].'" class="inputbox myformatselect cart ">';

          //if multiple variations, create select box
          for($j=0; $j < count($jason); $j++){
            $order->items[$i]->variation_select .= '<option value="'.$j.'" ';
            if($j == $this->cart[$i]["variation"]){
              $order->items[$i]->variation_select .= 'SELECTED=SELECTED';
            } 
            $ff = isset($jason[$j]->file_format)? Text::_('COM_MYMUSE_'.strtoupper($jason[$j]->file_format)): $jason[$j]->file_ext;
            $order->items[$i]->variation_select .= '>'.$ff.'</option>'."\n";
          }
          $order->items[$i]->variation_select  .= "</select>";
          
          $order->items[$i]->file_name = isset($jason[$this->cart[$i]["variation"]]->file_name)?
            $jason[$this->cart[$i]["variation"]]->file_name : '';
          $order->items[$i]->ext = isset($jason[$this->cart[$i]["variation"]]->file_ext)?
            $jason[$this->cart[$i]["variation"]]->file_ext : '';
          $order->items[$i]->format = isset($jason[$this->cart[$i]["variation"]]->file_format)?
            $jason[$this->cart[$i]["variation"]]->file_format : '';


        }elseif(is_object($jason)){


          $order->items[$i]->file_name = isset($jason->file_name)?
            $jason->file_name : '';
          $order->items[$i]->ext = isset($jason->file_ext)?
            $jason->file_ext : '';
          $order->items[$i]->format = isset($jason->file_format)?
            $jason->file_format : '';

        }else{
          $order->items[$i]->ext = '';
        }
    
        //physical
        if($order->items[$i]->product_physical){ 
          //check dimentions
          $dim_fields = ['product_weight','product_weight_uom','product_length','product_width','product_height','product_lwh_uom' ];
          foreach($dim_fields as $field){
            if( (!isset($order->items[$i]->$field) || !$order->items[$i]->$field || $order->items[$i]->$field == 0.0000) && isset($order->items[$i]->parent->$field)){
              $order->items[$i]->$field = $order->items[$i]->parent->$field;
            }
          }
        }

        //backordered?

        $order->items[$i]->backordered = isset($this->cart[$i]["backordered"])? $this->cart[$i]["backordered"] : '';

        //other cats
        $othercats = array();

        if(isset($order->items[$i]->artistid) && $order->items[$i]->artistid){
          $query = "SELECT c.title FROM #__mymuse_product_category_xref as x
              LEFT JOIN #__categories as c ON c.id=x.catid
            WHERE product_id = '".$this->cart[$i]['product_id']."' AND catid != ".$order->items[$i]->catid."
                AND catid !=".$order->items[$i]->artistid;

          $db->setQuery($query);
          $res = $db->loadObjectList();
          if(count($res)){
            foreach($res as $r){
              $othercats[] = $r->title;
            }
          }
        }
        $othercats = array_unique($othercats);
        $order->items[$i]->othercats = implode(", ",$othercats);
        
        $order->items[$i]->product_id = $order->items[$i]->id;
        $order->items[$i]->order_item_total = 0.00;
        $order->items[$i]->not_in_total = 0;
        
        if($order->items[$i]->product_physical){
          $order->need_shipping = 1;
        }
   
        
        if($order->items[$i]->title == ""){
          $order->items[$i]->title = $item->parent->title;
          
        }

        $order->items[$i]->quantity = $this->cart[$i]['quantity'];

        $order->update_form .= '
              <input type="hidden" name="productid['.$i.']" value="'.$order->items[$i]->id.'"/>
              ';

        // GET PRICES
        $price = $order->items[$i]->price;

        if("1" == $params->get('my_price_by_product')){
          $ff = isset($order->items[$i]->format)? strtolower($order->items[$i]->format): $order->items[$i]->ext;
          $price = isset($price[$ff])? $price[$ff] : $price;
        }
        

        $order->items[$i]->product_item_price = $price['product_price'];


        $order->items[$i]->product_item_subtotal = $price['product_price'] * $order->items[$i]->quantity;
        $order->items[$i]->price = $price;
        // shopper group discount
        if(isset($price["product_shopper_group_discount_amount"]) && $price["product_shopper_group_discount_amount"] > 0){
          $order->shopper_group_discount += $price["product_shopper_group_discount_amount"] * $order->items[$i]->quantity;
        }

        // add to order sub_total
        $order->order_subtotal += $order->items[$i]->product_item_subtotal ;
        if($order->items[$i]->product_physical){
          $order->order_subtotal_physical += $order->items[$i]->product_item_subtotal;
        }
        if(!$order->items[$i]->not_in_total){
          $order->non_res_total += $order->items[$i]->product_item_subtotal;
        }

        $order->items[$i]->delete_url = "index.php?option=com_mymuse";
        $order->items[$i]->delete_url .= "&task=cartdelete&view=cart";
        $order->items[$i]->delete_url .= "&product_id=".$order->items[$i]->id;
        $order->items[$i]->delete_url .= "&Itemid=$Itemid";
        $order->items[$i]->delete_url = Route::_($order->items[$i]->delete_url);

        // Build URL 
        if ($order->items[$i]->parentid){
          $pid = $order->items[$i]->parentid;
        }else{
          $pid = $order->items[$i]->id;
        }
        $cat = $params->get('my_product_link', 'catid');
        $aid = $order->items[$i]->$cat;

        $order->items[$i]->url = RouteHelper::getProductRoute($pid, $aid);
        $order->items[$i]->cat_url = RouteHelper::getCategoryRoute($aid);
        $order->items[$i]->flash = ''; 
      
      } //end of cart items
      $order->subtotal_before_discount = $order->order_subtotal;
      //MymuseHelper::print_pre($order->items[0]);
  

      //RESERVATION FEES
      if(count($order->reservation_fees)){
        foreach($order->reservation_fees as $fee){
          $order->reservation_fee += $fee;
        }
      }

      //COUPONS
      if($params->get('my_use_coupons') && @$coupon_id){
        
        $query = "SELECT * from #__mymuse_coupon where id='".$coupon_id."'";
        $db->setQuery($query);
        if($order->coupon = $db->loadObject()){
          //this function sets the $order->coupon->discount
          $order->coupon->discount = 0;
          MyMuseHelper::getCouponDiscount($order);
          $order->order_subtotal = $order->order_subtotal - $order->coupon->discount;
          $order->coupon_discount = $order->coupon->discount;
          $order->coupon_id = $coupon_id;
          if(!count($order->reservation_fees)){
            $order->must_pay_now = $order->order_subtotal;
          }
        }
      }
      
      //SHOPPER GROUP DISCOUNTS
      $order->shopper_group_name = isset($shopper->shopper_group_name)? $shopper->shopper_group_name : 'default';

      // SHIPPING
      if ($params->get('my_use_shipping') && isset($this->cart['shipping'])) {
          $order->order_shipping = $this->cart['shipping'];
      }
      
      //DISCOUNTS FROM PLUGINS
      PluginHelper::importPlugin('COM_MYMUSE');
      $result = $app->triggerEvent('onAfterBuildOrder', array(&$order, &$this->cart));
      if(count($result)){
        foreach($result as $res){
          $order->discount += $res;
        }
      }

      $order->order_subtotal = $order->order_subtotal - $order->discount - $order->shopper_group_discount;
      if($order->order_subtotal < 0){
        $order->order_subtotal = 0.00;
      }

      //TAXES
      $order_tax = $MyMuseCheckout->calc_order_tax($order);

      $order->tax_array = array();
      foreach($order_tax as $key => $val) {
        if($val< 0){
          $val = 0.00;
        }
        $val = round($val,2);
        $order->tax_total += $val;
        $order->tax_array[$key] = $val;

      }
    
      $order->update_url = '<a class="links" href="" onClick="';
      $order->update_url .= "javascript:document.update.submit(); return false;";
      $order->update_url .= '">';

      //the big total
      $order->order_total  = $order->order_subtotal + @$order->tax_total + 
      @$order->order_shipping->cost;
      if($order->order_total < 0){
        $order->order_total = 0.00;
      }

      if(!$edit){
        $order->do_html = 0;
      }else{
        $order->do_html = 1;
      }

      $order->colspan=3;
      if($params->get("my_show_cart_preview")){
        $order->colspan = 4;
      } 
      if($params->get("my_show_sku")){
        $order->colspan = 5;
      }
      
      $order->colspan2 = 1;
      if(@$order->do_html){
        $order->colspan2 = 1;
      }
      
      
      //licence and notes
      $order->licence = isset($this->cart["licence"])? $this->cart["licence"] : '';
      $order->notes = isset($this->cart["notes"])? $this->cart["notes"] : '';
     
      $this->order = $order;
      return $order;
  }
  
  /**
   * getRecommended
   */
  public function getRecommended()
  {
    $db     = Factory::getDBO();
    $params   = MyMuseHelper::getParams();
    $prods    = array();
    $recommends = array();

    for ($i=0;$i<$this->cart["idx"];$i++) {
      if(isset($this->cart[$i]["coupon_id"])){
        continue;
      }
      $query = "SELECT parentid FROM #__mymuse_product
          WHERE id = '".$this->cart[$i]["product_id"]."'";
      $db->setQuery($query);
      if($p = $db->loadResult()){
        $productid = $p;
      }else{
        $productid = $this->cart[$i]["product_id"];
      }
      $query = "SELECT * FROM #__mymuse_product_recommend_xref 
          WHERE product_id = '".$productid."' 
              ORDER BY RAND()";
      $db->setQuery($query);
      $res = $db->loadObjectList();
      if(count($res)){
        foreach($res as $r){
          $prods[] = $r->recommend_id;
        }
      }
      
    }

    $prods = array_unique($prods);
    $num = min($params->get('my_max_recommended'),count($prods));

    for($i = 0; $i<$num; $i++){
      $query = "SELECT * FROM #__mymuse_product
          WHERE id = '".$prods[$i]."'";
      $db->setQuery($query);
    
      ;
      // Build URL
      if($recommends[$i] = $db->loadObject()){
        if (isset ( $recommends [$i]->parentid ) && $recommends [$i]->parentid) {
          $parent = new MymuseTableproduct ( $db );
          $parent->load ( $recommends [$i]->parentid );
          $recommends [$i]->parent = $parent;
          $recommends [$i]->list_image = $recommends [$i]->parent->list_image;
          $recommends [$i]->detail_image = $recommends [$i]->parent->detail_image;
          $pid = $recommends [$i]->parentid;
          $aid = $recommends [$i]->parent->catid;
        } else {
          $pid = $recommends [$i]->id;
          $aid = $recommends [$i]->catid;
        }
        $recommends[$i]->url = RouteHelper::getProductRoute ( $pid, $aid );
        $recommends[$i]->cat_url = RouteHelper::getCategoryRoute ( $aid );
      }
    
      
    }
    return $recommends;
  }

  
  /**
   * getProduct
   * 
   * @param int $id The product id
   * @param int $variation The product variation id
   * @return object The product object
   */
  public function getProduct($id=null, $variation=0)
  {
    
    $params   = MyMuseHelper::getParams();;
    
    
    if(!$id){
      $this->error = Text::_("COM_MYMUSE_NO_PRODUCT_ID");
      return false;
    }

    $db = Factory::getDBO();
    $model= new ProductModel();

    if(!$row = $model->getItem($id, $variation)){
      $this->error =  "Error: id $id could not be loaded. ".$model->getError();
      $this->delete($id);
      return false;
    }


    if(isset($shopper->shopper_group_id)){
      $shopper_group_id = $shopper->shopper_group_id;
    }else{
      $shopper_group_id = $params->get("my_default_shopper_group_id");
    }
    if(!isset($row->price)){
      $row->price = ProductModel::getPrice($row);
    }
    

    return $row;

  }
}
