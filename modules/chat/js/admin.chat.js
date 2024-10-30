var lcsChatSaveTimeout = null
,	lcsChatSettingsIsSaving = false
,	lcsTinyMceEditorUpdateBinded = false
,	lcsSaveWithoutPreviewUpdate = false;
jQuery(document).ready(function(){
	jQuery('#lcsChatSettingsEditTabs').wpTabs({
		uniqId: 'lcsChatSettingsEditTabs'
	,	change: function(selector) {
			if(selector == '#lcsCodeEditor') {
				jQuery(selector).find('textarea').each(function(i, el){
					if(typeof(this.CodeMirrorEditor) !== 'undefined') {
						this.CodeMirrorEditor.refresh();
					}
				});
			}
			var tabChangeEvt = str_replace(selector, '#', '')+ '_tabSwitch';
			jQuery(document).trigger( tabChangeEvt );
		}
	});
	jQuery('#lcsChatDesignTabs').wpTabs({
		uniqId: 'lcsChatDesignTabs'
	});
	jQuery('.lcsChatSaveBtn').click(function(){
		jQuery('#lcsChatSettingsEditForm').submit();
		return false;
	});
	jQuery('#lcsChatSettingsEditForm').submit(function(){
		// Don't save if form isalready submitted
		if(lcsChatSettingsIsSaving) {
			lcsMakeAutoUpdate();
			return false;
		}
		lcsCheckSaveTriggerForm();
		if(!lcsSaveWithoutPreviewUpdate)
			lcsShowPreviewUpdating();
		lcsChatSettingsIsSaving = true;
		var addData = {};
		if(lcsChatTemplate && lcsChatTemplate.params && lcsChatTemplate.params.opts_attrs && lcsChatTemplate.params.opts_attrs.txt_block_number) {
			for(var i = 0; i < lcsChatTemplate.params.opts_attrs.txt_block_number; i++) {
				var textId = 'params_tpl_txt_'+ i
				,	sendValKey = 'params_tpl_txt_val_'+ i;
				addData[ sendValKey ] = encodeURIComponent( lcsGetTxtEditorVal( textId ) );
			}
		}
		if(lcsEngineTxtEditors && lcsEngineTxtEditors.length) {
			for(var i = 0; i < lcsEngineTxtEditors.length; i++) {
				var paramName = 'engine[params]['+ lcsEngineTxtEditors[i]+ ']'
				,	textId = str_replace(paramName, ['[', ']'], '_');
				jQuery(this).find('[name="'+ paramName+ '"]').val( lcsGetTxtEditorVal( textId ) );
			}
		}
		if(jQuery('#lcsChatCssEditor').get(0).CodeMirrorEditor)
			jQuery('#lcsChatCssEditor').val( jQuery('#lcsChatCssEditor').get(0).CodeMirrorEditor.getValue() );
		if(jQuery('#lcsChatHtmlEditor').get(0).CodeMirrorEditor)
			jQuery('#lcsChatHtmlEditor').val( jQuery('#lcsChatHtmlEditor').get(0).CodeMirrorEditor.getValue() );
		jQuery(this).sendFormLcs({
			btn: jQuery('.lcsChatSaveBtn')
		,	appendData: addData
		,	onSuccess: function(res) {
				lcsChatSettingsIsSaving = false;
				if(!res.error) {
					if(!lcsSaveWithoutPreviewUpdate)
						lcsRefreshPreview();
				}
				lcsSaveWithoutPreviewUpdate = false;
			}
		});
		return false;
	});
	
	jQuery('.lcsBgTypeSelect').change(function(){
		var iter = jQuery(this).data('iter');
		jQuery('.lcsBgTypeShell_'+ iter).hide();
		switch(jQuery(this).val()) {
			case 'img':
				jQuery('.lcsBgTypeImgShell_'+ iter).show();
				break;
			case 'color':
				jQuery('.lcsBgTypeColorShell_'+ iter).show();
				break;
		}
	}).change();
	var cssEditor = CodeMirror.fromTextArea(jQuery('#lcsChatCssEditor').get(0), {
		mode: 'css'
	,	lineWrapping: true
	,	lineNumbers: true
	,	matchBrackets: true
    ,	autoCloseBrackets: true
	});
	jQuery('#lcsChatCssEditor').get(0).CodeMirrorEditor = cssEditor;
	if(cssEditor.on && typeof(cssEditor.on) == 'function') {
		cssEditor.on('change', function(){
			lcsMakeAutoUpdate( 3000 );
		});
	}
	var htmlEditor = CodeMirror.fromTextArea(jQuery('#lcsChatHtmlEditor').get(0), {
		mode: 'text/html'
	,	lineWrapping: true
	,	lineNumbers: true
	,	matchBrackets: true
    ,	autoCloseBrackets: true
	});
	jQuery('#lcsChatHtmlEditor').get(0).CodeMirrorEditor = htmlEditor;
	if(htmlEditor.on && typeof(htmlEditor.on) == 'function') {
		htmlEditor.on('change', function(){
			lcsMakeAutoUpdate( 3000 );
		});
	}
	setTimeout(function(){
		lcsBindTinyMceUpdate();
		if(!lcsTinyMceEditorUpdateBinded) {
			jQuery('.wp-switch-editor.switch-tmce').click(function(){
				setTimeout(lcsBindTinyMceUpdate, 500);
			});
		}
	}, 500);
	// Close btn selection
	// TODO: Make close btns selection
	// Some main options can have additional sub-options - "descriptions" - that need to be visible if option is checked
	jQuery('#lcsChatSettingsEditForm').find('input[name="engine[params][main][show_on]"],input[name="engine[params][main][show_to]"],input[name="engine[params][main][show_pages]"]').change(function(){
		var name = jQuery(this).attr('name')
		,	value = jQuery(this).val()
		,	nameReplaced = str_replace( str_replace( str_replace(name, '][', '_'), '[', '_'), ']', '_' )
		,	nameValueReplaced = nameReplaced+ value
		,	descShell = jQuery('#lcsOptDesc_'+ nameValueReplaced);
		if(descShell.size()) {
			jQuery(this).attr('checked') ? descShell.slideDown( g_lcsAnimationSpeed ) : descShell.slideUp( g_lcsAnimationSpeed );
		}
	}).change();
	jQuery('.chosen').chosen();
	jQuery('.chosen.chosen-responsive').each(function(){
		jQuery(this).next('.chosen-container').addClass('chosen-responsive');
	});
	// Animation effect change
	// TODO: Make animation selection
	jQuery('.lcsChatPreviewBtn').click(function(){
		// TODO: Make scroll to preview here
		jQuery('html, body').animate({
			scrollTop: jQuery("#lcsChatTplPreview").offset().top
		}, 1000);
		return false;
	});
	// Don't allow users to set more then 100% width
	jQuery('#lcsChatSettingsEditForm').find('[name="tpl[params][width]"]').keyup(function(){
		var measureType = jQuery('#lcsChatSettingsEditForm').find('[name="tpl[params][width_measure]"]:checked').val();
		if(measureType == '%') {
			var currentValue = parseInt( jQuery(this).val() );
			if(currentValue > 100) {
				jQuery(this).val( 100 );
			}
		}
	});
	jQuery('#lcsChatSettingsEditForm').find('[name="tpl[params][width_measure]"]').change(function(){
		if(!jQuery(this).prop('checked'))
			return;
		var widthInput = jQuery('#lcsChatSettingsEditForm').find('[name="tpl[params][width]"]');
		if(jQuery(this).val() == '%') {
			var currentWidth = parseInt(widthInput.val());
			if(currentWidth > 100) {
				widthInput.data('prev-width', currentWidth);
				widthInput.val(100);
			}
		} else if(widthInput.data('prev-width')) {
			widthInput.val( widthInput.data('prev-width') );
		}
	});
	// Show/hide whole blocks after it's enable/disable by special attribute - data-switch-block
	jQuery('input[type=checkbox][data-switch-block]').change(function(){
		var blockToSwitch = jQuery(this).data('switch-block');
		if(jQuery(this).prop('checked')) {
			jQuery('[data-block-to-switch='+ blockToSwitch+ ']').slideDown( g_lcsAnimationSpeed );
		} else {
			jQuery('[data-block-to-switch='+ blockToSwitch+ ']').slideUp( g_lcsAnimationSpeed );
		}
	}).change();
	// Init Hide IP Dlg
	_lcsChatHideIpMoveFromText(true);
	lcsChatInitHideIpDlg();
	// Auto update bind, timeout - to make sure that all options is already setup and triggered required load changes
	setTimeout(function(){
		var autoUpdateBoxes = ['#lcsChatDesign', '#lcsWhenShow', '#lcsTriggers', '#lcsRegistration']
		,	ignoreInputs = [].join(',');
		for(var i = 0; i < autoUpdateBoxes.length; i++) {
			jQuery( autoUpdateBoxes[i] ).find('input[type=checkbox],input[type=radio],input[type=hidden],select').not( ignoreInputs ).change(function(){
				lcsMakeAutoUpdate();
			});
			jQuery( autoUpdateBoxes[i] ).find('input[type=text],textarea').not( ignoreInputs ).keyup(function(){
				lcsMakeAutoUpdate();
			});
		}
	}, 1000);
	jQuery(window).resize(function(){
		lcsAdjustChatEditTabs();
	});
	// Switch Off/Onn button
	lcsChatSettingsCheckSwitchActiveBtn();
	jQuery('.lcsChatSwitchActive').click(function(){
		var newActive = parseInt(jQuery( this ).data('active')) ? 0 : 1;
		jQuery.sendFormLcs({
			btn: this
		,	data: {mod: 'chat', action: 'switchActive', active: newActive, id: lcsChatEngine.id}
		,	onSuccess: function(res) {
				if(!res.error) {
					lcsChatSettingsCheckSwitchActiveBtn( newActive );
				}
			}
		});
		return false;
	});
	jQuery('#supsystic-breadcrumbs').bind('startSticky', function(){
		var currentPadding = parseInt(jQuery('#lcsChatMainControllsShell').css('padding-right'));
		jQuery('#lcsChatMainControllsShell').css('padding-right', currentPadding + 200).attr('data-padding-changed', 'padding is changed in admin.chat.edit.js');
	});
	jQuery('#supsystic-breadcrumbs').bind('stopSticky', function(){
		var currentPadding = parseInt(jQuery('#lcsChatMainControllsShell').css('padding-right'));
		jQuery('#lcsChatMainControllsShell').css('padding-right', currentPadding - 200);
	});
	// Change chat tpl btn
	_lcsInitChangeChatTplWnd();
	
	// Change show/hide parameters
	jQuery('.lcsSwitchShowHideOptLink').click(function(e){
		e.stopPropagation();
		jQuery(this).parents('.lcsMainOptLbl:first').find('.lcsSwitchShowHideOptLink').removeClass('active');
		jQuery(this).addClass('active');
		var inputName = jQuery(this).data('input-name')
		,	inputVal = jQuery(this).data('input-value');
		if(inputName) {
			jQuery('#lcsChatSettingsEditForm [name="'+ inputName+ '"]').val( inputVal );
			lcsSaveChatSettingsChanges( true );
		}
		return false;
	});
	var parsedShowHideInputNames = [];
	jQuery('.lcsSwitchShowHideOptLink').each(function(){
		var inputName = jQuery(this).data('input-name');
		if(toeInArray(inputName, parsedShowHideInputNames) === -1) {
			var inputVal = jQuery('#lcsChatSettingsEditForm [name="'+ inputName+ '"]').val();
			jQuery(this).parents('.lcsMainOptLbl:first').find('.lcsSwitchShowHideOptLink[data-input-value="'+ inputVal+ '"]').addClass('active');
			parsedShowHideInputNames.push( inputName );
		}
	});
	// Sound manipulations
	jQuery('.lcsAdminSetDefSoundBtn').click(function(){
		jQuery('#lcsChatSettingsEditForm [name="engine[params][sound]"]').val( lcsDefaultSound );
		lcsOnAdminSoundChange( lcsDefaultSound );
		return false;
	});
	// Code update functionality
	jQuery('.lcsChatCodeUpdateBtn').click(function(){
		var code = jQuery(this).data('code');
		jQuery.sendFormLcs({
			btn: this
		,	data: {mod: 'chat', action: 'updateCode', code: code, id: lcsChatTemplate.id}
		,	onSuccess: function(res){
				if(!res.error) {
					if(res.data.new_content) {	// Update code in editor
						jQuery('#lcsChatSettingsEditForm [name="tpl['+ code+ ']"]').val( res.data.new_content )
							.get(0).CodeMirrorEditor.setValue( res.data.new_content );
					}
				}
			}
		});
		return false;
	});
	// Time display settings manipulations
	jQuery('#lcsChatSettingsEditForm [name="engine[params][main][enb_show_time]"]').change(function(){
		if(jQuery(this).prop('checked')) {
			jQuery('.lcsTimeDisplayOptsShell').slideDown( g_lcsAnimationSpeed );
		} else {
			jQuery('.lcsTimeDisplayOptsShell').slideUp( g_lcsAnimationSpeed );
		}
	}).change();
	jQuery('.time-choosen').chosen({width: '90px'}); 
	// Date display settings manipulations
	jQuery('#lcsChatSettingsEditForm [name="engine[params][main][enb_show_date]"]').change(function(){
		if(jQuery(this).prop('checked')) {
			jQuery('.lcsDateDisplayOptsShell').slideDown( g_lcsAnimationSpeed );
		} else {
			jQuery('.lcsDateDisplayOptsShell').slideUp( g_lcsAnimationSpeed );
		}
	}).change();
	jQuery('#lcsChatSettingsEditForm').find('[name="engine[params][main][show_date_from]"],[name="engine[params][main][show_date_to]"]').datepicker();
	// Chat padding is not used - when it's located on center of the screen - let's hide it here
	jQuery('#lcsChatSettingsEditForm [name="engine[params][chat_position]"]').change(function(){
		toeInArrayLcs(jQuery(this).val(), ['top_center', 'bottom_center'])
			? jQuery('#lcsChatBarPaddingRow').slideUp( g_lcsAnimationSpeed )
			: jQuery('#lcsChatBarPaddingRow').slideDown( g_lcsAnimationSpeed );
	}).change();
	// Days display settings manipulations
	jQuery('#lcsChatSettingsEditForm [name="engine[params][main][enb_show_days]"]').change(function(){
		if(jQuery(this).prop('checked')) {
			jQuery('.lcsDaysDisplayOptsShell').slideDown( g_lcsAnimationSpeed );
		} else {
			jQuery('.lcsDaysDisplayOptsShell').slideUp( g_lcsAnimationSpeed );
		}
	}).change();
	jQuery(".supsystic-chat-settings .tooltipstered").removeAttr("title");
});
jQuery(window).load(function(){
	lcsAdjustChatEditTabs();
});
/**
 * Make chat edit tabs - responsive
 * @param {bool} requring is function - called in requring way
 */
