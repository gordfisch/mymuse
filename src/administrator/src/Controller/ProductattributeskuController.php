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

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;

/**
 * Productattributesku controller class.
 *
 * @since  1.6
 */
class ProductattributeskuController extends FormController
{
	use VersionableControllerTrait;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_MYMUSE_PRODUCTATTRIBUTE_SKU';

function add()
{
    //echo "add"; exit;
    $app        = Factory::getApplication();
    $input      = $app->input;
    $input->set('layout', 'edit');
    return parent::add();
}

function save($key = NULL, $urlVar = NULL)
    {
    	$app   		= Factory::getApplication();
    	$input 		= $app->input;
    	$post 		= $input->post->getArray();
		$form 		= $post['jform'];
		$id 		= $form['id'];
		
 		$app->setUserState('com_mymuse.edit.productattributesku.data', $form);

    	$arr['extra_base'] = $form['extra_base'];
    	$arr['extra_css'] = $form['extra_css'];

    	$bases = preg_split('/\r\n|\r|\n/', $form['extra_base']);
    	$csss = preg_split('/\r\n|\r|\n/', $form['extra_css']);

    	if($form['extra_base'] != '' && $form['extra_css'] != ''){
    		
    		if( count($bases) != count($csss) ){
    			$msg = "The base value count does not match the css count.";
    			$this->setRedirect( 'index.php?option=com_mymuse&task=productattributesku.edit&id='.$id, $msg );
    			return false;

    		}
    	}


    	$app->setUserState('com_mymuse.edit.productattributesku.data', null);	
    	return parent::save();

    }

    function myreturn()
    {
        $app        = Factory::getApplication();
        $input      = $app->input;
        $id         = $input->get('parentid');
        $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listitems&id='.$id );


    }
}