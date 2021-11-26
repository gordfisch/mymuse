<?php 
/**
 * @version		44.01
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
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Mymuse\Administrator\Extension\MymuseComponent;
use Joomla\Component\Mymuse\Site\Helper\RouteHelper;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;



global $shopper;

$this->assocParam        = (Associations::isEnabled() && $this->params->get('show_associations'));
$store = $this->store;
$cart = $this->cart;
$product 	=& $this->item;
$items		=& $this->item->items;
if(!is_countable($items)){ 
	$items = array();
}
$tracks		=& $this->item->tracks;
if(is_countable($tracks)){ 
	$tracks = array();
}
$params 	=& $this->params;
$user 		=& $this->user;
$print 		= $this->print;
$Itemid		= $this->Itemid;
$height 	= $this->params->get('product_product_image_height',0);
$this->check = 1;
$count		= 0;
$this->return_link = 'index.php?option=com_mymuse&view=product&task=product&id='.$product->id.'&catid='.$product->catid.'&Itemid='.$Itemid;
$this->canEdit	= $this->item->params->get('access-edit',0);
$items_select 	= $this->params->get('product_item_selectbox',0);
$lang = Factory::getLanguage();
$langtag = $lang->getTag();
$listOrder	= $this->sortColumn;
$listDirn	= $this->sortDirection;


//get artist URL if exists
$this->item->artist_link = '';
$db = Factory::getDBO();
$app = Factory::getApplication();
$artist = ApplicationHelper::stringURLSafe($this->item->artist_title);
$this->item->artist_link = '';
if($artist){
	$query = "SELECT link FROM #__menu WHERE alias = '$artist'";
	$db->setQuery($query);
	if($res = $db->loadObject()){
		$this->item->artist_link = Route::_($res->link);
	}
}

$uri 			= JUri::getInstance(); 
$prod_uri 		= $uri->toString();
$description 	= ($product->introtext != '')? $product->introtext : $product->title;
$document 		= JFactory::getDocument();
$document->setMetaData( 'og:site_name',$this->escape($this->store->title));
$document->setMetaData( 'og:type', 'article');
$document->setMetaData( 'og:url', $prod_uri);
$document->setMetaData( 'og:title', $this->escape($product->title));
$document->setMetaData( 'og:description', strip_tags($description));
$document->setMetaData( 'og:image', JURI::Root().$product->detail_image);

$document->setMetaData( 'twitter:title', $this->escape($product->title));
$document->setMetaData( 'twitter:card', 'summary_large_image');
$document->setMetaData( 'twitter:site', $this->params->get('twitter_handle'));
$document->setMetaData( 'twitter:creator', $this->params->get('twitter_handle'));
$document->setMetaData( 'twitter:url', $prod_uri);
$document->setMetaData( 'twitter:description', strip_tags($description));
$document->setMetaData( 'twitter:image', JURI::Root().$product->detail_image);


if("1" == $this->params->get('my_price_by_product')){//price by product
	$product_price_physical = array('product_price' => $this->item->attribs->get('product_price_physical'));

	foreach($this->params->get('my_formats') as $format){
		$str = 'product_price_'.$format;
		$$str = array('product_price' => $this->item->attribs->get($str));
		$str = 'product_price_'.$format.'_all';
		$$str = array('product_price' => $this->item->attribs->get($str));
	}
}

$this->all_tracks = 0;
if(count((is_countable($tracks)?$tracks:[]))){
    foreach($tracks as $track){ 
        if($track->product_allfiles == 1){
            $this->all_tracks = $track;
        }
    }
}

$url = "index.php?option=com_mymuse&task=ajaxtogglecart";
$this->products = array();
for ($i=0;$i<$this->cart["idx"];$i++) {
	if(isset($this->cart[$i]['product_id'])){
		$this->products[] =  $this->cart[$i]['product_id'];
	}
}

// get the count of all products, items and tracks
if($product->product_physical){
	$count++;
}
if(count(is_countable($items)?$items:[]) && !$items_select){ 
	$count += count($items);
}

if(count(is_countable($tracks)?$tracks:[])){ 
	$count += count($tracks);
}

//add javascript 
$js = '

function hasProduct(that, count){
';

if($items_select && count(is_countable($items)?$items:[])){
$js .= 	'    item_count='.count($items).';
	var pidselect=document.getElementById("pidselect");
    var pid = pidselect.options[pidselect.selectedIndex].value;
	if(pid != ""){
		return true;
	}
    ';
}
$js .= '	for(i = 1; i < count+1; i++)
	{
		var thisCheckBox = "box" + i;
		if (document.getElementById(thisCheckBox).checked)
		{
			return true;
		}
	}
	alert("'.JText::_("MYMUSE_PLEASE_SELECT_A_PRODUCT").'");
	return false;
}

function tableOrdering( order, dir, task )
{
	var form = document.adminForm;
	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit( task );
}

Number.prototype.formatMoney = function(c, d, t){
    var n = this, 
    c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = d == undefined ? "." : d, 
    t = t == undefined ? "," : t, 
    s = n < 0 ? "-" : "", 
    i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))), 
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 }; 
';

//flip price between formats. flip file_size
if(count($params->get('my_formats', array())) > 1 ){	
	
	$js .= 'function flip_price(id) {'."\n";
	$js .= ' var formats = new Array();'."\n";
	foreach($params->get('my_formats') as $index=>$format) {
		$js .= 'formats['.$index.'] = "'.$format.'"'."\n";
	}
	foreach($params->get('my_formats') as $format) {
		$js .= 'var  '.$format.'_id = "#'.$format.'_"+id'."\n";
		if($params->get('product_show_filesize', 0)) {
			$js .= 'var  '.$format.'_length_id = "#'.$format.'_length_"+id'."\n";
		}
		
	}
	$js .= 'var select_id = "#variation_"+id+"_id"'."\n";
    
	for($i=0; $i < count($params->get('my_formats')); $i++){
    	$js .= 'jQuery('.$params->get('my_formats')[$i].'_id).hide();'."\n";
    	if($params->get('product_show_filesize', 0)) {
    		$js .= 'jQuery('.$params->get('my_formats')[$i].'_length_id).hide();'."\n";
    	}
    	
	}   		
	$js .= '
			//alert(formats[jQuery(select_id).val()]+"_"+id);
			jQuery("#"+formats[jQuery(select_id).val()]+"_"+id).show();';
			
	if($params->get('product_show_filesize', 0)) {
		$js .= '
			//alert(formats[jQuery(select_id).val()]+"_"+id);
			jQuery("#"+formats[jQuery(select_id).val()]+"_length_"+id).show();';
	}
	

	$js .= "\n}";
}

//set up the ajax cart add
$url = JURI::Root()."index.php?option=com_mymuse&task=ajaxtogglecart";

if($product->product_physical){
	//cart add phyical product ajax
	$js .= '
jQuery(document).ready(function($){
		$("#box_'.$product->id.'").click(function(e){
			if(typeof document.mymuseform.variation_'.$product->id.'_id !== "undefined"){
				myvariation = document.mymuseform.variation_'.$product->id.'_id.value;
				//alert("variation = "+myvariation);
	
			}else{
				myvariation = "";
			}
            $.post("'.$url.'",
            {
                "productid":"'.$product->id.'",
                "variation['.$product->id.']":myvariation
	
            },
            function(data,status)
            {
	
                var res = jQuery.parseJSON(data);
                idx = res.idx;
                msg = res.msg;
                action = res.action;
                //alert(res.msg);
                if(action == "deleted" || action == "failed"){
                    $("#cart_image_'.$product->id.'").attr("src","'.JURI::root().'components/com_mymuse/assets/images/checkbox.png");
                }else{
                    $("#cart_image_'.$product->id.'").attr("src","'.JURI::root().'components/com_mymuse/assets/images/cart.png");
                }
                if(idx){
                    if(idx == 1){
                        txt = idx+" "+"item";
                    }else{
                        txt = idx+" "+"items";
                    }
                    link = \''.'<a href="'.JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$Itemid).'">'.JText::_('COM_MYMUSE_VIEW_CART').'</a>\';
                    $("#mini-cart-text").html(txt);
                    $("#mini-cart-link").html(link);
                }else{
	
                    $("#mini-cart-text").html(" ");
                    $("#mini-cart-link").html(\''.json_encode(JText::_('COM_MYMUSE_YOUR_CART_IS_EMPTY')).'\');
                    link = "";
                }
                my_modal.open({content: msg+"<br />"+link, width: 300, delay:'. $params->get('my_delay_fadeout', 3000)  .' });
            });
	
		});
	});
	
';
}


$items_select 	= $this->params->get('product_item_selectbox',0);
if(count(is_countable($items)?$items:[]) && $items_select){

	$js .= '
	jQuery(document).ready(function($){
		$("#box_'.$product->id.'").click(function(e){

            $.post("'.$url.'",
            {
                "productid":current_product_id
                		
            },
            function(data,status)
            {
        		
                var res = jQuery.parseJSON(data);
                idx = res.idx;
                msg = res.msg;
                action = res.action;

                //alert(res.msg);
                if(action == "deleted" || action == "failed"){
                    $("#cart_image").attr("src","'.JURI::root().'components/com_mymuse/assets/images/checkbox.png");
                }else{
                    $("#cart_image").attr("src","'.JURI::root().'components/com_mymuse/assets/images/cart.png");
                }

                if(idx){
                    if(idx == 1){
                        txt = idx+" "+"item";
                    }else{
                        txt = idx+" "+"items";
                    }
                    link = \''.'<a href="'.JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$Itemid).'">'.JText::_('COM_MYMUSE_VIEW_CART').'</a>\';
                    $("#mini-cart-text").html(txt);
                    $("#mini-cart-link").html(link);
                }else{
	
                    $("#mini-cart-text").html(" ");
                    $("#mini-cart-link").html(\''.json_encode(JText::_('COM_MYMUSE_YOUR_CART_IS_EMPTY')).'\');
                    link = "";
                }
                my_modal.open({content: msg+"<br />"+link, width: 300,target:'.$product->id.', delay:'. $params->get('my_delay_fadeout', 3000)  .'});
            });

		});
	});

	';
	}
if(count(is_countable($items)?$items:[]) && !$items_select){
	foreach($items as $item){
			$js .= '
			jQuery(document).ready(function($){
				$("#box_'.$item->id.'").click(function(e){

		            $.post("'.$url.'",
		            {
		                "productid":"'.$item->id.'"
		                		
		            },
		            function(data,status)
		            {
		        		
		                var res = jQuery.parseJSON(data);
		                idx = res.idx;
		                msg = res.msg;
		                action = res.action;

		                //alert(res.msg);
		                if(action == "deleted" || action == "failed"){
		                    $("#img_'.$item->id.'").attr("src","'.JURI::root().'components/com_mymuse/assets/images/checkbox.png");
		                }else{
		                    $("#img_'.$item->id.'").attr("src","'.JURI::root().'components/com_mymuse/assets/images/cart.png");
		                }

		                if(idx){
		                    if(idx == 1){
		                        txt = idx+" "+"item";
		                    }else{
		                        txt = idx+" "+"items";
		                    }
		                    link = \''.'<a href="'.JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$Itemid).'">'.JText::_('COM_MYMUSE_VIEW_CART').'</a>\';
		                        $("#mini-cart-text").html(txt);
		                        $("#mini-cart-link").html(link);
		                    }else{
		                    
		                        $("#mini-cart-text").html(" ");
		                        $("#mini-cart-link").html(\''.json_encode(JText::_('COM_MYMUSE_YOUR_CART_IS_EMPTY')).'\');
		                        link = "";
		                }
		                my_modal.open({content: msg+"<br />"+link, width: 300,target:'.$product->id.', delay:'. $params->get('my_delay_fadeout', 3000)  .'});
		            });

				});
			});

			';
	}
}
if(is_countable($tracks)){
	foreach($tracks as $track){

	$js .= '
	jQuery(document).ready(function($){
		$("#box_'.$track->id.'").click(function(e){
			if(typeof document.mymuseform.variation_'.$track->id.'_id !== "undefined"){	
				myvariation = document.mymuseform.variation_'.$track->id.'_id.value;
				//alert("variation = "+myvariation);

			}else{
				myvariation = 0;
			}
            $.post("'.$url.'",
            {
                "productid":"'.$track->id.'",
                "variation['.$track->id.']":myvariation
                		
            },
            function(data,status)
            {
        
                var res = jQuery.parseJSON(data);
                idx = res.idx;
                msg = res.msg;
                action = res.action;
                //alert(res.msg);
                if(action == "deleted" || action == "failed"){
                    $("#img_'.$track->id.'").attr("src","'.JURI::root().'components/com_mymuse/assets/images/checkbox.png");
                }else{
                    $("#img_'.$track->id.'").attr("src","'.JURI::root().'components/com_mymuse/assets/images/cart.png");
                }
                if(idx){
                    if(idx == 1){
                        txt = idx+" "+"item";
                    }else{
                        txt = idx+" "+"items";
                    }
                    link = \''.'<a href="'.JRoute::_('index.php?option=com_mymuse&task=showcart&view=cart&Itemid='.$Itemid).'">'.JText::_('COM_MYMUSE_VIEW_CART').'</a>\';
                    $("#mini-cart-text").html(txt);
                    $("#mini-cart-link").html(link);
                }else{

                    $("#mini-cart-text").html(" ");
                    $("#mini-cart-link").html(\''.json_encode(JText::_('COM_MYMUSE_YOUR_CART_IS_EMPTY')).'\');
                    link = "";
                }
                my_modal.open({content: msg+"<br />"+link, width: 300,target:'.$track->id.', delay:'. $params->get('my_delay_fadeout', 3000)  .'});
            });

		});
	});

	';
	}
}
$document->addScriptDeclaration($js);
?>
<!--  START PRODUCT VIEW -->

<?php echo $this->loadTemplate('heading'); ?>


<form method="post"
	action="<?php JRoute::_('index.php?lang='.$langtag) ?>"
	onsubmit="return hasProduct(this,<?php echo $count; ?>);"
	name="mymuseform">
	<input type="hidden" name="option" value="com_mymuse" /> 
	<input type="hidden" name="task" value="addtocart" /> 
	<input type="hidden" name="catid" value="<?php echo $product->catid; ?>" /> 
	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />


<div class="mymuse">

<?php echo $this->loadTemplate('layout'); ?>

</form>


<?php echo $this->item->event->afterDisplayProduct; ?>

<!--  end PRODUCT VIEW -->
</div>

<div id='my_overlay' style="display: none"></div>
<div id='my_modal' style="display: none">
	<div id='my_content'>No JavaScript!</div>
	<a href='#' id='my_close'>close</a>
</div>

