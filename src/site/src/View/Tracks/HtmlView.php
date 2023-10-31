<?php
/**
 * @version     $Id: view.html.php 1930 2017-11-24 14:04:05Z gfisch $
 * @package     com_mymuse3.0
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 */


namespace Joomla\Component\Mymuse\Site\View\Tracks;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Mymuse\Administrator\Extension\MymuseComponent;
use Joomla\Component\Mymuse\Site\Helper\AssociationHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

use Joomla\CMS\Uri\Uri;
use Joomla\Component\Mymuse\Site\Service\Mymuse;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

/**
 * HTML View class for the MyMuse component
 *
 */
class HtmlView extends BaseHtmlView
{
	protected $state;
	protected $items;
	protected $category;
	protected $pagination;
    protected $cart;
 
    
	function display($tpl = null)
	{
		$app	= Factory::getApplication();
		$user	= Factory::getApplication()->getIdentity();
		$db     = Factory::getDBO();
		$jinput = $app->input;
		//MymuseHelper::print_pre($jinput);
		$app 	= Factory::getApplication();
		$menu 	= $app->getMenu()->getActive()->id;

		// Get some data from the models
		$state		= $this->get('State');
		$params		= $state->params;
		$this->params 		= $params;

		$category	= $this->get('Category');
       // $products   = $this->get('Products');// sets list.prods for tracks query



        
        $this->state 			= $this->get('State');
        $this->sortDirection    = $this->state->get('list.direction');
        $this->sortColumn       = $this->state->get('list.ordering');
        $this->Itemid           = $jinput->get('Itemid', $menu);
        $filter_alpha           = $jinput->get('filter_alpha', '', 'STRING');
        $this->task             = $jinput->get('task', 'view', 'STRING');
        $Itemid 				= $jinput->get("Itemid",'');
        $layout   				= $jinput->get("layout",'tracks');
        $this->store			= Mymuse::getObject('Store','model')->getStore();
		$this->shopper 			= Mymuse::getObject('Shopper','model')->getShopper();
		$MyMuseCart				= Mymuse::getObject('Cart','helper');
		$this->cart 			= $MyMuseCart->cart;
        
        if($layout){
        	$this->setLayout($layout);
        }

		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$active	= $app->getMenu()->getActive();

		if ( $this->getLayout() != "tracks" && ( !$active || ((strpos($active->link, 'view=category') === false) || (strpos($active->link, '&id=' . (string) $category->id) === false)))) {
			// Get the layout from the merged category params
			if ($layout = $category->params->get('category_layout')) {
				$this->setLayout($layout);
			}
		}
		// At this point, we are in a menu item, so we don't override the layout
		elseif (!$layout && isset($active->query['layout'])) {
				
			// We need to set the layout from the query in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}

		//items!
        $result = $this->get('Items');

        $query = "SELECT sub.id FROM #__categories as sub 
			INNER JOIN #__categories as this ON sub.lft > this.lft 
			AND sub.rgt < this.rgt WHERE this.id = " . $category->id;

		$db->setQuery ( $query );
		if($cat_ids = $db->loadColumn()){
			$cat_ids = implode(',',$cat_ids);
		}else{
			$cat_ids = 0;
		}
		


        if($result){
			$items = $result [0];
			$pagination = $result [2];
		}else{
			$items = array();
			$pagination = NULL;
		}

			
			if ($params->get ( 'show_alphabet', 1 )) {
				$alpha = array ();
				$class = '';
				if ($filter_alpha == "ALL") {
						$class = "selected";
					}
				$alpha [] = '<a class="letter ' . $class . '" href="' . Route::_ ( 'index.php?option=com_mymuse&view=tracks&layout=tracks&id=' . $category->id . '&filter_alpha=ALL&Itemid=' . $this->Itemid ) . '">' . Text::_('COM_MYMUSE_ALL') . '</a>';

				$alphabet = explode ( ":", Text::_ ( 'COM_MYMUSE_ALPHABET' ) );
				$IN = $state->get ( 'list.prods', '' );
				$featured = $params->get ( 'featured', '0' );
				
				foreach ( $alphabet as $letter ) {
					$query = "SELECT count(*) as total
            FROM #__mymuse_product as a
            LEFT JOIN #__mymuse_product as p ON a.parentid = p.id
            LEFT JOIN #__categories as c ON a.artistid = c.id
            WHERE a.product_downloadable = 1
            AND a.state=1
            AND c.title LIKE '$letter%'

            AND (a.artistid = " . $category->id . " OR a.artistid IN ( $cat_ids ) 
			OR a.id IN (0)) 
            ";
					// echo $query."<br />";
					if ($featured) {
						$query .= " AND a.featured=1
            	";
					}
					
					$db->setQuery ( $query );
					$total = $db->loadResult ();
					/**
					 * $total = 0;
					 * foreach($items as $item){
					 * //echo $item->category_name."<br />";
					 * if(strtolower($letter) == strtolower(mb_substr($item->category_name, 0, 1))){
					 * $total = 1;
					 *
					 * }
					 * }
					 */
					
					$class = "";
					if ($filter_alpha == $letter) {
						$class = "selected";
					}
					if ($total) {
						$alpha [] = '<a class="letter ' . $class . '" href="' . Route::_ ( 'index.php?option=com_mymuse&view=tracks&layout=tracks&id=' . $category->id . '&filter_alpha=' . $letter . '&Itemid=' . $this->Itemid ) . '">' . $letter . '</a>';
					} else {
						$alpha [] = '<span class="letter">' . $letter . '</span>';
					}
				}
				$this->alpha = $alpha;
				$this->filterAlpha = $jinput->get ( 'filter_alpha', '' );
			}
			$this->total = $this->get ( 'Total' );
			$this->limit = $params->get ( 'display_num', 10 );

        
        
        
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			Factory::getApplication()->enqueueMessage(500, implode("\n", $errors));
			return false;
		}

		if ($category == false) {
			Factory::getApplication()->enqueueMessage(404, Text::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}


		// Setup the category parameters.
		$cparams = $category->params;
		$category->params = clone($params);
		$category->params->merge($cparams);

		// Check whether category access level allows access.
		$user	= Factory::getApplication()->getIdentity();
		$groups	= $user->getAuthorisedViewLevels();
		if (!in_array($category->access, $groups)) {
			Factory::getApplication()->enqueueMessage(403, Text::_('JERROR_ALERTNOAUTHOR'));
		}

		//if multiple track variations, create select box
		if(isset($items) && is_countable($items) && count($items)){
			for($i=0; $i < count($items); $i++){
				if(is_array($items[$i]->digital) && count($items[$i]->digital) > 1){
					$items[$i]->variation_select = '<select name="variation['.$items[$i]->id.']" 
							id = "variation_'.$items[$i]->id.'_id" class="inputbox variation_select"
							onchange="javascript:flip_price(\''.$items[$i]->id.'\')"
							';
							for($j = 0; $j < count($items[$i]->digital); $j++){
								$items[$i]->variation_select .= '
								data-variation_'.$j.'="'.$items[$i]->digital[$j]->file_id.'"';
							}
							
							$items[$i]->variation_select .= '>
									';
					for($j = 0; $j < count($items[$i]->digital); $j++){
						$items[$i]->variation_select .= '<option value="'.$j.'" >'
						.Text::_(strtoupper($items[$i]->digital[$j]->file_format)).'</option>'."\n";
					}		
					$items[$i]->variation_select .= "</select>";
				}
				
			}
		}

		//set  up formats
		$this->formats = array();
		$pformats = $this->params->get('my_formats', array());
		foreach($pformats as $i => $f){
			$this->formats[$f->ordering] = strtolower($f->format_key);
		}


		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''));

