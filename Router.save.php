<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2021 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterBase;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Categories\Categories;

/**
 * Routing class for com_mymuse
 *
 * @since  3.3
 */
class Router extends RouterBase
{

	/**
	 * Build the route for the com_mymuse component
	 *
	 * @param   array  $query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
	{
//print_r($query);
		/* ex product: index.php?option=com_mymuse&view=category&layout=blog&id=11 */

	$params 	= MyMuseHelper::getParams();
	$menu		= Factory::getApplication()->getMenu();
	$advanced	= $params->get('sef_ids', 0);
	$segments 	= array();
	$dbo 		= Factory::getDbo();
	static $home_id;

	if(!$home_id){
		$q = 'SELECT id from #__menu WHERE home=1';
		$dbo->setQuery($q);
		$home_id = $dbo->loadResult();
	}

	// we need a menu item.  Either the one specified in the query, or the current active one if none specified
	if (empty($query['Itemid'])) {
		$menuItem = $menu->getActive();
		$menuItemGiven = false;
	}
	else {
		$menuItem = $menu->getItem($query['Itemid']);
		$menuItemGiven = true;
		//$query = $menuItem->query;
	}

	if($params->get('top_menu_item',0)){
		$q = 'SELECT alias from #__menu WHERE id="'.$params->get('top_menu_item').'"';
		$dbo->setQuery($q);
		if($alias = $dbo->loadResult()){
			
			if($menuItemGiven && $query['Itemid'] == $params->get('top_menu_item')){
				unset($query['view']);
				$segments[] = $alias;
				return $segments;
			}
		}

	}

	

	// we need to have a view in the query or it is an invalid URL
	if (isset($query['view'])) {
		$view = $query['view'];
	}
	else {
		return $segments;
	}
	
	// is there a top menu item specified?


	//echo $query['Itemid'];
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

    //top level store?
    if(isset($query['view']) && $query['view'] == "store"){

    }

	// are we dealing with an product or category that is attached to a menu item?
	if ($menuItemGiven && 
		$menuItem->query['view'] == $query['view'] && 
		isset($query['id']) && 
		isset($menuItem->query['id']) && 
		$menuItem->query['id'] == intval($query['id'])) 
	{
		unset($query['view']);

		if (isset($query['id'])) {
			unset($query['id']);
		}
		
		if (isset($query['layout'])) {
			unset($query['layout']);
		}

		//$segments[] = $menuItem->alias;
		return $segments;
	}

	if ($view == 'product'){

		//is there a menu item?
		if (strpos($query['id'], ':') === false) {
			$id = $query['id'];
		}else{
			list($id,$rest) = explode(':',$query['id']);
		}

		$pquery = $dbo->getQuery(true)
			->select('*')
			->from('#__menu')
			->where('link LIKE "%id='.$id.'%"')
			->where('link LIKE "%view=product%"')
			->where('link LIKE "%option=com_mymuse%"')
		;
		//echo $pquery->__toString();
		$dbo->setQuery($pquery);
		if($res = $dbo->loadObject()){
			//$segments['Itemid'] = $res->id;
			//$segments[] = $res->path;
			unset($query['view']);

			if (isset($query['id'])) {
				unset($query['id']);
			}
			
			if (isset($query['layout'])) {
				unset($query['layout']);
			}
			unset($query['Itemid']);
			return $segments;
		}
		
		
	}















