<a href="<?php echo $this->addNewAgentUrl;?>" target="_blank" class="button button-primary lcsAgentAddNew">
	<i class="fa fa-plus"></i>
	<?php _e('Add New Agent', LCS_LANG_CODE)?>
</a>
<a href="#" class="button button-primary lcsAgentsTblBtn" id="lcsAgentsTblRemoveGroupBtn">
	<i class="fa fa-fw fa-trash-o"></i>
	<?php _e('Delete selected', LCS_LANG_CODE)?>
</a>
<div style="clear: both;"></div>
<table id="lcsAgentsTbl"></table>
<div id="lcsAgentsTblNav"></div>
<div id="lcsAgentsTblEmptyMsg" style="display: none;">
	<h3><?php _e('You have no agents for now.', LCS_LANG_CODE)?></h3>
</div>
<div id="lcsAgentsEditNameShell" class="lcsAgentsEditNameShell">
	<?php echo htmlLcs::text('name', array(
		'disabled' => true,
	))?>
	<a href="#" class="button lcsAgentSaveNameBtn"><i class="fa fa-save"></i></a>
	<div style="clear: both;"></div>
</div>