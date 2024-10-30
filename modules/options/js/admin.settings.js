jQuery(document).ready(function(){
	jQuery('#lcsSettingsSaveBtn').click(function(){
		jQuery('#lcsSettingsForm').submit();
		return false;
	});
	jQuery('#lcsSettingsForm').submit(function(){
		jQuery(this).sendFormLcs({
			btn: jQuery('#lcsSettingsSaveBtn')
		});
		return false;
	});
});