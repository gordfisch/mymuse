<?php
/**

 */
namespace Joomla\Component\Mymuse\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;


/**
 * MyMuse master display controller.
 *
 * @since  5.0.0
 */
class DisplayController extends BaseController
{
	/**
		 * The default view.
		 *
		 * @var    string
		 * @since  1.0.0
		 */
		protected $default_view = 'mymuse';
		
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  BaseController|bool  This object to support chaining.
	 *
	 * @since   1.0.0
	 *
	 * @throws  \Exception
	 */
	public function display($cachable = false, $urlparams = array())
	{


        $app  = Factory::getApplication();
		if($app->getUserState('com_mymuse.convertTo4', false)){
			$input = $app->input;
			$input->set('view','products');
			$input->set('task','update');
		}



		return parent::display();
	}
}