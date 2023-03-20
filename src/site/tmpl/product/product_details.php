<?php 
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\CMS\Layout\FileLayout;

$product = $this->item;


if( ($this->params->get('info_block_show'))) : ?>
	<div class="product-details <?php echo $this->itemClass; ?>">

		<?php if( $this->params->get("info_block_show_title",0) ) : ?>
			<h3 class="product-details-title"><?php echo Text::_('COM_MYMUSE_PRODUCT_DETAILS'); ?></h3>
		<?php endif; ?>

		<?php if( $this->params->get("show_special_status",0) && $this->item->special_status) : ?>
			<h4 class="pre-order-text"><?php echo Text::_($this->item->special_status); ?></h4>
		<?php endif; ?>



		<ul class="product-detail-list">

			<?php if( $this->params->get("show_artist",0) && $this->item->artistid ) : ?>
        		<li class="product-detail-item mymuse-grid-1-2">
        			<div class="key artist">
        				<?php echo Text::_('COM_MYMUSE_ARTIST'); ?>
        			</div>
	        		<div class="value">
	        			<?php if( $this->params->get("link_artist",0) ) :
		        		 	$title = '<a href="'.Route::_(RouteHelper::getCategoryRoute($this->item->artistid)).'">'.$this->item->artist_title.'</a>';
		                else:
		                    $title = $this->item->artist_title;
		                endif;
		            ?>
		        	<?php echo $title; ?>
	        		</div>
        		</li>

		    <?php endif; ?>

		    <?php if( $this->params->get("show_category",0) && $this->item->catid ) : ?>
        		<li class="product-detail-item mymuse-grid-1-2">
        			<div class="key category">
        				<?php echo Text::_('COM_MYMUSE_CATEGORY'); ?>
        			</div>
	        		<div class="value">
	        			<?php if( $this->params->get("link_category",0) ) :
		        		 	$title = '<a href="'.Route::_(RouteHelper::getCategoryRoute($this->item->catid)).'">'.$this->item->category_title.'</a>';
		                else:
		                    $title = $this->item->category_title;
		                endif;
		            ?>
		        	<?php echo $title; ?>
	        		</div>

        		</li>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_product_sku",0) ) : ?>
		    	<li class="product-detail-item mymuse-grid-1-2">
            		<div class="key product-sku"><?php echo Text::_('COM_MYMUSE_CATALOG'); ?></div>
					<div class="value"><?php echo $this->item->product_sku;?></div>
        		</li>

        	<?php endif; ?>

		    <?php if($this->assocParam) : 
		    $associations = $this->item->associations; ?>
		    <li class="product-detail-item mymuse-grid-1-2">
		    	<div class="icon-globe icon-fw key associations" aria-hidden="true"><?php echo Text::_('JASSOCIATIONS'); ?></div>
		    	<div class="value">
		    	<?php foreach ($associations as $association) : ?>
		    		<?php if ($this->item->params->get('flags', 1) && $association['language']->image) : ?>
		    			<?php $flag = HTMLHelper::_('image', 'mod_languages/' . $association['language']->image . '.gif', $association['language']->title_native, array('title' => $association['language']->title_native), true); ?>
		    			<a href="<?php echo Route::_($association['item']); ?>"><?php echo $flag; ?></a>
		    		<?php else : ?>
		    			<?php $class = 'btn btn-secondary btn-sm btn-' . strtolower($association['language']->lang_code); ?>
		    			<a class="<?php echo $class; ?>" title="<?php echo $association['language']->title_native; ?>" href="<?php echo Route::_($association['item']); ?>"><?php echo $association['language']->lang_code; ?>
		    				<div class="visually-hidden"><?php echo $association['language']->title_native; ?></div>
		    			</a>
		    		<?php endif; ?>
		    	<?php endforeach; ?>
		    	</div>
			</li>
			<?php endif; ?>



		    <?php 

		    if( $this->params->get("show_date",0) ) : 
		    		$date_type = $this->params->get("show_which_date","release");
		    		$date_string = 'COM_MYMUSE_DATE_'.strtoupper($date_type); 
		    		if( $this->item->$date_type ) :
		    		?>
				    <li class="product-detail-item mymuse-grid-1-2">
				        <div class="key date"><?php echo Text::_($date_string); ?>:</div>
				        <div class="value"><?php echo $this->item->$date_type; ?></div>
				    </li>
		    	<?php endif; ?>
		    <?php endif; ?>


		    <!-- recording details -->

		    <?php if( $this->params->get("show_product_full_time",0) && $this->item->recording->get('product_full_time','0') ) : ?>
		    <li class="product-detail-item mymuse-grid-1-2">
		        <div class="key full-time"><?php echo Text::_('COM_MYMUSE_PRODUCT_FULL_TIME_LABEL'); ?>:</div>
		        <div class="value"><?php echo $this->item->recording->get('product_full_time','0'); ?></div>
		    </li>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_product_studio",0) && $this->item->recording->get('product_studio','') ) : ?>
		    <li class="product-detail-item mymuse-grid-1-2">
		        <div class="key hits"><?php echo Text::_('COM_MYMUSE_PRODUCT_STUDIO_LABEL'); ?>:</div>
		        <div class="value"><?php echo $this->item->recording->get('product_studio',''); ?></div>
		    </li>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_product_publisher",0) && $this->item->recording->get('product_publisher','') ) : ?>
		    <li class="product-detail-item mymuse-grid-1-2">
		        <div class="key hits"><?php echo Text::_('COM_MYMUSE_PRODUCT_PUBLISHER_LABEL'); ?>:</div>
		        <div class="value"><?php echo $this->item->recording->get('product_publisher',''); ?></div>
		    </li>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_product_producer",0) && $this->item->recording->get('product_producer','') ) : ?>
		    <li class="product-detail-item mymuse-grid-1-2">
		        <div class="key hits"><?php echo Text::_('COM_MYMUSE_PRODUCT_PRODUCER_LABEL'); ?>:</div>
		        <div class="value"><?php echo $this->item->recording->get('product_producer',''); ?></div>
		    </li>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_product_country",0) && $this->item->recording->get('product_country','') ) : ?>
		    <li class="product-detail-item mymuse-grid-1-2">
		        <div class="key hits"><?php echo Text::_('COM_MYMUSE_PRODUCT_COUNTRY_LABEL'); ?>:</div>
		        <div class="value"><?php echo $this->item->recording->get('product_country',''); ?></div>
		    </li>
		    <?php endif; ?>

		   <?php if( $this->params->get("show_author",0) ) : ?>
			<li class="product-detail-item mymuse-grid-1-2">
		        <div class="key author"><?php echo Text::_('JAUTHOR'); ?>:</div>
		        <div class="value">
		        	<?php $author = $this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author; ?>
		        	<?php if (!empty($this->item->contactid) && $this->params->get('link_author') == true): ?>
		        	<?php
		        		$needle = 'index.php?option=com_contact&view=contact&id=' . $this->item->contactid;
		        		$menu = JFactory::getApplication()->getMenu();
		        		$item = $menu->getItems('link', $needle, true);
		        		$cntlink = !empty($item) ? $needle . '&Itemid=' . $item->id : $needle;
		        	?>
		        		<?php echo JHtml::_('link', Route::_($cntlink), $author); ?>
		        	<?php else: ?>
		        		<?php echo $author; ?>
		        	<?php endif; ?>
		        	</div>
		    </li>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_hits",0) ) : ?>
		    <li class="product-detail-item mymuse-grid-1-2">
		        <div class="key hits"><?php echo Text::_('COM_MYMUSE_HITS_FILTER_LABEL'); ?>:</div>
		        <div class="value"><?php echo $this->item->hits; ?></div>
		    </li>
		    <?php endif; ?>

		    <?php if ($this->params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
		    	<?php $this->item->tagLayout = new FileLayout('joomla.content.tags'); ?>

		    	<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_news_release_link",0) && $this->item->attribs->get('media_rls','') ) : ?>
		    <li class="product-detail-item mymuse-grid-1-2">
		        <div class="key news-release"><a href="<?php echo $this->item->attribs->get('media_rls',''); ?>"><?php echo Text::_('COM_MYMUSE_NEWS_RELEASE'); ?></a></div>
		        
		    </li>
		    <?php endif; ?>


		    <?php if( $this->params->get("show_media_link",0) && $this->item->attribs->get('media_link','') ) : ?>
		    <li class="product-detail-item mymuse-grid-1-2">
		        <div class="key media-link"><a href="<?php echo $this->item->attribs->get('media_link',''); ?>"><?php echo Text::_('COM_MYMUSE_MEDIA'); ?></a></div>

		    </li>
		    <?php endif; ?>


		    <?php if(isset($this->all_tracks->flash) && isset($this->all_tracks->digital[0])) : ?>
		    <li class="product-detail-item product-purchase ">
		    
			    <div class="key full-release">
			    	<div class="product-preview-play"><?php echo $this->all_tracks->flash; ?></div>
			    	<?php echo Text::_('COM_MYMUSE_BUY_FULL_RELEASE'); ?>
			    </div>
			    <?php 
			    $full_cols = 2;
			    if(isset($this->all_tracks->variation_select)) : 
			    	$full_cols = 3;
			    endif; 
			    ?>
			    <div class="value my-grid columns-<?php echo $full_cols ?>">
			       <?php if($this->all_tracks) : ?>
	        
	                    <?php
	                    if("1" == $this->params->get('my_price_by_product')) :
	                      $first = 1;
	                      $types = array();
	                      foreach($this->all_tracks->digital as $file) :

	                        if(isset($file->file_format)) :
	                          $types[] = strtolower($file->file_format);
	                        endif;
	                        
	                      endforeach;
	                      foreach($this->formats as $format) :
	                        if(in_array(strtolower($format), $types)):
	                          $product_price = $this->all_tracks->price[$format];
	                              echo '<div id="'.$format.'_'.$this->all_tracks->id.'" class="price"';
	                              if(!$first):
	                                echo ' style="display:none" ';
	                              endif;
	                              $first = 0;
	                          echo '>'.MyMuseHelper::printMoneyPublic($product_price).'</div>';
	                        endif;
	                      endforeach;
	                    
	                    else :
	                    	echo '<span id="track_'.$this->all_tracks->id.'" class="price"';
	                    	echo '>'.MyMuseHelper::printMoneyPublic($this->all_tracks->price).'</span>';

	                    endif;
	                    $this->all_tracks->shown = 1;
	                    ?>
	                    
	                    <?php if(isset($this->all_tracks->variation_select)) : ?>
	                    	<span class="format"> <?php echo $this->all_tracks->variation_select; ?></span> 
	                    <?php endif ?>
	                    

	                    <div><a class="trackpicker" href="javascript:void(0)" class="box_<?php echo $this->all_tracks->id; ?>" 
	                        	id="box_<?php echo $this->all_tracks->id; ?>"
	                        	data-id="<?php echo $this->all_tracks->id; ?>"
	                        	data-variation="<?php echo $this->all_tracks->digital[0]->file_id; ?>"

	                        	> <img id="img_<?php echo $this->all_tracks->id; ?>"
		                    	src="<?php
			                    if(isset($this->products) && in_array($this->all_tracks->id, $this->products)) :
			                        echo Route::_("components/com_mymuse/assets/images/minus-button-30.png");
			                    else :
			                        echo Route::_("components/com_mymuse/assets/images/plus-button-30.png");
			                    endif;
			                    ?>"></a></div>
	                    
	                <?php endif; ?>
	            </div>
	        </li>
	        <?php endif; ?>
	    </ul>

<!-- "show_vote"  -->

		<?php  if ($product->introtext) : ?>
		<div class="product-description">     

		    <?php echo $product->introtext ?>

		    <?php if($product->introtext && $product->fulltext && !$this->params->get('show_readmore') && !$this->params->get('split_text')) :  
		    	echo $product->fulltext;
		    endif; ?>

			<?php if($product->introtext && $product->fulltext && $this->params->get('show_readmore') && $this->params->get('split_text')) : ?>
				<div><a href="#readmore" class="readon"><?php echo Text::_("COM_MYMUSE_READ_MORE"); ?>
		        <?php 
		        if ($this->params->get('show_readmore_title', 0) != 0) :
		            echo JHtml::_('string.truncate', ($product->title), $this->params->get('readmore_limit'));
		        endif;
		        ?></a></div>
			 <?php endif; ?>
		</div>
		<?php endif; ?>


		</div>
<?php endif; ?>