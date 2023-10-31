<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mymuse\Administrator\View\Productattributesku;

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
use Joomla\Component\Mymuse\Administrator\Model\ProductattributeskuModel;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

/**
 * View to edit a product attribute.
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
		$this->parent	= $model->getParent();
		$this->item  	= $model->getItem();
		$this->state 	= $model->getState();
		$this->params 	= MyMuseHelper::getParams();

		if(!$this->item->id){

			$this->form->setValue('product_parent_id', null , $this->parent->id);
		}else{
			$this->parentid = $this->item->product_parent_id;
		}
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

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
	protected function addToolbar(): void
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$isNew      = ($this->item->id == 0);

		$checkedOut = 0;

		$canDo = ContentHelper::getActions('com_mymuse', 'productattributesku', $this->item->id);

		ToolbarHelper::title($isNew ? Text::_('COM_MYMUSE_NEW_ATTRIBUTE') : Text::_('COM_MYMUSE_EDIT_ATTRIBUTE'), 'bookmark productattributeskus');

		$toolbarButtons = [];

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit'))
		{
			ToolbarHelper::apply('productattributesku.apply');
			$toolbarButtons[] = ['save', 'productattributesku.save'];

			if ($canDo->get('core.create'))
			{
				$toolbarButtons[] = ['save2new', 'productattributesku.save2new'];
			}
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			$toolbarButtons[] = ['save2copy', 'productattributesku.save2copy'];
		}

		ToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('productattributesku.cancel');
		}
		else
		{
			ToolbarHelper::cancel('productattributesku.cancel', 'JTOOLBAR_CLOSE');

		}

		ToolbarHelper::divider();
		ToolbarHelper::help('', false, 'http://www.joomlamymuse.com/documentation/documentation-2-5/154-product-item-add-attributes?tmpl=component');
	}
}