	//Array([option]=com_mymuse,[view]=product,[id]=1,[catid]=9,[lang]=en-GB,[Itemid]=90)
	if ($view == 'category' || $view == 'product')
	{
		if($params->get('top_menu_item','')){
			$query['Itemid'] = $params->get('top_menu_item');
		}
		
		unset($query['view']);
		
		if ($view == 'product') {
			
			if($params->get('my_use_alias','')){
					$aquery = $dbo->setQuery($dbo->getQuery(true)
						->select('alias')
						->from('#__mymuse_product')
						->where('id='.(int)$query['id'])
					);
					$alias = $dbo->loadResult();
					$segments[] = $alias;
					unset($query['id']);
					unset($query['catid']);
					unset($query['layout']);
					unset($query['Itemid']);
					return $segments;
			}
			
			if (isset($query['id']) && isset($query['catid']) && $query['catid']) {
				$catid = $query['catid'];
				
				// Make sure we have the id and the alias
				if (strpos($query['id'], ':') === false) {
					
					$aquery = $dbo->setQuery($dbo->getQuery(true)
						->select('alias')
						->from('#__mymuse_product')
						->where('id='.(int)$query['id'])
					);
					$alias = $dbo->loadResult();
					$query['id'] = $query['id'].':'.$alias;
					
				}
			} else {
				// we should have these two set for this view.  If we don't, it is an error
				return $segments;
			}
		}
		else {
			if (isset($query['id'])) {
				$catid = $query['id'];
			} else {
				// we should have id set for this view.  If we don't, it is an error
				return $segments;
			}
		}


		if ($menuItemGiven && isset($menuItem->query['id'])) {
			$mCatid = $menuItem->query['id'];
		} else {
			$mCatid = 0;
		}

		$categories = Categories::getInstance('Mymuse');
		$category = $categories->get($catid);

		if (!$category) {
			// we couldn't find the category we were given.  Bail.
			return $segments;
		}

		$path = array_reverse($category->getPath());
		$array = array();
		foreach($path as $id) {
			if ((int)$id == (int)$mCatid) {
				break;
			}

			list($tmp, $id) = explode(':', $id, 2);

			$array[] = $id;
		}

		$array = array_reverse($array);
		if($params->get('my_use_alias')){

		}elseif (!$advanced && count($array)) {
			$array[0] = (int)$catid.':'.$array[0];
		}
		$array = [array_pop($array)];
		$segments = array_merge($segments, $array);
	
		if ($view == 'product') {
			if ($advanced || $params->get('my_use_alias')) {
				list($tmp, $id) = explode(':', $query['id'], 2);
			}
			else {
				$id = $query['id'];
			}
			$segments[] = $id;
		}
		unset($query['id']);
		unset($query['catid']);
		unset($query['Itemid']);
	}



		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  $segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
	
		$vars = array();

		//Get the active menu item.
		$app	= Factory::getApplication();
		$jinput = $app->input;
		$task = $jinput->get('task','');
		if($task == "user.logout"){
			return $segments;
		}

		$menu	= $app->getMenu();
		$item	= $menu->getActive();
		
		$params = MyMuseHelper::getParams();
		$advanced	= $params->get('sef_ids', 0);
		$dbo = Factory::getDBO();
		
		// Count route segments
		$count = count($segments);
		//MyMuseHelper::print_pre($segments); 
		//echo "item"; MyMuseHelper::print_pre($item); 


		// Standard routing for products.  If we don't pick up an Itemid then we get the view from the segments
		// the first segment is the view and the last segment is the id of the product or category.

	    if (!isset($item)) {
	    	if($params->get('top_menu_item','')){
	    		$item	= $menu->getItem($params->get('top_menu_item'));
	    		$jinput->set('Itemid', $params->get('top_menu_item'));
	    	}
	    }

	    if(isset($item->alias) && $item->alias == $segments[0]){
	    	$vars['option'] = 'com_mymuse';
	    	if($count == 1){
	    		$vars['view'] = $item->query['view'];

	    		return $vars;
	    	}
	    	$count--;
	    	$first = array_shift($segments);
	    }



