jQuery(document).ready(function(){
	var chat = new lcsChat({
		session: lcsSession
	,	is_admin: true
	,	engine: lcsEngine
	});
	chat._idle({
		firstTime: true
	});
	jQuery('#lcsChatStatusSel').change(function(){
		jQuery.sendFormLcs({
			msgElID: 'lcsChatStatusMsg'
		,	data: {mod: 'chat', action: 'setSessionStatus', session_id: lcsSession.id, status_id: jQuery(this).val()}
		,	onSuccess: function(res) {
				
			}
		});
	});
	jQuery('.chosen').chosen({
		disable_search_threshold: 10
	});
});