var g_lcsAdminDashIsAutoUpdate = false
,	g_lcsAdminDashLastLoadId = false
,	g_lcsNotifySoundObj = null;
jQuery(document).ready(function(){
	var tblId = 'lcsChatsTbl';
	jQuery('#'+ tblId).jqGrid({
		url: lcsChatListTblUrl
	,	datatype: 'json'
	,	autowidth: true
	,	shrinkToFit: true
	,	colNames:[toeLangLcs('ID'), toeLangLcs('Status'), toeLangLcs('User'), toeLangLcs('Agent'), toeLangLcs('Date Created'), toeLangLcs('Actions')]
	,	colModel:[
			{name: 'id', index: 'id', searchoptions: {sopt: ['eq']}, width: '50', align: 'center'}
		,	{name: 'status_label', index: 'status_label', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'user_email', index: 'user_email', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'agent_email', index: 'agent_email', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'date_created', index: 'date_created', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'actions', index: 'actions', searchoptions: {sopt: ['eq']}, align: 'center'}
		/*,	{name: 'unique_views', index: 'unique_views', searchoptions: {sopt: ['eq']}, align: 'center'}
		
		,	{name: 'conversion', index: 'conversion', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'active', index: 'active', searchoptions: {sopt: ['eq']}, align: 'center'}*/
		]
	,	postData: {
			search: {
				text_like: jQuery('#'+ tblId+ 'SearchTxt').val()
			}
		,	session_status: jQuery('#'+ tblId+ 'SearchStatus').val()
		}
	,	rowNum:10
	,	rowList:[10, 20, 30, 1000]
	,	pager: '#'+ tblId+ 'Nav'
	,	sortname: 'id'
	,	viewrecords: true
	,	sortorder: 'desc'
	,	jsonReader: { repeatitems : false, id: '0' }
	,	caption: toeLangLcs('Current Chat')
	,	height: '100%' 
	,	emptyrecords: toeLangLcs('You have no Chats for now.')
	,	multiselect: true
	,	onSelectRow: function(rowid, e) {
			var tblId = jQuery(this).attr('id')
			,	selectedRowIds = jQuery('#'+ tblId).jqGrid ('getGridParam', 'selarrrow')
			,	totalRows = jQuery('#'+ tblId).getGridParam('reccount')
			,	totalRowsSelected = selectedRowIds.length;
			if(totalRowsSelected) {
				jQuery('#lcsChatRemoveGroupBtn').removeAttr('disabled');
				if(totalRowsSelected == totalRows) {
					jQuery('#cb_'+ tblId).prop('indeterminate', false);
					jQuery('#cb_'+ tblId).attr('checked', 'checked');
				} else {
					jQuery('#cb_'+ tblId).prop('indeterminate', true);
				}
			} else {
				jQuery('#lcsChatRemoveGroupBtn').attr('disabled', 'disabled');
				jQuery('#cb_'+ tblId).prop('indeterminate', false);
				jQuery('#cb_'+ tblId).removeAttr('checked');
			}
			lcsCheckUpdate(jQuery(this).find('tr:eq('+rowid+')').find('input[type=checkbox].cbox'));
			lcsCheckUpdate('#cb_'+ tblId);
		}
	,	gridComplete: function() {
			var tblId = jQuery(this).attr('id');
			jQuery('#lcsChatRemoveGroupBtn').attr('disabled', 'disabled');
			jQuery('#cb_'+ tblId).prop('indeterminate', false);
			jQuery('#cb_'+ tblId).removeAttr('checked');
			// Custom checkbox manipulation
			lcsInitCustomCheckRadio('#'+ jQuery(this).attr('id') );
			lcsCheckUpdate('#cb_'+ jQuery(this).attr('id'));
			jQuery('.lcsChatsTblRefreshBtn').removeAttr('disabled')
				.find('.fa').removeClass('fa-spinner fa-spin').addClass('fa-refresh');
		}
	,	loadComplete: function() {
			var tblId = jQuery(this).attr('id');

			var tblDataIds = jQuery('#'+ tblId).jqGrid('getDataIDs');
			if(tblDataIds && tblDataIds.length) {
				var firstRowData = jQuery('#'+ tblId).jqGrid('getRowData', tblDataIds[ 0 ]);
				if(firstRowData && firstRowData.id) {
					var thisId = parseInt(firstRowData.id);
					if(g_lcsAdminDashIsAutoUpdate && (!g_lcsAdminDashLastLoadId || thisId > g_lcsAdminDashLastLoadId)) {
						lcsAgentNewChatSessionArived( firstRowData );
					}
					if(thisId > g_lcsAdminDashLastLoadId) {
						g_lcsAdminDashLastLoadId = thisId;
					}
				}
			}
			g_lcsAdminDashIsAutoUpdate = false;

			if (this.p.reccount === 0) {
				jQuery(this).hide();
				jQuery('#'+ tblId+ 'EmptyMsg').show();
			} else {
				jQuery(this).show();
				jQuery('#'+ tblId+ 'EmptyMsg').hide();
			}
		}
	});
	jQuery('#'+ tblId+ 'NavShell').append( jQuery('#'+ tblId+ 'Nav') );
	jQuery('#'+ tblId+ 'Nav').find('.ui-pg-selbox').insertAfter( jQuery('#'+ tblId+ 'Nav').find('.ui-paging-info') );
	jQuery('#'+ tblId+ 'Nav').find('.ui-pg-table td:first').remove();
	jQuery('#'+ tblId+ 'SearchTxt').keyup(function(){
		var searchVal = jQuery.trim( jQuery(this).val() );
		if(searchVal && searchVal != '') {
			lcsGridDoListSearch({
				text_like: searchVal
			}, tblId);
		}
	});
	
	jQuery('#'+ tblId+ 'EmptyMsg').insertAfter(jQuery('#'+ tblId+ '').parent());
	jQuery('#'+ tblId+ '').jqGrid('navGrid', '#'+ tblId+ 'Nav', {edit: false, add: false, del: false});
	jQuery('#cb_'+ tblId+ '').change(function(){
		jQuery(this).attr('checked') 
			? jQuery('#lcsChatRemoveGroupBtn').removeAttr('disabled')
			: jQuery('#lcsChatRemoveGroupBtn').attr('disabled', 'disabled');
	});
	jQuery('#lcsChatRemoveGroupBtn').click(function(){
		var selectedRowIds = jQuery('#lcsChatsTbl').jqGrid ('getGridParam', 'selarrrow')
		,	listIds = [];
		for(var i in selectedRowIds) {
			var rowData = jQuery('#lcsChatsTbl').jqGrid('getRowData', selectedRowIds[ i ]);
			listIds.push( rowData.id );
		}
		var sessionLabel = '';
		if(listIds.length == 1) {	// In table label cell there can be some additional links
			var labelCellData = lcsGetGridColDataById(listIds[0], 'user_email', 'lcsChatsTbl');
			sessionLabel = jQuery(labelCellData).text();
		}
		var confirmMsg = listIds.length > 1
			? toeLangLcs('Are you sur want to remove '+ listIds.length+ ' Sessions?')
			: toeLangLcs('Are you sure want to remove "'+ sessionLabel+ '" Session?')
		if(confirm(confirmMsg)) {
			jQuery.sendFormLcs({
				btn: this
			,	data: {mod: 'chat', action: 'removeGroup', listIds: listIds, _model: 'chat_sessions'}
			,	onSuccess: function(res) {
					if(!res.error) {
						jQuery('#lcsChatsTbl').trigger( 'reloadGrid' );
					}
				}
			});
		}
		return false;
	});
	lcsInitCustomCheckRadio('#'+ tblId+ '_cb');
	jQuery('.lcsChatsTblRefreshBtn').click(function(){
		jQuery(this).attr('disabled', 'disabled')
			.find('.fa').removeClass('fa-refresh').addClass('fa-spinner fa-spin');
		jQuery('#lcsChatsTbl').trigger( 'reloadGrid' );
		return false;
	});
	jQuery('#'+ tblId+ 'SearchStatus').change(function(){
		jQuery('#'+ tblId).setGridParam({
			postData: {
				session_status: jQuery(this).val()
			}
		});
		jQuery('#'+ tblId).trigger( 'reloadGrid' );
	});
	jQuery('.chosen').chosen({
		disable_search_threshold: 10
	});
	if(parseInt(lcsChatEngine.params.enb_agent_auto_update)) {
		var idleDelay = parseInt(lcsChatEngine.params.idle_delay);
		//idleDelay = 5;	// Just for test
		if(idleDelay) {
			setInterval(function(){
				g_lcsAdminDashIsAutoUpdate = true;
				jQuery('#'+ tblId).trigger( 'reloadGrid' );
			}, idleDelay * 1000);
		}
	}
});
function lcsShowAgentChatSessionWnd(sessionId) {
	var wndWidth = 800
	,	wndHeight = 760
	,	wndName = 'lcs_agent_chat'+ sessionId;
	window.open(lcsAgentChatSessionUrl+ '&session_id='+ sessionId, wndName, 'width='+ wndWidth+ ',height='+ wndHeight+ ',menubar=no,location=no,resizable=yes,scrollbars=yes');
}
function lcsAgentNewChatSessionArived( chatSession ) {
	// TODO: Notify agent somehow here
	if(!g_lcsNotifySoundObj
		&& lcsChatEngine.params['sound'] 
		&& lcsChatEngine.params['sound'] != ''
		&& typeof(Audio) !== 'undefined'
	) {
		g_lcsNotifySoundObj = new Audio( lcsChatEngine.params['sound'] );
	}
	if(g_lcsNotifySoundObj) {
		// Stop audio in case it didn't finish prev. play
		if(g_lcsNotifySoundObj.currentTime) {
			g_lcsNotifySoundObj.pause();
			g_lcsNotifySoundObj.currentTime = 0;
		}
		// Play sound again
		g_lcsNotifySoundObj.play();
	}
}
