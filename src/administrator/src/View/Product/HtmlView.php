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
use Joomla\Component\Mymuse\Administrator\Model\TaxtrateModel;

/**
 * View to edit a store.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
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
		/** @var MymuseModel $model */
		$model       	= $this->getModel();
		$this->form  	= $model->getForm();
		$this->item  	= $model->getItem();
		$this->state 	= $model->getState();
		$this->lists 	= $this->get('Lists');
		$this->params 	= MyMuseHelper::getParams();

		$input = JFactory::getApplication()->input;
		$this->task 	= $task 	= $input->get('task', 'edit');
		
		if($task == "addfile" || $task == "additem" || $task == "new_allfiles"){
			$input->set('id',0);
		}

				$app 			= JFactory::getApplication();
		$subtype 		= $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'details');
		$subtype 		='details';
		$view 			= $input->get('view');
		
        $isNew  		= ($this->item->id < 1);
		$lists['isNew'] = $isNew;
		
		//setlayout
		$layout = $input->get('layout', 'edit');
		
		//listtracks
		if($layout == "listtracks"){
			$this->tracks 	= $this->get('Tracks');

			//See if there is an all files zip
			$this->all_files = 0;
			for ($i=0, $n=count( $this->tracks ); $i < $n; $i++){
				if($this->tracks[$i]->product_allfiles == "1"){
					$this->all_files = 1;
				}
			}
			$this->trackPagination = $this->get('TrackPagination');
		}
		if($layout == "listitems"){
			$this->items 	= $this->get('Items');
			$this->itemPagination = $this->get('ItemPagination');
		}

		$this->setLayout($layout);
		
		//new file || edit file
		if($task == "addfile" || $task == "editfile" || 
				(isset($this->item->parentid) && $this->item->parentid > 0 
						&& !$this->item->product_allfiles && $subtype == "file")){
			if($task == "addfile"){
				$input->set('id','0');
			}

			$layout = 'edittracks';
        	$this->setLayout('edittracks');
        	$filelists = $this->get('FileLists');

        	$this->lists = array_merge($this->lists,$filelists);
        	
        	if(!$this->item->parentid){
        		$this->item->parentid = $input->get('parentid', 0);
        	}
        	$input->set('subtype','file');
        	$subtype = $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'file');
        	
        	
        }
        
        // allfiles
        elseif($task == "edit_allfiles" || $task == "new_allfiles" || $task == "product.new_allfiles" || ($this->item->parentid && $this->item->product_allfiles)){
        	$this->setLayout('edit_allfiles');
			if(!$this->item->parentid){
        		$this->item->parentid = $input->get('parentid', 0);
        	}
        	$subtype = 'allfiles';
  
        }
     
        //item
        elseif($task == "additem" || $task == "product.additem" || (isset($this->item->parentid) && $this->item->parentid > 0 && $this->item->product_physical == 1)){
        
        	$layout = 'edititems';
        	$this->setLayout('edititems');
        	$this->attribute_skus = $this->get('Attributeskus');
        	$this->attributes = $this->get('Attributes');
        	
        	if(!count($this->attribute_skus)){
        		//no attributes yet!!
        		$msg = JText::_("MYMUSE_CREATE_ATTRIBUTE_FIRST");
        		$url = "index.php?option=com_mymuse&view=product&layout=listitems&id=".$this->item->parentid;
        		$app->redirect($url, $msg);
        		exit;
        	}
        	
        	
        	$isNew  = (@$items->id < 1);
        	$this->lists['isNew'] = $isNew;
        	$input->set('subtype','item');
        	$subtype = $app->getUserStateFromRequest("com_mymuse.subtype", 'subtype', 'item');
        	
        }



        //It's the parent, set the user state
        if($this->item->id && $this->item->parentid == 0){
        	$app = JFactory::getApplication();
        	$parentid = $app->getUserStateFromRequest("com_mymuse.parentid", 'parentid', $this->item->id);
        }
        if(!$this->item->id  && $this->item->parentid == 0){
        	$subtype = "details";
        }
        
		$this->lists['subtype'] 	= $subtype;
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar($subtype,$this->item->parentid);

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
	protected function addToolbar($subtype='', $parentid=0): void
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user       = Factory::getUser();
		$userId     = $user->id;
		$isNew      = ($this->item->id == 0);
		$checkedOut = !(is_null($this->item->checked_out) || $this->item->checked_out == $userId);

		$canDo	= MymuseHelper::getActions('com_mymuse', 'store', $this->item->id);
		$title = JText::_('COM_MYMUSE_TITLE_PRODUCT');

		if($this->item->parentid){
			$title .= ' : <a href="index.php?option=com_mymuse&view=product&task=product.edit&id='.$this->item->parentid.'">'.$this->item->parent->title."</a>";
		}else{
			$title .= " : ".$this->item->title;
		}
		JToolBarHelper::title(JText::_('COM_MYMUSE').' : '. $title, 'mymuse.png');
	
		if($layout == "listtracks"){
			// LIST TRACKS
			if($this->params->get('storage', 'regular') == 'regular' ){
				JToolBarHelper::custom('product.uploadtrack', 'save-new.png', 'save-new_f2.png', 'MYMUSE_UPLOAD_TRACKS', false);
				JToolBarHelper::custom('product.uploadpreview', 'save-new.png', 'save-new_f2.png', 'MYMUSE_UPLOAD_PREVIEWS', false);
			}
			
			
			JToolBarHelper::editList('product.edit', 'MYMUSE_EDIT_TRACK');
			JToolBarHelper::addNew('product.addfile', 'MYMUSE_NEW_TRACK');
			JToolBarHelper::deleteList('','product.removefile','MYMUSE_DELETE_TRACKS');
			
			
			if(!$this->all_files){ 
				JToolBarHelper::addNew('product.new_allfiles', 'MYMUSE_ALL_TRACKS');
			}		  
			JToolBarHelper::apply('product.productreturn', 'MYMUSE_RETURN_TO_PRODUCT');
			
			
			JToolBarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/product-tracks?tmpl=component');
		}elseif($layout == "listitems"){
			// LIST ITEMS
			JToolBarHelper::apply('product.productreturn', 'MYMUSE_RETURN_TO_PRODUCT');
			JToolBarHelper::custom('products.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('products.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/product-items?tmpl=component');
			
		}elseif($subtype == "file" && $parentid){
			//TRACK
			// If not checked out, can save the item.
			if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
			{
				JToolBarHelper::apply('product.applyfile', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('product.savefile', 'JTOOLBAR_SAVE');
			}
			if (!$checkedOut && ($canDo->get('core.create'))){
				JToolBarHelper::custom('product.save2newfile', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}

			if (empty($this->item->id)) {
				JToolBarHelper::cancel('product.cancelfile', 'JTOOLBAR_CANCEL');
			}
			else {
				JToolBarHelper::cancel('product.cancelfile', 'JTOOLBAR_CLOSE');
			}
			JToolBarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/product-tracks?tmpl=component#new-edit-track');			
		
		}elseif($subtype == "allfiles" && $parentid){
			// ALLFILES
			// If not checked out, can save the item.
			if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
			{
				JToolBarHelper::apply('product.apply_allfiles', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('product.save_allfiles', 'JTOOLBAR_SAVE');
			}

			if (empty($this->item->id)) {
				JToolBarHelper::cancel('product.cancelitem', 'JTOOLBAR_CANCEL');
			}
			else {
				JToolBarHelper::cancel('product.cancelitem', 'JTOOLBAR_CLOSE');
			}
			JToolBarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/product-tracks?tmpl=component#tracks-all-tracks');		
		
		}elseif( $subtype == 'item' ){
			// If not checked out, can save the item.
			if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
			{
				JToolBarHelper::apply('product.applyitem', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('product.saveitem', 'JTOOLBAR_SAVE');
			}
			if (!$checkedOut && ($canDo->get('core.create'))){
				JToolBarHelper::custom('product.save2newitem', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}

			if (empty($this->item->id)) {
				JToolBarHelper::cancel('product.cancelitem', 'JTOOLBAR_CANCEL');
			}
			else {
				JToolBarHelper::cancel('product.cancelitem', 'JTOOLBAR_CLOSE');
			}
			JToolBarHelper::help('', false, 'https://www.joomlamymuse.com/index.php/support/documentation/help-files-4-x/product-items?tmpl=component');
		}else{
			// If not checked out, can save the item.
			if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
			{
				JToolBarHelper::apply('product.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('product.save', 'JTOOLBAR_SAVE');
			}
			if (!$checkedOut && ($canDo->get('core.create'))){
				JToolBarHelper::custom('product.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				JToolbarHelper::save2copy('product.save2copy');
			}

			

			JToolBarHelper::custom('product.listracks', 'featured.png', 'featured_f2.png', 'MYMUSE_TRACKS', false);
			JToolBarHelper::custom('product.listitems', 'featured.png', 'featured_f2.png', 'MYMUSE_ITEMS', false);

			if (empty($this->item->id)) {
				JToolBarHelper::cancel('product.cancel', 'JTOOLBAR_CANCEL');
			}
			else {
				JToolBarHelper::cancel('product.cancel', 'JTOOLBAR_CLOSE');
			}
		}

		

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit'))
		{
			ToolbarHelper::apply('store.apply');
			$toolbarButtons[] = ['save', 'store.save'];

		}


		ToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('store.cancel');
		}
		else
		{
			ToolbarHelper::cancel('store.cancel', 'JTOOLBAR_CLOSE');

			if (ComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $canDo->get('core.edit'))
			{
				ToolbarHelper::versions('com_mymuse.store', $this->item->id);
			}
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_COMPONENTS_MYMUSE_PRODUCT_EDIT');
	}
}
