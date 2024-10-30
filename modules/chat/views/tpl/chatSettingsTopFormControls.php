<span id="lcsChatMainControllsShell" style="float: right; padding-right: 95px;">
	<button class="button button-primary lcsChatSaveBtn" title="<?php _e('Save all changes', LCS_LANG_CODE)?>">
		<i class="fa fa-fw fa-save"></i>
		<?php _e('Save', LCS_LANG_CODE)?>
	</button>
	<button class="button button-primary lcsChatPreviewBtn">
		<i class="fa fa-fw fa-eye"></i>
		<?php _e('Preview', LCS_LANG_CODE)?>
	</button>
	<button class="button button-primary lcsChatSwitchActive" 
			data-txt-off="<?php _e('Turn Off', LCS_LANG_CODE)?>" 
			data-txt-on="<?php _e('Turn On', LCS_LANG_CODE)?>"
			data-active="<?php echo $this->chatEngine['active']?>">
		<i class="fa fa-fw"></i>
		<span></span>
	</button>
</span>
<div style="clear: both; height: 0;"></div>
