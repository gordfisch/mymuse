<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Site\Service;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\RulesInterface;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;


/**
 * Rule to process URLs without a menu item
 *
 * @since  3.4
 */
class MymuseNomenuRules implements RulesInterface
{
    /**
     * Router this rule belongs to
     *
     * @var RouterView
     * @since 3.4
     */
    protected $router;

    

    /**
     * Params Registry object
     *
     * @var params
     * @since 3.4
     */
    protected $params = null;

    /**
     * use alias
     *
     * @var use_alias
     * @since 3.4
     */
    protected $use_alias = null;

    /**
     * top_menu_item
     *
     * @var top_menu_item
     * @since 3.4
     */
    protected $top_menu_item = null;

    /**
     * use sef_ids
     *
     * @var sef_ids
     * @since 3.4
     */
    protected $sef_ids = null;

    /**
     * Database object
     *
     * @var dbo
     * @since 3.4
     */
    protected $dbo = null;

    /**
     * Class constructor.
     *
     * @param   RouterView  $router  Router this rule belongs to
     *
     * @since   3.4
     */
    public function __construct(RouterView $router)
    {
        $this->router = $router;
        $this->dbo = $dbo = Factory::getDBO();

        $this->params = MymuseHelper::getParams('com_mymuse');
        if($this->params->get('my_use_alias')){
            $this->use_alias =1;
        }
        if($this->params->get('top_menu_item')){
            $this->top_menu_item = $this->params->get('top_menu_item');
        }
        if($this->params->get('sef_ids')){
            $this->sef_ids =1;
        }
    }

    /**
     * Dummymethod to fullfill the interface requirements
     *
     * @param   array  &$query  The query array to process
     *
     * @return  void
     *
     * @since   3.4
     * @codeCoverageIgnore
     */
    public function preprocess(&$query)
    {

    }

    /**
     * Parse a menu-less URL
     *
     * @param   array  &$segments  The URL segments to parse
     * @param   array  &$vars      The vars that result from the segments
     *
     * @return  void
     *
     * @since   3.4
     */
    public function parse(&$segments, &$vars)
    {
        //with this url: http://localhost/j4x/my-walks/mywalk-n/walk-title.html
        // segments: [[0] => mywalk-n, [1] => walk-title]
        // vars: [[option] => com_mymuse, [view] => mymuse, [id] => 0]

        //$vars['view'] = 'product';
        //$vars['id'] = substr($segments[0], strpos($segments[0], '-') + 1);
        //array_shift($segments);
        //array_shift($segments);

//print_r($segments);

        // Count route segments
        $count = count($segments);
        // if there is only one segment, then it points to either a product or a category
        // we test it first to see if it is a category.  If the id and alias match a category
        // then we assume it is a category.  If they don't we assume it is an product
        // or it could be a cart thing
        if ($count == 1) {
            
            //shipping|addtocart|updatecart|cartdelete|showcart|checkout
            if($segments[0] == "checkout"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'cart';
                $vars['task'] = 'checkout';
                array_shift($segments);
                return;
            }
            if($segments[0] == "shipping"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'cart';
                $vars['task'] = 'shipping';
                array_shift($segments);
                return;
            }
            if($segments[0] == "addtocart"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'cart';
                $vars['task'] = 'addtocart';
                array_shift($segments);
                return;
            }
            if($segments[0] == "updatecart"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'cart';
                $vars['task'] = 'updatecart';
                array_shift($segments);
                return;
            }
            if($segments[0] == "cartdelete"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'cart';
                $vars['task'] = 'cartdelete';
                array_shift($segments);
                return;
            }
            if($segments[0] == "showcart"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'cart';
                $vars['task'] = 'showcart';
                array_shift($segments);
                return;
            }
            if($segments[0] == "register"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'shopper';
                $vars['task'] = 'register';
                $vars['layout'] = 'register';
                array_shift($segments);
                return;
            }
            if($segments[0] == "confirm"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'cart';
                $vars['task'] = 'confirm';
                array_shift($segments);
                return;
            }
            if($segments[0] == "thankyou"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'cart';
                $vars['task'] = 'thankyou';
                array_shift($segments);
                return;
            }
            if($segments[0] == "vieworder"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'cart';
                $vars['task'] = 'vieworder';
                array_shift($segments);
                return;
            }
            if($segments[0] == "downloads"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'store';
                $vars['task'] = 'downloads';
                array_shift($segments);
                return;
            }
            if($segments[0] == "accdownloads"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'store';
                $vars['task'] = 'downloads';
                array_shift($segments);
                return;
            }
            if($segments[0] == "vieworder"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'cart';
                $vars['task'] = 'vieworder';
                $vars['layout'] = 'cart';
                array_shift($segments);
                return;
            }
            if($segments[0] == "paycancel"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'cart';
                $vars['task'] = 'paycancel';
                $vars['layout'] = 'cart';
                array_shift($segments);
                return;
            }
            if($segments[0] == "downloadfile"){
                $vars['option'] = 'com_mymuse';
                $vars['view'] = 'store';
                $vars['task'] = 'downloadfile';
                array_shift($segments);
                return;
            }


            if($this->params->get('my_use_alias')){
                $orig_segments = $segments;
                if(strpos($segments[0],':')){
                    
                    $segments[0] = preg_replace('/:/',"-",$segments[0]);
                }
            
                //check if this is a category alias.
                $query = 'SELECT id from #__categories WHERE alias="'.$segments[0].'" and extension="com_mymuse"';
                
                $this->dbo->setQuery($query);
                if($category = $this->dbo->loadObject()){
                    $vars['option'] = 'com_mymuse';
                    $vars['view'] = 'category';
                    $vars['id'] = (int)$category->id;
                    return;
                }
                
                //check if this is a product alias.
                $query = 'SELECT id,catid from #__mymuse_product WHERE alias="'.$segments[0].'"';

                $this->dbo->setQuery($query);
                if($product = $this->dbo->loadObject()){
                    $vars['option'] = 'com_mymuse';
                    $vars['view'] = 'product';
                    $vars['id'] = (int)$product->id;
                    $vars['catid'] = (int)$product->catid;
                    array_shift($segments);
                    //print_r($vars); exit;
                    return;
                }
                

            }

        }

        return;
    }

