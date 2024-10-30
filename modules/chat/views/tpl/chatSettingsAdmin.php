<div id="lcsChatSettingsEditTabs">
	<section class="supsystic-bar supsystic-sticky sticky-padd-next sticky-save-width sticky-base-width-auto" data-prev-height="#supsystic-breadcrumbs" data-next-padding-add="15">
		<h3 class="nav-tab-wrapper" style="margin-bottom: 0px; margin-top: 12px;">
			<?php $i = 0;?>
			<?php foreach($this->tabs as $tKey => $tData) { ?>
				<?php
					$iconClass = 'lcs-edit-icon';
					if(isset($tData['avoid_hide_icon']) && $tData['avoid_hide_icon']) {
						$iconClass .= '-not-hide';	// We will just exclude it from selector to hide, jQuery.not() - make browser slow down in this case - so better don't use it
					}
				?>
				<a class="nav-tab <?php if($i == 0) { echo 'nav-tab-active'; }?>" href="#<?php echo $tKey?>">
					<?php if(isset($tData['fa_icon'])) { ?>
						<i class="<?php echo $iconClass?> fa <?php echo $tData['fa_icon']?>"></i>
					<?php } elseif(isset($tData['icon_content'])) { ?>
						<i class="<?php echo $iconClass?> fa"><?php echo $tData['icon_content']?></i>
					<?php }?>
					<span class="lcsChatSettingsTabTitle"><?php echo $tData['title']?></span>
				</a>
			<?php $i++; }?>
		</h3>
	</section>
	<section>
		<div id="lcsMainChatSetPanel" class="supsystic-item supsystic-panel" style="padding-left: 10px;">
			<div id="containerWrapper">
				<form id="lcsChatSettingsEditForm">
					<?php foreach($this->tabs as $tKey => $tData) { ?>
						<div id="<?php echo $tKey?>" class="lcsTabContent">
							<?php echo $tData['content']?>
						</div>
					<?php }?>
					<?php echo htmlLcs::hidden('mod', array('value' => 'chat'))?>
					<?php echo htmlLcs::hidden('action', array('value' => 'saveSettings'))?>
				</form>
			</div>
		</div>
		<div id="lcsChatTplPreview">
			<iframe id="lcsChatTplPreviewFrame" width="" height="" frameborder="0" src="" style=""></iframe>
			<script type="text/javascript">
			jQuery('#lcsChatTplPreviewFrame').load(function(){
				if(typeof(lcsHidePreviewUpdating) === 'function')
					lcsHidePreviewUpdating();
				var contentDoc = jQuery(this).contents()
				,	chatTplShell = contentDoc.find('.lcsShell')
				,	paddingSize = 40
				,	newWidth = jQuery(jQuery(this).get(0).contentWindow.document).find('.lcsShell').outerWidth()
				,	newHeight = jQuery(jQuery(this).get(0).contentWindow.document).find('.lcsShell').outerHeight() + paddingSize
				,	parentWidth = jQuery('#lcsChatTplPreview').width()
				,	widthMeasure = jQuery('#lcsChatSettingsEditForm').find('[name="tpl[params][width_measure]"]:checked').val();

				if(widthMeasure == '%') {
					newWidth = parentWidth;
				}
				jQuery(this).width( newWidth+ 'px' );
				jQuery(this).height( newHeight+ 'px' );
				var top = 15
				,	left = 0;
				chatTplShell.css({
					'position': 'fixed'
				,	'top': top+ 'px'
				,	'left': left+ 'px'
				});
				contentDoc.click(function(){
					return false;
				});
			}).attr('src', '<?php echo $this->previewUrl?>');
			</script>
		</div>
	</section>
</div>
<div id="lcsChatTplPreviewUpdatingMsg">
	<?php _e('Loading preview...', LCS_LANG_CODE)?>
</div>
<div id="lcsGoToTop">
	<a id="lcsGoToTopBtn" href="#">
		<img src="<?php echo uriLcs::_(LCS_IMG_PATH)?>pointer-up.png" /><br />
		<?php _e('Back to top', LCS_LANG_CODE)?>
	</a>
</div>
<?php dispatcherLcs::doAction('afterChatSettingsEdit');?>