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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;


/**
 * Product controller class.
 *
 * @since  1.6
 */
class ProductController extends FormController
{
	use VersionableControllerTrait;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_MYMUSE_PRODUCT';

    /**
     * The input.
     *
     * @var    object
     * @since  1.6
     */
    protected $input = NULL;

    /**
     * The id.
     *
     * @var    int
     * @since  1.6
     */
    protected $id = 0;

	
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   Input                $this->input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{

		parent::__construct($config, $factory, $app, $input);

        $this->registerTask( 'apply', 'saveitem' );
        $this->registerTask( 'additem', 'edititem' );
        $this->registerTask( 'applyitem', 'saveitem' );
        $this->registerTask( 'save2newitem', 'saveitem' );
        
        $this->registerTask( 'addfile', 'edititem' );
        $this->registerTask( 'editfile', 'edititem' );
        $this->registerTask( 'save2copy', 'saveitem' );
        $this->registerTask( 'save2newfile', 'saveitem' );
        $this->registerTask( 'savefile', 'saveitem' );
        $this->registerTask( 'applyfile', 'saveitem' );
        $this->registerTask( 'publishfile', 'publishitem' );
        $this->registerTask( 'unpublishfile', 'publishitem' );
        $this->registerTask( 'cancelfile', 'cancelitem' );
        $this->registerTask( 'removefile', 'removeitem' );
        
        $this->registerTask( 'new_allfiles', 'edititem' );
        $this->registerTask( 'edit_allfiles', 'edititem' );
        $this->registerTask( 'save_allfiles', 'saveitem' );
        $this->registerTask( 'apply_allfiles', 'saveitem' );
        
        
        $this->registerTask( 'addattribute', 'editattribute' );
        $this->registerTask( 'save2newfile', 'saveitem' );
        $this->registerTask( 'deletevariation', 'saveitem' );

        $this->input       = Factory::getApplication()->input;
        
        $subtype = $this->input->get('subtype');
        if(isset($subtype) && $subtype == "file"){
            $this->view_list = "product";
        }else{
            $this->view_list = 'products';
        }

        $cid = $this->input->get( 'cid', array(0));
        if($cid[0] > 0){
            $this->input->set('id',$cid[0]);
        }

        $this->input->set('view','product');
	}


