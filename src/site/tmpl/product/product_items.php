<?php 
/**
 * @version		$Id: product_items.php 1986 2018-07-22 19:11:13Z gfisch $
 * @package		mymuse
 * @copyright	Copyright © 2018 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author 		info@joomlamymuse.com
 * @website		http://www.joomlamymuse.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Component\Mymuse\Administrator\Extension\MymuseComponent;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;
use Joomla\Component\Mymuse\Site\Service\Mymuse;


$product 	=& $this->item;
$items		=& $this->item->items;
$items_select 	= $this->params->get('product_item_selectbox',1);

if( is_countable($items) && count($items)) : 
?>

<!-- ITEMS   ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS ITEMS -->
<?php if(count($items) && !$items_select){  ?>
	<style type="text/css">
	@media (max-width: 767px) { 

	<?php foreach($product->attribute_sku as $a_sku){ ?>
		td.my<?php echo $a_sku->name ?>:before { content: "<?php echo Text::_($a_sku->name); ?>";}
		td.my<?php echo $a_sku->name ?>{
			text-align: left;
		}
		td.my<?php echo $a_sku->name ?>:before {
			white-space: nowrap;
			padding-right: 7%;
			margin-right: 7%;
			width: 23%;
			display: inline-block;
			border-right: 1px solid #ccc;
		}
		td.my<?php echo $a_sku->name ?> {
			clear: both;
		}
	<?php } ?>

		}
	</style>
	<div class="product-items">
		<h3><?php echo Text::_('COM_MYMUSE_ITEMS'); ?></h3>
		<table class="mymuse_cart">
			<thead>
		    <tr>
		    
        		<th class="mytitle" align="left" width="55%" ><?php echo Text::_('COM_MYMUSE_NAME'); ?>
				</th>
       			<?php foreach($product->attribute_sku as $a_sku){ ?>
						<th class="my<?php echo $a_sku->name ?>" align="left"><?php echo $a_sku->name; ?></th>
				<?php } ?>
       			
				<th class="myprice" align="center" width="20%"><?php echo Text::_('COM_MYMUSE_COST'); ?></th>
        	<?php if ($this->params->get('product_show_quantity')) :?>
        		<th class="myquantity" align="left" width="20%"><?php echo Text::_('COM_MYMUSE_QUANTITY'); ?></th>
        		
      	    <?php endif; ?>
      	    	<th class="myselect" align="left" width="5%" ><?php echo Text::_('COM_MYMUSE_SELECT'); ?></th>
      		</tr>
      		</thead>
			<?php 

			foreach($items as $item){  
				?>
			  		<tr>
        				
        				<td class="mytitle"><?php echo $item->title; ?></td>
        			<?php foreach($product->attribute_sku as $a_sku){ ?>
						<td class="my<?php echo $a_sku->name ?>"><?php echo $item->attributes[$a_sku->name]; ?></td>
					<?php } ?>
						<td class="myprice">
						<?php echo MyMuseHelper::printMoneyPublic($item->price); 
				?></td>
        			<?php if ($this->params->get('product_show_quantity')) :?>
						<td class="myquantity"><input class="inputbox" type="text" name="quantity[<?php echo $item->id; ?>]" size="2" value="1" /></td>
					<?php endif; ?>
					<td class="mymuse_cart_top myselect" nowrap><a href="javascript:void(0)"
					id="box_<?php echo $item->id; ?>"><img
						id="img_<?php echo $item->id; ?>"
						src="<?php
                    if(in_array($item->id, $this->products)) :
                       echo "components/com_mymuse/assets/images/cart.png";
                    else :
                        echo "components/com_mymuse/assets/images/checkbox.png";
                     endif;
                 ?>"></a> <span class="mycheckbox"><input
						style="display: none;" type="checkbox" name="productid[]"
						value="<?php echo $item->id; ?>"
						id="box<?php echo $this->check; $this->check++; ?>" /> </span></td>
      				</tr>
      		<?php  } ?>
		</table>
		<br />
		<br />
<?php } ?>

<?php 
//select option
if(count($items) && $items_select){ 


$product 		=& $this->item;
$items			=& $this->item->items;
$items_select 	= $this->params->get('product_item_selectbox',0);
$params 		=& $this->params;
$user 			=& $this->user;
$Itemid			= $this->Itemid;


$myarrays 			= count($product->attribute_sku); // -1;
$js 				= "<script>\n var prices = new Array; \n var items = new Array;\n var attributes = new Array;\n var item_images=new Array; \n";
$tmp 				= '';
$prices				= '';
$v 					= 'items';
$current_product_id = 0;
$current_price 		= 0;
$default 			= 0;
$defaults 			= '';
$attrs 				= array();
$done 				= array();
$item_images 		= '';



foreach($product->attribute_sku as $k => $attr){
	$js .= 'attributes['.$k.']="'.$attr->name.'";'."\n";
	
	$attrs[$attr->name] = array();
	$v .= "[".$attr->name."]";
	if($k >= $myarrays){ continue; }
	foreach($items as $key => $item){
		if(!in_array($item->attributes[$attr->name], $done)){
			$tmp .= "items['".$item->attributes[$attr->name]."'] = new Array\n";
		}
		$done[] = $item->attributes[$attr->name];
		if(isset($item->product_default) && $item->product_default){
			$default = $key;
		}
		$prices .= "prices[".$item->id."]=".$item->price['product_price']."\n";
	}
	
}

$js .= $tmp;

$current_product_id = $items[$default]->id;
$current_price = $items[$default]->price;
$done = array();

foreach($items as $item){
	$js .= "items";
	foreach($product->attribute_sku as $attr){
		$js .= "['".$item->attributes[$attr->name]."']";
		if(!in_array($item->attributes[$attr->name], $done)){
			$attrs[$attr->name][] = $item->attributes[$attr->name];
			$done[] = $item->attributes[$attr->name];
		}
		
	}
	$js .= " = ".$item->id."; \n";
	$item_images  .= "item_images[".$item->id."]='".JURI::Root(). $item->detail_image."'\n";
}
foreach($product->attribute_sku as $attr){
	$name = 'current_'.$attr->name;
	$$name = $items[$default]->attributes[$attr->name];
	$defaults .= "$name='".$items[$default]->attributes[$attr->name]."';\n";
}
$js .= $prices . "\n" . $item_images;
$js .= "\n\nvar current_product_id=".$current_product_id.";\n";
$js .= $defaults;
$js .= "\n\nfunction changeItem(key, value){
	
	var arrayLength = attributes.length;
	for (var i = 0; i < arrayLength; i++) {
		if(attributes[i] == key){
		";
		foreach($attrs as $name => $val){
			$js .= "if(key == '".$name."'){
				old_value = current_".$name."
				current_".$name." = value;
			}
			";
		}
		$js .= "
		}
	}

	current_product_id = items";
	foreach($attrs as $name => $val){
		$js .= "[current_".$name."]";
	}
	
	$js .= "

	
	document.getElementById('product_price_".$product->id."').innerHTML = '$'+(prices[current_product_id]).formatMoney(2);
	active = 'attr_'+value;
	document.getElementById(active).classList.add('active');
	old = 'attr_'+old_value;
	document.getElementById(old).classList.remove('active');
	
	//alert('item_images[current_product_id] = '+item_images[current_product_id]);
	if( null != item_images[current_product_id]){
		updateProductImage(".$product->id.", item_images[current_product_id]);
	}
}

function updateProductImage(id, image)
{
	var el = '#img_'+id;
	jQuery(el).attr('src',image);
}
";


$js .= "</script>\n";
echo $js;
?>
<div class="mymuse">
<h3><?php echo Text::_('COM_MYMUSE_ITEMS'); ?></h3> 
    <div class="product-items">
    <ul class="product-content">
        <li class="product-content-item-actions">
            <div class="value">
                <div class="product-full">
                    <div class="product-full-title"><a href="javascript:void(0)" id="box_<?php echo $product->id; ?>"><img
							id="cart_image"
							src="<?php
                            if(in_array($product->id, $this->products)) :
                                echo "components/com_mymuse/assets/images/cart.png";
                            else :
                                echo "components/com_mymuse/assets/images/checkbox.png";
                            endif;
                        ?>"></a></div>
                    <div id="product_price_<?php echo $product->id; ?>" class="price"><?php echo MyMuseHelper::printMoneyPublic($current_price); ?></div>
                </div>
            </div>
        </li>
		 
		<?php foreach($attrs as $key => $at){ 
				$css_array = array();
				foreach($product->attribute_sku as $k => $attr){
					if($attr->name == $key){
						if($attr->extra_css != ''){
							$css_array = preg_split('/\r\n|\r|\n/', $attr->extra_css);
						}
					}
				}

			?>
		<li class="product-content-item-actions">
			<div class="product-full">
				<div class="product-attribute-name">

						<div class="product-full"><?php echo $key ?></div>

				</div>
				<div class="product-attribute-values">
					<div class="product-attribute-values-inner">
					<?php 

					$i = 0;
					foreach($at as $a){ 
							?><a href="javascript:void(0)" 
							<?php 
							if(count($css_array)){
								echo 'style="color:'.$css_array[$i].';"';
								
							} 
							?>
							onclick="changeItem('<?php echo $key; ?>','<?php echo $a; ?>')"><div class="attribute <?php echo $key; ?> 
							<?php 
							$name = "current_".$key;
							if( $a == $$name ){
								echo " active";
							} ?>" id="attr_<?php echo $a; ?>"
							<?php 
							if(count($css_array)){
								echo 'style="background-color:'.$css_array[$i].';"';
							} 
							$i++;
							?>

							><?php echo $a; ?></div></a><?php
					 } ?>
					</div>
				</div>
			</div>
		</li>
	<?php } ?>
		           

    </ul>
    </div>
    
    <div style="clear: both"></div>
    
    <?php if($product->product_images != ''){
    	$document = JFactory::getDocument();
        $document->addScript( 'components/com_mymuse/assets/javascript/owl.carousel.js' );
		$document->addStyleSheet('components/com_mymuse/assets/css/owl.carousel.css');
		$document->addStyleSheet('components/com_mymuse/assets/css/owl.theme.default.css');

    	echo '<script>
    	jQuery(document).ready(function($){
    	    $(".owl-carousel").owlCarousel({  
    			loop:true,
		        items:8,
		        margin:0,        
		        responsive:{
		        	0:{
		                items:2,
		                nav:true
		            },
		            480:{
		                items:2,
		                nav:true
		            },
		            600:{
		                items:4,
		                nav:true                
		            },
		            1000:{
		                items:4,
		                nav:true                
		            },
		            2560:{
		                items:6,
		                nav:true                
		            },
		            4000:{
		                items:8,
		                nav:true                
		            }                                       
		        },
		        loop:true,
		    });
    });
    </script>
    ';
		echo '<div class="carousel">'.$this->makeCarousel($product->id, $product->product_images).'</div>';
	} ?>
    
<!--  END ITEMS -->
</div>
<?php } 
endif; ?>
