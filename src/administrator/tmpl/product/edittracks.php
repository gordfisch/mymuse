<?php
/**
 * @version		$Id$
 * @package		mymuse
 * @copyright	Copyright © 2010 - Arboreta Internet Services - All rights reserved.
 * @license		GNU/GPL
 * @author		Gordon Fisch
 * @author mail	info@mymuse.ca
 * @website		http://www.mymuse.ca
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_contenthistory');
$wa->useScript('keepalive')
    ->useScript('form.validate');

$item = $this->item;
$lists = $this->lists;


?>
<!-- product edittracks.php -->
		<script type="text/javascript">
		<!--

		function submitbutton(pressbutton)
		{

			var form = document.adminForm;

			if (pressbutton == 'cancelitem') {
				submitform( pressbutton );
				return;
			}

			// do field validation

			if (form.title.value == ""){
				alert( "<?php echo Text::_( 'COM_MYMUSE_FILE_MUST_HAVE_A_TITLE', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}

		var variation = 0;
		function addvariation()
		{
			
			row_number = "#row_"+variation;
			jQuery(row_number).removeClass('hidden');
			variation++;
			
			
		}

		function deletevariation (variationid){
			var form = document.adminForm;
			form.variation.value = variationid;
			form.task.value = 'product.deletevariation'
			form.submit();

		}
		//-->
		</script>
		<h2><?php echo empty($this->item->id) ? Text::_('COM_MYMUSE_NEW_TRACK') : Text::_('COM_MYMUSE_EDIT_TRACK'); ?> : <?php echo $item->title; ?></h2>

		<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">



<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>
<!--  DETAILS TAB -->
<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_MYMUSE_DETAILS', true)); ?>

   <div class="row">
    <div class="col-12 col-lg-6">
        <fieldset id="fieldset-details" class="options-form">
            <legend><?php echo Text::_('COM_MYMUSE_DETAILS'); ?></legend>
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('id'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('id'); ?>
                </div>
            </div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('title'); ?>
				</div>
			</div>
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('file_type'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('file_type'); ?>
                </div>
            </div>

			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('alias'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('alias'); ?>
				</div>
			</div>

			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('file_time'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('file_time'); ?>
				</div>
			</div>
				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('product_sku'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('product_sku'); ?>
				</div>
			</div>
				
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('ordering'); ?>
				</div>
				<div class="controls">
				<?php echo $this->form->getInput('ordering'); ?>
				</div>
			</div>
        </fieldset>
	</div>
    <div class="col-12 col-lg-6">
        <fieldset id="fieldset-digital" class="options-form">
            <legend><?php echo Text::_('COM_MYMUSE_DETAILS'); ?></legend>
            <?php if(!$this->params->get('my_price_by_product')){ ?>

                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('price'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('price'); ?>
                    </div>
                </div>
            <?php } ?>
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('product_discount'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('product_discount'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('state'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('state'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('access'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('access'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('featured'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('featured'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('language'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('language'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label"><?php echo $this->form->getLabel('isrc_code'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('isrc_code'); ?>
                </div>
            </div>



        </fieldset>
	</div>
            </div>
            <div class="row">
    <div class="col-12 col-lg-12">
		<fieldset class="adminform">

			<legend></legend>
			<?php echo $this->form->getLabel('articletext'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('articletext'); ?>

		</fieldset>
    </div>
            </div>
<?php echo HTMLHelper::_('uitab.endTab'); ?>


<!--  TRACKS TAB -->
<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'tracks', Text::_('COM_MYMUSE_TRACKS', true)); ?>
    <div class="row">

        <div class="col-12 col-lg-12">
            <fieldset class="adminform">

            <legend><?php echo Text::_('COM_MYMUSE_TRACKS'); ?></legend>

                <div class="control-group">
                        <div class="control-label"><?php echo Text::_( 'COM_MYMUSE_DOWNLOAD_PATH' ); ?>
                        </div>
                        <div class="controls"><?php echo $lists['download_dir']; ?>
                        </div>
                </div>
                <table class="table table-striped" id="articleList">
                        <thead>
                            <tr>
                            	<th class="title">ID
                                </th>
                                <th class="title"><?php echo Text::_( 'COM_MYMUSE_SELECT_FILE' ); ?>
                                </th>
                                <th class="title"><?php echo Text::_( 'COM_MYMUSE_FILE_NAME_LABEL' ); ?>
                                </th>
                                <th class="title"><?php echo JText::_("COM_MYMUSE_FORMAT")?>
								</th>
                                <th class="title"><?php echo Text::_( 'COM_MYMUSE_FILE_LENGTH_LABEL' ); ?>
                                </th>
                                <th class="title"><?php echo Text::_( 'COM_MYMUSE_FILE_DOWNLOADS_LABEL' ); ?>
                                </th>
                                <th class="title"><?php echo Text::_( 'COM_MYMUSE_DELETE_ITEM' ); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if(isset($this->item->formats)){
                        	$formats = $this->item->formats;
                        }else{
                        	//$formats = $this->params->get('my_formats');
                        }
                        $my_formats = $this->params->get('my_formats');
                 

                        for($i = 0; $i < count($my_formats); $i++){
                            $class = '';
                            if($i >= count($my_formats)){
                                //$class = "hidden";
                            }
                            ?>
                            <tr class="<?php echo $class;?>" id="row_<?php echo $i; ?>">
                                <td><?php 
                                if(isset($formats[$i]->id)){
                                	echo $formats[$i]->id;
                                	?>
                                	<input type="hidden" name="format_id[<?php echo $i; ?>]" value="<?php 
                                	echo isset($formats[$i]->id)? $formats[$i]->id : ''; ?>">
                                	<?php
                                } ?>
                                


                                </td>
                                <td><?php echo $lists['select_file'][$i]; ?>
                                </td>
                                <td><?php echo isset($formats[$i]->file_data->file_name)? $formats[$i]->file_data->file_name : ''; ?>
                                </td>
                                <td>
                                <?php echo $lists['formats'][$i] ?>
                                </td>
                                <td><?php echo isset($formats[$i]->file_data->file_length)? MyMuseHelper::ByteSize($formats[$i]->file_data->file_length) : ''; ?>
                                </td>
                                <td><?php echo isset($formats[$i]->file_data->file_downloads)? $formats[$i]->file_data->file_downloads : '0'; ?>
                                </td>
                                <td>
                                <?php if(isset($formats[$i]->id)){
                                	?><a href="javascript:deletevariation(<?php echo $formats[$i]->id; ?>)"><?php echo Text::_( 'COM_MYMUSE_DELETE_ITEM' ); ?></a>
                                <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if(count($this->params->get('my_formats')) > 1){ ?>
                            <!--   <tr>
                                <td colspan="7"><a href="javascript:addvariation();"><?php echo Text::_('COM_MYMUSE_ADD_VARIATION')?></a></td>
                            </tr> -->
                        <?php } ?>
                        </tbody>
			        </table>
                </fieldset>
            </div>
        </div>
<?php echo HTMLHelper::_('uitab.endTab'); ?>
	

<!--  PREVIEWS TAB -->
<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'previews', Text::_('COM_MYMUSE_PREVIEWS', true)); ?>
	
<fieldset class="adminform">
		
	<legend><?php echo Text::_( 'COM_MYMUSE_PREVIEW' ); ?></legend>
		<div class="pull-left span12">
			<div class="control-group">
				<div class="control-label"><?php echo Text::_( 'COM_MYMUSE_PREVIEW_PATH' ); ?>
				</div>
				<div class="controls"><?php echo $lists['preview_dir']; ?>
				</div>
			</div>
			<table class="table table-striped" id="articleList">
				<thead>
					<tr>
						<th class="title"><?php echo Text::_( 'COM_MYMUSE_SELECT_FILE' ); ?>
						</th>
						<th class="title"><?php echo Text::_( 'COM_MYMUSE_FILE_NAME_LABEL' ); ?>
						</th>
						<th class="title"><?php echo Text::_( 'COM_MYMUSE_DELETE_ITEM' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr class="<?php echo $class;?>" id="row_<?php echo $i; ?>">
						<td><?php echo $lists['previews']; ?>
						</td>
						<td><?php echo $this->form->getInput('file_preview'); ?>
						</td>
						<td><input type="checkbox" name="remove_preview" id="jform_remove_preview" /></a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
</fieldset>
	

<?php echo HTMLHelper::_('uitab.endTab'); ?>
</div>
</div>

<div style="clear: both;"></div>

		<input type="hidden" name="parentid" value="<?php echo $item->parentid ?>" />
		<input type="hidden" name="jform[parentid]" value="<?php echo $item->parentid ?>" />
		<input type="hidden" name="jform[catid]" value="<?php echo $item->parent->catid ?>" />
		<input type="hidden" name="jform[artistid]" value="<?php echo $item->parent->artistid ?>" />
		<input type="hidden" name="current_preview" value="<?php echo stripslashes($item->file_preview) ?>" />
		
		<input type="hidden" name="view" value="product" />
		<input type="hidden" name="id" value="<?php echo $item->id; ?>" />
		<input type="hidden" name="jform[version]" value="<?php echo $item->version; ?>" />
		<input type="hidden" name="jform[product_downloadable]" value="1" />
		<input type="hidden" name="type" value="file" />
		<input type="hidden" name="layout" value="editfile" />
		<input type="hidden" name="option" value="com_mymuse" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="variation" value="" />

		<?php echo HTMLHelper::_( 'form.token' ); ?>
		</form>