    /**
     * saveitem
     *
     * store the item to the database
     * @return false
     * @since 1.0
     */
    function saveitem()
    { 

        $post 				= $this->input->post->getArray();

        $this->id 			= isset($post['id'])? $post['id'] : null ;
        $this->parentid 	= isset($post['parentid'])? $post['parentid'] : 0;
        $form 				= $post['jform'];
		$this->product_sku 	= isset($form['product_sku'])? $form['product_sku'] : '';
		$db 				= Factory::getDBO();


		$subtype 			= $post['subtype'];
		$layout 			= isset($post['layout'])? $post['layout'] : '';
		$model 				= $this->getModel();
        $table 				= $model->getTable();

    	// is this the special 'AllFiles'?
		if(isset($form['product_allfiles']) && $form['product_allfiles'] == 1){
			$subtype = 'allfiles';
			$table->product_allfiles = 1;
		}elseif(!isset($post['subtype']) || $post['subtype'] == ""){
			$subtype = 'item';
		}

		$task = $this->input->get('task');

        $oldtask = $task;
        if($task == "save2copy"){
            $this->input->set('task', "apply");
        }

		if($subtype == "file" || $subtype == "allfiles"){
	
			if ($model->save($form)) {
                if(!$this->id){
                    $item = $model->getItem();
                    $this->id = $item->id;
                }
                
				switch ($task )
				{
				case 'apply_allfiles':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_CHANGES_TO_ALL_FILE_SAVED'), 'notice');
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edit_allfiles&id='. $this->id.'&subtype='.$post['subtype'] );
					break;
				case 'save_allfiles':
                    $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ALL_FILE_SAVED' ), 'notice');
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='. $this->parentid.'&subtype=files' );
					break;
				case 'save2newfile':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_CHANGES_TO_FILE_SAVED'), 'notice');
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.addfile&subtype='.$post['subtype'].'&parentid='.$this->parentid );
					break;
				case 'applyfile':
                    $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_CHANGES_TO_FILE_SAVED' ), 'notice');
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.editfile&id='. $this->id.'&subtype='.$post['subtype'] );
					break;
				case 'deletevariation':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_FORMAT_DELETED' ), 'notice');
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.editfile&id='. $this->id.'&subtype='.$post['subtype'] );
					break;
				case 'savefile':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_FILE_SAVED' ), 'notice');
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='. $this->parentid.'&subtype='.$post['subtype'] );
					break;
                case 'apply':
                default:
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_ITEM_SAVED' ), 'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=edit&id='. $this->id.'&subtype='.$post['subtype'] );
				}
			}else {

        		Factory::getApplication()->enqueueMessage($model->getError(), 'error');
        		switch ($task )
        		{
        			case 'apply_allfiles':
        				
        				
        			case 'save_allfiles':
        				if($this->id){
        					$this->setRedirect( "index.php?option=com_mymuse&view=product&task=product.edit_allfiles&parentid=".$this->parentid.'&id='.$this->id.'&subtype='.$post['subtype'] );
        				}else{
        					$this->setRedirect( "index.php?option=com_mymuse&view=product&task=product.new_allfiles&parentid=".$this->parentid.'&subtype='.$post['subtype'] );
        				}
        				break;
        			default:
        				if($this->id){
        					$this->setRedirect( "index.php?option=com_mymuse&view=product&task=product.editfile&parentid=".$this->parentid.'&id='.$this->id.'&subtype='.$post['subtype'] );
        				}else{
        					$this->setRedirect( "index.php?option=com_mymuse&view=product&task=product.addfile&parentid=".$this->parentid.'&id='.$this->id.'&subtype='.$post['subtype'] );
						}
						break;
        		}
        	}
 
		//save an item
		}elseif ($model->save($form)) {


            $this->id = $form['id'];
            $this->id = $model->getState('product.id');

            if(!$this->id && $this->product_sku){
    			$query = "SELECT id FROM #__mymuse_product WHERE product_sku='".$this->product_sku."'";
    			$db->setQuery($query);

    			if(!$this->id = $db->loadResult()){
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_COULD_NOT_FIND_ID' ), 'notice');
    				$this->setRedirect( 'index.php?option=com_mymuse&iew=product&task=product.edit&id='. $this->parentid );
    				return false;
    			}
            }
			//now we have an id, update the attributes
			$this->input->set('itemid',$this->id);
			$model->updateAttributes();

        	switch ( $oldtask )
			{
                case 'save2copy':
                    $this->msg = Text::_( 'COM_MYMUSE_ITEM_SAVED' );
                    $this->input->set('task', "save2copy");
                    $form['id'] = '';
                    if(!$model->save($form)){
                        $this->app->enqueueMessage($model->getError(), 'error');
                        return false;
                    }
                    $newid = $model->getState('product.id');

                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edititem&id='. $newid."&subtype=item" );

                    break;

				case 'save2newitem':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_CHANGES_TO_ITEM_SAVED'),'notice');
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.additem&subtype=item&parentid='.$this->parentid );
					break;
					
				case 'applyitem':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_CHANGES_TO_ITEM_SAVED'),'notice');
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edititem&id='. $this->id."&subtype=item" );
					break;

                case 'apply':
                    $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_CHANGES_TO_ITEM_SAVED' ),'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edititem&id='. $this->id."&subtype=item" );
                    break;

				case 'saveitem':
				default:
                    $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ITEM_SAVED' ),'warning');
					$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listitems&id='. $this->parentid."&subtype=item" );
					break;
				}

        } else {
            $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ERROR_SAVING_ITEM' ).' : '.$model->getError());
            //exit;
        	$this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edit&id='.$this->parentid."&subtype=item" );
        }

    }


    /*
    * save the parent item then redirect to listtracks
    *
    * @return  mixed
    *
    * @since   4.0
    */    
    function listracks () 
    {
        
        $post               = $this->input->post->getArray();
        $this->id           = isset($post['id'])? $post['id'] : null ;
        if($this->save()){
            //get the product id
            if(!$this->id){
                $query = "SELECT id FROM #__mymuse_product WHERE product_sku='".$this->product_sku."'";
                $db->setQuery($query);

                if(!$this->id = $db->loadResult()){
                    $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_COULD_NOT_FIND_ID' ), 'error');
                    $this->setRedirect( 'index.php?option=com_mymuse&iew=product&task=product.edit&id='. $this->id );
                    return false;
                }
            }
            $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ITEM_SAVED' ), 'notice');
            $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='.$this->id );
        }else{
            $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ERROR_SAVING_ITEM' ).' : '.$this->getError(), 'error');
            $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edit&id='.$this->id );
        }
    }


