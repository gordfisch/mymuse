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
global $store, $shopper, $cart;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

$product  =& $this->item;
$items    =& $this->item->items;
$tracks   =& $this->item->tracks;
$params   =& $this->params;
$user     =& $this->user;
$listOrder  = $this->sortColumn;
$listDirn = $this->sortDirection;
$user     = JFactory::getUser();


?>

<!--  TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS  -->
    <h3><?php echo JText::_('COM_MYMUSE_DOWNLOADABLE_ITEMS'); ?></h3>

<?php if (!$this->hide_player){ ?>
    <!-- PLAYER -->
    <?php if($this->params->get('product_player_type') == "single") : ?>
      <div id="product_player" 
        <?php if($this->params->get('product_player_height')) : ?>
        style="height: <?php echo $this->params->get('product_player_height'); ?>px"
        <?php endif; ?>
        >
        <?php echo $product->flash; ?>
      </div>
      <?php if($product->flash) : ?>
      <div><?php echo JText::_('COM_MYMUSE_NOW_PLAYING');?> <span
        id="jp-title-li"></span>
      </div>
      <?php endif; ?>
    <?php endif; ?>
    
    <?php if($this->params->get('product_player_type') == "playlist" && isset($product->flash)){ ?>
      <div id="product_player"><?php echo $product->flash; ?>
      </div>
    <?php } ?>
    
    <div style="clear: both"></div>

    <div class=""
      <?php if($this->params->get('product_player_type') == "each"){ ?>
      id="product_player" <?php } ?>>

      <div class="track-count"><?php echo count($tracks); 
        if(count($tracks) == 1){ $word = "Track"; }else{ $word = "Tracks";} ?> 
            <?php echo $word; ?> Total</div>
      <!-- END PLAYER -->


<?php } 