    /**
     * Build a menu-less URL
     *
     * @param   array  &$query     The vars that should be converted
     * @param   array  &$segments  The URL segments to create
     *
     * @return  void
     *
     * @since   3.4
     */
    public function build(&$query, &$segments)
    {
        // content of $query ($segments is empty or [[0] => mywalk-3])
        // when called by the menu: [[option] => com_mymuse, [Itemid] => 126]
        // when called by the component: [[option] => com_mymuse, [view] => product, [id] => 1, [Itemid] => 126]
        // when called from a module: [[option] => com_mymuse, [view] => product, [format] => html, [Itemid] => 126]
        // when called from breadcrumbs: [[option] => com_mymuse, [view] => product, [Itemid] => 126]

        // the url should look like this: /site-root/mymuse/walk-n/walk-title.html
//print_r($segments);
        //top_menu_item
        if($this->top_menu_item && $this->top_menu_item != $query['Itemid']
            && isset( $query['view']) && $query['view'] == 'product'){
            $aquery = $this->dbo->setQuery($this->dbo->getQuery(true)
                        ->select('alias')
                        ->from('#__menu')
                        ->where('id='.(int)$this->top_menu_item)
                    );
            $alias = $this->dbo->loadResult();
            $segments[] = $alias;
            unset($query['catid']);
            $query['Itemid'] = $this->top_menu_item;

            
        }
        //get alias/path
        if($this->use_alias){
            if(isset($query['view']) && $query['view'] == 'product' && isset($query['id'])){
                
                $aquery = $this->dbo->setQuery($this->dbo->getQuery(true)
                            ->select('alias')
                            ->from('#__mymuse_product')
                            ->where('id='.(int)$query['id'])
                        );
                $alias = $this->dbo->loadResult();
                $segments[] = $alias;
               
                unset($query['view']);
                unset($query['id']);
            }
        }

        if(isset($query['task']) && $query['task'] == "checkout"){
            unset($query['task']);
            unset($query['view']);
            $segments[] = "checkout";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "shipping"){
            unset($query['task']);
            unset($query['view']);
            $segments[] = "shipping";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "addtocart"){
            unset($query['task']);
            unset($query['view']);
            $segments[] = "addtocart";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "updatecart"){
            unset($query['task']);
            unset($query['view']);
            $segments[] = "updatecart";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "cartdelete"){
            unset($query['task']);
            unset($query['view']);
            $segments[] = "cartdelete";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "showcart"){
            unset($query['task']);
            unset($query['view']);
            $segments[] = "showcart";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "register"){

            unset($query['task']);
            unset($query['view']);
            $segments[] = "register";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "confirm"){
        
            unset($query['task']);
            unset($query['view']);
            $segments[] = "confirm";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "thankyou"){

            unset($query['task']);
            unset($query['view']);
            $segments[] = "thankyou";
            return $segments;
        }   
        if(isset($query['task']) && $query['task'] == "vieworder"){
        
            unset($query['task']);
            unset($query['view']);
            $segments[] = "vieworder";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "accdownloads"){
            
            unset($query['task']);
            unset($query['view']);
            $segments[] = "accdownloads";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "downloads"){
        
            unset($query['task']);
            unset($query['view']);
            $segments[] = "downloads";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "vieworder"){
        
            unset($query['task']);
            unset($query['view']);
            $segments[] = "vieworder";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "paycancel"){
        
            unset($query['task']);
            unset($query['view']);
            $segments[] = "paycancel";
            return $segments;
        }
        if(isset($query['task']) && $query['task'] == "downloadfile"){
        
            unset($query['task']);
            unset($query['view']);
            $segments[] = "downloadfile";
            return $segments;
        }
        
        if(!isset($query['view'])){
            return $segments;
        }

        $view = $query['view'];
        if ($view == 'category' || $view == 'product')
        {

            if (!isset($query['Itemid'])) {
                $segments[] = $view;
            }

            if($this->params->get('top_menu_item','')){
                $query['Itemid'] = $this->params->get('top_menu_item');
            }
            
            

            unset($query['view']);
            
            if ($view == 'product') {
                
                
                
                if($this->params->get('top_menu_item','') && $this->params->get('my_use_alias','')){
                        $aquery = $this->dbo->setQuery($this->dbo->getQuery(true)
                            ->select('alias')
                            ->from('#__mymuse_product')
                            ->where('id='.(int)$query['id'])
                        );
                        $alias = $this->dbo->loadResult();
                        $segments[] = $alias;
                        unset($query['id']);
                        unset($query['catid']);
                        return $segments;
                }
                
                
                
                if (isset($query['id']) && isset($query['catid']) && $query['catid']) {
                    $catid = $query['catid'];
                    
                    // Make sure we have the id and the alias
                    if (strpos($query['id'], ':') === false) {
                        
                        $aquery = $this->dbo->setQuery($this->dbo->getQuery(true)
                            ->select('alias')
                            ->from('#__mymuse_product')
                            ->where('id='.(int)$query['id'])
                        );
                        $alias = $this->dbo->loadResult();
                        $query['id'] = $query['id'].':'.$alias;
                        
                    }
                } else {
                    // we should have these two set for this view.  If we don't, it is an error
                    return $segments;
                }
            }
            else {  // $view == category
                if (isset($query['id'])) {
                    $catid = $query['id'];
                } else {
                    // we should have id set for this view.  If we don't, it is an error
                    return $segments;
                }
            }
/*
            if ($menuItemGiven && isset($menuItem->query['id'])) {
                $mCatid = $menuItem->query['id'];
            } else {
                $mCatid = 0;
            }
*/
            //$categories = Categories::getInstance('Mymuse');
            


            $category = $this->router->getCategories(['access' => true])->get($catid);

            if (!$category) {
                // we couldn't find the category we were given.  Bail.
                return $segments;
            }

            $path = array_reverse($category->getPath());

            $array = array();

            foreach($path as $id) {
                if ((int)$id == (int)$catid) {
                    break;
                }

                list($tmp, $id) = explode(':', $id, 2);

                $array[] = $id;
            }

            $array = array_reverse($array);
 
            if($this->params->get('my_use_alias')){

            }elseif (!$this->sef_ids && count($array)) {
                $array[0] = (int)$catid.':'.$array[0];
            }
           //print_r($segments);
            $segments = array_merge($segments, $array);
        
            if ($view == 'product') {
                if ($this->sef_ids || $this->params->get('my_use_alias')) {
                    list($tmp, $id) = explode(':', $query['id'], 2);
                }
                else {
                    $id = $query['id'];
                }
                $segments[] = $id;
            }
            unset($query['id']);
            unset($query['catid']);
        }



        return $segments;

/*

    

        // if the view is not mywalk - the single walk view
        if (!isset($query['view']) || (isset($query['view']) && $query['view'] !== 'product') || isset($query['format']))
        {
            return;
        }
        $segments[] = $query['view'] . '-' . $query['id'];
        // the last part of the url may be missing
        if (isset($query['slug'])) {
            $segments[] = $query['slug'];
            unset($query['slug']);
        }
        unset($query['view']);
        unset($query['id']);
        */
    }
}