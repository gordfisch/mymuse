<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2020 Arboreta Internet Services. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Mymuse\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

/**
 * Productattributeskus list controller class.
 *
 * @since  5.0.0
 */
class ProductattributeskusController extends AdminController
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
	public function getModel($name = 'productattributeskus', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	public function remove()
	{
		$input 	= Factory::getApplication()->input;
		$cid 	= $input->post->get( 'cid', array(), 'ARRAY');

		ArrayHelper::toInteger($cid);
		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'COM_MYMUSE_SELECT_AN_ATTRIBUTE_TO_DELETE' ) );
		}
	
		$model = $this->getModel();
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>
			";
		}
		$this->msg = Text::_( 'COM_MYMUSE_ATTRIBUTE_DELETED' );
		$this->setRedirect( 'index.php?option=com_mymuse&task=list&view=productattributeskus', $this->msg );
	}

	public function list()
	{
		//echo "list"; exit;
		$this->setRedirect( 'index.php?option=com_mymuse&task=list&view=productattributeskus');
	}

}
