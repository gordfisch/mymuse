 <?php 
 /**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
 // no direct access
 use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
 use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

$shopper 	= $this->shopper;
$params 	= $this->params;

if(!isset($shopper->profile['name']) && 
		( isset($shopper->profile['first_name']) && isset($shopper->profile['last_name']) )
	){
	$shopper->profile['name'] = $shopper->profile['first_name'].' '.$shopper->profile['last_name'];
	
}
//if($this->user->username == "buyer"){
	$fields = MyMuseHelper::getNoRegFields();
	foreach($fields as $field){
		if(!isset($shopper->profile[$field]) && isset($shopper->$field) && $shopper->$field != ''){
			$shopper->profile[$field] = $shopper->$field;
			
		}
	}
//}
$colspan = 1;
if($params->get('my_use_shipping') && isset($this->order->need_shipping)
			&& $this->order->need_shipping ){
	$colspan = 2;
}
?>     <!-- Begin bill-ship to -->
        <h2><?php echo Text::_('COM_MYMUSE_SHOPPER_INFORMATION') ?></h2>
		
           <!-- Begin BillTo -->

            <ul class="mymuse-container" >
                <li class="mymuse-cart-top mymuse-grid-1-2">
                	<div class="mymuse-cart-top"><?php echo Text::_('COM_MYMUSE_BILLING_ADDRESS') ?></div>
                    <div></div>
                </li>
                
                <li class="mymuse-grid-1-2">
                	<div class=" mymuse-label"><?php echo Text::_('COM_MYMUSE_FULL_NAME') ?>:</div>
                	<div class="myfullname mymuse-value">
                	<?php echo $shopper->profile['name'] ?>
                	</div>
                </li>
                <li class="mymuse-grid-1-2">
                	<div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_EMAIL') ?>:</div>
                	<div class="myemail mymuse-value"><?php echo $shopper->profile['email'] ?></div>
                </li>
                
            <?php if(isset($shopper->profile)){ ?>
            
              <?php if(isset($shopper->profile['phone']) && $shopper->profile['phone'] != ''){ ?> 
                <li class="mymuse-grid-1-2">
                	<div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_PHONE') ?>:</div>
                	<div class="myphone mymuse-value"><?php echo $shopper->profile['phone'] ?></div>
                </li>
              <?php } ?>
              
              <?php if(isset($shopper->profile['mobile']) && $shopper->profile['mobile'] != ''){ ?> 
                <li class="mymuse-grid-1-2">
                	<div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_MOBILE') ?>:</div>
                	<div class="myphone mymuse-value"><?php echo $shopper->profile['mobile'] ?></div>
                </li>
              <?php } ?>
              
              <?php if(isset($shopper->profile['address1']) && $shopper->profile['address1'] !=''){ ?> 
                <li class="mymuse-grid-1-2">
                	<div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_ADDRESS') ?>:</div>
                	<div class="myaddress mymuse-value">
                	<?php echo $shopper->profile['address1'] ?>
                	
                	<?php echo @$shopper->profile['address2'] ?>
                	</div>
                </li>
              <?php } ?>
              
              <?php if(isset($shopper->profile['city']) && $shopper->profile['city'] != ''){ ?> 
                <li class="mymuse-grid-1-2">
                	<div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_CITY') ?>:</div>
                	<div class="mycity mymuse-value"><?php echo $shopper->profile['city'] ?></div>
                </li>
              <?php } ?>
              
              <?php if(isset($shopper->profile['region_name']) && $shopper->profile['region_name'] != ''){ ?>
                <li class="mymuse-grid-1-2">
                	<div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_STATE') ?>:</div>
                	<div class="myregion mymuse-value"><?php echo $shopper->profile['region_name'] ?></div>
                </li>
              <?php } ?>
              
              <?php if(isset($shopper->profile['country']) && $shopper->profile['country'] != ''){ ?>
                <li class="mymuse-grid-1-2">
                	<div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_COUNTRY') ?>:</div>
                	<div class="mycountry mymuse-value"><?php echo $shopper->profile['country'] ?></div>
                </li>
            <?php  } ?>
            
            <?php if(isset($shopper->profile['postal_code']) && $shopper->profile['postal_code'] != ''){ ?>
                <li class="mymuse-grid-1-2">
                	<div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_ZIP') ?>:</div>
                	<div class="myzip mymuse-value"><?php echo $shopper->profile['postal_code'] ?></div>
                </li>
              <?php } ?>
              
			<?php } //end if profile?>
                
            </ul>
            <!-- End BillTo --> 
           
        
            
    
        <?php 
        if( $params->get('my_use_shipping') && ( 
            (isset($this->order->need_shipping) && $this->order->need_shipping) 
            || ( isset($this->order->shipping) && is_object($this->order->shipping) )  
            || ( isset($this->order->order_shipping) && isset($this->order->order_shipping->cost) &&
             $this->order->order_shipping->cost > 0) 
        ) ){

        ?>
            <ul class="mymuse-container">
                <li class="mymuse-grid-1-2 mymuse-cart-top">
                    <div class="mymuse-cart-top"><?php echo Text::_('COM_MYMUSE_SHIPPING_ADDRESS') ?></div>
                    <div></div>
                </li>
            <?php
            if(
                (!isset($shopper->profile['shipping_first_name']) ||
                $shopper->profile['shipping_first_name'] == '' ) &&
                (!isset($shopper->profile['shipping_last_name']) ||
                $shopper->profile['shipping_last_name'] == '') &&
                (!isset($shopper->profile['shipping_address1']) ||
                $shopper->profile['shipping_address1'] == '') &&
                (!isset($shopper->profile['shipping_city']) ||
                $shopper->profile['shipping_city'] == '') &&
                (!isset($shopper->profile['shipping_region_name']) ||
                    $shopper->profile['shipping_region_name'] == '') &&
                (!isset($shopper->profile['shipping_country']) ||
                    $shopper->profile['shipping_country'] == '') &&
                (!isset($shopper->profile['shipping_postal_code']) ||
                $shopper->profile['shipping_postal_code'] == '')
            )
            {
                
                ?>
                <li class="mymuse-grid-1-2">
                    <div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_SHIPPING_ADDRESS_SAME_AS_BILLING'); ?></div>
                    <div></div>
                </li>
                <?php
            }
            else
            {
                MymuseHelper::print_pre($shopper->profile);
        ?>



             <?php if(isset($shopper->profile['shipping_first_name']) && isset($shopper->profile['shipping_last_name'])){ ?>
                <li class="mymuse-grid-1-2">
                    <div class="mymuse-label" width="20%"><?php echo Text::_('COM_MYMUSE_NAME') ?>:</div>
                    <div class="myfullname mymuse-value"><?php
                    echo $shopper->profile['shipping_first_name']." ".$shopper->profile['shipping_last_name'] ?></div>
                </li>
            <?php } ?>
            <?php if(isset($shopper->profile['shipping_address1'])){ ?>
                <li  class="mymuse-grid-1-2">
                    <div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_ADDRESS') ?>:</div>
                    <div class="myaddress mymuse-value">
                    <?php echo $shopper->profile['shipping_address1'] ?>
                    <br />
                    <?php echo isset($shopper->profile['shipping_address2'])? $shopper->profile['shipping_address2'] : '' ?>
                    </div>
                </li>
            <?php } ?>
            <?php if(isset($shopper->profile['shipping_city'])){ ?>
                <li class="mymuse-grid-1-2">
                    <div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_CITY') ?>:</div>
                    <div class="mycity mymuse-value"><?php echo $shopper->profile['shipping_city'] ?></div>
                </li>
            <?php } ?>
            <?php if(isset($shopper->profile['shipping_region_name'])){ ?>
                <li class="mymuse-grid-1-2">
                    <div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_STATE') ?>:</div>
                    <div class="myregion mymuse-value"><?php echo $shopper->profile['shipping_region_name'] ?></div>
                </li>
            <?php } ?>
            <?php if(isset($shopper->profile['shipping_country'])){ ?>
                <li class="mymuse-grid-1-2">
                    <div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_COUNTRY') ?>:</div>
                    <div class="mycountry mymuse-value"><?php echo $shopper->profile['shipping_country'] ?></div>
                </li>
            <?php } ?>
            <?php if(isset($shopper->profile['shipping_postal_code'])){ ?>
                <li class="mymuse-grid-1-2">
                    <div class="mymuse-label"><?php echo Text::_('COM_MYMUSE_ZIP') ?>:</div>
                    <div class="myzip mymuse-value"><?php echo $shopper->profile['shipping_postal_code'] ?></div>
                </li>
            <?php } ?>
            
		
          <?php } ?>
        </ul>
            <!-- End ShipTo -->
            <!-- End Customer Information --> 
       

        <?php 
       
        if($this->user->id 
        		&& $this->params->get('my_registration') != 'no_reg'
        		&& $this->user->username != 'buyer'
        		&& $this->task != 'notify'
        		&& $this->task != 'thankyou'
        		&& $this->task != 'confirm'
        		&& $this->task != 'makemail'
        		){ 
        		$url = JURI::base()."index.php?option=com_mymuse&view=cart&layout=cart&Itemid=".$this->Itemid;
        		$return = base64_encode($url);
        	?>
            
        	<div class="float-start cart"><form method="post" action="<?php echo JURI::base(); ?>index.php" id="profile_form">
        	<input type="hidden" name="option" value="com_users">
        	<input type="hidden" name="view" value="profile">
        	<input type="hidden" name="layout" value="edit">
        	<input type="hidden" name="user_id" value="<?php echo $this->user->id;?>">
        	<input type="hidden" name="return" value="<?php echo $return;?>">
        	<input type="submit" name="submit" class="button btn btn-primary" value="<?php echo Text::_("COM_MYMUSE_EDIT_PROFILE");?>">
        	</form></div>

        <?php } ?>
      
<?php } ?>