function lcsAdjustChatEditTabs(requring) {
	var checkTabsNavs = ['#lcsChatSettingsEditTabs .nav-tab-wrapper:first'];
	for(var i = 0; i < checkTabsNavs.length; i++) {
		var tabs = jQuery(checkTabsNavs[i])
		,	delta = 10
		,	lineWidth = tabs.width() + delta
		,	fullCurrentWidth = 0
		,	currentState = '';	//full, text, icons

		if(!tabs.find('.lcs-edit-icon').is(':visible')) {
			currentState = 'text';
		} else if(!tabs.find('.lcsChatSettingsTabTitle').is(':visible')) {
			currentState = 'icons';
		} else {
			currentState = 'full';
		}

		tabs.find('.nav-tab').each(function(){
			fullCurrentWidth += jQuery(this).outerWidth();
		});
		if(fullCurrentWidth > lineWidth) {
			switch(currentState) {
				case 'full':
					tabs.find('.lcs-edit-icon').hide();
					lcsAdjustChatEditTabs(true);	// Maybe we will require to make it more smaller
					break;
				case 'text':
					tabs.find('.lcs-edit-icon').show().end().find('.lcsChatSettingsTabTitle').hide();
					break;
				default:
					// Nothing can do - all that can be hidden - is already hidden
					break;
			}
		} else if(fullCurrentWidth < lineWidth && (lineWidth - fullCurrentWidth > 400) && !requring) {
			switch(currentState) {
				case 'icons':
					tabs.find('.lcs-edit-icon').hide().end().find('.lcsChatSettingsTabTitle').show();
					break;
				case 'text':
					tabs.find('.lcs-edit-icon').show().end().find('.lcsChatSettingsTabTitle').show();
					break;
				default:
					// Nothing can do - all that can be hidden - is already hidden
					break;
			}
		}
	}
}
function _lcsInitChangeChatTplWnd() {
	// TODO: Make this more smart
	var wndWidth = jQuery(window).width() - 50
	,	wndHeight = jQuery(window).height() - 50;
	var $dialog = jQuery('#lcsChatDesignSelectWnd').dialog({
		modal:    true
	,	autoOpen: false
	,	width: wndWidth
	,	height: wndHeight
	,	buttons: {
			Cancel: function() {
				$dialog.dialog('close');
			}
		}
	});
	jQuery('#lcsChangeChatTplBtn').click(function(){
		$dialog.dialog('open');
		return false;
	});
	jQuery('.tpls-list-item .preset-select-btn').click(function(e){
		if(jQuery(this).hasClass('sup-promo')) {
			e.stopPropagation();
		} else {
			e.preventDefault();
		}
	});
	jQuery('.tpls-list-item.sup-promo').click(function(){
		toeRedirect(jQuery(this).find('.preset-select-btn').attr('href'), true);
	});
	jQuery('.tpls-list-item:not(.sup-promo)').click(function(){
		var currentTplId = parseInt( jQuery('#lcsChatSettingsEditForm [name="tpl[original_id]"]').val() )
		,	newTplId = parseInt( jQuery(this).data('id') );
		if(currentTplId != newTplId) {
			var tplName = jQuery(this).find('.lcsTplLabel').html();
			if(confirm(toeLangLcs('You you sure want to change you curent chat template - to "'+ tplName+ '" template?'))) {
				jQuery('.tpls-list-item')
					.removeClass('active')
					.find('.preset-select-btn').each(function(){
						jQuery(this).html( jQuery(this).data('txt') );
					});
				jQuery(this).addClass('active')
					.find('.preset-select-btn').each(function(){
						jQuery(this).html( jQuery(this).data('txt-active') );
					});
				jQuery.sendFormLcs({
					msgElID: jQuery(this).find('.lcsTplMsg')
				,	data: {mod: 'chat', action: 'changeTpl', new_tpl_id: newTplId, id: lcsChatTemplate.id}
				,	onSuccess: function(res) {
						if(!res.error) {
							toeReload();
						}
					}
				});
			}
		}
		return false;
	});
	var tplId = jQuery('#lcsChatSettingsEditForm [name="tpl[original_id]"]').val();
	jQuery('.tpls-list-item[data-id="'+ tplId+ '"]').addClass('active')
	.find('.preset-select-btn').each(function(){
		jQuery(this).html( jQuery(this).data('txt-active') );
	});
}
function lcsShowImgPrev(url, attach, buttonId) {
	var iter = jQuery('#'+ buttonId).data('iter');
	jQuery('.lcsBgImgPrev_'+ iter).attr('src', url);
}
function lcsSaveChatSettingsChanges(withoutPreviewUpdate) {
	// Triger save
	if(withoutPreviewUpdate)
		lcsSaveWithoutPreviewUpdate = true;
	jQuery('.lcsChatSaveBtn').click();
}
function lcsRefreshPreview() {
	document.getElementById('lcsChatTplPreviewFrame').contentWindow.location.reload();
}
function lcsChangedUpdate() {
	lcsMakeAutoUpdate( 0 );
}
function lcsMakeAutoUpdate(delay) {
	// TODO: Disabled for now, maybe need to enable this in future?
	return;
	delay = delay ? delay : 1500;
	if(lcsChatSaveTimeout)
		clearTimeout( lcsChatSaveTimeout );
	if(delay) {
		lcsChatSaveTimeout = setTimeout(lcsSaveChatSettingsChanges, delay);
	} else {
		lcsChatSaveTimeout = 0;
		lcsSaveChatSettingsChanges();
	}
}
function lcsBindTinyMceUpdate() {
	if(!lcsTinyMceEditorUpdateBinded && typeof(tinyMCE) !== 'undefined' && tinyMCE.editors && tinyMCE.editors.length) {
		for (var edId in tinyMCE.editors) {
			tinyMCE.editors[edId].onKeyUp.add(function(){
				lcsMakeAutoUpdate();
			});
		}
		lcsTinyMceEditorUpdateBinded = true;
	}
}
function lcsShowPreviewUpdating() {
	this._posSet;
	if(!this._posSet) {
		this._posSet = true;
		jQuery('#lcsChatTplPreviewUpdatingMsg').css({
			'left': 'calc(50% - '+ (jQuery('#lcsChatTplPreviewUpdatingMsg').width() / 2)+ 'px)'
		});
	}
	jQuery('#lcsChatTplPreviewFrame').css({
		'opacity': 0.5
	});
	jQuery('#lcsChatTplPreviewUpdatingMsg').slideDown( g_lcsAnimationSpeed );
}
function lcsHidePreviewUpdating() {
	jQuery('#lcsChatTplPreviewFrame').show().css({
		'opacity': 1
	});
	jQuery('#lcsChatTplPreviewUpdatingMsg').slideUp( 100 );
}