		$this->maxLevel 	= $params->get('maxLevel', -1);
		$this->state 		= $state;
		$this->items 		= isset($items)? $items : array();
		$this->category 	= $category;
		
		$this->pagination 	= isset($pagination)? $pagination: NULL;
		$this->user 		= $user;

		$this->_prepareDocument();
		$layout = $this->getLayout();
		if($layout == "listdetail"){
			$this->_prepareItems($items);
		}

		parent::display($tpl);
	}
	
	/**
	 * Prepares the items
	 */
	protected function _prepareItems(&$items)
	{
		
		$model = JModel::getInstance('Product', 'MyMuseModel', array('ignore_request' => true));
		for($i=0; $i<count($items); $i++){
			$items[$i] = $model->getItem($items[$i]->id);
		}
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= Factory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title		= null;


		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', Text::_('JGLOBAL_ARTICLES'));
		}

		$id = (int) @$menu->query['id'];
/**
		if ($menu && ($menu->query['option'] != 'com_mymuse' || $menu->query['view'] == 'article' || $id != $this->category->id)) {
			$path = array(array('title' => $this->category->title, 'link' => ''));
			$category = $this->category->getParent();

			while (($menu->query['option'] != 'com_mymuse' || $menu->query['view'] == 'article' || $id != $category->id) && $category->id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => RouteHelper::getCategoryRoute($category->id));
				$category = $category->getParent();
			}

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}
*/
		$title = $this->params->get('page_title', '');

		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = Text::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = Text::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->category->metadesc)
		{
			$this->document->setDescription($this->category->metadesc);
		}
		elseif (!$this->category->metadesc && $this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->category->metakey)
		{
			$this->document->setMetadata('keywords', $this->category->metakey);
		}
		elseif (!$this->category->metakey && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}


		$mdata = json_decode($this->category->metadata, true);

		foreach ($mdata as $k => $v)
		{
			if ($v) {
				$this->document->setMetadata($k, $v);
			}
		}

		// Add feed links
		if ($this->params->get('show_feed_link', 1)) {
			$link = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}