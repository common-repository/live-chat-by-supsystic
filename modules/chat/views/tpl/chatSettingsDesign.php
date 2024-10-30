<div id="lcsChatDesignTabs">
	<h3 class="nav-tab-wrapper">
		<a href="#" id="lcsChangeChatTplBtn" class="button button-primary"><?php _e('Choose Chat Template', LCS_LANG_CODE)?></a>
		<?php $i = 0;?>
		<?php foreach($this->designTabs as $tKey => $tData) { ?>
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
				<span class="lcsChatTabTitle"><?php echo $tData['title']?></span>
			</a>
		<?php $i++; }?>
	</h3>
	<div style="clear: both;"></div>
	<?php foreach($this->designTabs as $tKey => $tData) { ?>
		<div id="<?php echo $tKey?>" class="lcsTabContent">
			<?php echo $tData['content']?>
		</div>
	<?php }?>
</div>
<?php /*Additional not changable data*/ ?>
<?php echo htmlLcs::hidden('tpl[id]', array('value' => $this->chatTemplate['id']))?>
<?php echo htmlLcs::hidden('tpl[original_id]', array('value' => $this->chatTemplate['original_id']))?>
<?php echo htmlLcs::hidden('tpl[engine_id]', array('value' => $this->chatTemplate['engine_id']))?>
<?php foreach($this->chatTemplate['params']['opts_attrs'] as $optAttrKey => $optAttrVal) { ?>
	<?php echo htmlLcs::hidden('tpl[params][opts_attrs]['. $optAttrKey. ']', array('value' => $this->chatTemplate['params']['opts_attrs'][ $optAttrKey ]))?>
<?php }?>

<?php echo htmlLcs::hidden('engine[id]', array('value' => $this->chatEngine['id']))?>
<?php if(isset($this->chatEngine['params']['opts_attrs']) && !empty($this->chatEngine['params']['opts_attrs'])) {?>
	<?php foreach($this->chatEngine['params']['opts_attrs'] as $optAttrKey => $optAttrVal) { ?>
		<?php echo htmlLcs::hidden('engine[params][opts_attrs]['. $optAttrKey. ']', array('value' => $this->chatEngine['params']['opts_attrs'][ $optAttrKey ]))?>
	<?php }?>
<?php }?>
<?php /*****/ ?>
<div id="lcsChatDesignSelectWnd" style="display: none;" title="<?php _e('Select your chat design', LCS_LANG_CODE)?>">
	<div  class="tpls-list">
		<?php foreach($this->chatTemplates as $t) { ?>
			<?php $isPromo = isset($t['promo']) && $t['promo'];?>
			<?php $promoClass = $isPromo ? 'sup-promo' : '';?>
			<div class="tpls-list-item preset <?php echo $promoClass;?>" data-id="<?php echo $isPromo ? 0 : $t['id']?>">
				<img src="<?php echo $t['img_preview_url']?>" class="lcsTplPrevImg" />
				<?php if($isPromo) { ?>
					<a href="<?php echo $t['promo_link']?>" target="_blank" class="button button-primary preset-select-btn <?php echo $promoClass;?>"><?php _e('Get in PRO', LCS_LANG_CODE)?></a>
				<?php } else { ?>
					<a href="#" class="button button-primary preset-select-btn" data-txt="<?php _e('Select', LCS_LANG_CODE)?>" data-txt-active="<?php _e('Selected', LCS_LANG_CODE)?>"><?php _e('Select', LCS_LANG_CODE)?></a>
				<?php }?>
				<div class="preset-overlay">
					<h3>
						<span class="lcsTplLabel"><?php echo $t['label']?></span>
					</h3>
					<div class="lcsTplMsg"></div>
				</div>
			</div>
		<?php }?>
		<div style="clear: both;"></div>
	</div>
</div>