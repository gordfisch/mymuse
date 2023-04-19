<?php 
/**
 * @version     $Id: alphatunes.php 1930 2017-11-24 14:04:05Z gfisch $
 * @package     com_mymuse3.0
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch info@joomlamymuse.com
 */


use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;

// no direct access
defined('_JEXEC') or die('Restricted access');

$cells = 0;
$category_height = $this->params->get('category_image_height',0);
$product_height = $this->params->get('category_product_image_height',0);
$params = $this->params;
$Itemid		= $this->Itemid;
$check = 0;
$return_link = RouteHelper::getCategoryRoute($this->category->id); 
$count = 0;
$listOrder	= $this->sortColumn;
$listDirn	= $this->sortDirection;
$item = $this->category;
$document = JFactory::getDocument();
$url = "index.php?option=com_mymuse&task=ajaxtogglecart";
$products = array();
for ($i=0;$i<$this->cart["idx"];$i++) {
    $products[] = $this->cart[$i]['product_id'];
}
$catlink = $this->params->get('my_product_link', 'catid');
$js = '';
foreach($this->items as $track){

    			$js .= '
jQuery(document).ready(function($){
		$("#box_'.$track->id.'").click(function(e){
            
            //alert("'.$url.'");
            $.post("'.$url.'",
            {
                productid:"'.$track->id.'"
            },
            function(data,status)
            {
                var res = jQuery.parseJSON(data);
                idx = res.idx;
                msg = res.msg;
                action = res.action;
                //alert(res.msg + "\nStatus: " + status);
                if(action == "deleted"){
                    $("#img_'.$track->id.'").attr("src","'.JURI::root().'/components/com_mymuse/assets/images/plus-button-30.png");
                }else{
                    $("#img_'.$track->id.'").attr("src","'.JURI::root().'/components/com_mymuse/assets/images/minus-button-30.png");
                }
                if(idx){
                    if(idx == 1){
                        txt = idx+" "+"item";
                    }else{
                        txt = idx+" "+"items";
                    }
                    link = \''.'<a href="'.Route::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$Itemid).'">'.Text::_('COM_MYMUSE_VIEW_CART').'</a>\';
                    $("#mini-cart-text").html(txt);
                    $("#mini-cart-link").html(link);
                }else{
                    
                    $("#mini-cart-text").html(" ");
                    $("#mini-cart-link").html("'.Text::_('COM_MYMUSE_YOUR_CART_IS_EMPTY').'");
                }
                my_modal.open({content: msg+"<br />"+link, width: 300 });
            });
            
		}); 
	});

';
			
    $count++;
}

//flip price between formats

if(count($params->get('my_formats')) > 1  && $params->get('my_price_by_product')) :
	$js .= 'function flip_price(id) {'."\n";
	$js .= ' var formats = new Array();'."\n";
	foreach($params->get('my_formats') as $index=>$format) :
		$js .= 'formats['.$index.'] = "'.$format.'"'."\n";
	endforeach;
	foreach($params->get('my_formats') as $format) :
		$js .= 'var  '.$format.'_id = "#'.$format.'_"+id'."\n";
	endforeach;
	$js .= 'var select_id = "#variation_"+id+"_id"'."\n";

	for($i=0; $i < count($params->get('my_formats')); $i++) :
		$js .= 'jQuery('.$params->get('my_formats')[$i].'_id).hide();'."\n";
	endfor;
	$js .= '
			//alert(formats[jQuery(select_id).val()]+"_"+id);
			jQuery("#"+formats[jQuery(select_id).val()]+"_"+id).show();'."\n}";
endif;

$document->addScriptDeclaration($js);


$cols = 1;
if($this->params->get('list_show_artist', 0)) :
  $cols++;
endif;
if($this->params->get('list_show_album', 0)) :
  $cols++;
endif;
if($this->params->get('product_show_filetime', 0)) :
  $cols++;
endif;
if($this->params->get('list_show_price', 0)) : 
  $cols++;
endif;
if(count($params->get('my_formats')) > 1) : 
  $cols++;
endif;
if($this->params->get('list_show_discount', 1)) :
  $cols++;
endif;
if($this->params->get('list_show_date', 1)) :
  $cols++;
endif;
if($this->params->get('list_show_sales', 1)) : 
  $cols++;
endif;
if($this->params->get('product_show_downloads', 1)) : 
  $cols++;
