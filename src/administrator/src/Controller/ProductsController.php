<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Mymuse\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Session\Session;

/**
 * Products list controller class.
 *
 * @since  5.0.0
 */
class ProductsController extends AdminController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

	}



	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'products', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to get the number of published articles for quickicons
	 *
	 * @return  string  The JSON-encoded amount of published articles
	 *
	 * @since   4.0
	 */
	public function getQuickiconContent()
	{
		$model = $this->getModel('products');

		$model->setState('filter.published', 1);

		$amount = (int) $model->getTotal();

		$result = [];

		$result['amount'] = $amount;
		$result['sronly'] = Text::plural('COM_CONTENT_N_QUICKICON_SRONLY', $amount);
		$result['name'] = Text::plural('COM_CONTENT_N_QUICKICON', $amount);

		echo new JsonResponse($result);
	}


    /**
     * Method override to check-in a record or an array of record
     *
     * @param   mixed  $pks  The ID of the primary key or an array of IDs
     *
     * @return  integer|boolean  Boolean false if there is an error, otherwise the count of records checked in.
     *
     * @since   1.6
     * */
    public function checkin() {

        $pks = $this->app->input->get('cid');
        $model = $this->getModel('product');
        $model->checkin($pks);
        $this->setRedirect( 'index.php?option=com_mymuse&view=products' );

    }

    /**
     * Method to toggle the featured setting of a list of products.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function featured()
    {
    	// Check for request forgeries
    	//$this->checkToken();

    	$user        = $this->app->getIdentity();
    	$ids         = $this->input->get('cid', array(), 'array');
    	$values      = array('featured' => 1, 'unfeatured' => 0);
    	$task        = $this->getTask();
    	$value       = ArrayHelper::getValue($values, $task, 0, 'int');
    	$redirectUrl = 'index.php?option=com_mymuse&view=products';

    	// Access checks.
    	foreach ($ids as $i => $id)
    	{
    		if (!$user->authorise('core.edit.state', 'com_mymuse.product.' . (int) $id))
    		{
    			// Prune items that you can't change.
    			unset($ids[$i]);
    			$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'notice');
    		}
    	}

    	if (empty($ids))
    	{
    		$this->app->enqueueMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'error');
    	}
    	else
    	{
    		// Get the model.
    		/** @var \Joomla\Component\Content\Administrator\Model\ArticleModel $model */
    		$model = $this->getModel();

    		// Publish the items.
    		if (!$model->featured($ids, $value))
    		{
    			$this->setRedirect(Route::_($redirectUrl, false), $model->getError(), 'error');

    			return;
    		}

    		if ($value == 1)
    		{
    			$message = Text::plural('COM_MYMUSE_N_ITEMS_FEATURED', count($ids));
    		}
    		else
    		{
    			$message = Text::plural('COM_MYMUSE_N_ITEMS_UNFEATURED', count($ids));
    		}
    	}

    	$this->setRedirect(Route::_($redirectUrl, false), $message);
    }



    public function unfeatured()
    {

    	$this->featured();
    }

    /**
     * Method to publish a list of items
     *
     * @return  void
     *
     * @since   11.1
     */
    public function publish()
    {
    	// Check for request forgeries
    	$this->checkToken();
    	$input 		= Factory::getApplication()->input;
    	$view 		= $input->get('view','');
    	$parentid 	= $input->get('parentid','');
    	$layout 	= $input->get('layout','');
    	$id 		= $input->get('id','');
    	$subtype 	= $input->get('subtype', '');

    	// Get items to publish from the request.
    	$cid 		= $input->post->get('cid', array(), 'array');
    	$data 		= array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
    	$task 		= $this->getTask();
    	$value 		= ArrayHelper::getValue($data, $task, 0, 'int');
    	$redirectUrl = 'index.php?option=com_mymuse&view=products';

    	if (empty($cid))
    	{
    		$message = JText::_($this->text_prefix . '_NO_ITEM_SELECTED');
    		$this->setRedirect(Route::_($redirectUrl, $message, 'error'));
    	}
    	else
    	{
    		// Get the model.
    		$model = $this->getModel();

    		// Make sure the item ids are integers
    		ArrayHelper::toInteger($cid);

    		// Publish the items.
    		if (!$model->publish($cid, $value))
    		{
    			$this->setRedirect(Route::_($redirectUrl, false), $model->getError(), 'error');
    		}
    		else
    		{
    			if ($value == 1)
    			{
    				$ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
    			}
    			elseif ($value == 0)
    			{
    				$ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
    			}
    			elseif ($value == 2)
    			{
    				$ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
    			}
    			else
    			{
    				$ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
    			}
    			$this->setMessage(JText::plural($ntext, count($cid)));
    		}
    	}
    	
    	if($view == 'product' && $layout == 'edit' && $parentid){
    		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$parentid.'&subtype='.$subtype, false));
    	}else{
    		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $view .'&layout='.$layout.'&id='.$id, false));
    	}
    }

    public function unpublish()
    {

    	$this->publish();
    }



    

}