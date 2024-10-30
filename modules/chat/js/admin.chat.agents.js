jQuery(document).ready(function(){
	var tblId = 'lcsAgentsTbl';
	jQuery('#'+ tblId).jqGrid({ 
		url: lcsChatAgentsListTblUrl
	,	datatype: 'json'
	,	autowidth: true
	,	shrinkToFit: true
	,	colNames:[toeLangLcs('ID'), toeLangLcs('Name'), toeLangLcs('Email'), toeLangLcs('Role'), toeLangLcs('Active'), toeLangLcs('Date Created')]
	,	colModel:[
			{name: 'id', index: 'id', searchoptions: {sopt: ['eq']}, width: '50', align: 'center'}
		,	{name: 'name', index: 'name', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'email', index: 'email', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'position_label', index: 'position_label', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'active', index: 'active', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'date_created', index: 'active', searchoptions: {sopt: ['eq']}, align: 'center'}
		]
	,	rowNum: 10
	,	rowList: [10, 20, 30, 1000]
	,	pager: '#'+ tblId+ 'Nav'
	,	sortname: 'id'
	,	viewrecords: true
	,	sortorder: 'desc'
	,	jsonReader: { repeatitems : false, id: '0' }
	,	caption: toeLangLcs('Current Agent')
	,	height: '100%' 
	,	emptyrecords: toeLangLcs('You have no Agents for now.')
	,	multiselect: true
	,	onSelectRow: function(rowid, selected, e) {
			var tblId = jQuery(this).attr('id')
			,	selectedRowIds = jQuery('#'+ tblId).jqGrid ('getGridParam', 'selarrrow')
			,	totalRows = jQuery('#'+ tblId).getGridParam('reccount')
			,	totalRowsSelected = selectedRowIds.length;
			if(totalRowsSelected) {
				jQuery('#lcsAgentsTblRemoveGroupBtn').removeAttr('disabled');
				if(totalRowsSelected == totalRows) {
					jQuery('#cb_'+ tblId).prop('indeterminate', false);
					jQuery('#cb_'+ tblId).attr('checked', 'checked');
				} else {
					jQuery('#cb_'+ tblId).prop('indeterminate', true);
				}
			} else {
				jQuery('#lcsAgentsTblRemoveGroupBtn').attr('disabled', 'disabled');
				jQuery('#cb_'+ tblId).prop('indeterminate', false);
				jQuery('#cb_'+ tblId).removeAttr('checked');
			}
			lcsCheckUpdateArea('#gview_'+ tblId);
		}
	,	gridComplete: function(a, b, c) {
			var tblId = jQuery(this).attr('id');
			jQuery('#lcsAgentsTblRemoveGroupBtn').attr('disabled', 'disabled');
			jQuery('#cb_'+ tblId).prop('indeterminate', false);
			jQuery('#cb_'+ tblId).removeAttr('checked');
			if(jQuery('#'+ tblId).jqGrid('getGridParam', 'records'))	// If we have at least one row - allow to clear whole list
				jQuery('#lcsAbClearBtn').removeAttr('disabled');
			else
				jQuery('#lcsAbClearBtn').attr('disabled', 'disabled');
			// Custom checkbox manipulation
			lcsInitCustomCheckRadio('#gview_'+ tblId);
			lcsCheckUpdateArea('#gview_'+ tblId);
		}
	,	loadComplete: function() {
			var tblId = jQuery(this).attr('id');
			if (this.p.reccount === 0) {
				jQuery('#gbox_'+ tblId).hide();
				jQuery('.'+ tblId+ 'Btn').hide();
				jQuery('#'+ tblId+ 'EmptyMsg').show();
			} else {
				jQuery('#gbox_'+ tblId).show();
				jQuery('.'+ tblId+ 'Btn').show();
				jQuery('#'+ tblId+ 'EmptyMsg').hide();
			}
		}
	});
	jQuery('#'+ tblId+ 'NavShell').append( jQuery('#'+ tblId+ 'Nav') );
	jQuery('#'+ tblId+ 'Nav').find('.ui-pg-table td:first').remove();
	jQuery('#'+ tblId+ '').jqGrid('navGrid', '#'+ tblId+ 'Nav', {edit: false, add: false, del: false});
	// Disabled sticky for this table header
	jQuery('#gview_'+ tblId).find('.ui-jqgrid-hdiv').addClass('sticky-ignore');
	jQuery('#cb_'+ tblId+ '').change(function(){
		jQuery(this).attr('checked') 
			? jQuery('#lcsAgentsTblRemoveGroupBtn').removeAttr('disabled')
			: jQuery('#lcsAgentsTblRemoveGroupBtn').attr('disabled', 'disabled');
		lcsCheckUpdateArea('#gview_'+ tblId);
	});
	jQuery('#lcsAgentsTblRemoveGroupBtn').click(function(){
		if(jQuery(this).attr('disabled')) return false;
		var selectedRowIds = jQuery('#lcsAgentsTbl').jqGrid ('getGridParam', 'selarrrow')
		,	listIds = [];
		for(var i in selectedRowIds) {
			var rowData = jQuery('#lcsAgentsTbl').jqGrid('getRowData', selectedRowIds[ i ]);
			listIds.push( rowData.id );
		}
		var confirmMsg = listIds.length > 1
			? toeLangLcs('Are you sur want to remove '+ listIds.length+ ' Agents?')
			: toeLangLcs('Are you sure want to remove "'+ lcsGetGridColDataById(listIds[0], 'name', 'lcsAgentsTbl')+ '" Agent?')
		if(confirm(confirmMsg)) {
			jQuery.sendFormLcs({
				btn: this
			,	data: {mod: 'chat', action: 'removeGroup', _model: 'chat_users', listIds: listIds}
			,	onSuccess: function(res) {
					if(!res.error) {
						jQuery('#lcsAgentsTbl').trigger( 'reloadGrid' );
					}
				}
			});
		}
		return false;
	});
	jQuery(document).bind('lcsAgents_tabSwitch', function(){
		jQuery('#lcsAgentsTbl').setGridWidth( jQuery('#gbox_lcsAgentsTbl').parent().width(), true ); //Back to original width
	});
});
function lcsEditAgentName(link, id) {
	var $link = jQuery(link)
	,	$parent = $link.parents('td:first')
	,	colProps = jQuery('#lcsAgentsTbl').jqGrid('getColProp', 'name');
	// Save col width - to be able resotre it after finish editing
	jQuery('#lcsAgentsTbl').jqGrid('setColProp', 'name', {_lcsOldWidth: colProps.width});
	jQuery('#lcsAgentsTbl').jqGrid('setColProp', 'name', {widthOrg: 300});
	// This need to trigger column width update
	var gw = jQuery('#lcsAgentsTbl').jqGrid('getGridParam', 'width');
	jQuery('#lcsAgentsTbl').jqGrid('setGridWidth', gw);
	
	$parent.width('650px');
	var $editShell = $parent.find('.lcsAgentsEditNameShell');
	if(!$editShell || !$editShell.size()) {
		$parent.append( jQuery('#lcsAgentsEditNameShell').clone().removeAttr('id') );
		$editShell = $parent.find('.lcsAgentsEditNameShell');
		$editShell.find('input').removeAttr('disabled').blur(function(){
			lcsFinishAgentNameEdit( jQuery(this).val(), link, id );
		}).keydown(function(e){
			if(e.keyCode == 13) {	// Enter pressed
				lcsFinishAgentNameEdit( jQuery(this).val(), link, id );
			}
		});
		$editShell.find('.lcsAgentSaveNameBtn').click(function(){
			lcsFinishAgentNameEdit( $editShell.find('input').val(), link, id );
			return false;
		});
	}
	$editShell.find('input[name="name"]').val( $link.find('.lcsAgentTblName').html() ).focus();
	$link.hide( g_lcsAnimationSpeed );
	$editShell.show( g_lcsAnimationSpeed );
}
function lcsFinishAgentNameEdit(name, link, id) {
	var $link = jQuery(link)
	,	$parent = $link.parent()
	,	$editShell = $parent.find('.lcsAgentsEditNameShell');
	
	jQuery.sendFormLcs({
		btn: $editShell.find('.lcsAgentSaveNameBtn')
	,	data: {mod: 'chat', action: 'saveAgentName', id: id, name: name}
	,	onSuccess: function(res) {
			if(!res.error) {
				var colProps = jQuery('#lcsAgentsTbl').jqGrid('getColProp', 'name');
				jQuery('#lcsAgentsTbl').jqGrid('setColProp', 'name', {widthOrg: colProps._lcsOldWidth});
				// This need to trigger column width update
				var gw = jQuery('#lcsAgentsTbl').jqGrid('getGridParam', 'width');
				jQuery('#lcsAgentsTbl').jqGrid('setGridWidth', gw);
				$link.find('.lcsAgentTblName').html( name );
				$link.show( g_lcsAnimationSpeed );
				$editShell.hide( g_lcsAnimationSpeed );
			}
		}
	});
	
}