?><!-- TRACKS -->
  <table class="mymuse_cart cart">
    <thead>
      <tr class="mymuse_cart cart">
        <th class="mytitle cart" align="center" width="40%">
          <?php echo JHtml::_('grid.sort', 'COM_MYMUSE_NAME', 'title', $listDirn, $listOrder); ?></th>
          
          <?php  if($this->params->get('product_show_artist', 0)) :?>
            <th class="myartist cart" align="center"
              width="30%">
            <?php echo JHtml::_('grid.sort', 'COM_MYMUSE_GENRE', 'category_name', $listDirn, $listOrder); ?></th>
            
          <?php endif; ?>
          
          <?php  if($this->params->get('product_show_filetime', 0)) :?>
            <th class="mytime cart" align="center" width="10%">
            <?php echo JHtml::_('grid.sort', 'COM_MYMUSE_TIME', 'file_time', $listDirn, $listOrder); ?></th>
          <?php endif; ?>
          
          <?php  if($this->params->get('product_show_filesize', 0)) :?>
            <th class="myfilesize cart" align="center"
              width="10%">
            <?php echo JHtml::_('grid.sort', 'COM_MYMUSE_FILE_SIZE', 'file_length', $listDirn, $listOrder); ?></th>
          <?php endif; ?>
          
          <?php if($this->params->get('product_show_sales', 0)) : ?>
            <th class="mysales cart" align="left" width="10%">
            <?php echo JHtml::_('grid.sort', 'COM_MYMUSE_SALES', 'sales', $listDirn, $listOrder); ?></th>
          <?php endif; ?>
          
          <?php if($this->params->get('product_show_downloads', 0)) : ?>
            <th class="mydownloads cart" align="left"
              width="10%">
            <?php echo JHtml::_('grid.sort', 'COM_MYMUSE_NUMBER_DOWNLOADS', 'file_downloads', $listDirn, $listOrder); ?></th>
          <?php endif; ?>
          
          <?php  if($this->params->get('product_show_cost_column', 1)) :?>
            <th class="myprice cart" align="center" width="10%">
            <?php echo JHtml::_('grid.sort', 'COM_MYMUSE_CART_PRICE', 'price', $listDirn, $listOrder); ?></th>
          <?php endif; ?>
            
            <?php if(count($this->params->get('my_formats')) > 1) :?>
          <th class="myselect cart" align="left" width="20%"><?php echo JText::_('COM_MYMUSE_FORMAT'); ?></th>
          <?php endif;?>
            
            <?php  if($this->params->get('product_show_select_column', 1)) :?>
          <th class="myselect cart" align="left" width="20%"><?php echo JText::_('COM_MYMUSE_SELECT'); ?></th>
          <?php endif; ?>

          <?php if($this->params->get('product_show_preview_column', 1) && $this->params->get('product_player_type') != "playlist") : ?>
            <th class="mypreviews cart" align="left"
              width="10%"><?php echo JText::_('COM_MYMUSE_PREVIEWS'); ?></th>
          <?php endif; ?>
            
          </tr>
        </thead>

          
      <?php 
      $groups = $user->getAuthorisedViewLevels();
        foreach($tracks as $track) : 
  
            if($track->product_allfiles == 1) :
                   // continue;
              endif;

          if(in_array($track->access, $groups)) :
              ?>
            <tr>
          <!--  TITLE COLUMN -->
          <td class="mytitle cart"> 
              <?php if($track->detail_image && $track->detail_image != '') :
                echo '<span class="track-img"><img src="'.$track->detail_image.'"></span>';
              endif; 
              ?>
                 <span class="track-title"><?php echo $track->title; ?></span>
                  <?php  
                  if($track->product_allfiles == "1") : 
                echo "(".JText::_("MYMUSE_ALL_TRACKS").")";
              endif; ?>
              <?php if($track->introtext && $track->introtext != $track->title) :
                echo '<br /><span class="track-text">'.$track->introtext.'</span>';
              endif; 
              ?>

                            
                           
                </td>
              <?php  if($this->params->get('product_show_artist', 0)) :?>
              <!-- GENRE COLUMN -->
          <td class="myartist cart"><a
            href="<?php 
            echo JRoute::_(MyMuseHelperRoute::getCategoryRoute($track->catid, true));?>">
            <?php if(!empty($track->category_name )) { echo $track->category_name; } ?></a>
            <?php foreach((array)$track->othercats as $id=>$name): ?>
                <br /> <a
            href="<?php
                echo JRoute::_(MyMuseHelperRoute::getCategoryRoute($id, true));?>">
                <?php echo $name ?></a>
            <?php endforeach; ?>
              </td>
              <?php endif; ?>   
              <!--  TIME COLUMN -->
              <?php  if($this->params->get('product_show_filetime', 0)) : ?>  
                <td class="mytime cart">
                <?php echo $track->file_time ?>
                </td>
              <?php endif; ?>
              
              <!--  FILE SIZE COLUMN -->
              <?php  if($this->params->get('product_show_filesize', 0)) : ?>  
                <td class="myfilesize cart">
                <?php 
                if(!$track->product_allfiles && $track->file_length > 0) :
                  $first = 1;
                  foreach($track->file_name as $file){
                    $this_format = isset($file->file_format)? $file->file_format: $file->file_ext;
                    $this_length = isset($file->file_length)? $file->file_length: '';
                    echo '<div id="'.$this_format.'_length_'.$track->id.'" class="length"';
                          if(!$first):
                            echo ' style="display:none" ';
                          endif;
                          $first = 0;
                      echo '>'.MyMuseHelper::ByteSize($this_length).'</div>';


 
                  }
                endif; ?>
                </td>
              <?php endif; ?>
              
              <!--  SALES COLUMN -->
              <?php  if($this->params->get('product_show_sales', 0)) : ?> 
                <td class="mysales cart">
                <?php echo $track->sales; ?>
                </td>
              <?php endif; ?>
              
              <!--  DOWNLOADS COLUMN -->
              <?php  if($this->params->get('product_show_downloads', 0)) : ?> 
                <td class="mydownloads cart">
                <?php echo $track->file_downloads; ?>
                </td>
              <?php endif; ?>
              
          <!--  PRICE COLUMN -->
              <?php  if($this->params->get('product_show_cost_column', 1)) :?>  
                <td class="myprice cart">
                <?php 

                if("1" == $this->params->get('my_price_by_product')) :
                  $first = 1;
                  $types = array();
                  foreach($track->file_name as $file){
                    if(isset($file->file_format)){
                      $types[] = $file->file_format;
                    }
                    
                  }

                  foreach($this->params->get('my_formats') as $format) :
                    if(in_array($format, $types)):
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
                    $link = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
                    $link->setVar('return', base64_encode(JRoute::_(myMuseHelperRoute::getProductRoute($this->item->id, $this->item->catid, $this->item->language))));
                  else :
                    if(is_array($track->free_download_link)) :
                      $first = 1;
                      foreach($this->params->get('my_formats') as $format) :
                        $link = $track->free_download_link[$format];
                        echo '<div id="'.$format.'_'.$track->id.'" class="price"';
                        if(!$first):
                          echo ' style="display:none" ';
                        endif;
                        $first = 0;
                        $img_link = JRoute::_("components/com_mymuse/assets/images/download_dark.png");
                        echo '><a class="free_download_link"
                    href="'.$link.'"><img
                    src="'.$img_link.'"
                    border="0" /></a></div>';
                      endforeach;
                    else :
                  $link = $track->free_download_link;
                  $img_link = JRoute::_("components/com_mymuse/assets/images/download_dark.png");
                  ?>
                      <a class="free_download_link"
                  href="<?php echo $link; ?>"><img
                  src="<?php echo $img_link; ?>"
                  border="0" /></a><?php
                      
                endif;
              endif;
       

      else :
        
        echo MyMuseHelper::printMoneyPublic($track->price);
                
                endif; ?>
                </td>
              <?php endif; ?> 
                    
                    <!--  FORMAT COLUMN -->
              <?php if(count($this->params->get('my_formats')) > 1) :?>
                <td class="myformat cart">
                <?php if(isset($track->variation_select)) :
                    echo $track->variation_select;
                   endif;
                ?>
                </td>
              
                    <?php endif; ?>
                    <!--  SELECT COLUMN -->
            <?php  if($this->params->get('product_show_select_column', 1)) :?>
                <td class="myselect cart" nowrap>
                        <?php if($track->file_name || $track->product_allfiles) :?>

                        <a href="javascript:void(0)"
            id="box_<?php echo $track->id; ?>"><img
              id="img_<?php echo $track->id; ?>"
              src="<?php
                            if(isset($this->products) && in_array($track->id, $this->products)) :
                                echo JRoute::_("components/com_mymuse/assets/images/cart.png");
                            else :
                                echo JRoute::_("components/com_mymuse/assets/images/checkbox.png");
                            endif;
                        ?>"></a>
                <?php  endif; ?>
                        
                <?php if($track->file_name || $track->product_allfiles) :?>
                <span class="mycheckbox"><input style="display: none;"
              type="checkbox" name="productid[]"
              value="<?php echo $track->id; ?>"
              id="box<?php echo $this->check; $this->check++; ?>" /> </span>

                <?php  endif; ?>
                </td>
              <?php  endif; ?>  
              
              
              <?php  if($this->params->get('product_show_preview_column', 1)) :?>
                <!--  PREVIEW COLUMN -->
          <td class="mypreviews tracks  cart"><span class="jp-gui ui-widget"><?php echo isset($track->flash)? $track->flash : ''; ?></span></td>
              <?php  endif; ?>  

              </tr>
          <?php  
          endif; //access
        endforeach; ?>
    </table>
  </div>
  <div style="clear: both"></div>
  <!-- END TRACKS -->
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
  </div>
  <?php endif; ?>

<?php  endif; ?>