		$segment = array_shift($segments);
	    //shipping|addtocart|updatecart|cartdelete|showcart|checkout
	    if($segment == "checkout"){
	        $vars['option'] = 'com_mymuse';
	        $vars['view'] = 'cart';
	        $vars['task'] = 'checkout';
	        
	        return $vars;
	    }
	    if($segment == "shipping"){
	        $vars['option'] = 'com_mymuse';
	        $vars['view'] = 'cart';
	        $vars['task'] = 'shipping';
	        
	        return $vars;
	    }
	    if($segment == "addtocart"){
	        $vars['option'] = 'com_mymuse';
	        $vars['view'] = 'cart';
	        $vars['task'] = 'addtocart';
	        
	        return $vars;
	    }
	    if($segment == "updatecart"){
	        $vars['option'] = 'com_mymuse';
	        $vars['view'] = 'cart';
	        $vars['task'] = 'updatecart';
	        
	        return $vars;
	    }
	    if($segment == "cartdelete"){
	        $vars['option'] = 'com_mymuse';
	        $vars['view'] = 'cart';
	        $vars['task'] = 'cartdelete';
	        
	        return $vars;
	    }
	    if($segment == "showcart"){
	        $vars['option'] = 'com_mymuse';
	        $vars['view'] = 'cart';
	        $vars['task'] = 'showcart';

	        return $vars;
	    }
	    if($segment == "register"){
	    	$vars['option'] = 'com_mymuse';
	    	$vars['view'] = 'shopper';
	    	$vars['task'] = 'register';
	    	$vars['layout'] = 'register';
	    
	    	return $vars;
	    }
	    if($segment == "confirm"){
	    	$vars['option'] = 'com_mymuse';
	    	$vars['view'] = 'cart';
	    	$vars['task'] = 'confirm';
	  
	    	return $vars;
	    }
	    if($segment == "thankyou"){
	    	$vars['option'] = 'com_mymuse';
	    	$vars['view'] = 'cart';
	    	$vars['task'] = 'thankyou';
	   
	    	return $vars;
	    }
	    if($segment == "vieworder"){
	    	$vars['option'] = 'com_mymuse';
	    	$vars['view'] = 'cart';
	    	$vars['task'] = 'vieworder';
	    
	    	return $vars;
	    }
	    if($segment == "downloads"){
	    	$vars['option'] = 'com_mymuse';
	    	$vars['view'] = 'store';
	    	$vars['task'] = 'downloads';

	    	return $vars;
	    }
	    if($segment == "accdownloads"){
	    	$vars['option'] = 'com_mymuse';
	    	$vars['view'] = 'store';
	    	$vars['task'] = 'downloads';
	    
	    	return $vars;
	    }
		if($segment == "vieworder"){
	    	$vars['option'] = 'com_mymuse';
	    	$vars['view'] = 'cart';
	    	$vars['task'] = 'vieworder';
	    	$vars['layout'] = 'cart';
	    	return $vars;
	    }
	    if($segment == "paycancel"){
	    	$vars['option'] = 'com_mymuse';
	    	$vars['view'] = 'cart';
	    	$vars['task'] = 'paycancel';
	    	$vars['layout'] = 'cart';
	    	return $vars;
	    }
	    if($segment == "downloadfile"){
	    	$vars['option'] = 'com_mymuse';
	    	$vars['view'] = 'store';
	    	$vars['task'] = 'downloadfile';
	    	return $vars;
	    }

		if(strpos($segment,':')){
	    	list($id, $alias) = explode(':', $segment, 2);
	    }else{
	    	//no numbers.
	    	$alias = $segment;
	    }


		//check if this is a product alias.
		$query = 'SELECT id,catid from #__mymuse_product WHERE alias="'.$alias.'"';

		$dbo->setQuery($query);
		if($product = $dbo->loadObject()){
			$vars['option'] = 'com_mymuse';
			$vars['view'] = 'product';
			$vars['id'] = (int)$product->id;
			$vars['catid'] = (int)$product->catid;
			//our work here is done
			return $vars;
		}

		$category = '';
		//check if this is a category alias.
		$query = 'SELECT id from #__categories WHERE alias="'.$alias.'" and extension="com_mymuse"';
		
		$dbo->setQuery($query);
		if($category = $dbo->loadResult()){
			$vars['option'] = 'com_mymuse';
			$vars['view'] = 'category';
			$vars['id'] = (int)$category;
			if(\count($segments) == 0){
				//our work here is done
				return $vars;
			}
		}
		$segment = array_shift($segments);



		//could be category/product
		
		$prodid = '';

		//first get category
		/*
		if($advanced){
	    	//no numbers.
	    	$cat_alias = $segment;
	    	//check if this is a category alias.
	    	$query = 'SELECT id from #__categories WHERE alias="'.$cat_alias.'" and extension="com_mymuse"';
	    	
	    	$dbo->setQuery($query);
	    	if($catid = $dbo->loadResult()){
	    		$vars['option'] = 'com_mymuse';
	    		$vars['catid'] = $catid;
	    	}
	    }elseif(strpos($segment,':')){
	    	list($catid, $cat_alias) = explode(':', $segment, 2);
	    	$category = JCategories::getInstance('Mymuse')->get($catid);
	    	if ($category && $category->alias == $alias) {
	    		$vars['option'] = 'com_mymuse';
				$vars['catid'] = $catid;
			}
	    }
	    */

	    if($category){
	    	//look for a product
	    	if($advanced){
	    		//no numbers.
	    		$prod_alias = $segment;
	    	}else{
				list($id, $prod_alias) = explode(':', $segment, 2);
			}

			$query = 'SELECT id FROM #__mymuse_product WHERE alias = "'.$prod_alias.'"';
			$dbo->setQuery($query);
			if($id = $dbo->loadResult()){
				$vars['view'] = 'product';
				$vars['catid'] = $category;
				$vars['id'] = (int)$id;

				//MyMuseHelper::print_pre($vars); exit;
				return $vars;
			}
	    }		

		return $vars;
	}
}