    /*
    * listitems: save the parent item then redirect to listitems
    *
    * @return  mixed
    *
    * @since   4.0
    */    
    function listitems () 
    {
        $this->input              = Factory::getApplication()->input;
        $post               = $this->input->post->getArray();
        $this->id           = isset($post['id'])? $post['id'] : null ;
        if($this->save()){
            //get the product id
            if(!$this->id){
                $query = "SELECT id FROM #__mymuse_product WHERE product_sku='".$this->product_sku."'";
                $db->setQuery($query);

                if(!$this->id = $db->loadResult()){
                    $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_COULD_NOT_FIND_ID'), 'error');
                    $this->setRedirect( 'index.php?option=com_mymuse&iew=product&task=product.edit&id='. $this->id );
                    return false;
                }
            }
            //$this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ITEM_SAVED' ).' here');
            $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listitems&id='.$this->id );
        }else{
            $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ERROR_SAVING_ITEM' ).' : '.$this->getError(), 'error');
            $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edit&id='.$this->id );
        }
    }

    /*
    * Function that creates a set of new items based on attribute values
    *
    * @return  mixed
    *
    * @since   4.0
    */
    function create_items ()
    {

        $this->input      = Factory::getApplication()->input;
        $db         = Factory::getDBO();
        $id         = $this->input->get('id');
        $model      = $this->getModel('product');
        $num_items  = 1;
        $this->input->set('parentid', $id);

/*      
[option] => com_mymuse
[task] => create_items
[view] => product
[layout] => listitems
[id] => 8
*/
        
        //delete current items
        $query = "SELECT id from #__mymuse_product where parentid=$id AND product_physical=1";
        $db->setQuery($query);
        $old_ids = $db->loadColumn();
        if(!$model->delete($old_ids)){
            $this->msg = "Could not delete old items";
            Factory::getApplication()->enqueueMessage($this->msg, 'error');
            return false;
        }

        $att_skus = $model->getAttributeskus();

        $colorid = 0;
        foreach($att_skus as $k => $att){
            $keys[$att->id] = preg_split('/\r\n|\r|\n/', trim($att->extra_base));
            $keys['count_'.$att->id] = count($keys[$att->id]);
            $num_items = $num_items * $keys['count_'.$att->id];
            $attribute_name[$att->id] = $att->name;
            if($att->name == "Color"){
                $colorid = $att->id;
            }
        }
        $this->input->set( 'attribute_name', $attribute_name );
        $values = array();

        foreach($keys[$att_skus[0]->id] as $vals_0){
            foreach($keys[$att_skus[1]->id] as $vals_1){
                $res[] = array(
                    $att_skus[0]->id => $vals_0, 
                    $att_skus[1]->id => $vals_1
                );
            }
        }

        for($i = 0; $i < $num_items; $i++){
            $item_title = '';
            foreach($res[$i] as $k => $v){
                $item_title .= " -".$v."";
                if($k == $colorid){
                     $this->input->set('color',strtolower($v));
                }
            }
            $this->input->set('jform[parentid]',$id);
            $this->input->set('item_title',$item_title);

            if(!$newid = $model->save2copy($id, 1)){
                $this->msg = $this->getError();
                $this->app->enqueueMessage($this->msg, 'error');
                return false;
            }
            $this->input->set('itemid',$newid);
            $this->input->set( 'attribute_value', $res[$i] );
            $model->updateAttributes();
        
        }

        $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_CHANGES_TO_ITEM_SAVED' ), 'notice');
        $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listitems&id='. $id."&subtype=item" );
        
    }