function lcsChatInitHideIpDlg() {
	var $container = jQuery('#lcsHideForIpWnd').dialog({
		modal:    true
	,	autoOpen: false
	,	width: 400
	,	height: 460
	,	buttons:  {
			OK: function() {
				_lcsChatHideIpMoveFromText();
				lcsSaveChatSettingsChanges();
				$container.dialog('close');
			}
		,	Cancel: function() {
				$container.dialog('close');
			}
		}
	});
	jQuery('#lcsHideForIpBtn').click(function(){
		_lcsPopupHideIpMoveToText();
		$container.dialog('open');
		return false;
	});
}
function _lcsChatHideIpMoveFromText(notUserOpen) {
	var ips = notUserOpen ? jQuery('#lcsChatSettingsEditForm').find('[name="engine[params][main][hide_for_ips]"]').val() : jQuery('#lcsHideForIpTxt').val()
	var ipsArr = [];
	if(ips) {
		ipsArr = ips.split(notUserOpen ? "," : "\n");
	}
	if(!ipsArr || !ipsArr.length)
		ipsArr = false;
	if(ipsArr) {
		jQuery.map(ipsArr, jQuery.trim);
	}
	if(!notUserOpen)
		jQuery('#lcsChatSettingsEditForm').find('[name="engine[params][main][hide_for_ips]"]').val( ipsArr ? ipsArr.join(',') : '' ).trigger('change');
	jQuery('#lcsHiddenIpStaticList').html(ipsArr 
		? ipsArr.length+ ' '+ toeLangLcs('IPs are blocked') 
		: toeLangLcs('No IPs are currently in block list'));
}
function _lcsPopupHideIpMoveToText() {
	var ips = jQuery('#lcsChatSettingsEditForm').find('[name="engine[params][main][hide_for_ips]"]').val()
	,	ipsArr = ips ? ips.split(",") : false;
	jQuery('#lcsHideForIpTxt').val(ipsArr ? ipsArr.join("\n") : '');
}
function lcsChatSettingsCheckSwitchActiveBtn( newActive ) {
	if(typeof(newActive) !== 'undefined') {
		jQuery('.lcsChatSwitchActive').data('active', newActive);
	}
	if(parseInt(jQuery('.lcsChatSwitchActive').data('active'))) {
		jQuery('.lcsChatSwitchActive .fa').removeClass('fa-toggle-on').addClass('fa-toggle-off');
		jQuery('.lcsChatSwitchActive span').html( jQuery('.lcsChatSwitchActive').data('txt-off') );
	} else {
		jQuery('.lcsChatSwitchActive .fa').removeClass('fa-toggle-off').addClass('fa-toggle-on');
		jQuery('.lcsChatSwitchActive span').html( jQuery('.lcsChatSwitchActive').data('txt-on') );
	}
}
function lcsShowTipScreenPopUp(link) {
	var $container = jQuery('<div style="display: none;" title="'+ toeLangLcs('How make Chat appear after click on your content link')+'" />')
	,	$img = jQuery('<img src="'+ jQuery(link).attr('href')+ '" />').load(function(){
		// Show popup after image was loaded - to make it's size according to image size
			var dialog = $container.dialog({
				modal: true
			,	width: this.width + 40
			,	height: this.height + 120
			,	buttons: {
					OK: function() {
						dialog.dialog('close');
					}
				}
			,	close: function() {
					dialog.remove();
				}
			});
	});
	$container.append( $img ).appendTo('body');
}
function lcsOnAdminSoundChange(audioUrl, attachment) {
	if(audioUrl && audioUrl != '') {
		var isSoundDefault = audioUrl == lcsDefaultSound;
		jQuery('.lcsAdminSoundLink')
			.show()
			.attr('href', audioUrl)
			.attr('title', audioUrl)
			.html( isSoundDefault ? toeLangLcs('Default') : audioUrl );
		if(isSoundDefault) {
			jQuery('.lcsAdminSetDefSoundBtn').hide();
		} else {
			jQuery('.lcsAdminSetDefSoundBtn').show();
		}
	} else {
		jQuery('.lcsAdminSoundLink').hide();
		jQuery('.lcsAdminSetDefSoundBtn').show();
	}
}