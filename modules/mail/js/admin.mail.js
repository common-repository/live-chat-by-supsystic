jQuery(document).ready(function(){
	jQuery('#lcsMailTestForm').submit(function(){
		jQuery(this).sendFormLcs({
			btn: jQuery(this).find('button:first')
		,	onSuccess: function(res) {
				if(!res.error) {
					jQuery('#lcsMailTestForm').slideUp( 300 );
					jQuery('#lcsMailTestResShell').slideDown( 300 );
				}
			}
		});
		return false;
	});
	jQuery('.lcsMailTestResBtn').click(function(){
		var result = parseInt(jQuery(this).data('res'));
		jQuery.sendFormLcs({
			btn: this
		,	data: {mod: 'mail', action: 'saveMailTestRes', result: result}
		,	onSuccess: function(res) {
				if(!res.error) {
					jQuery('#lcsMailTestResShell').slideUp( 300 );
					jQuery('#'+ (result ? 'lcsMailTestResSuccess' : 'lcsMailTestResFail')).slideDown( 300 );
				}
			}
		});
		return false;
	});
	jQuery('#lcsMailSettingsForm').submit(function(){
		jQuery(this).sendFormLcs({
			btn: jQuery(this).find('button:first')
		});
		return false; 
	});
});