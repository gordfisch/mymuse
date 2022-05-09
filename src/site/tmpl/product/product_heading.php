<?php 
$Itemid		= $this->Itemid;
?>
<!--  HEADING / MINICART / TITLE / ICONS -->
<?php echo $this->item->event->beforeDisplayHeader; ?>

<?php if ($this->params->get('show_page_heading', 0)) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php endif; ?>

<?php 

if($this->params->get('show_minicart')) :?>
	<!--  INLINE MINICART  -->
	<!--  the cart box  -->
	<div id="mini-cart">
		<div class="mini-cart-top">
			<div class="mini-cart-content">
				<div class="mini-cart-cart"></div>
				<div class="mini-cart-text" id="mini-cart-text"><?php
	if($this->cart['idx']) :
	    $word = ($this->cart['idx'] == 1) ? "item" : "items"; 
	    echo $this->cart['idx']." $word";
	endif;
	?></div>
				<div class="mini-cart-link" id="mini-cart-link"><?php
	if($this->cart['idx']) :
	    echo '<a href="'.JRoute::_('index.php?option=com_mymuse&view=cart&task=showcart&Itemid='.$Itemid).'">'.JText::_('COM_MYMUSE_VIEW_CART').'</a>';
	else :
	    echo JText::_('COM_MYMUSE_YOUR_CART_IS_EMPTY');
	endif;
	?></div>
			</div>
		</div>
	</div>
	<div class="clear"></div>
	<!--  END INLINE MINICART  -->
<?php  endif; ?>

<?php echo $this->item->event->beforeDisplayProduct; ?>


<?php echo $this->item->event->beforeDisplayTitle; ?>

<?php if( $this->params->get("show_title",0) ) : ?>
	<h1><?php echo $this->item->title; ?></h1>
<?php endif; ?>

<?php echo $this->item->event->afterDisplayTitle; ?>



<!--  END HEADING -->
