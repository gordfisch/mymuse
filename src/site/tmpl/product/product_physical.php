<?php 
/**
 * @version		$Id: product_physical.php 1986 2018-07-22 19:11:13Z gfisch $
 * @package		mymuse
 * @copyright	Copyright © 2018 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author 		info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

$product 	=& $this->item;
$params 	=& $this->params;
$items    =& $this->item->items;

if( $product->product_physical && !count($items)) : 

$cols 		= 2;
if($this->params->get('product_show_filetime', 0)) :
	$cols++;
endif;
if ($this->params->get('product_show_quantity')) :
	$cols++;
endif;


?>
<style>
@media (min-width: 768px) {
  .physical .my-grid {
      grid-template-columns: 5fr<?php echo str_repeat(' 1fr',$cols); ?>;
  }
}
.jp-gui {
  width: inherit;
}
</style>


<!--  PRODUCT PHYSICAL -->
		<h3><?php echo Text::_('COM_MYMUSE_PRODUCT'); ?></h3>


  <section class="physical">
    <ul class="mymuse-container">
    <li class="my-grid">
        <div class="mymuse-header title">
        		<?php echo Text::_('COM_MYMUSE_NAME'); ?>
        </div>

        <?php  if($params->get('product_show_filetime', 0)) :?>
          <div class="mymuse-header time" >
          <?php echo Text::_('COM_MYMUSE_TIME'); ?></div>
        <?php endif; ?>

        <div class="mymuse-header price right">
        		<?php echo Text::_('COM_MYMUSE_COST'); ?>
        </div>

        <?php if ($this->params->get('product_show_quantity') && $this->available) :?>
        		<div class="mymuse-header quantity" >
          <?php echo Text::_('COM_MYMUSE_QUANTITY'); ?></div>
        <?php endif; ?>

        <?php if($this->available) : ?>
        <div class="mymuse-header select">
        		<?php echo Text::_('COM_MYMUSE_SELECT'); ?>
        </div>
        <?php endif; ?>
     </li>

     <li class="my-grid">
        <div class="mycart-inner title" data-name="<?php echo Text::_('COM_MYMUSE_NAME'); ?>">
        		<?php echo $product->title; ?>
        </div>

        <?php  if($params->get('product_show_filetime', 0)) :?>
          <div class="mycart-inner  mytime" data-name="<?php echo Text::_('COM_MYMUSE_TIME'); ?>">
          <?php echo $product->recording->get('product_full_time','0'); ?></div>
        <?php endif; ?>

        <div class="mycart-inner price right" data-name="<?php echo Text::_('COM_MYMUSE_COST'); ?>">
        		<?php  echo MyMuseHelper::printMoneyPublic($product->price);
				?>
        </div>

        <?php if ($this->params->get('product_show_quantity') && $this->available) :?>
        		<div class="mycart-inner  quantity" data-name="<?php echo Text::_('COM_MYMUSE_QUANTITY'); ?>">
          <input class="inputbox" type="text"
					name="quantity[<?php echo $product->id; ?>]" size="2" value="1" /></div>
        <?php endif; ?>

        <?php if($this->available) : ?>
        <div class="mycart-inner  select" data-name="<?php echo Text::_('COM_MYMUSE_SELECT'); ?>">
        		<a href="javascript:void(0)"
					id="box_<?php echo $product->id; ?>"><img
						id="cart_image_<?php echo $product->id; ?>"
						src="<?php
                    if(in_array($product->id, $this->products)) :
                       echo Route::_("components/com_mymuse/assets/images/minus-button-30.png");
                    else :
                        echo Route::_("components/com_mymuse/assets/images/plus-button-30.png");
                     endif;
                 ?>"></a> <span class="mycheckbox"><input
						style="display: none;" type="checkbox" name="productid[]"
						value="<?php echo $product->id; ?>"
						id="box<?php echo $this->check; $this->check++; ?>" /> </span>
        </div>
        <?php endif; ?>
     </li>
  </ul>
</section>





		<!-- END PRODUCT PHYSICAL -->

<?php endif; ?>