endif;
if($this->params->get('product_show_preview_column', 1)) : 
  $cols++;
endif;
if($this->params->get('product_show_cartadd', 1)) : 
  $cols++;
endif;

?>
<script type="text/javascript">
function updateTop(idx)
{
    
    
}
function tableOrdering( order, dir, task )
{
	var form = document.adminForm;
	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit( task );
}


</script>

<div class="track-list<?php echo $this->pageclass_sfx;?>">

	<?php if ($this->params->get('show_page_heading')) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>
	
	<?php if ($this->params->get('show_category_title')) : ?>
	<h2>
			<span class="category-title"><?php echo $this->category->title;?></span>
	</h2>
	<?php endif; ?>
	
	<?php if ($this->params->get('page_subheading')) : ?>
	<h3>
		<span class="category-subheading"><?php echo $this->escape($this->params->get('page_subheading')); ?></span>
	</h3>
	<?php endif; ?>
	
	<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="category-desc">
		<?php if ($this->params->get('show_description_image') && $this->category->params->get('image'))  : ?>
			<img src="<?php echo $this->category->params->get('image'); ?>"
			<?php if ($this->params->get('category_image_height')) : ?>
				style="height: <?php echo $this->params->get('category_image_height'); ?>px; "
			<?php endif; ?>
		/>
		<?php endif; ?>
		<?php if ($this->params->get('show_description') && $this->category->description) : ?>
			<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
		<?php endif; ?>
		<div class="clr"></div>
	</div>
	<?php endif; ?>
<div class="clear"></div>


<?php if($this->params->get('show_minicart')) :?>
<!--  INLINE MINICART  -->
<!--  the cart box  -->
<div id="mini-cart-top">
	<div id="mini-cart-content">
		<div id="mini-cart-cart"></div>
		<div id="mini-cart-text"><?php
		if($this->cart['idx']) :
		    $word = ($this->cart['idx'] == 1) ? "item" : "items"; 
		    echo $this->cart['idx']." $word";
		endif;
		?></div>
		<div id="mini-cart-link"><?php
		if($this->cart['idx']) :
		    echo '<a href="'.Route::_('index.php?option=com_mymuse&view=cart&task=showcart&Itemid='.$Itemid).'">'.Text::_('COM_MYMUSE_VIEW_CART').'</a>';
		else :
		    echo Text::_('COM_MYMUSE_YOUR_CART_IS_EMPTY');
		endif;
		?></div>
	</div>
</div>

<div class="clear"></div>
<!--  END INLINE MINICART  -->
<?php  endif; ?>


<?php if($this->params->get('show_alphabet')) : ?>
<!--  the alphabet  -->
<div id="alphabet">
	<?php foreach($this->alpha as $letter) :
		echo $letter;
	endforeach;
	?>
</div>
<div class="clear"></div>
<?php  endif; ?>



<!--  the filters  -->
<?php $uri = Uri::getInstance();
$uri->setQuery('');
$url = $uri->toString();
?>
<form action="<?php echo $url; ?>" method="post" name="adminForm" id="adminForm" class="com-content-category__articles">
    <?php if ($this->params->get('filter_field') !== 'hide') : ?>
        <div class="com-content__filter btn-group">
            <?php if ($this->params->get('filter_field') === 'tag') : ?>
                <span class="visually-hidden">
                    <label class="filter-search-lbl" for="filter-search">
                        <?php echo Text::_('JOPTION_SELECT_TAG'); ?>
                    </label>
                </span>
                <select name="filter_tag" id="filter-search" class="form-select" onchange="document.adminForm.submit();" >
                    <option value=""><?php echo Text::_('JOPTION_SELECT_TAG'); ?></option>
                    <?php echo HTMLHelper::_('select.options', HTMLHelper::_('tag.options', array('filter.published' => array(1), 'filter.language' => $langFilter), true), 'value', 'text', $this->state->get('filter.tag')); ?>
                </select>
            <?php elseif ($this->params->get('filter_field') === 'month') : ?>
                <span class="visually-hidden">
                    <label class="filter-search-lbl" for="filter-search">
                        <?php echo Text::_('JOPTION_SELECT_MONTH'); ?>
                    </label>
                </span>
                <select name="filter-search" id="filter-search" class="form-select" onchange="document.adminForm.submit();">
                    <option value=""><?php echo Text::_('JOPTION_SELECT_MONTH'); ?></option>
                    <?php echo HTMLHelper::_('select.options', HTMLHelper::_('content.months', $this->state), 'value', 'text', $this->state->get('list.filter')); ?>
                </select>
            <?php else : ?>
                <label class="filter-search-lbl visually-hidden" for="filter-search">
                    <?php echo Text::_('COM_MYMUSE_' . $this->params->get('filter_field') . '_FILTER_LABEL'); ?>
                </label>
                <input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" placeholder="<?php echo Text::_('COM_MYMUSE_' . $this->params->get('filter_field') . '_FILTER_LABEL'); ?>">
            <?php endif; ?>

            <?php if ($this->params->get('filter_field') !== 'tag' && $this->params->get('filter_field') !== 'month') : ?>
                <button type="submit" name="filter_submit" class="btn btn-primary"><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?></button>
            <?php endif; ?>
            <button type="submit" name="filter-clear-button" class="btn btn-secondary"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>
    <?php endif; ?>

    <?php if ($this->params->get('show_pagination_limit') && isset($this->pagination)) : ?>
        <div class="com-content-category__pagination btn-group float-end">
            <label for="limit" class="visually-hidden">
                <?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>
            </label>
            <?php echo $this->pagination->getLimitBox(); ?>
        </div>
    <?php endif; ?>

