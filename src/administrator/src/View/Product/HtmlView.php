<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\View\Product;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;
use Joomla\Component\Mymuse\Administrator\Model\ProductModel;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Administrator\Controller\ProductController;

/**
 * View to edit a store.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The Input object
	 *
	 * @var    input
	 * @since  1.5
	 */
	public $input;
	/**
	 * The Form object
	 *
	 * @var    Form
	 * @since  1.5
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var    object
	 * @since  1.5
	 */
	protected $state;

	/**
	 * The layout
	 *
	 * @var    string
	 * @since  1.5
	 */
	protected $layout;

	/**
	 * A \JForm instance with filter fields.
	 *
	 * @var    \JForm
	 * @since  3.6.3
	 */
	public $filterForm;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null): void
	{

		$this->input = Factory::getApplication()->input;
		$this->params 	= MyMuseHelper::getParams();
		/** @var MymuseModel $model */
		$model       	= $this->getModel();
		$this->item  = $model->getItem();


		$this->form  	= $model->getForm();
		$this->state 	= $model->getState();
		$this->lists 	= $model->getLists();
		$filelists 		= $model->getFileLists();
        $this->lists 	= array_merge($this->lists,$filelists);
		


		$this->task 	= $task 	= $this->input->get('task', 'edit');
		$this->type 	= $type 	= $this->input->get('type', 'product');


		if($task == "addfile" || $task == "additem" || $task == "new_allfiles"){
			$this->input->set('id',0);
		}

		$app 			= Factory::getApplication();
		$type 		= $app->getUserStateFromRequest("com_mymuse.type", 'type', 'product');

		$view 			= $this->input->get('view');
		
        $isNew  		= ($this->item->id < 1);
		$lists['isNew'] = $isNew;
		
		//setlayout
		$this->layout 	= $this->input->get('layout', 'edit');
		
		//new file || edit file
		if($task == "addfile" || $task == "editfile" || 
				(isset($this->item->parentid) && $this->item->parentid > 0 
						&& !$this->item->product_allfiles && $type == "file")){
			if($task == "addfile"){
				$this->input->set('id','0');
			}

            if(!$this->item->parentid){
        		$this->item->parentid = $this->lists->items[0]->parentid;
        	}

			$this->layout = 'edittracks';
        	$this->setLayout('edittracks');
        	

        	if($task == "editfile"){
        		$this->item->formats = $model->getFormats($this->item->id);
        	}
        	
        	
        	$this->input->set('type','file');
        	$type = $app->getUserStateFromRequest("com_mymuse.type", 'type', 'file');
            if(!isset($this->item->parent)){
                $this->item->parent = $model->getItem($this->item->parentid);
            }
        	
        }
        
        // allfiles
        elseif($task == "edit_allfiles" || $task == "new_allfiles" || $task == "product.new_allfiles" || ($this->item->parentid && $this->item->product_allfiles)){

        	$this->layout = "edit_allfiles";
        	$this->setLayout('edit_allfiles');
			if(!$this->item->parentid){
        		$this->item->parentid = $this->input->get('parentid', 0);
        	}

        	$type = 'allfiles';
  
        }
     
        //item
        elseif($task == "edititem" || $task == "product.edititem"){
        	$this->layout 			= 'edititems';
        	$this->attribute_skus 	= $model->getAttributeskus();
        	$this->attributes 		= $model->getAttributes();
        }
        elseif($task == "additem" || $task == "product.additem"){

        	if(!$this->item->id){
        		$this->item->catid = $this->item->parent->catid;
        		$this->item->artistid = $this->item->parent->artistid;
        		if($this->params->get('my_price_by_product',0)) {
        			$registry = new Registry($this->item->parent->attribs);
        			$attribs = $registry->toArray();
        			$this->item->price = $attribs['product_price_physical'];
        		}
        		$this->item->detail_image = $this->item->parent->detail_image;
        		$registry = new Registry($this->item->parent->physical);
        		$this->item->physical = $registry->toArray();
        		$this->item->product_in_stock = $this->item->parent->product_in_stock;
        	}

        	$this->layout 			= 'edititems';
        	$this->attribute_skus 	= $model->getAttributeskus();
        	$this->attributes 		= $model->getAttributes();
        	$isNew  = (@$items->id < 1);
        	$this->lists['isNew'] 	= $isNew;
        	$this->setLayout('edititems');
        	$this->input->set('type','item');
        	$type = $app->getUserStateFromRequest("com_mymuse.type", 'type', 'item');

        }


        //listtracks
        if($this->layout == "listtracks"){
        	$this->tracks 		= $model->getTracks();
        	$this->filterForm   = $model->filterForm;

        	//See if there is an all files zip
        	$this->all_files = 0;
        	for ($i=0, $n=count( $this->tracks ); $i < $n; $i++){
        		if($this->tracks[$i]->product_allfiles == "1"){
        			$this->all_files = 1;
        		}
        	}
        	$this->trackPagination = $this->get('TrackPagination');
        }

        //listitems
        if($this->layout == "listitems"){
        	
        	$this->items 			= $model->getItems();

        	$this->itemPagination 	= $model->getItemPagination();
        	$this->id 				= $this->input->get('id');
        	$this->input->set('parentid',$this->id);
        	
        	$this->attribute_skus 	= $model->getAttributeskus();
        	$this->attributes 		= $model->getAttributes();
        }
        $this->setLayout($this->layout);

        //It's the parent, set the user state
        if($this->item->id && $this->item->parentid == 0){
        	
        	$app->setUserState("com_mymuse.parentid", $this->item->id);
        }
        if(!$this->item->id  && $this->item->parentid == 0){
        	$type = "details";
        }
        
		$this->lists['type'] 	= $type;
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar($type,$this->item->parentid);
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  Exception
	 */
	protected function addToolbar($type='', $parentid=0): void
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
 
		$user       = Factory::getUser();
		$userId     = $user->id;
		$isNew      = ($this->item->id == 0);
		$checkedOut = !(is_null($this->item->checked_out) || $this->item->checked_out == $userId);

		$canDo	= MymuseHelper::getActions('com_mymuse', 'store', $this->item->id);
		

		//title
		$title = Text::_('COM_MYMUSE_TITLE_PRODUCT');
		if($this->item->parentid){
			$title .= ' : <a href="index.php?option=com_mymuse&view=product&task=product.edit&id='.$this->item->parentid.'">'.$this->item->parent->title."</a>";
		}else{
			$title .= " : ".$this->item->title;
		}
		
		if($this->layout == "listitems") {
			$title .= ": ".Text::_("COM_MYMUSE_LIST_ITEMS");
		}
		if($this->layout == "listtracks"){
			$title .= ": ".Text::_("COM_MYMUSE_LIST_TRACKS");
		}
		ToolBarHelper::title(Text::_('COM_MYMUSE').' : '. $title, 'mymuse.png');
	


		if($this->layout == "listtracks"){
			// LIST TRACKS
			if($this->params->get('storage', 'regular') == 'regular' ){
				ToolBarHelper::custom('product.uploadtrack', 'save-new.png', 'save-new_f2.png', 'COM_MYMUSE_UPLOAD_TRACKS', false);

				if(str_contains($this->params->get('my_preview_dir'), "images",)){
					ToolBarHelper::custom('product.uploadpreview', 'save-new.png', 'save-new_f2.png', 'COM_MYMUSE_UPLOAD_PREVIEWS', false);
				}
				
			}
			
			
			ToolBarHelper::editList('product.edit', 'COM_MYMUSE_EDIT_TRACK');
			ToolBarHelper::addNew('product.addfile', 'COM_MYMUSE_NEW_TRACK');
			ToolBarHelper::deleteList('','product.removefile','COM_MYMUSE_DELETE_TRACKS');
			
			
			if(!$this->all_files){ 
				ToolBarHelper::addNew('product.new_allfiles', 'COM_MYMUSE_ALL_TRACKS');
			}		  
			ToolBarHelper::apply('product.productreturn', 'COM_MYMUSE_RETURN_TO_PRODUCT');
			
			
			ToolBarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/product-tracks?tmpl=component');

		}elseif($this->layout == "listitems"){
			// LIST ITEMS

			
			ToolBarHelper::addNew('product.additem', 'COM_MYMUSE_NEW_ITEM');
			ToolBarHelper::deleteList('','product.removeitem','COM_MYMUSE_DELETE_ITEM');

			ToolBarHelper::custom('products.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			ToolBarHelper::custom('products.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			ToolBarHelper::divider('|');
			
			ToolBarHelper::addNew('productattributeskus.list', 'COM_MYMUSE_LIST_ATTRIBUTES');

			ToolBarHelper::addNew('productattributesku.add', 'COM_MYMUSE_ADD_ATTRIBUTES');
			ToolBarHelper::apply('product.productreturn', 'COM_MYMUSE_RETURN_TO_PRODUCT');
						


			ToolBarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/product-items?tmpl=component');
			
		}elseif($type == "file" && $parentid){
			//TRACK

			// If not checked out, can save the item.
			if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
			{
				ToolBarHelper::apply('product.applyfile', 'JTOOLBAR_APPLY');
				ToolBarHelper::save('product.savefile', 'JTOOLBAR_SAVE');
			}
			if (!$checkedOut && ($canDo->get('core.create'))){
				ToolBarHelper::custom('product.save2newfile', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}

			if (empty($this->item->id)) {
				ToolBarHelper::cancel('product.cancelfile', 'JTOOLBAR_CANCEL');
			}
			else {
				ToolBarHelper::cancel('product.cancelfile', 'JTOOLBAR_CLOSE');
			}
			ToolBarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/product-tracks?tmpl=component#new-edit-track');			
		
		}elseif($type == "allfiles" && $parentid){
			// ALLFILES
			// If not checked out, can save the item.
			if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
			{
				ToolBarHelper::apply('product.apply_allfiles', 'JTOOLBAR_APPLY');
				ToolBarHelper::save('product.save_allfiles', 'JTOOLBAR_SAVE');
			}

			if (empty($this->item->id)) {
				ToolBarHelper::cancel('product.cancelitem', 'JTOOLBAR_CANCEL');
			}
			else {
				ToolBarHelper::cancel('product.cancelitem', 'JTOOLBAR_CLOSE');
			}
			ToolBarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/product-tracks?tmpl=component#tracks-all-tracks');		
		
		}elseif( $type == 'item' ){
			// If not checked out, can save the item.
			if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
			{
				ToolBarHelper::apply('product.applyitem', 'JTOOLBAR_APPLY');
				ToolBarHelper::save('product.saveitem', 'JTOOLBAR_SAVE');
			}
			if (!$checkedOut && ($canDo->get('core.create'))){
				ToolBarHelper::custom('product.save2newitem', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}

			if (empty($this->item->id)) {
				ToolBarHelper::cancel('product.cancelitem', 'JTOOLBAR_CANCEL');
			}
			else {
				ToolBarHelper::cancel('product.cancelitem', 'JTOOLBAR_CLOSE');
			}
			ToolBarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/product-items?tmpl=component');
		}else{
			// If not checked out, can save the item.
			if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
			{
				ToolBarHelper::apply('product.apply', 'JTOOLBAR_APPLY');
				ToolBarHelper::save('product.save', 'JTOOLBAR_SAVE');
			}
			if (!$checkedOut && ($canDo->get('core.create'))){
				ToolBarHelper::custom('product.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				ToolBarHelper::save2copy('product.save2copy');
			}

			

			ToolBarHelper::custom('product.listracks', 'featured.png', 'featured_f2.png', 'COM_MYMUSE_TRACKS', false);
			ToolBarHelper::custom('product.listitems', 'featured.png', 'featured_f2.png', 'COM_MYMUSE_ITEMS', false);

			if (empty($this->item->id)) {
				ToolBarHelper::cancel('product.cancel', 'JTOOLBAR_CANCEL');
			}
			else {
				ToolBarHelper::cancel('product.cancel', 'JTOOLBAR_CLOSE');
			}
		}

		



		if (empty($this->item->id))
		{

		}
		else
		{

			if (ComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $canDo->get('core.edit'))
			{
				ToolbarHelper::versions('com_mymuse.product', $this->item->id);
			}
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_COMPONENTS_MYMUSE_PRODUCT_EDIT');
	}

	/**
	 * Returns an array of fields the table can be sorted by for TRACKS
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields2()
	{
		return array(
				'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
				'a.state' => Text::_('JSTATUS'),
				'a.title' => Text::_('JGLOBAL_TITLE'),
				'a.id' => Text::_('JGRID_HEADING_ID'),
				'a.price' => Text::_('COM_MYMUSE_PRICE'),
				'a.product_discount' => Text::_('COM_MYMUSE_DISCOUNT')
		);
	}

	/**
	 * Returns an array of fields the table can be sorted by for ITEMS
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields3()
	{
		return array(
				'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
				'a.state' => Text::_('JSTATUS'),
				'a.title' => Text::_('JGLOBAL_TITLE'),
				'a.id' => Text::_('JGRID_HEADING_ID'),
				'a.price' => Text::_('COM_MYMUSE_PRICE'),
				'a.product_discount' => Text::_('COM_MYMUSE_DISCOUNT'),
				'a.product_in_stock' => Text::_('COM_MYMUSE_PRODUCT_IN_STOCK_LABEL')
		);
	}
}
