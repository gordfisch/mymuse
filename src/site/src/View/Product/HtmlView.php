<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2021 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Site\View\Product;

\defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Helper\AssociationHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\Component\Mymuse\Site\Helper\CartHelper;
use Joomla\Component\Mymuse\Site\Model\StoreModel;


/**
 * HTML product View class for the Content component
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The product object
	 *
	 * @var  \stdClass
	 */
	protected $item;

	/**
	 * The page parameters
	 *
	 * @var    \Joomla\Registry\Registry|null
	 * @since  4.0.0
	 */
	protected $params = null;

	/**
	 * Should the print button be displayed or not?
	 *
	 * @var  boolean
	 */
	protected $print = false;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * The user object
	 *
	 * @var  \JUser|null
	 */
	protected $user = null;

	/**
	 * The page class suffix
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $pageclass_sfx = '';

	/**
	 * The flag to mark if the active menu item is linked to the being displayed product
	 *
	 * @var boolean
	 */
	protected $menuItemMatchproduct = false;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'pagebreak')
		{
			parent::display($tpl);

			return;
		}

		$app        			= Factory::getApplication();
		$user       			= Factory::getUser();
		$jinput 				= $app->input;

		$this->item  			= $this->get('Item');
		//$this->tracks 			= $this->get('Tracks');

		$this->print 			= $app->input->getBool('print', false);
		$this->state 			= $this->get('State');
		$this->user  			= $user;
		$this->Itemid 			= $jinput->get("Itemid",'');
		$this->sortDirection    = $this->state->get('list.direction');
		$this->sortColumn       = $this->state->get('list.ordering');
		$this->store			= StoreModel::getStore();
		$MyMuseCart				= new CartHelper;
		$this->cart 			= $MyMuseCart->cart;
		$this->filterAlpha     	= $jinput->get('filter_alpha', '', 'STRING');


		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Create a shortcut for $item.
		$item            = $this->item;
		$item->tagLayout = new FileLayout('joomla.content.tags');

		// Add router helpers.
		$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

		// No link for ROOT category
		if ($item->parent_alias === 'root')
		{
			$item->parent_id = null;
		}

		// TODO: Change based on shownoauth
		$item->readmore_link = Route::_(RouteHelper::getProductRoute($item->slug, $item->catid, $item->language));

		// Merge product params. If this is single-product view, menu params override product params
		// Otherwise, product params override menu item params
		$this->params = $this->state->get('params');
		$active       = $app->getMenu()->getActive();
		$temp         = clone $this->params;

		// Check to see which parameters should take priority. If the active menu item link to the current product, then
		// the menu item params take priority
		if ($active
			&& $active->component == 'com_mymuse'
			&& isset($active->query['view'], $active->query['id'])
			&& $active->query['view'] == 'product'
			&& $active->query['id'] == $item->id)
		{
			$this->menuItemMatchproduct = true;

			// Load layout from active query (in case it is an alternative menu item)
			if (isset($active->query['layout']))
			{
				$this->setLayout($active->query['layout']);
			}
			// Check for alternative layout of product
			elseif ($layout = $item->params->get('product_layout'))
			{
				$this->setLayout($layout);
			}

			// $item->params are the product params, $temp are the menu item params
			// Merge so that the menu item params take priority
			$item->params->merge($temp);
		}
		else
		{
			// The active menu item is not linked to this product, so the product params take priority here
			// Merge the menu item params with the product params so that the product params take priority
			$temp->merge($item->params);
			$item->params = $temp;

			// Check for alternative layouts (since we are not in a single-product menu item)
			// Single-product menu item layout takes priority over alt layout for a product
			if ($layout = $item->params->get('product_layout'))
			{
				$this->setLayout($layout);
			}else{
				$this->setLayout('product');
			}
		}

		$offset = $this->state->get('list.offset');

		// Check the view access to the product (the model has already computed the values).
		if ($item->params->get('access-view') == false && ($item->params->get('show_noauth', '0') == '0'))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return;
		}

		/**
		 * Check for no 'access-view' and empty fulltext,
		 * - Redirect guest users to login
		 * - Deny access to logged users with 403 code
		 * NOTE: we do not recheck for no access-view + show_noauth disabled ... since it was checked above
		 */
		if ($item->params->get('access-view') == false && !strlen($item->fulltext))
		{
			if ($this->user->get('guest'))
			{
				$return = base64_encode(Uri::getInstance());
				$login_url_with_return = Route::_('index.php?option=com_users&view=login&return=' . $return);
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'notice');
				$app->redirect($login_url_with_return, 403);
			}
			else
			{
				$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
				$app->setHeader('status', 403, true);

				return;
			}
		}

		/**
		 * NOTE: The following code (usually) sets the text to contain the fulltext, but it is the
		 * responsibility of the layout to check 'access-view' and only use "introtext" for guests
		 */
		if ($item->params->get('show_intro', '1') == '1')
		{
			$item->text = $item->introtext . ' ' . $item->fulltext;
		}
		elseif ($item->fulltext)
		{
			$item->text = $item->fulltext;
		}
		else
		{
			$item->text = $item->introtext;
		}

		$item->tags = new TagsHelper;
		$item->tags->getItemTags('com_mymuse.product', $this->item->id);

		if (Associations::isEnabled() && $item->params->get('show_associations'))
		{
			$item->associations = AssociationHelper::displayAssociations($item->id);
		}

		// Process the mymuse plugins.
		PluginHelper::importPlugin('mymuse');
		Factory::getApplication()->triggerEvent('onContentPrepare', array('com_mymuse.product', &$item, &$item->params, $offset));

		$item->event = new \stdClass;
		$results = $app->triggerEvent('onProductBeforeHeader', array ('com_mymuse.product', &$item, &$this->params, $offset));
		$item->event->beforeDisplayHeader = trim(implode("\n", $results));

		$results = $app->triggerEvent('onProductBeforeTitle', array('com_mymuse.product', &$item, &$this->params, $offset));
		$item->event->beforeDisplayTitle = trim(implode("\n", $results));
		
		$results = $app->triggerEvent('onProductAfterTitle', array('com_mymuse.product', &$item, &$this->params, $offset));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $app->triggerEvent('onProductBeforeDisplay', array('com_mymuse.product', &$item, &$this->params, $offset));
		$item->event->beforeDisplayProduct = trim(implode("\n", $results));

		$results = $app->triggerEvent('onProductAfterDisplay', array('com_mymuse.product', &$item, &$this->params, $offset));
		$item->event->afterDisplayProduct = trim(implode("\n", $results));

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->item->params->get('pageclass_sfx'));

		//set  up formats
		$this->formats = array();
		$pformats = $this->params->get('my_formats', array());
		foreach($pformats as $i => $f){
			$this->formats[$f->ordering] = strtolower($f->format_key);
		}

		//if multiple track variations, create select box
		if(is_countable($item->tracks) && count($item->tracks)){
			for($i=0; $i < count($item->tracks); $i++){
				if(is_array($item->tracks[$i]->digital) && count($item->tracks[$i]->digital) > 1){
					$item->tracks[$i]->variation_select = '<select name="variation['.$item->tracks[$i]->id.']" 
							id = "variation_'.$item->tracks[$i]->id.'_id" class="inputbox variation_select"
							onchange="javascript:flip_price(\''.$item->tracks[$i]->id.'\')"
							';
							for($j = 0; $j < count($item->tracks[$i]->digital); $j++){
								$item->tracks[$i]->variation_select .= '
								data-variation_'.$j.'="'.$item->tracks[$i]->digital[$j]->file_id.'"';
							}
							
							$item->tracks[$i]->variation_select .= '>
									';
					for($j = 0; $j < count($item->tracks[$i]->digital); $j++){
						$item->tracks[$i]->variation_select .= '<option value="'.$j.'" >'
						.Text::_(strtoupper($item->tracks[$i]->digital[$j]->file_format)).'</option>'."\n";
					}		
					$item->tracks[$i]->variation_select .= "</select>";
				}
				
			}
		}

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document.
	 *
	 * @return  void
	 */
	protected function _prepareDocument()
	{
		$app     = Factory::getApplication();
		$pathway = $app->getPathway();

		/**
		 * Because the application sets a default page title,
		 * we need to get it from the menu item itself
		 */
		$menu = $app->getMenu()->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('JGLOBAL_productS'));
		}

		$title = $this->params->get('page_title', '');

		// If the menu item is not linked to this product
		if (!$this->menuItemMatchproduct)
		{
			// If a browser page title is defined, use that, then fall back to the product title if set, then fall back to the page_title option
			$title = $this->item->params->get('product_page_title', $this->item->title ?: $title);

			// Get ID of the category from active menu item
			if ($menu && $menu->component == 'com_mymuse' && isset($menu->query['view'])
				&& in_array($menu->query['view'], ['categories', 'category']))
			{
				$id = $menu->query['id'];
			}
			else
			{
				$id = 0;
			}

			$path     = array(array('title' => $this->item->title, 'link' => ''));
			$category = Categories::getInstance('Content')->get($this->item->catid);

			while ($category !== null && $category->id != $id && $category->id !== 'root')
			{
				$path[]   = array('title' => $category->title, 'link' => RouteHelper::getCategoryRoute($category->id, $category->language));
				$category = $category->getParent();
			}

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		if (empty($title))
		{
			$title = $this->item->title;
		}

		$this->setDocumentTitle($title);

		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetaData('robots', $this->params->get('robots'));
		}

		if ($app->get('MetaAuthor') == '1')
		{
			$author = $this->item->created_by_alias ?: $this->item->author;
			$this->document->setMetaData('author', $author);
		}

		$mdata = $this->item->metadata->toArray();

		foreach ($mdata as $k => $v)
		{
			if ($v)
			{
				$this->document->setMetaData($k, $v);
			}
		}

		// If there is a pagebreak heading or title, add it to the page title
		if (!empty($this->item->page_title))
		{
			$this->item->title = $this->item->title . ' - ' . $this->item->page_title;
			$this->setDocumentTitle(
				$this->item->page_title . ' - ' . Text::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $this->state->get('list.offset') + 1)
			);
		}

		if ($this->print)
		{
			$this->document->setMetaData('robots', 'noindex, nofollow');
		}
	}


	public function makeCarousel($id=0, $path=0){

		if(!$path || !$id){
			return '';
		}
		$full_path = JPATH_ROOT."/images/".$path;
		$url_path = JURI::Root()."/images/".$path;

		//JFolder::files($path, $filter = '.', $recurse, $fullpath , $exclude);
		$files = JFolder::files($full_path, $filter = '.', false, false );

		$html = '
		<div class="owl-carousel owl-theme">
		';
		foreach ($files as $item) : 
			$html .= '<div>
				<img src="images/'.$path.'/'.$item.'" onclick="updateProductImage('.$id.',\''.$url_path.'/'.$item.'\')">
			</div>
			';
		endforeach;

		$html .= '</div>';

		return $html;
	}
}
