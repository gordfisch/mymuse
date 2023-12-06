<?php 
/**
 * @version   $Id: product_tracks.php 1986 2018-05-22 19:11:13Z gfisch $
 * @package   mymuse
 * @copyright Copyright © 2018 - Arboreta Internet Services - All rights reserved.
 * @license   GNU/GPL
 * @author    Gordon Fisch
 * @author    info@joomlamymuse.com
 * @website   http://www.joomlamymuse.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;

$product    =& $this->item;
$tracks     =& $this->item->tracks;
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$catlink    = $this->params->get('my_product_link', 'catid');
$user       = JFactory::getApplication()->getIdentity();
//echo '$listDirn ='.$listDirn. ' $listOrder = '.$listOrder;

$cols = 1;
if($this->params->get('product_show_artist', 0)) :
  $cols++;
endif;
if($this->params->get('product_show_filetime', 0)) :
  $cols++;
endif;
if($this->params->get('product_show_filesize', 0)) :
  $cols++;
endif;
if($this->params->get('product_show_sales', 0)) : 
  $cols++;
endif;
if($this->params->get('product_show_downloads', 0)) : 
  $cols++;
endif;
if($this->params->get('product_show_cost_column', 1)) :
  $cols++;
endif;
if(is_countable($this->formats) && count($this->formats) > 1) :
  $cols++;
endif;
if($this->params->get('product_show_select_column', 1) && $this->available) :
  $cols++;
endif;
if($this->params->get('product_show_preview_column', 1)) : 
  $cols++;
endif;

if(is_countable($tracks) && count($tracks) && $this->params->get('product_show_tracks', 1)) :
?>

<div class="tracks">
<!--  TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS  -->
    <h3><?php echo Text::_('COM_MYMUSE_DOWNLOADABLE_ITEMS'); ?></h3>


<?php if ($this->params->get('show_player','1')) : ?>
    <!-- PLAYER -->
    <?php 
    if($this->params->get('product_player_type') == "single") : ?>
      <div id="product_player" 
        <?php if($this->params->get('product_player_height')) : ?>
        style="height: <?php echo $this->params->get('product_player_height'); ?>px"
        <?php endif; ?>
        >
        <?php echo $product->flash; ?>
      </div>
      <?php if(isset($product->flash) && $product->flash != '') : ?>
      <div><?php echo Text::_('COM_MYMUSE_NOW_PLAYING');?> <span
        id="jp-title-li"></span>
      </div>
      <?php endif; ?>
    <?php endif; ?>
    
    <?php if($this->params->get('product_player_type') == "playlist" && isset($product->flash)) : ?>
      <div id="product_player"><?php echo $product->flash; ?>
      </div>
    <?php endif; ?>
    
    <div style="clear: both"></div>

    <div class=""
      <?php if($this->params->get('product_player_type') == "each") : ?>
      id="product_player" 
      <?php endif; ?>>

      
      <!-- END PLAYER -->
  

<style>

.jp-gui {
  width: inherit;
}
</style>

<!-- TRACKS -->
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="trackForm" id="trackForm" class="">
<div class="track-count"><?php echo count($tracks); 
        if(count($tracks) == 1){ $word = "Track"; }else{ $word = "Tracks";} ?> 
            <?php echo $word; ?> Total</div>
        <?php endif; ?>
<div class="mymuse-cart ">

    <ul class="mymuse-container list-products">
      <li class="my-grid item-container cols-<?php echo $cols; ?> header">
        <div class="mymuse-header name"><?php echo HtmlHelper::_('grid.sort', 'COM_MYMUSE_NAME', 'title', $listDirn, $listOrder, null, 'asc', '', 'trackForm'); ?></div>

        <?php  if($this->params->get('product_show_artist', 0)) :?>
          <div class="mymuse-header artist">
          <?php echo Text::_('COM_MYMUSE_GENRE'); ?></div>
        <?php endif; ?>
        
        <?php  if($this->params->get('product_show_filetime', 0)) :?>
          <div class="mymuse-header time">
          <?php echo Text::_('COM_MYMUSE_TIME'); ?></div>
        <?php endif; ?>
        
        <?php  if($this->params->get('product_show_filesize', 0)) :?>
          <div class="mymuse-header filesize">
          <?php echo Text::_('COM_MYMUSE_FILE_SIZE'); ?></div>
        <?php endif; ?>
        
        <?php if($this->params->get('product_show_sales', 0)) : ?>
          <div class="mymuse-header sales">
          <?php echo Text::_('COM_MYMUSE_SALES'); ?></div>
        <?php endif; ?>
        
        <?php if($this->params->get('product_show_downloads', 0)) : ?>
          <div class="mymuse-header downloads">
          <?php echo Text::_('COM_MYMUSE_NUMBER_DOWNLOADS'); ?></div>
        <?php endif; ?>
        
        <?php  if($this->params->get('product_show_cost_column', 1)) :?>
          <div class="mymuse-header price">
          <?php echo Text::_('COM_MYMUSE_CART_PRICE'); ?></div>
        <?php endif; ?>
          
          <?php if(is_countable($this->formats) && count($this->formats) > 1) :?>
        <div class="mymuse-header format"><?php echo Text::_('COM_MYMUSE_FORMAT'); ?></div>
        <?php endif;?>
          
          <?php  if($this->params->get('product_show_select_column', 1) && $this->available) :?>
        <div class="mymuse-header select"><?php echo Text::_('COM_MYMUSE_SELECT'); ?></div>
        <?php endif; ?>

        <?php if($this->params->get('product_show_preview_column', 1) ) : ?>
          <div class="mymuse-header previews"><?php echo Text::_('COM_MYMUSE_PREVIEWS'); ?></div>
        <?php endif; ?>
    </li>

<?php
  $groups = $user->getAuthorisedViewLevels();

  foreach($tracks as $track) : 

  
    if($track->product_allfiles == 1 && (isset($this->all_tracks->shown) && $this->all_tracks->shown) ) :
      continue;
    endif;

    if(in_array($track->access, $groups)) :
?>

      <li class="my-grid item-container cols-<?php echo $cols; ?> ">
      <div class="mycart-inner title" data-name="<?php echo Text::_('COM_MYMUSE_TITLE'); ?>"><?php if($track->detail_image && $track->detail_image != '') :
                echo '<span class="track-img"><img src="'.$track->detail_image.'"></span>';
              endif; 
              ?>
                 <span class="track-title"><?php echo $track->title; ?></span>
                  <?php  
                  if($track->product_allfiles == "1") : 
                echo "(".Text::_("COM_MYMUSE_ALL_TRACKS").")";
              endif; ?>
              <?php if($track->introtext && $track->introtext != $track->title) :
                echo '<br /><span class="track-text">'.$track->introtext.'</span>';
              endif; 
              ?></div>

    <?php  if($this->params->get('product_show_artist', 0)) :?>
      <div class="mycart-inner artist" data-name="<?php echo Text::_('COM_MYMUSE_GENRE'); ?>"><a
            href="<?php 
            echo Route::_(RouteHelper::getCategoryRoute($track->catid, true));?>">
            <?php if(!empty($track->category_name )) { echo $track->category_name; } ?></a>
            <?php foreach((array)$track->othercats as $id=>$name): ?>
                <br /> <a
            href="<?php
                echo Route::_(RouteHelper::getCategoryRoute($id, true));?>">
                <?php echo $name ?></a>
            <?php endforeach; ?></div>
    <?php endif; ?>
    
    <?php  if($this->params->get('product_show_filetime', 0)) :?>
      <div class="mycart-inner time" data-name="<?php echo Text::_('COM_MYMUSE_TIME'); ?>">
      <?php echo $track->file_time ?></div>
    <?php endif; ?>
    
    <?php  if($this->params->get('product_show_filesize', 0)) :?>
      <div class="mycart-inner filesize" data-name="<?php echo Text::_('COM_MYMUSE_FILE_SIZE'); ?>">
      <?php 
                if(!$track->product_allfiles && isset($track->file_length) && $track->file_length > 0) :
                  $first = 1;
                  foreach($track->digital as $file){
                    $this_format = isset($file->file_format)? strtolower($file->file_format): $file->file_ext;
                    $this_length = isset($file->file_length)? $file->file_length: '';
                    echo '<div id="'.$this_format.'_length_'.$track->id.'" class="length"';
                          if(!$first):
                            echo ' style="display:none" ';
                          endif;
                          $first = 0;
                      echo '>'.MyMuseHelper::ByteSize($this_length).'</div>';


 
                  }
                endif; ?></div>
    <?php endif; ?>
    
    <?php if($this->params->get('product_show_sales', 0)) : ?>
      <div class="mycart-inner sales" data-name="<?php echo Text::_('COM_MYMUSE_SALES'); ?>">
      <?php echo $track->sales; ?></div>
    <?php endif; ?>
    
    <?php if($this->params->get('product_show_downloads', 0)) : ?>
      <div class="mycart-inner downloads" data-name="<?php echo Text::_('COM_MYMUSE_DOWNLOADS'); ?>">
      <?php if(!$track->product_allfiles && $track->file_length > 0) :
                  $first = 1;
                  foreach($track->digital as $file){
                    $this_downloads = isset($file->file_downloads)? $file->file_downloads: 0;
                    $format = strtolower($file->file_format);
                    echo '<div id="'.$format.'_downloads_'.$track->id.'" class="downloads"';
                          if(!$first):
                            echo ' style="display:none" ';
                          endif;
                          $first = 0;
                      echo '>'.$this_downloads.'</div>';


 
                  }
                endif; ?></div>
    <?php endif; ?>
    
    <?php  if($this->params->get('product_show_cost_column', 1)) :?>
      <div class="mycart-inner price" data-name="<?php echo Text::_('COM_MYMUSE_CART_PRICE'); ?>">
    <?php 

                if("1" == $this->params->get('my_price_by_product')) :
                  $first = 1;
                  $types = array();
                  foreach($track->digital as $file) :

                    if(isset($file->file_format)) :
                      $types[] = strtolower($file->file_format);
                    endif;
                    
                  endforeach;
                  foreach($this->formats as $format) :
                    if(in_array(strtolower($format), $types)):
                      $product_price = $track->price[$format];
                          echo '<div id="'.$format.'_'.$track->id.'" class="price"';
                          if(!$first):
                            echo ' style="display:none" ';
                          endif;
                          $first = 0;
                      echo '>'.MyMuseHelper::printMoneyPublic($product_price).'</div>';
                    endif;
                  endforeach;
              
                elseif($this->params->get('my_free_downloads') && isset($track->free_download) && $track->free_download) :
                  if($user->get('guest')) :
                    $menu = JFactory::getApplication()->getMenu();
                    $active = $menu->getActive();
                    $itemId = $active->id;
                    $link = new JUri(Route::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
                    $link->setVar('return', base64_encode(Route::_(RouteHelper::getProductRoute($this->item->id, $this->item->$catlink, $this->item->language))));
                  else :
                    if(is_array($track->free_download_link)) :
                      $first = 1;
                      foreach($this->formats as $format) :
                        $link = $track->free_download_link[$format];
                        echo '<div id="'.$format.'_'.$track->id.'" class="price"';
                        if(!$first):
                          echo ' style="display:none" ';
                        endif;
                        $first = 0;
                        $img_link = Route::_("components/com_mymuse/assets/images/download_dark.png");
                        echo '><a class="free_download_link"
                    href="'.$link.'"><img
                    src="'.$img_link.'"
                    border="0" /></a></div>';
                      endforeach;
                    else :
                  $link = $track->free_download_link;
                  $img_link = Route::_("components/com_mymuse/assets/images/download_dark.png");
                  ?>
                      <a class="free_download_link"
                  href="<?php echo $link; ?>"><img
                  src="<?php echo $img_link; ?>"
                  border="0" /></a><?php
                      
                  endif;
                endif;
       

              else :
        
                echo MymuseHelper::printMoneyPublic($track->price);
                
              endif; ?></div>
      <?php endif; ?>
      
      <?php if(is_countable($this->formats) && count($this->formats) > 1) :?>
              <div class="mycart-inner format" data-name="<?php echo Text::_('COM_MYMUSE_FORMAT'); ?>"><?php if(isset($track->variation_select)) :
                          echo $track->variation_select;
                         endif;
              ?></div>
      <?php endif;?>
      
      <?php  if($this->params->get('product_show_select_column', 1) && $this->available) :?>
        <div class="mycart-inner select" data-name="<?php echo Text::_('COM_MYMUSE_SELECT'); ?>"><?php if($track->digital || $track->product_allfiles) :?>

                    <a href="javascript:void(0)" class="trackpicker" data-id="<?php echo $track->id; ?>"
                      data-variation="<?php echo $track->digital[0]->file_id; ?>"
                    id="box_<?php echo $track->id; ?>"><img id="img_<?php echo $track->id; ?>"
                    src="<?php
                      if(isset($this->products) && in_array($track->id, $this->products)) :
                          echo Route::_("components/com_mymuse/assets/images/minus-button-30.png");
                      else :
                          echo Route::_("components/com_mymuse/assets/images/plus-button-30.png");
                      endif;
                      ?>"></a>
                <?php  endif; ?>
                        
                <?php if($track->digital || $track->product_allfiles) :?>
                <span class="mycheckbox"><input style="display: none;"
              type="checkbox" name="productid[]"
              value="<?php echo $track->id; ?>"
              id="box<?php echo $this->check; $this->check++; ?>" /> </span>

                <?php  endif; ?></div>
    <?php endif; ?>

    <?php if($this->params->get('product_show_preview_column', 1) ) : ?>
      <div class="mycart-inner previews" data-name="<?php echo Text::_('COM_MYMUSE_PREVIEWS'); ?>"><span class="jp-gui ui-widget"><?php echo isset($track->flash)? $track->flash : ''; ?></span></div>
    <?php endif; ?>
    </li>
    <?php endif; ?>
  <?php endforeach; ?>


</div>



  <?php // Add pagination links ?>
<?php if (count($tracks) && isset($this->pagination)) : ?>
  <?php if (($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
  <div class="pagination">

    <?php if ($this->params->def('show_pagination_results', 1)) : ?>
      <p class="counter">
        <?php echo $this->pagination->getPagesCounter(); ?>
      </p>
    <?php endif; ?>

    <?php echo $this->pagination->getPagesLinks(); ?>
 
  <?php endif; ?>

<?php  endif; ?>
</div>
<?php  endif; ?>

    <div>
        <input type="hidden" name="filter_order" value="">
        <input type="hidden" name="filter_order_Dir" value="">
        <input type="hidden" name="limitstart" value="">
        <input type="hidden" name="task" value="">
    </div>
</form>