<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mymuse
 *
 * @copyright   Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

$params 	= MymuseHelper::getParams();

$missing = $this->products['missing'];
unset($this->products['missing']);
?>


<form action="<?php echo Route::_('index.php?option=com_mymuse&view=products'); ?>" method="post" name="adminForm" id="adminForm">

	<div id="j-main-container" class="span10">
	
	<h2>File Check</h2>
<table>
<?php 
//MymuseHelper::print_pre($this->products);
foreach($this->products as $product){
	echo "<tr><td colspan='3'>". $product->id .' <b><a href="index.php?option=com_mymuse&view=product&layout=listtracks&id='.$product->id.'">'.$product->title."</b></td></tr>";
	foreach($product->items as $item){
		echo "<tr><td>&nbsp;</td><td>". $item->id ."</td><td>". $item->title ."</td></tr>";
		
		$filenames = array();
		$previews = array();
		$path = MyMuseHelper::getDownloadPath($product->id,1);
		$preview_path = MyMuseHelper::getSitePath($product->id,1);
		
		if(isset($item->preview) && $item->preview != ''){
			$previews[] = $item->preview;
		}
		foreach($previews as $key => $p){
			foreach($p as $k => $i){
				$full_path = $preview_path.$k;
				//MyMuseHelper::print_pre($p);
				echo "<tr><td>----</td><td>Preview</td><td class='".$p[$k]['class']."'>$full_path</td></tr>";
			}
		}
		
		
		if(isset($item->download) && $item->download != ''){
			$filenames[] = $item->download;
		}

		if($params->get('my_download_dir_format')){
			foreach($params->get('my_formats') as $format){
				foreach($filenames as $key => $f){
					foreach($f as $k => $i){
						$full_path = $path.$format->format_value.DS.$k;
						echo "<tr><td>----</td><td>Download</td><td class='".$f[$k]['class']."'>$full_path</td></tr>";
					}
				}
				echo "<tr><td colspan='3'>&nbsp;</td></tr>";
			}
		}else{
			foreach($filenames as $key => $f){
				foreach($f as $k => $i){
					$full_path = $path.$k;
					echo "<tr><td>----</td><td>Download</td><td class='".$f[$k]['class']."'>$full_path</td></tr>";
				}
			}
				echo "<tr><td colspan='3'>&nbsp;</td></tr>";
			
		}
		
		
		
	}
		echo "<tr><td colspan='3'>&nbsp;</td></tr>";
}
?>
</table>

<?php if(count($missing)){ ?>
<h2>Missing!</h2>
	<?php foreach($missing as $m){ 
		echo '<p class="alert alert-danger">'.$m.'</p>';
		
	}?>
	
<?php }?>
</div>