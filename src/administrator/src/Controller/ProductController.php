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
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseStorage;
use Joomla\Component\Mymuse\Administrator\Controller\OrderController;
use Joomla\Component\Mymuse\Site\Service\Mymuse;
use Joomla\Component\Mymuse\Site\Model\StoreModel;

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
     * Mymuse Storage.
     *
     * @var    object
     * @since  1.6
     */
    protected $storage = NULL;

    
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
        $this->registerTask( 'save', 'saveitem' );
        $this->registerTask( 'additem', 'edititem' );
        $this->registerTask( 'applyitem', 'saveitem' );
        $this->registerTask( 'save2newitem', 'saveitem' );
        
        $this->registerTask( 'addfile', 'edititem' );
        $this->registerTask( 'editfile', 'edititem' );
        $this->registerTask( 'save2copy', 'saveitem' );
        $this->registerTask( 'save2new', 'saveitem' );
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
        
        $type = $this->input->get('type');
        if(isset($type) && $type == "file"){
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

        $post               = $this->input->post->getArray();

        $this->id           = isset($post['id'])? $post['id'] : null ;
        $this->parentid     = isset($post['parentid'])? $post['parentid'] : 0;
        $form               = $post['jform'];
        $this->product_sku  = isset($form['product_sku'])? $form['product_sku'] : '';
        $db                 = Factory::getDBO();
        $task               = $this->input->get('task');

        $type               = $post['type'];
        $layout             = isset($post['layout'])? $post['layout'] : '';
        $model              = $this->getModel();
        $table              = $model->getTable();

    
        if(isset($form['product_allfiles']) && $form['product_allfiles'] == 1){
            $type = 'allfiles';
            $table->product_allfiles = 1;
        }elseif(!isset($type) || $type == ""){
            $type = 'file';
        }

        

        $oldtask = $task;
        if($task == "save2copy"){
           $this->input->set('task', "apply");
        }

        if($type == "product") {

            if(!$model->save($form)){
                $this->app->enqueueMessage($model->getError(), 'error');
                return false;
            }
            $this->postSaveHook($model);

            switch ( $oldtask )
            {
                //apply, save, save2new save2copy, cancel
                case 'save2copy':
                    
                    $this->msg = Text::_( 'COM_MYMUSE_ITEM_SAVED' );
                    $newid = $model->save2copy($form['id']);
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edit&id='. $newid."&type=product" );

                    break;

                case 'save':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_ITEM_SAVED' ), 'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=products' );
                    break;

                case 'save2new':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_ITEM_SAVED' ), 'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=edit&type=product' );
                    break;

                case 'apply':
                default:
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_ITEM_SAVED' ), 'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=edit&id='. $this->id.'&type=product' );
            }
        }
        elseif($type == "file" || $type == "allfiles"){
            /* SAVING A FILE */
            if(!$model->save($form)){
                $this->app->enqueueMessage($model->getError(), 'error');
                return false;
            }
            $this->postSaveHook($model);
                
            switch ($task )
            {
                case 'apply_allfiles':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_CHANGES_TO_ALL_FILE_SAVED'), 'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edit_allfiles&id='. $this->id.'&type='.$type );
                    break;
                case 'save_allfiles':
                    $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ALL_FILE_SAVED' ), 'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='. $this->parentid.'&type=files' );
                    break;
                case 'save2newfile':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_CHANGES_TO_FILE_SAVED'), 'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.addfile&type='.$type.'&parentid='.$this->parentid );
                    break;
                case 'applyfile':
                    $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_CHANGES_TO_FILE_SAVED' ), 'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.editfile&id='. $this->id.'&type='.$type );
                    break;
                case 'deletevariation':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_FORMAT_DELETED' ), 'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.editfile&id='. $this->id.'&type='.$type );
                    break;
                case 'savefile':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_FILE_SAVED' ), 'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='. $this->parentid.'&type='.$type );
                    break;
                case 'apply':
                default:
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_ITEM_SAVED' ), 'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=edit&id='. $this->id.'&type='.$type );
                }

        }elseif($type == "item"){
            /* SAVING ITEM */
            if(!$model->save($form)){
                $this->app->enqueueMessage($model->getError(), 'error');
                return false;
            }
            $this->postSaveHook($model);

            $this->input->set('itemid',$this->id);
            $model->updateAttributes();

                
            switch ( $oldtask )
            {
                case 'save2copy':
                    $this->msg = Text::_( 'COM_MYMUSE_ITEM_SAVED' );
                    $newid = $model->save2copy($form['id'], 1);
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edititem&id='. $newid."&type=item" );
                    break;

                case 'save2newitem':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_CHANGES_TO_ITEM_SAVED'),'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.additem&type=item&parentid='.$this->parentid );
                    break;
                    
                case 'applyitem':
                case 'apply':
                    $this->app->enqueueMessage(Text::_('COM_MYMUSE_CHANGES_TO_ITEM_SAVED'),'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edititem&id='. $this->id."&type=item&parentid='.$this->parentid" );
                    break;

                case 'saveitem':
                default:
                    $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ITEM_SAVED' ),'notice');
                    $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listitems&id='. $this->parentid."&type=item" );
                    break;
            }
       

        } else {
            $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ERROR_SAVING_ITEM' ).' : '.$model->getError(), 'error');
            if($this->parentid){
                $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edit&id='.$this->parentid );
            }elseif($this->id){
                $this->setRedirect( 'index.php?option=com_mymuse&view=product&task=product.edit&id='.$this->id );
            }else{
                $this->setRedirect( 'index.php?option=com_mymuse&view=products' );
            }
        }

    }


    /*
    * check for errors on the way to edit items
    *
    * @return  mixed
    *
    * @since   4.0
    */    
    function edititem() 
    {
        $task   = $this->input->get('task');
        $id     = $this->input->get('id');
        $app    = Factory::getApplication();
        $model  = $this->getModel();

        if($task == "additem") {
            $this->attribute_skus = $model->getAttributeskus();

            if(!count($this->attribute_skus)){
                //no attributes yet!!
                $app->enqueueMessage(Text::_("COM_MYMUSE_CREATE_ATTRIBUTE_FIRST"), 'notice');
                $this->setRedirect( "index.php?option=com_mymuse&view=product&layout=listitems&id=".$id);
                return;
            }
        }
        $this->display();

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

        $db             = Factory::getDBO();
        $id             = $this->input->get('id');
        $model          = $this->getModel('product');
        $num_items      = 1;
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
            if(isset($att_skus[1])){
                foreach($keys[$att_skus[1]->id] as $vals_1){
                    $res[] = array(
                        $att_skus[0]->id => $vals_0, 
                        $att_skus[1]->id => $vals_1
                    );
                }
            }else{
                $res[] = array(
                        $att_skus[0]->id => $vals_0
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
        $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listitems&id='. $id."&type=item" );
        
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

        $this->input    = Factory::getApplication()->input;
        $parentid       = $this->input->get( 'parentid', '' );
        $type           = $this->input->getr( 'type', '' );
        $this->app->enqueueMessage(Text::_( 'COM_MYMUSE_ITEM_CANCELLED' ), 'notice');

        if($type == 'file'){
            $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listtracks&id='.$parentid);
        }elseif($type == 'item'){
            $this->setRedirect( 'index.php?option=com_mymuse&view=product&layout=listitems&id='.$parentid);
        }elseif($type == 'allfiles'){
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
        $type     = $this->input->get( 'type', '' );
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



    public function update()
    {
        echo "<h3>UPDATE FUNCTION</h3>";
        $model      = $this->getModel('product');
        $app        = Factory::getApplication();
        $input      = Factory::getApplication()->input;
        $limitstart = $input->get('limitstart',0);
        $params     = MyMuseHelper::getParams();
        $my_limit   = $params->get('my_update_limit', 200);
        $limit      = $input->get('limit',$my_limit);
        $type       = $input->get('type','physical');
        $this->storage = new MymuseStorage();


        if($res = $model->updateDB($limit, $limitstart, $type)){
            //echo "result $res <BR />";
            if($res[0] == "tracks-done"){
                echo "<h3>". Text::_('COM_MYMUSE_UPDATE_ALL_DONE')."</h3>";
                echo $res[1];
                // updated physical and tracks. Remove the blocking file
                $v3File = JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_mymuse'.DIRECTORY_SEPARATOR.'manifest.xml';

                if($this->storage->fileDelete($v3File)){

                    echo Text::_('COM_MYMUSE_DB_UPDATED');
                    $session  = Factory::getSession();
                    $session->set('com_mymuse.convertTo4', false);
                
                }else{
                    echo Text::_('COM_MYMUSE_COULD_NOT_DELETE_FILE').' : '. $v3File;
                }
                
              
                return true;
            }elseif($res[0] == "physical-continue"){
                $limitstart = $limitstart + $limit;
                echo '<h3><a href="index.php?option=com_mymuse&task=product.update&limit='.$limit.'&limitstart='.$limitstart.'&type=physical">--> More physical products to process!</a></h3>';
                echo $res[1];
                return true;
            }elseif($res[0] == "physical-done"){
                $limitstart = 0;
                echo '<h3><a href="index.php?option=com_mymuse&task=product.update&limit='.$limit.'&limitstart='.$limitstart.'&type=tracks">-->Tracks to process!</a></h3>';
                echo $res[1];
                return true;

            }elseif($res[0] == 'tracks-continue'){

                $limitstart = $limitstart + $limit;
                echo '<h3><a href="index.php?option=com_mymuse&task=product.update&limit='.$limit.'&limitstart='.$limitstart.'&type=tracks">-->MORE TRACKS TO PROCESS</a></h3>';
                echo $res[1];
                return true;

            }else{
                $model->getError();
                return false;
            }
            
        }else{
            echo "No result";
            $model->getError();
            return false;
        }
        return true;

    }


}