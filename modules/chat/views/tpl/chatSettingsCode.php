<?php //TODO: Insert correct editor link in notification below?>
<p class="alert alert-danger">
	<?php printf(__('Edit this ONLY if you know basics of HTML, CSS and have been acquainted with the rules of template editing described <a target="_blank" href="%s">here</a>', LCS_LANG_CODE), 'http://supsystic.com/')?>
</p>
<fieldset>
	<legend>
		<?php _e('CSS code', LCS_LANG_CODE)?>
		<a href="#" class="button supsystic-tooltip-right lcsChatCodeUpdateBtn" data-code="css" title="<?php _e('Update CSS code to latest version from original template', LCS_LANG_CODE)?>">
			<i class="fa fa-refresh"></i>
			<?php _e('Update from Default', LCS_LANG_CODE)?>
		</a>
	</legend>
	<?php echo htmlLcs::textarea('tpl[css]', array('value' => esc_html($this->chatTemplate['css']), 'attrs' => 'id="lcsChatCssEditor"'))?>
</fieldset>
<fieldset>
	<legend>
		<?php _e('HTML code', LCS_LANG_CODE)?>
		<a href="#" class="button supsystic-tooltip-right lcsChatCodeUpdateBtn" data-code="html" title="<?php _e('Update HTML code to latest version from original template', LCS_LANG_CODE)?>">
			<i class="fa fa-refresh"></i>
			<?php _e('Update from Default', LCS_LANG_CODE)?>
		</a>
	</legend>
	<?php echo htmlLcs::textarea('tpl[html]', array('value' => esc_html($this->chatTemplate['html']), 'attrs' => 'id="lcsChatHtmlEditor"'))?>
</fieldset>