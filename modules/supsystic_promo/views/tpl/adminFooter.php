<div class="lcsAdminFooterShell">
	<div class="lcsAdminFooterCell">
		<?php echo LCS_WP_PLUGIN_NAME?>
		<?php _e('Version', LCS_LANG_CODE)?>:
		<a target="_blank" href="http://wordpress.org/plugins/live-chat-by-supsystic/changelog/"><?php echo LCS_VERSION?></a>
	</div>
	<div class="lcsAdminFooterCell">|</div>
	<?php  if(!frameLcs::_()->getModule(implode('', array('l','ic','e','ns','e')))) {?>
	<div class="lcsAdminFooterCell">
		<?php _e('Go', LCS_LANG_CODE)?>&nbsp;<a target="_blank" href="<?php echo $this->getModule()->getMainLink();?>"><?php _e('PRO', LCS_LANG_CODE)?></a>
	</div>
	<div class="lcsAdminFooterCell">|</div>
	<?php } ?>
	<div class="lcsAdminFooterCell">
		<a target="_blank" href="http://wordpress.org/support/plugin/live-chat-by-supsystic"><?php _e('Support', LCS_LANG_CODE)?></a>
	</div>
	<div class="lcsAdminFooterCell">|</div>
	<div class="lcsAdminFooterCell">
		Add your <a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/live-chat-by-supsystic?filter=5#postform">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on wordpress.org.
	</div>
</div>