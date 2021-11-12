<?php
/**
 * @version     $$
 * @package     com_mymuse3
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Gord Fisch arboreta.ca
 */


// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Button\FeaturedButton;
use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Button\TransitionButton;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Mymuse\Administrator\Helper\MymuseHelper;

HTMLHelper::_('behavior.multiselect');

$app       = Factory::getApplication();
$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';


if ($saveOrder && !empty($this->tracks)) :

	$saveOrderingUrl = 'index.php?option=com_mymuse&task=products.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
endif ;

$sortFields = $this->getSortFields2();
$assoc 		= Associations::isEnabled();
$params 	= $this->params;
$lists 		= $this->lists;
$ordering 	= ($listOrder == 'a.ordering');

$workflow_enabled  	= false;
$workflow_state    	= false;
$workflow_featured 	= false;
$this->vote 		= false;

//TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS TRACKS

?>



<h2><?php echo Text::_( 'COM_MYMUSE_TRACKS' ); ?></h2>


<form action="<?php echo Route::_('index.php?option=com_mymuse&view=product&layout=listtracks'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->tracks)) : ?>
					<div class="alert alert-info">
						<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php endif ?>


			<div class="tracks">
				<div id="product_player"
					<?php if($params->get('product_player_height')){ ?>
					style="height: <?php echo $params->get('product_player_height'); ?>px"
					<?php } ?>
					><?php if(isset($this->item->flash)){ echo $this->item->flash; }?>
				</div>
				<div id="jp-title-li"></div>