    /**
     * Function that allows child controller access to model data
     * after the data has been saved.
     *
     * @param   \JModelLegacy  $model      The data model object.
     * @param   array          $validData  The validated data.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
    {

        $this->id = $model->getState($this->context . '.id');

    }


    function cancelitem()
    {
        // Checkin the item
        $model      = $this->getModel('product');
        $model->checkin();

        $this->input      = Factory::getApplication()->input;
        $parentid   = $this->input->get( 'parentid', '' );
        $subtype    = $this->input->getr( 'subtype', '' );
        $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ITEM_CANCELLED' ), 'notice');

        if($subtype == 'file'){
        	$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='.$parentid);
        }elseif($subtype == 'item'){
        	$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listitems&id='.$parentid);
        }elseif($subtype == 'allfiles'){
        	$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='.$parentid);
        }elseif($parentid){
        	$this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=edit&id='.$parentid);
        }else{
            $this->setRedirect( 'index.php?option=com_mymuse&view=products');
        }
    }

	

    function removeitem()
    {
        $cid = $this->input->get( 'cid', array(), 'ARRAY' );
       
        ArrayHelper::toInteger($cid);
        if (count( $cid ) < 1) {
            Error::raiseError(500, Text::_( 'COM_MYMUSE_SELECT_AN_ITEM_TO_DELETE' ) );
        }
		$parentid = $this->input->get( 'parentid', '' );
		$subtype  = $this->input->get( 'subtype', '' );
		$layout   = $this->input->get( 'layout', '' );
        $model    = $this->getModel('product');

        if(!$model->delete($cid)) {
            echo "<script> alert('Error: ".$model->getError(true)."'); window.history.go(-1); </script>
            ";
            }
        $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ITEM_DELETED' ), 'notice');
        $url = 'index.php?option=com_mymuse&view=product&task=edit&id='.$parentid;
        if($layout){
        	$url .= "&layout=$layout";
        }
        $this->setRedirect( $url,$this->msg  );
    }
	
    /**
     * saveOrderAjax()
     * Method to save the submitted ordering values for records via AJAX.
     * brought in by arboreta from libraries/legacy/controller/admin.php
     * for saving tracks
     *
     * @return  void
     *
     * @since   3.0
     */
    public function saveOrderAjax()
    {
    	//MyMuseHelper::logMessage("here we are Ajax\n");
    	// Get the input
    	$pks = $this->input->post->get('cid', array(), 'array');
    	$order = $this->input->post->get('order', array(), 'array');
    
    	// Sanitize the input
    	ArrayHelper::toInteger($pks);
    	ArrayHelper::toInteger($order);
    
    	// Get the model
    	$model = $this->getModel();
    	//$model = $this->getModel('Products', 'MyMuseModel', array('ignore_request' => true));;
    
    	// Save the ordering
    	$return = $model->saveorder($pks, $order);
    
    	if ($return)
    	{
    		echo "1";
    	}
    
    	// Close the application
    	Factory::getApplication()->close();
    }
    
    /**
     * productreturn()
     *
     * Redirect to product edit page
     *
     * @since 1.0
    */
    public  function productreturn()
    {
        // Checkin the item
        $model = $this->getModel('product');
        $model->checkin();

        $this->input    = Factory::getApplication()->input;
        $parentid       = $this->input->get( 'parentid', '');

        $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ITEM_CANCELLED' ), 'warning');
        $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=edit&id='.$parentid);
    }

    /**
     * listtracks()
     *
     * Redirect to tracks list page
     *
    */
    public function listtracks()
    {
        $model      = $this->getModel('product');
        $model->checkin();
        //store the product_id
        $app        = Factory::getApplication();

        $this->input      = Factory::getApplication()->input;
        $id         = $this->input->get('id',0);
        $app->getUserStateFromRequest( "com_mymuse.product_id", 'product_id', $id );
        $url         = 'index.php?option=com_mymuse&view=tracks&product_id='.$id;
        $this->setRedirect( $url);
        return;
    }

    /**
     * uploadtrack()
     *
     * Redirect to com media download dir
     *
    */
    public function uploadtrack()
    {
        $params     = MyMuseHelper::getParams();
        $remove     = JPATH_SITE."/images/";

        $dir        = preg_replace("#$remove#", '',$params->get('my_download_dir'));
        $url        = 'index.php?option=com_media&path=local-images:/'.$dir;

        $this->setRedirect( $url);
        return;

    }

    /**
     * uploadpreview()
     *
     * Redirect to com media preview dir
     *
    */
    public function uploadpreview()
    {
        $params     = MyMuseHelper::getParams();
        $dir        = preg_replace("#^images/#", '',$params->get('my_preview_dir'));
        $url        = 'index.php?option=com_media&path=local-images:/'.$dir;

        $this->setRedirect( $url);
        return;
    }

	/**
	 * Method to run batch operations.
	 *
	 * @param   string  $model  The model
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function batch($model = null)
	{
		$this->checkToken();

		// Set the model
		$model = $this->getModel('product', '', array());

		// Preset the redirect
		$this->setRedirect(Route::_('index.php?option=com_mymuse&view=product' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}
}