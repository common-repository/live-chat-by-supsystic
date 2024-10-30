var g_lcsRegFieldsFrame = {
	_$editWnd: null
,	_$editRow: null
,	_$tbl: null
,	_fields: {
		enb: {html: 'hidden', htmlInTable: 'checkbox'}
	,	name: {html: 'text', mandatory: 1}
	,	label: {html: 'text', mandatory: 1}
	,	value: {html: 'text'}
	,	html: {html: 'text'}	// TODO: Will be selectbox for PRO version
	,	mandatory: {html: 'checkbox'}
	}
,	_$rows: []
,	_isNew: false
,	init: function() {
		this._$tbl = jQuery('#lcsRegFieldsTbl')
		var	$regFieldsExRow = this._$tbl.find('#lcsRegExRow');
		$regFieldsExRow.find('input,select').attr('disabled', 'disabled');
		if(lcsChatEngine.params.reg_fields) {
			for(var i = 0; i < lcsChatEngine.params.reg_fields.length; i++) {
				this._storeFieldToTable({
					$regFieldsExRow: $regFieldsExRow
				,	data: lcsChatEngine.params.reg_fields[ i ]
				});
			}
			this._updateSortOrder();
		}
		this._initTablSortable();
		this._initEditWnd();
		if(!LCS_DATA.isPro) {
			g_lcsRegFieldsFrame._$editWnd.find('[name="html"]').change(function(e){
				var value = jQuery(this).val();
				if(value && value.indexOf('-pro') !== -1) {
					jQuery(this).val( jQuery(this).data('current') );
					lcsProOptChangedClb(e, this);
					return false;
				}
				jQuery(this).data('current', value);
			}).data('current', 'text');
		}
		jQuery(document).trigger('lcsAfterRegEditInit');
	}
,	_initTablSortable: function() {
		var self = this;
		this._$tbl.sortable({
			revert: true
		,	handle: '.lcsMoveHandle'
		,	axis: 'y'
		,	items: '.lcsRegFieldRow'
		,	update: function() {
				self._updateSortOrder();
			}
		});
	}
,	_initEditWnd: function() {
		var self = this;
		this._$editWnd = jQuery('#lcsRegFieldSettingsWnd').dialog({
			modal:    true
		,	autoOpen: false
		,	width: 460
		,	height: 640
		,	buttons: {
				'Ok': function() {
					if(self._saveFieldWnd()) {
						self._$editWnd.dialog('close');
					}
					self._isNew = false;
				}
			,	'Cancel': function() {
					self._$editWnd.dialog('close');
					self._isNew = false;
				}
			}
		});
	}
,	clearEditFieldWnd: function() {
		this._$editWnd.clearForm( true );
		this._$editWnd.find('#lcsRegFieldSelectOptsShell .lcsRegFieldSelectOptShell:not(#lcsRegFieldSelectOptShellExl)').remove();
		lcsCheckUpdateArea( this._$editWnd );
	}
,	showEditFieldWnd: function( $row ) {
		this._$editRow = $row;
		
		this.clearEditFieldWnd();
		if(this._$editRow) {
			for(var k in this._fields) {
				var $input = this._$editWnd.find('[name="'+ k+ '"]');
				if(!$input.size()) continue;
				switch(this._fields[ k ].html) {
					case 'checkbox':
						parseInt(this._$editRow.find('[name*="['+ k+ ']"]').val())
							? $input.attr('checked', 'checked')
							: $input.removeAttr('checked');
						break;
					case 'selectbox': case 'text': default:
						$input.val( this._$editRow.find('[name*="['+ k+ ']"]').val() );
						break;
				}
			}
			this._afterFillDataToEditWnd( this._$editRow );
		}
		this._$editWnd.find('[name="html"]').trigger('change');
		lcsCheckUpdateArea( this._$editWnd );
		this._$editWnd.dialog('open');
	}
,	_afterFillDataToEditWnd: function( $row ) {
		// To re-definition
	}
,	_updateSortOrder: function() {
		var $rows = this._$tbl.find('.lcsRegFieldRow:not(#lcsRegExRow)')
		,	i = 0
		,	self = this;
		this._$rows = [];
		$rows.each(function(){
			var $inputs = jQuery(this).find('[name^="engine[params][reg_fields]"]');
			$inputs.each(function(){
				var name = jQuery(this).attr('name');
				jQuery(this).attr('name', name.replace(/(\[reg_fields\]\[\]|\[reg_fields\]\[\d+\])/g, '[reg_fields]['+ i+ ']'));
			});
			self._$rows.push( jQuery(this) );
			i++;
		});
	}
,	_storeFieldToTable: function( params ) {
		var update = params.update && this._$editRow;
		var data = params.data ? params.data : false
		,	$row = null;

		if(update) {
			$row = this._$editRow;
		} else {
			var $regFieldsExRow = params.$regFieldsExRow ? params.$regFieldsExRow : this._$tbl.find('#lcsRegExRow');
			lcsCheckDestroyArea( $regFieldsExRow );
			$row = $regFieldsExRow.clone().removeAttr('id');
		}
		if(data) {
			for(var k in this._fields) {
				if(typeof(data[ k ]) === 'undefined' && this._fields[ k ].html !== 'checkbox') continue;
				var $input = $row.find('[name*="['+ k+ ']"]')
				,	value = data[ k ];
				switch(this._fields[ k ].htmlInTable) {
					case 'checkbox':
						parseInt(value)
							? $input.attr('checked', 'checked')
							: $input.removeAttr('checked');
						break;
					case 'selectbox': case 'text': default:
						$input.val( value );
						break;
				}
			}
			this._afterStoreFieldsDataToTable( $row, data );
		}
		if(!update) {
			$row.find('input,select').removeAttr('disabled');
			$row.appendTo( this._$tbl );
			lcsInitCustomCheckRadio( $row );
		}
		lcsCheckUpdateArea( $row );
	}
,	_afterStoreFieldsDataToTable: function( $row, data ) {
		// To re-definition
	}
,	_saveFieldWnd: function() {
		var fieldData = this._$editWnd.serializeAnythingLcs(false, true);
		if(this._isNew) {
			fieldData.enb = 1;	// New fields enabled by default
		}
		this._storeFieldToTable({
			data: fieldData
		,	update: true
		});
		this._updateSortOrder();
		this._$editRow = null;
		return true;	// TODO: Add validation and false result here
	}
,	removeFieldRow: function( $row ) {
		var label = $row.find('[name*="[label]"]').val();
		if(confirm(toeLangLcs('Are you sure want to remove field '+ label+ '?'))) {
			$row.animateRemoveLcs();
		}
	}
};
jQuery(document).ready(function(){
	g_lcsRegFieldsFrame.init();
	jQuery('#lcsChatSettingsEditForm [name="engine[params][reg_type]"]').change(function(){
		if(!jQuery(this).prop('checked')) return;
		if(jQuery(this).val() == 'none') {
			jQuery('#lcsChatSettingsRegistrationShell').slideUp( g_lcsAnimationSpeed );
		} else {
			jQuery('#lcsChatSettingsRegistrationShell').slideDown( g_lcsAnimationSpeed );
		}
	}).change();
	lcsInitRegFieldsPromoPopup();
});
function _lcsShowRegFieldSettingsWndClk(btn) {
	g_lcsRegFieldsFrame.showEditFieldWnd( jQuery(btn).parents('.lcsRegFieldRow:first') );
}
function _lcsRemoveRegFieldClk(btn) {
	g_lcsRegFieldsFrame.removeFieldRow( jQuery(btn).parents('.lcsRegFieldRow:first') );
}
function lcsInitRegFieldsPromoPopup() {
	if(!LCS_DATA.isPro) {
		var $proOptWnd = jQuery('#lcsRegAddFieldWnd').dialog({
			modal:    true
		,	autoOpen: false
		,	width: 580
		,	height: 580
		,	buttons: {
				'Get It': function() {
					window.open( $proOptWnd.find('.lcsPromoImgUrl').attr('href') );
					$proOptWnd.dialog('close');
				}
			,	'Cancel': function() {
					$proOptWnd.dialog('close');
				}
			}
		});
		jQuery('#lcsRegAddFieldBtn').click(function(){
			$proOptWnd.dialog('open');
			return false;
		});
	}
}