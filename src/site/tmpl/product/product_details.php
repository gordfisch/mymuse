<?php 
use Joomla\CMS\Router\Route;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;


if( ($this->params->get('info_block_show'))) : ?>
	<div class="product-details">

		<?php echo $this->loadTemplate('title'); ?>

		<?php if( $this->params->get("info_block_show_title",0) ) : ?>
			<h2 class="product-details-title"><?php echo JText::_('COM_MYMUSE_PRODUCT_DETAILS'); ?></h2>
		<?php endif; ?>

		<?php if( $this->params->get("show_special_status",0) && $this->item->special_status) : ?>
			<h3 class="pre-order-text"><?php echo JText::_($this->item->special_status); ?></h3>

		<?php endif; ?>

		

		<ul class="product-detail-list">

			<?php if( $this->params->get("show_artist",0) ) : ?>
			<li class="product-detail-item">
		        <span class="key artist"><?php echo JText::_('COM_MYMUSE_ARTIST'); ?>:</span>
		        <span class="value">
		        	<?php if( $this->params->get("link_artist",0) ) :
		        		 	$title = '<a href="'.Route::_(RouteHelper::getCategoryRoute($this->item->artistid)).'">'.$this->item->artist_title.'</a>';
		                else:
		                    $title = $this->item->artist_title;
		                endif;
		            ?>
		        	<?php echo $title; ?>
		        	</span>
		    </li>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_category",0) ) : ?>
			<li class="product-detail-item">
		        <span class="key category"><?php echo JText::_('COM_MYMUSE_CATEGORY'); ?>:</span>
		        <span class="value">
		        	<?php if( $this->params->get("link_category",0) ) :
		        		 	$title = '<a href="'.Route::_(RouteHelper::getCategoryRoute($this->item->catid)).'">'.$this->item->category_title.'</a>';
		                else:
		                    $title = $this->item->category_title;
		                endif;
		            ?>
		        	<?php echo $title; ?>
		        	</span>
		    </li>
		    <?php endif; ?>

		    <?php if($this->assocParam) : 
		    $associations = $this->item->associations; ?>
		    <li class="product-detail-item">
		    	<span class="icon-globe icon-fw key associations" aria-hidden="true"><?php echo Text::_('JASSOCIATIONS'); ?></span>
		    	<span class="value">
		    	<?php foreach ($associations as $association) : ?>
		    		<?php if ($this->item->params->get('flags', 1) && $association['language']->image) : ?>
		    			<?php $flag = HTMLHelper::_('image', 'mod_languages/' . $association['language']->image . '.gif', $association['language']->title_native, array('title' => $association['language']->title_native), true); ?>
		    			<a href="<?php echo Route::_($association['item']); ?>"><?php echo $flag; ?></a>
		    		<?php else : ?>
		    			<?php $class = 'btn btn-secondary btn-sm btn-' . strtolower($association['language']->lang_code); ?>
		    			<a class="<?php echo $class; ?>" title="<?php echo $association['language']->title_native; ?>" href="<?php echo Route::_($association['item']); ?>"><?php echo $association['language']->lang_code; ?>
		    				<span class="visually-hidden"><?php echo $association['language']->title_native; ?></span>
		    			</a>
		    		<?php endif; ?>
		    	<?php endforeach; ?>
		    	</span>
			</li>
			<?php endif; ?>



		    <?php if( $this->params->get("show_date",0) ) : 
		    		$date_type = $this->params->get("show_which_date","release");
		    		$date_string = 'COM_MYMUSE_DATE_'.strtoupper($date_type); 
		    		?>
		    <li class="product-detail-item">
		        <span class="key date"><?php echo JText::_($date_string); ?>:</span>
		        <span class="value"><?php echo $this->item->$date_type; ?></span>
		    </li>
		    <?php endif; ?>


		    <!-- recording details -->

		    <?php if( $this->params->get("show_product_full_time",0) ) : ?>
		    <li class="product-detail-item">
		        <span class="key full-time"><?php echo JText::_('COM_MYMUSE_PRODUCT_FULL_TIME_LABEL'); ?>:</span>
		        <span class="value"><?php echo $this->item->recording->get('product_full_time','0'); ?></span>
		    </li>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_product_studio",0) ) : ?>
		    <li class="product-detail-item">
		        <span class="key hits"><?php echo JText::_('COM_MYMUSE_PRODUCT_STUDIO_LABEL'); ?>:</span>
		        <span class="value"><?php echo $this->item->recording->get('product_studio',''); ?></span>
		    </li>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_product_publisher",0) ) : ?>
		    <li class="product-detail-item">
		        <span class="key hits"><?php echo JText::_('COM_MYMUSE_PRODUCT_PUBLISHER_LABEL'); ?>:</span>
		        <span class="value"><?php echo $this->item->recording->get('product_publisher',''); ?></span>
		    </li>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_product_producer",0) ) : ?>
		    <li class="product-detail-item">
		        <span class="key hits"><?php echo JText::_('COM_MYMUSE_PRODUCT_PRODUCER_LABEL'); ?>:</span>
		        <span class="value"><?php echo $this->item->recording->get('product_producer',''); ?></span>
		    </li>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_product_country",0) ) : ?>
		    <li class="product-detail-item">
		        <span class="key hits"><?php echo JText::_('COM_MYMUSE_PRODUCT_COUNTRY_LABEL'); ?>:</span>
		        <span class="value"><?php echo $this->item->recording->get('product_country',''); ?></span>
		    </li>
		    <?php endif; ?>

		   <?php if( $this->params->get("show_author",0) ) : ?>
			<li class="product-detail-item">
		        <span class="key author"><?php echo JText::_('JAUTHOR'); ?>:</span>
		        <span class="value">
		        	<?php $author = $this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author; ?>
		        	<?php if (!empty($this->item->contactid) && $this->params->get('link_author') == true): ?>
		        	<?php
		        		$needle = 'index.php?option=com_contact&view=contact&id=' . $this->item->contactid;
		        		$menu = JFactory::getApplication()->getMenu();
		        		$item = $menu->getItems('link', $needle, true);
		        		$cntlink = !empty($item) ? $needle . '&Itemid=' . $item->id : $needle;
		        	?>
		        		<?php echo JHtml::_('link', JRoute::_($cntlink), $author); ?>
		        	<?php else: ?>
		        		<?php echo $author; ?>
		        	<?php endif; ?>
		        	</span>
		    </li>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_hits",0) ) : ?>
		    <li class="product-detail-item">
		        <span class="key hits"><?php echo JText::_('COM_MYMUSE_HITS_FILTER_LABEL'); ?>:</span>
		        <span class="value"><?php echo $this->item->hits; ?></span>
		    </li>
		    <?php endif; ?>

		    <?php if ($this->params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
		    	<?php $this->item->tagLayout = new FileLayout('joomla.content.tags'); ?>

		    	<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
		    <?php endif; ?>

		    <?php if( $this->params->get("show_news_release_link",0) ) : ?>
		    <li class="product-detail-item">
		        <span class="key hits"><?php echo JText::_('COM_MYMUSE_NEWS_RELEASE'); ?>:</span>
		        <span class="value"><?php echo $this->item->attribs->get('media_rls',''); ?></span>
		    </li>
		    <?php endif; ?>


		    <?php if( $this->params->get("show_media_link",0) ) : ?>
		    <li class="product-detail-item">
		        <span class="key hits"><?php echo JText::_('COM_MYMUSE_MEDIA'); ?>:</span>
		        <span class="value"><?php echo $this->item->attribs->get('media_link',''); ?></span>
		    </li>
		    <?php endif; ?>


		    <li class="product-detail-item product-content-item-actions">
		    <div class="product-preview-play"><?php echo isset($tracks[0]->flash)? $tracks[0]->flash : ''; ?></div>
			    <div class="value">
			       <?php if($this->all_tracks) : ?>
	                <div class="product-full">
	                    <div class="product-full-title">
	                     
	                        <a href="javascript:void(0)" class="box_<?php echo $this->all_tracks->id; ?>" id="box_<?php echo $this->all_tracks->id; ?>">&#10010;</a>
	                      
	                    </div>
	                    <div id="mp3_<?php echo $this->all_tracks->id; ?>" class="price"><?php echo MyMuseHelper::printMoneyPublic($this->product_price_mp3_all); ?></div>
	                    <div id="wav_<?php echo $this->all_tracks->id; ?>" class="price" style="display:none"><?php echo MyMuseHelper::printMoneyPublic($this->product_price_wav_all); ?></div>
	                    <div class="format"> <?php 
	                    if(isset($this->all_tracks->variation_select)) :
	                        echo $this->all_tracks->variation_select;
	                    endif;
	                    ?>
	                    </div>
	                </div>
	                <?php elseif($this->item->product_physical) : ?>
	                    <div class="product-full">
	                        <div class="product-full-title">

	                                 <a href="javascript:void(0)"
	                                 class="box_<?php echo $this->item->id; ?>"
	                                id="box_<?php echo $this->item->id; ?>"><?php
	                                    if(in_array($this->item->id, $this->products)) {
	                                        echo "&#8722";
	                          }else{
	                            echo "&#10010;";
	                          }
	                          ?></a> 
	                            </div>
	                        <div id="physical_<?php echo $this->item->id; ?>" class="price"><?php echo MyMuseHelper::printMoneyPublic($this->item->price); ?></div>
	                    </div> 
	                <?php endif; ?>
	            </div>
	        </li>



"show_vote"


"show_product_sku" 



		</div>
<?php endif; ?>

<?php  //MymuseHelper::print_pre($this->item);?>
<?php echo $this->item->event->beforeDisplayProduct; ?>