<?php //MymuseHelper::print_pre($this->tracks[0]->formats); ?>

				<table class="table itemList" id="trackList">
					<caption id="captionTable" class="sr-only">
						<?php echo Text::_('COM_MYMUSE_TRACKS_TABLE_CAPTION'); ?>,
						<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
						<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
					</caption>
					<thead>
						<tr>

							<td class="w-1 text-center">
								<?php echo HtmlHelper::_('grid.checkall'); ?>
							</td>
							<th scope="col" class="w-1 text-center d-none d-md-table-cell">
								<?php echo HtmlHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'fas fa-sort'); ?>
							</th>
							<th scope="col" class="w-3 d-none d-lg-table-cell">
								<?php echo HtmlHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-1 text-center d-none d-md-table-cell">
								<?php echo HtmlHelper::_('searchtools.sort', 'JFEATURED', 'a.featured', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HtmlHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HtmlHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-3 d-none d-md-table-cell">
								<?php echo HtmlHelper::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
							</th>

							<?php if(!$this->params->get('my_price_by_product')){ ?>
								<th scope="col" class="w-3 text-center">
									<?php echo HtmlHelper::_('grid.sort',  Text::_('COM_MYMUSE_PRICE'), 'a.price', $listDirn, $listOrder); ?>

								</th>
								<th scope="col" class="w-3 text-center">
									<?php echo HtmlHelper::_('grid.sort',  Text::_('COM_MYMUSE_DISCOUNT'), 'a.product_discount', $listDirn, $listOrder); ?>
								</th>
							<?php } ?>
							<?php if (Multilanguage::isEnabled()) : ?>
								<th scope="col" class="w-3 d-none d-md-table-cell">
									<?php echo HtmlHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>
							<th scope="col" class="w-3 d-none d-md-table-cell">
								<?php echo Text::_('COM_MYMUSE_FORMAT');?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo Text::_('COM_MYMUSE_FILE_NAME_LABEL');?>
							</th>
							<th scope="col" class="w-3 d-none d-md-table-cell">
								<?php echo Text::_('COM_MYMUSE_DOWNLOADS');?>
							</th>
							<th scope="col" class="w-3 d-none d-md-table-cell">
								<?php echo Text::_('COM_MYMUSE_FILE_SIZE');?>
							</th>
							<th scope="col" class="w-3 d-none d-md-table-cell">
								<?php echo Text::_('COM_MYMUSE_PREVIEW_NAME');?>
							</th>

							
						</tr>
					</thead>

				<?php if (count($this->tracks) > 0) : ?>
					<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
					<?php
					$k = 0;
					$config = JFactory::getConfig();
				    $tzoffset = $config->get('config.offset');
				    $now = JFactory::getDate();
					for ($i=0, $n=count( $this->tracks ); $i < $n; $i++)
					{
						$track = &$this->tracks[$i];
						$track->max_ordering = 0;
						if($track->product_allfiles == "1"){
							$link 	= Route::_('index.php?option=com_mymuse&task=product.edit_allfiles&type=allfiles&id='. $track->id.'&parentid='.$track->parentid);
						}else{
							$link 	= Route::_('index.php?option=com_mymuse&task=product.editfile&type=file&id='. $track->id.'&parentid='.$track->parentid);
						}

						$ordering   = ($listOrder == 'a.ordering');
						$canEdit	= $user->authorise('core.edit',			'com_mymuse.product.'.$track->id);
						$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $track->checked_out == $userId || is_null($track->checked_out);
						$canEditOwn	= $user->authorise('core.edit.own',		'com_mymuse.product.'.$track->id) && $track->created_by == $userId;
						$canChange	= $user->authorise('core.edit.state',	'com_mymuse.product.'.$track->id) && $canCheckin;

						?>


						<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $track->catid; ?>">
							<td class="text-center">
								<?php echo HtmlHelper::_('grid.id', $i, $track->id, false, 'cid', 'cb', $track->title); ?>
							</td>
							<td class="text-center d-none d-md-table-cell">
								<?php
								$iconClass = '';
								if (!$canChange)
								{
									$iconClass = ' inactive';
								}
								elseif (!$saveOrder)
								{
									$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
								}
								?>
								<span class="sortable-handler<?php echo $iconClass ?>">
									<span class="fas fa-ellipsis-v" aria-hidden="true"></span>
								</span>
								<?php if ($canChange && $saveOrder) : ?>
									<input type="text" name="order[]" size="5" value="<?php echo $track->ordering; ?>" class="width-20 text-area-order hidden">
								<?php endif; ?>
							</td>
							<td class="d-none d-lg-table-cell">
								<?php echo (int) $track->id; ?>
							</td>
							<td class="text-center d-none d-md-table-cell">
								<?php 
								if(!$track->track_parentid) :
									echo HTMLHelper::_('mymuseadministrator.featured', $track->featured, $i, $canChange); 
								endif;

								?>
							</td>
							<td class="product-status text-center">
							<?php
							if(!$track->track_parentid) :
								$options = [
									'task_prefix' => 'products.',
									'disabled' => $workflow_state || !$canChange,
									'id' => 'state-' . $track->id
								];

								echo (new PublishedButton)->render((int) $track->state, $i, $options, $track->publish_up, $track->publish_down);
							endif;
							?>
							</td>
							<td scope="row" class="has-context">
								<div class="break-word">
								<?php if(!$track->track_parentid) : ?>

									<?php if ($track->checked_out) : ?>
										<?php echo HtmlHelper::_('jgrid.checkedout', $i, $track->editor, $track->checked_out_time, 'products.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo $link ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($track->title); ?>">
											<?php echo $this->escape($track->title); ?></a>
									<?php else : ?>
										<span title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($track->alias)); ?>"><?php echo $this->escape($track->title); ?></span>
									<?php endif; ?>
										<div class="small break-word">
											<?php if (empty($track->note)) : ?>
												<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($track->alias)); ?>
											<?php else : ?>
												<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($track->alias), $this->escape($track->note)); ?>
											<?php endif; ?>
										</div>
									<?php  if($track->product_allfiles == "1") :  ?>
										<div class="small break-word">
										<?php echo Text::_("MYMUSE_ALL_TRACKS"); ?>
										</div>
									 <?php endif; ?>
								<?php endif ?>

								</div>
							</td>
							<td class="small d-none d-md-table-cell">
								<?php echo $this->escape($track->access_level); ?>
							</td>

						<?php if(!$this->params->get('my_price_by_product')){ ?>
							<td class="track-price"><?php echo $track->price; ?></td>
							<td class="track-discount"><?php echo $track->product_discount; ?></td>
						<?php } ?>
							<?php if (Multilanguage::isEnabled()) : ?>
								<td class="small d-none d-md-table-cell">
									<?php echo LayoutHelper::render('joomla.content.language', $track); ?>
								</td>
							<?php endif; ?>
							<td class="track-format text-center">
								<?php
								foreach($track->formats as $f){
									echo $f->id." ".stripslashes($f->file_data->file_format)."<br />";
								}
								?>

							</td>

							<td class="track-file-name text-center">
								<?php
								foreach($track->formats as $f){
									echo stripslashes($f->file_data->file_name)."<br />";
								}
								?>

							</td>
							<td class="track-file-downloads text-center">
								<?php
								foreach($track->formats as $f){
									echo stripslashes($f->file_data->file_downloads)."<br />";
								}
								?>
							</td>
							<td class="track-file-length text-center">
								<?php
								foreach($track->formats as $f){
									echo MyMuseHelper::ByteSize($f->file_data->file_length)."<br />";
								}
								?>
							</td>

							<td class="track-file-preview text-center">
								<?php echo htmlspecialchars($track->file_preview, ENT_QUOTES);
								?>
							</td>


							
						</tr>
						<?php
						$k = 1 - $k;
					}
					?>
					</tbody>

		<?php endif; ?>
                </table>
		<?php echo JHTML::_( 'form.token' ); ?>
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="subtype" value="file" />
		<input type="hidden" name="boxchecked" value="" />
		<input type="hidden" name="parentid" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	</form>
	</div>

</div>
