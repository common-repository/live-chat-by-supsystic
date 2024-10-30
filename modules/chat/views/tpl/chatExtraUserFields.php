<h3><?php echo LCS_WP_PLUGIN_NAME;?></h3>
<table class="form-table">
	<tr>
		<th><label for="lcs[make_agent]"><?php _e('Live Chat Agent', LCS_LANG_CODE)?></label></th>
		<td>
			<?php echo htmlLcs::checkbox('lcs[make_agent]', array(
				'checked' => $this->makeAgentCheck,
			))?>
			<span class="description"><?php _e('You can make anyone - agent for your live chat', LCS_LANG_CODE)?></span>
		</td>
	</tr>
</table>