<!--  the tracks  -->
<?php if(count($this->items)) :
	$tracks = $this->items;
	?>

<section class="tracks">
    <ul class="list-products">
      <li class="my-grid item-container cols-<?php echo $cols; ?>">
		
			<div class="mymuse-header name">
       			<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_TRACK', 'a.title', $listDirn, $listOrder); ?>
       		</div>


		<?php if($params->get('list_show_artist')) : ?>
        	<div class="mymuse-header artist">
        		<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_ARTIST', 'artist_name', $listDirn, $listOrder); ?>
        	</div>
       	<?php endif; ?>
       			
       	<?php if($params->get('list_show_album')) : ?>
        	<div class="mymuse-header album">
        		<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_ALBUM', 'product_title', $listDirn, $listOrder); ?>
        	</div>
       	<?php endif; ?>
       			
       			
       			
       	<?php  if($params->get('product_show_filetime', 0)) : ?>
       		<div class="mymuse-header time">
       			<?php echo Text::_('COM_MYMUSE_TIME'); ?>
       		</div>
       	<?php endif; ?>
       	
     	<?php if($params->get('list_show_price')) : ?>
        	<div class="mymuse-header price">
        		<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_CART_PRICE', 'a.price', $listDirn, $listOrder); ?>
        	</div>
        <?php endif; ?>

        <?php if(count($params->get('my_formats')) > 1) :?>
		    <div class="mymuse-header format">
		    	<?php echo Text::_('COM_MYMUSE_FORMAT'); ?>
		    </div>
        <?php endif;?>

        <?php if($params->get('list_show_discount', 0)) { ?>
        	<div class="mymuse-header discount">
        		<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_DISCOUNT', 'a.product_discount', $listDirn, $listOrder); ?>
        	</div>
        <?php } ?>
                
        <?php if ($this->params->get('list_show_date') && $this->params->get('order_date')) : 
                		$date = $this->params->get('order_date');
        	?>
			<div class="mymuse-header date">
					<?php if ($date == "created") : ?>
					<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_'.$date.'_DATE', 'p.created', $listDirn, $listOrder); ?>
				<?php elseif ($date == "modified") : ?>
					<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_'.$date.'_DATE', 'p.modified', $listDirn, $listOrder); ?>
				<?php elseif ($date == "published") : ?>
					<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_'.$date.'_DATE', 'p.publish_up', $listDirn, $listOrder); ?>
				<?php elseif ($date == "product_made_date") : ?>
					<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_'.$date.'_DATE', 'p.product_made_date', $listDirn, $listOrder); ?>
				<?php endif; ?>
			</div>
				<?php endif; ?>
				
				<?php if($params->get('list_show_sales',0)){ ?>
				<div class="mymuse-header sales">
        			<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_SALES', 's.sales', $listDirn, $listOrder); ?>
        		</div>	
                <?php } ?>
                
                <?php if($params->get('product_show_downloads')){ ?>
				<div class="mymuse-header dowmloads">
        			<?php echo JHtml::_('grid.sort', 'COM_MYMUSE_DOWNLOADS', 'a.file_downloads', $listDirn, $listOrder); ?>
        		</div>	
                <?php } ?>
                
                
                <?php if($params->get('product_show_preview_column')  && $params->get('product_player_type') != "playlist") { ?>
                	<div class="mymuse-header preview"><?php echo Text::_('COM_MYMUSE_PLAY'); ?></div>
                <?php } ?>
                
                <?php if($params->get('product_show_cartadd')) { ?>
                	<div class="mymuse-header select"><?php echo Text::_('COM_MYMUSE_ADD'); ?></div>
    			<?php } ?>
    
      		</li>
 
      		<?php foreach($tracks as $track){ ?>
      				
			  		<li class="my-grid item-container cols-<?php echo $cols; ?>">
			  		
			  		<!--  TITLE COLUMN -->			
						<div class="mycart-inner title" data-name="<?php echo Text::_('COM_MYMUSE_TITLE'); ?>">
							<?php echo $track->title; ?> <br />
							<?php if($params->get('product_show_filesize') && $track->file_length) : ?>
								<?php echo "(".MyMuseHelper::ByteSize($track->file_length).")"; ?>
      						<?php endif; ?>
      						
      						<?php  if($track->product_allfiles == "1") : 
								echo Text::_("COM_MYMUSE_ALL_TRACKS");
					 			endif; ?>
      					</div>

      				<?php if($params->get('list_show_artist')) { ?>
			  		<!--  ARTIST COLUMN -->
                        <div class="mycart-inner artist" data-name="<?php echo Text::_('COM_MYMUSE_ARTIST'); ?>">
                            <?php 
                				if($params->get('category_product_link_titles')){
                            		$link = myMuseHelperRoute::getCategoryRoute($track->artistid);
                            		echo '<a href="'.$link.'">';
                            	}
                            	echo $track->artist_title;
                            	if($params->get('category_product_link_titles')){
                            		echo '</a>';
                            	}
                            ?>
                        </div>
                     <?php } ?>
                     
                     <?php if($params->get('list_show_album')) { ?>
			  		<!--  ALBUM COLUMN -->
                        <div class="mycart-inner artist" data-name="<?php echo Text::_('COM_MYMUSE_ALBUM'); ?>">
                            <?php
                            	if($params->get('category_product_link_titles')){
                            		$link = myMuseHelperRoute::getProductRoute($track->parentid,$track->$catlink);
                            		echo '<a href="'.$link.'">';
                            	}		
                            	echo $track->product_title;
                            	if($params->get('category_product_link_titles')){
                            		echo '</a>';
                            	} 
                            ?>
                        </div>
                     <?php } ?>
                     
      				

        			<?php  if($params->get('product_show_filetime', 0)) { ?>
        			<!--  TIME COLUMN -->	
        				<div class="mycart-inner time" data-name="<?php echo Text::_('COM_MYMUSE_TIME'); ?>">
        				<?php echo $track->file_time ?>
        				</div>
        			<?php } ?>
        			
        			<?php if($params->get('list_show_price')) : ?>
        			<!--  PRICE COLUMN -->	
        				<div class="mycart-inner price" data-name="<?php echo Text::_('COM_MYMUSE_PRICE'); ?>">
        				<?php 
        				if("1" == $params->get('my_price_by_product')) :
        					$first = 1;
        					
							foreach($this->params->get('my_formats') as $format) :
								$product_price = $track->price[$format];
        						echo '<div id="'.$format.'_'.$track->id.'" class="price"';
        						if(!$first):
        							echo ' style="display:none" ';
      							endif;
      							$first = 0;
 								echo '>'.MyMuseHelper::printMoneyPublic($product_price).'</div>';
 							endforeach;
 							
        				elseif($params->get('my_free_downloads') && isset($track->free_download) && $track->free_download) :
        					if($user->get('guest')) :
        						$menu = JFactory::getApplication()->getMenu();
        						$active = $menu->getActive();
        						$itemId = $active->id;
        						$link = new JUri(Route::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
        						$link->setVar('return', base64_encode(Route::_(myMuseHelperRoute::getProductRoute($this->item->id, $this->item->$catlink, $this->item->language))));
        					else :
        						$link = $track->free_download_link;
        					endif;
        					?>
        				    <a class="free_download_link" href="<?php echo $link; ?>"
        				    ><img src="components/com_mymuse/assets/images/download_dark.png" border="0" /></a>
							<?php 
        				else :
        					
        					echo MyMuseHelper::printMoneyPublic($track->price);
        				
        				endif; ?>
        				</div>
        			<?php endif; ?>	
        			
        			
        			<?php if(count($params->get('my_formats')) > 1) :?>
        			<!--  FORMAT COLUMN -->
        				<div class="mycart-inner format">
        				<?php if(isset($track->variation_select)) :
      							echo $track->variation_select;
      						 endif;
      					?>
        				</div>
        			
                    <?php endif; ?>
                    
        			
        			<?php if($params->get('list_show_discount', 0)) { ?>
        			<!--  DISCOUNT COLUMN -->	
        				<div class="mycart-inner discount" data-name="<?php echo Text::_('COM_MYMUSE_DISCOUNT'); ?>">
        				<?php echo MyMuseHelper::printMoneyPublic($track->product_discount); ?>
        				</div>
        			<?php } ?>
        			
        			<?php if ($this->params->get('list_show_date') && $track->displayDate) : ?>
        			<!--  DATE COLUMN -->	
					<div class="mycart-inner date" data-name="<?php echo Text::_('COM_MYMUSE_DATE'); ?>">
						<?php if($track->displayDate != "0000-00-00"){
							echo JHtml::_('date', $track->displayDate, $this->escape(
							$this->params->get('date_format', Text::_('DATE_FORMAT_LC3')))); 
      					} ?>
					</div>
					<?php endif; ?>
					
					<?php if($params->get('list_show_sales', 0)) { ?>
					<!--  SALES COLUMN -->	
        				<div class="mycart-inner sales" data-name="<?php echo Text::_('COM_MYMUSE_SALES'); ?>">
        					<?php echo $track->sales; ?>	
        				</div>
        			<?php } ?>
        			
        			<?php if($params->get('product_show_downloads')){ ?>
        			<!-- DOWNLOADS COLUMN -->	
        				<div class="mycart-inner downloads" data-name="<?php echo Text::_('COM_MYMUSE_DOWNLOADS'); ?>">
        					<?php echo $track->file_downloads; ?>
        				</div>
        			<?php } ?>
        				
        			<?php if($params->get('product_show_preview_column') && $params->get('product_player_type') != "playlist"){ ?>
        				<!--  PREVIEW COLUMN -->	
        				<div class="mycart-inner previews" data-name="<?php echo Text::_('COM_MYMUSE_PREVIEWS'); ?>">
        					<?php echo $track->flash; ?>
        				</div>
        			<?php }?>
        			
                    <?php if($params->get('product_show_cartadd')) { ?>
                    <!--  SELECT COLUMN -->	
                        <div class="mycart-inner select" data-name="<?php echo Text::_('COM_MYMUSE_SELECT'); ?>">
        				<?php if($track->digital || $track->product_allfiles) :?>
                        <a href="javascript:void(0)" id="box_<?php echo $track->id; ?>"><img id="img_<?php echo $track->id; ?>" src="<?php
                            if(in_array($track->id, $products)){
                                echo "components/com_mymuse/assets/images/minus-button-30.png";
                            }else{
                                echo "components/com_mymuse/assets/images/plus-button-30.png";
                            }
                        ?>"></a>
      					<?php  endif; ?>
      					</div>
      				<?php } ?>
      				</li>
      		<?php  } ?>
		</ul>
</section>
<?php else: ?>
	<div class="no_products"><?php echo Text::_('COM_MYMUSE_NO_PRODUCTS'); ?></div>
<?php endif; ?>


<?php // Add pagination links ?>
<?php if (!empty($this->items)) : ?>
	<?php if (($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
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
<input type="hidden" name="option" value="com_mymuse" />
<input type="hidden" name="view" value="tracks" />
<input type="hidden" name="layout" value="tracks" />
<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
<input type="hidden" name="id" value="<?php echo $this->category->id; ?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
<input type="hidden" name="filter_alpha" value="" />

</form>
</div>

<div id='my_overlay' style="display:none"></div>
<div id='my_modal' style="display:none">
    <div id='my_content'>No JavaScript!</div>
    <a href='#' id='my_close'>close</a>
</div>

