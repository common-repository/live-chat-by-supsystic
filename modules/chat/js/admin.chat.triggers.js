var g_lcsChatTriggersTbl = null;
jQuery(document).ready(function(){
	jQuery('.lcsChatTriggersAddBtn').click(function(){
		var clickConfirm = true;
		if(!jQuery(this).data('editing')) {
			lcsTriggersClearForm();
			lcsTriggersConditionAdd();	// Add one default row
			lcsTriggersShowAddForm();
		} else {
			if(confirm(toeLangLcs('Are you sure want to cancell your changes?'))) {
				lcsTriggersClearForm();
				lcsTriggersHideAddForm();
			} else {
				clickConfirm = false;
			}
		}
		if(clickConfirm) {
			_lcsTriggersCheckAddBtn( jQuery(this) );
		}
		return false;
	});
	jQuery('.lcsChatTriggersSaveBtn').click(function(){
		lcsSaveTriggerForm();
		return false;
	});
	lcsTriggersBuildTable();
	jQuery('.lcsChatTriggersConditions').sortable({
		revert: true
	,	handle: '.lcsMoveHandle'
	,	axis: 'y'
	,	items: '.lcsChatTriggersConditionRow'
	,	update: function() {
			lcsTriggersConditionUpdateIter();
		}
	});
	jQuery('.lcsChatTriggerActionCheck').change(function(){
		var enb = jQuery(this).prop('checked')
		,	code = jQuery(this).data('code')
		,	$optsShell = jQuery('#lcsChatTriggerAction_'+ code);
		if($optsShell && $optsShell.size()) {
			var $toActArrow = jQuery('#lcsTriggerToActionDataArrow_'+ code);
			if(enb) {
				$optsShell.slideDown( g_lcsAnimationSpeed );
				$toActArrow.slideDown( g_lcsAnimationSpeed );
			} else {
				$optsShell.slideUp( g_lcsAnimationSpeed );
				$toActArrow.slideUp( g_lcsAnimationSpeed );
			}
		}
	});
});
function lcsCheckSaveTriggerForm() {
	if(jQuery('.lcsChatTriggersForm').data('opened')) {
		lcsSaveTriggerForm();
	}
}
function lcsSaveTriggerForm() {
	var dataSend = jQuery('.lcsChatTriggersForm').serializeAnythingLcs({
		mod: 'chat', action: 'saveTrigger'
	});
	jQuery.sendFormLcs({
		btn: jQuery('.lcsChatTriggersSaveBtn')
	,	data: dataSend
	,	onSuccess: function(res) {
			if(!res.error && res.data.trigger) {
				if(lcsChatEngine.triggers && lcsChatEngine.triggers.length) {
					var found = false;
					for(var i = 0; i < lcsChatEngine.triggers.length; i++) {
						if(lcsChatEngine.triggers[ i ].id == res.data.trigger.id) {
							lcsChatEngine.triggers[ i ] = res.data.trigger;
							found = true;
							break;
						}
					}
					if(!found) {
						lcsChatEngine.triggers.push( res.data.trigger );
					}
				} else {
					lcsChatEngine.triggers = [ res.data.trigger ];
				}
				jQuery('.lcsChatTriggersForm input[name="trigger[id]"]').val( res.data.trigger.id );
				lcsTriggersBuildTable();
			}
		}
	});
}
function _lcsTriggersCheckAddBtn( $btn, forceEditing ) {
	$btn = $btn ? $btn : jQuery('.lcsChatTriggersAddBtn');
	if($btn.data('editing') && !forceEditing) {
		$btn.find('.lcsChatTriggersAddBtnTxt').show();
		$btn.find('.lcsChatTriggersCancellBtnTxt').hide();
		$btn.data('editing', 0);
	} else {
		$btn.find('.lcsChatTriggersAddBtnTxt').hide();
		$btn.find('.lcsChatTriggersCancellBtnTxt').show();
		$btn.data('editing', 1);
	}
	$btn.data('forceEdit', forceEditing ? 1 : 0);
}
function lcsTriggersShowAddForm() {
	jQuery('.lcsChatTriggersForm').slideDown( g_lcsAnimationSpeed, function(){
		if (jQuery(this).is(':visible'))
			jQuery(this).css('display','inline');
	}).data('opened', true);
}
function lcsTriggersHideAddForm() {
	jQuery('.lcsChatTriggersForm').slideUp( g_lcsAnimationSpeed ).data('opened', false);
}
function lcsTriggersClearForm() {
	lcsTriggersConditionsClear();
	var $form = jQuery('.lcsChatTriggersForm');
	$form.clearForm( true );
	$form.find('[name="trigger[id]"]').val('');
	lcsCheckUpdateArea( $form );
	$form.find('.lcsChatTriggerActionDataShell').hide();
	lcsTriggerSetEyeCatchImg('');
}
function lcsTriggersConditionGetParentRow(el) {
	return jQuery(el).parents('.lcsChatTriggersConditionRow:first');
}
function lcsTriggersConditionAddClk(btn) {
	lcsTriggersConditionAdd({
		addAfter: lcsTriggersConditionGetParentRow(btn)
	});
}
function lcsTriggersConditionRemoveClk(btn) {
	lcsTriggersConditionGetParentRow(btn).remove();
	lcsTriggersConditionsCheckRemove();
	lcsTriggersConditionUpdateIter();
}
function lcsTriggersConditionAdd(params) {
	params = params || {};
	var $shell = params.shell ? params.shell : jQuery('.lcsChatTriggersConditions')
	,	$exRow = params.exRow ? params.exRow : jQuery('.lcsChatTriggersConditions .lcsChatTriggersConditionExRow')
	,	$addAfter = params.addAfter ? params.addAfter : false
	,	$newRow = $exRow.clone().removeClass('lcsChatTriggersConditionExRow');
	
	$newRow.find('input,select').removeAttr('disabled');
	$addAfter ? $newRow.insertAfter( $addAfter ) : $shell.append( $newRow );
	if(params.data) {
		for(var key in params.data) {
			if(key == 'value') continue;	// Let's set value - after it's type will be initialized
			$newRow.find('[name*="['+ key+ ']"]').val( params.data[ key ] );
		}
	}
	lcsTriggersConditionsCheckRemove();
	lcsTriggersConditionChangeType($newRow.find('.lcsTriggersConditionsTypeSel:first').val(), $newRow, {
		updateIter: false
	});
	lcsTriggersConditionUpdateIter();
	if(params.data) {
		// Set Equal select value
		$newRow.find('[name*="[equal]"]').val( params.data.equal );
		// Set condition value itself
		var $valueInput = $newRow.find('[name*="[value]"]');
		if($valueInput && $valueInput.size()) {
			switch($valueInput.tagName()) {
				case 'SELECT':
					if(typeof(params.data.value) === 'array' || typeof(params.data.value) === 'object') {
						for(var i in params.data.value) {
							$valueInput.find('option[value="'+ params.data.value[ i ]+ '"]').attr('selected', 'selected');
						}
					} else
						$valueInput.find('option[value="'+ params.data.value+ '"]').attr('selected', 'selected');
					
					if($valueInput.hasClass('chosen-dynamic')) {
						$valueInput.trigger('chosen:updated');
					}
					break;
				default:
					if(typeof(params.data.value) === 'array' || typeof(params.data.value) === 'object') {
						for(var i in params.data.value) {
							$valueInput.filter('[name*="[value]['+ i+ ']"]').val( params.data.value[ i ] );
						}
					} else
						$valueInput.val( params.data.value );
					
					break;
			}
		}
	}
}
function lcsTriggersConditionsGetRows() {
	return jQuery('.lcsChatTriggersConditions .lcsChatTriggersConditionRow:not(.lcsChatTriggersConditionExRow)');
}
function lcsTriggersConditionsClear() {
	lcsTriggersConditionsGetRows().remove();
}
function lcsTriggersConditionsCheckRemove() {
	var $rows = lcsTriggersConditionsGetRows()
	,	condNum = $rows.size();
	if(condNum > 1) {
		$rows.find('.lcsChatTriggersRemoveCondition').show();
	} else {
		$rows.find('.lcsChatTriggersRemoveCondition').hide();
	}
}
function lcsTriggersConditionChangeTypeClk(select, event) {
	var $select = jQuery(select)
	,	value = $select.val()
	,	typeData = lcsConditionTypes[ value ];
	if(!LCS_DATA.isPro && typeData && typeData['code'].indexOf('-pro') !== -1) {
		var current = $select.data('current');
		if(!current) {
			current = $select.find('option:first').val();
		}
		$select.val( current );
		lcsProOptChangedClb(event, $select);
		return false;
	}
	$select.data('current', value);
	lcsTriggersConditionChangeType(value, lcsTriggersConditionGetParentRow(select));
}
function lcsTriggersConditionChangeType(type, $parentRow, params) {
	params = params || {};
	// Show only available equals and values for this condition type
	var showSelector = []
	,	typeData = lcsConditionTypes[ type ]
	,	$equals = $parentRow.find('.lcsTriggersConditionsEqualSel:first')
	,	$options = $equals.find('option');
	$options.hide();
	
	for(var id in lcsConditionEquals) {
		if(toeInArrayLcs(lcsConditionEquals[ id ].code, typeData.equals)) {
			showSelector.push( id );
		}
	}
	for(var i = 0; i < showSelector.length; i++) {
		$options.filter('[value="'+ showSelector[ i ]+ '"]').show();
	}
	$equals.val( showSelector[ 0 ] );
	
	var $valCell = $parentRow.find('.lcsTriggerConditionValCell');
	$valCell.html('&nbsp;');
	var valueSelector = false;
	
	switch(typeData.code) {
		case 'pages_posts':
			valueSelector = '#lcsTriggerCondValue_pages_posts';
			break;
		case 'country':
			valueSelector = '#lcsTriggerCondValue_country';
			break;
		case 'day_hour':
			valueSelector = '#lcsTriggerCondValue_hour';
			break;
		case 'week_day':
			valueSelector = '#lcsTriggerCondValue_week_day';
			break;
		case 'page_url':
			valueSelector = '#lcsTriggerCondValue_txt';
			break;
		case 'time_on_page':
			valueSelector = '#lcsTriggerCondValue_time_on_page';
			break;
		case 'agent_online':
			// Do nothing - here we will collect bool types for example
			break;
	}
	if(valueSelector) {
		var $newValueHtml = jQuery( valueSelector ).clone().removeAttr('id');
		$valCell.html( $newValueHtml );
		var $chosenSels = $valCell.find('.chosen-dynamic');
		if($chosenSels && $chosenSels.size()) {
			$chosenSels.chosen();
		}
		if(params.updateIter !== false) {
			lcsTriggersConditionUpdateIter( lcsTriggersConditionGetParentRow($valCell) );
		}
	}
}
function lcsTriggersConditionUpdateIter($rows) {
	var iter = 0
	,	updateAll = true;
	if($rows) {
		updateAll = false;
	} else {
		$rows = lcsTriggersConditionsGetRows();
	}
	$rows.each(function(){
		if(!updateAll) {
			iter = jQuery(this).data('iter');
		} else {
			jQuery(this).data('iter', iter);
		}
		jQuery(this).find('input,select').attr('name', function(i, name){
			if(!name) return name;
			return name.replace(/\[\d+\]/, '['+ iter+ ']');
		});
		iter++;
	});
}
function lcsTriggersEditClk( id ) {
	var trigger = false;
	if(lcsChatEngine.triggers && lcsChatEngine.triggers.length) {
		for(var i = 0; i < lcsChatEngine.triggers.length; i++) {
			if(lcsChatEngine.triggers[ i ].id == id) {
				trigger = lcsChatEngine.triggers[ i ];
				break;
			}
		}
	}
	if(trigger) {
		_lcsTriggersCheckAddBtn(false, true);
		lcsTriggersClearForm();
		var $form = jQuery('.lcsChatTriggersForm');
		$form.find('[name="trigger[label]"]').val( trigger.label );
		$form.find('[name="trigger[id]"]').val( trigger.id );
		
		if(trigger.conditions && trigger.conditions.length) {
			var addConditionParams = {
				shell: jQuery('.lcsChatTriggersConditions')
			,	exRow: jQuery('.lcsChatTriggersConditions .lcsChatTriggersConditionExRow')
			};
			for(var i = 0; i < trigger.conditions.length; i++) {
				addConditionParams.data = trigger.conditions[ i ];
				lcsTriggersConditionAdd( addConditionParams );
			}
		}
		if(trigger.actions) {
			for(var i in trigger.actions) {
				for(var k in trigger.actions[ i ]) {
					var $field = $form.find('[name="trigger[actions]['+ trigger.actions[ i ].id+ ']['+ k+ ']"]')
					,	value = trigger.actions[ i ][ k ];
					if($field && $field.size()) {
						switch($field.attr('type')) {
							case 'checkbox':
								$field.prop('checked', 'checked').trigger('change');
								break;
							default:
								$field.val( value );
								break;
						}
					}
				}
				if(trigger.actions[ i ].code == 'show_eye_cach') {
					lcsTriggerSetEyeCatchImg(trigger.actions[ i ].eye_img);
				}
			}
		}
		lcsCheckUpdateArea( $form );
		lcsTriggersShowAddForm();
	}
}
function lcsTriggersRemoveClk( id, btn ) {
	if(confirm(toeLangLcs('Are you sure want to remove this trigger?'))) {
		jQuery.sendFormLcs({
			btn: btn
		,	data: {mod: 'chat', action: 'removeTrigger', id: id}
		,	onSuccess: function(res) {
				if(!res.error) {
					if(lcsChatEngine.triggers && lcsChatEngine.triggers.length) {
						for(var i = 0; i < lcsChatEngine.triggers.length; i++) {
							if(lcsChatEngine.triggers[ i ].id == id) {
								lcsChatEngine.triggers.splice(i, 1);
								if(jQuery('.lcsChatTriggersForm [name="trigger[id]"]').val() == id) {
									lcsTriggersClearForm();
									lcsTriggersHideAddForm();
								}
								break;
							}
						}
					}
					lcsTriggersBuildTable();
				}
			}
		});
	}
}
function lcsTriggersToggleActiveClk( id, btn ) {
	var $btn = jQuery(btn)
	,	active = parseInt($btn.attr('data-active'));
	jQuery.sendFormLcs({
		btn: btn
	,	data: {mod: 'chat', action: 'toggleTrigger', id: id, active: active}
	,	onSuccess: function(res) {
			if(!res.error) {
				$btn
					.attr('data-active', active ? 0 : 1)
					.attr('title', active ? $btn.data('txt-off') : $btn.data('txt-on'));
				if(active) {
					$btn.find('.fa').removeClass('fa-toggle-on').addClass('fa-toggle-off');
				} else {
					$btn.find('.fa').addClass('fa-toggle-on').removeClass('fa-toggle-off');
				}
				if(lcsChatEngine.triggers && lcsChatEngine.triggers.length) {
					for(var i = 0; i < lcsChatEngine.triggers.length; i++) {
						if(lcsChatEngine.triggers[ i ].id == id) {
							lcsChatEngine.triggers[i].active = active ? 0 : 1;
							break;
						}
					}
				}
			}
		}
	});
}
function lcsTriggersBuildTable() {
	var tblId = 'lcsChatTriggersTbl'
	,	triggersTblData = [];
	if(lcsChatEngine.triggers && lcsChatEngine.triggers.length) {
		for(var i = 0; i < lcsChatEngine.triggers.length; i++) {
			var t = lcsChatEngine.triggers[ i ];
			// Yea, bad, really bad, but fast way to include those buttons here..............
			t['btn_actions'] = '<a href="#" onclick="lcsTriggersEditClk('+ lcsChatEngine.triggers[ i ].id+ '); return false;" class="button"><i class="fa fa-pencil"></i></a>'
				+ '&nbsp;<a href="#" onclick="lcsTriggersToggleActiveClk('+ lcsChatEngine.triggers[ i ].id+ ', this); return false;" '
					+ 'class="button" '
					+ 'data-txt-on="'+ toeLangLcs('Enabled')+ '" '
					+ 'data-txt-off="'+ toeLangLcs('Disabled')+ '" '
					+ 'data-active="'+ lcsChatEngine.triggers[ i ].active+ '" '
					+ 'title="'+ (parseInt(lcsChatEngine.triggers[ i ].active) ? toeLangLcs('Enabled') : toeLangLcs('Disabled'))+ '">'
						+ '<i class="fa '+ (parseInt(lcsChatEngine.triggers[ i ].active) ? 'fa-toggle-on' : 'fa-toggle-off')+ '"></i></a>'
				+ '&nbsp;<a href="#" onclick="lcsTriggersRemoveClk('+ lcsChatEngine.triggers[ i ].id+ ', this); return false;" class="button"><i class="fa fa-trash-o"></i></a>';
			triggersTblData.push( t );
		}
	}
	if(g_lcsChatTriggersTbl) {
		jQuery('#'+ tblId).jqGrid('clearGridData')
		.setGridParam({
			data: triggersTblData
		,	datatype: 'local'
		}).trigger('reloadGrid');
		return;
	}
	g_lcsChatTriggersTbl = jQuery('#'+ tblId).jqGrid({
		data: triggersTblData
	,	datatype: 'local'
	,	colNames:[toeLangLcs('ID'), toeLangLcs('Name'), toeLangLcs('Action')]
	,	colModel:[
			{name: 'id', index: 'id', searchoptions: {sopt: ['eq']}, width: '30', align: 'center'}
		,	{name: 'label', index: 'label', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'btn_actions', index: 'btn_actions', /*searchoptions: {sopt: ['eq']},*/ align: 'center'}
		]
	,	rowNum: 30
	,	rowList: [10, 20, 30, 1000]
	,	pager: '#'+ tblId+ 'Nav'
	,	sortname: 'id'
	,	viewrecords: true
	,	sortorder: 'desc'
	,	caption: toeLangLcs('Current Trigger')
	,	height: '100%'
	,	width: '680'
	,	emptyrecords: toeLangLcs('You have no Triggers for now.')
	/*,	multiselect: true
	,	onSelectRow: function(rowid, e) {
			var tblId = jQuery(this).attr('id')
			,	selectedRowIds = jQuery('#'+ tblId).jqGrid ('getGridParam', 'selarrrow')
			,	totalRows = jQuery('#'+ tblId).getGridParam('reccount')
			,	totalRowsSelected = selectedRowIds.length;
			if(totalRowsSelected) {
				jQuery('#'+ tblId+ 'RemoveGroupBtn').removeAttr('disabled');
				if(totalRowsSelected == totalRows) {
					jQuery('#cb_'+ tblId).prop('indeterminate', false);
					jQuery('#cb_'+ tblId).attr('checked', 'checked');
				} else {
					jQuery('#cb_'+ tblId).prop('indeterminate', true);
				}
			} else {
				jQuery('#'+ tblId+ 'RemoveGroupBtn').attr('disabled', 'disabled');
				jQuery('#cb_'+ tblId).prop('indeterminate', false);
				jQuery('#cb_'+ tblId).removeAttr('checked');
			}
			lcsCheckUpdateArea('#gview_'+ tblId);
		}
	,	gridComplete: function(a, b, c) {
			var tblId = jQuery(this).attr('id');
			jQuery('#'+ tblId+ 'RemoveGroupBtn').attr('disabled', 'disabled');
			jQuery('#cb_'+ tblId).prop('indeterminate', false);
			jQuery('#cb_'+ tblId).removeAttr('checked');
			// Custom checkbox manipulation
			lcsInitCustomCheckRadio('#gview_'+ tblId);
			lcsCheckUpdateArea('#gview_'+ tblId);
		}*/
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
}
function lcsTriggerSetEyeCatchImgClk(attachmentUrl, attachment, buttonId) {
	lcsTriggerSetEyeCatchImg(attachmentUrl);
}
function lcsTriggerSetEyeCatchImg(url) {
	var $img = jQuery('.lcsCahtTriggerEyeCachImg');
	$img.attr( 'src', url );
	//jQuery('.lcsChatTriggerEyeImgUrl').val( url );
	if(!url || url == '') {
		$img.hide();
	} else {
		$img.show();
	}
}