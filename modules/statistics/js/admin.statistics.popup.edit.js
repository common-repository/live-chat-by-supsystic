var g_lcsCurrentPlot = null
,	g_lcsCurrentChartType = ''
,	g_lcsPieAllActionDone = false
,	g_lcsPieAllShareDone = false
,	g_lcsCurrentStats = []
,	g_lcsCurrentStatGroup = 'day'
,	g_tblDataToDate = {};
jQuery(document).ready(function(){
	jQuery('.lcsPopupStatChartTypeBtn').click(function(){
		lcsUpdatePopupStatsGraph( jQuery(this).data('type') );
		return false;
	});
	jQuery('#lcsPopupStatClear').click(function(){
		if(confirm(toeLangLcs('Are you sure want to clear all PopUp Statistics?'))) {
			jQuery.sendFormLcs({
				btn: this
			,	data: {mod: 'statistics', action: 'clearForPopUp', id: jQuery(this).data('id')}
			,	onSuccess: function(res) {
					if(!res.error) {
						toeReload();
					}
				}
			});
		}
		return false;
	});
	jQuery('#lcsPopupEditForm').find('input[name=stat_from_txt],input[name=stat_to_txt]').datepicker({
		onSelect: function() {
			jQuery('#lcsPopupStatClearDateBtn').show();
			lcsPopupStatUpdateGraphRange();
		}
	});
	jQuery('#lcsPopupStatClearDateBtn').click(function(){
		jQuery('#lcsPopupEditForm').find('input[name=stat_from_txt],input[name=stat_to_txt]').val('');
		lcsPopupStatUpdateGraphRange();
		jQuery(this).hide();
		return false;
	});
	lcsPopupStatSetGoup( lcsPopupStatGetGoup() );
	jQuery('[data-stat-group]').click(function(){
		lcsPopupStatChangeGroup( jQuery(this).data('stat-group'), this );
		return false;
	});
	jQuery('.lcsPopupStatGraphZoomReset').click(function(){
		if(g_lcsCurrentPlot) {
			g_lcsCurrentPlot.resetZoom();
			jQuery(this).hide();
		}
		return false;
	});
	jQuery('#lcsPopupStatExportCsv').click(function(){
		var baseUrl = '';
		if(jQuery(this).data('base-url')) {
			baseUrl = jQuery(this).data('base-url');
		} else {
			baseUrl = jQuery(this).attr('href');
			jQuery(this).data('base-url', baseUrl);
		}
		
		jQuery(this).attr('href', baseUrl+ '&group='+ lcsPopupStatGetGoup());
	});
});
function lcsPopupStatChangeGroup(newGroup, btn) {
	var btnName = jQuery(btn).html();
	jQuery(btn).html('<i class="fa fa-spinner fa-spin"></i>');
	jQuery.sendFormLcs({
		btn: btn
	,	data: {mod: 'statistics', action: 'getUpdatedStats', group: newGroup, id: lcsPopup.id}
	,	onSuccess: function(res) {
			jQuery(btn).html( btnName );	
			if(!res.error) {
				lcsPopupStatSetGoup( newGroup );
				lcsPopupAllStats = res.data.stats;
				lcsUpdatePopupStatsGraph(false, {force: true});
			}
		}
	});
}
function lcsPopupStatGetGoup() {
	var savedValue = getCookieLcs('lcs_stat_group');
	return savedValue && savedValue != '' ? savedValue : 'day';
}
function lcsPopupStatSetGoup(group) {
	jQuery('[data-stat-group]').removeClass('focus');
	jQuery('[data-stat-group="'+ group+ '"]').addClass('focus');
	setCookieLcs('lcs_stat_group', group);
}
function lcsPopupStatUpdateGraphRange() {
	if(g_lcsCurrentPlot) {
		lcsUpdatePopupStatsGraph(false, {force: true});
	}
}
function lcsPopupStatGetChartType() {
	var savedValue = getCookieLcs('lcs_chart_coockie');
	return savedValue && savedValue != '' ? savedValue : 'line';
}
function lcsPopupStatSetChartType(type) {
	jQuery('.lcsPopupStatChartTypeBtn').removeClass('focus');
	jQuery('.lcsPopupStatChartTypeBtn[data-type="'+ type+ '"]').addClass('focus');
	setCookieLcs('lcs_chart_coockie', type);
}
function lcsDrawPopupCharts() {
	lcsUpdatePopupStatsGraph();
	lcsUpdateAllActionChart();
	lcsUpdateAllShareChart();
}
function lcsUpdateAllActionChart() {
	if(!g_lcsPieAllActionDone) {
		if(typeof(lcsPopupAllStats) != 'undefined') {
			var	plotDataByCode = {}
			,	haveData = false;
			for(var i = 0; i < lcsPopupAllStats.length; i++) {
				if(lcsPopupAllStats[i]['points'] 
					&& lcsPopupAllStats[i]['points'].length 
					&& !toeInArrayLcs(lcsPopupAllStats[i]['code'], ['show'])	// make sure - this was exactly action, not like just display
				) {
					var labelCode = lcsPopupAllStats[i].label.replace(/\W+/g, "_");
						plotDataByCode[ labelCode ] = {label: lcsPopupAllStats[i].label, total: 0};
					for(var j = 0; j < lcsPopupAllStats[i]['points'].length; j++) {
						plotDataByCode[ labelCode ].total += parseInt(lcsPopupAllStats[ i ]['points'][ j ]['total_requests']);
					}
					haveData = true;
				}
			}
			if(haveData) {
				var plotData = [];
				for(var code in plotDataByCode) {
					plotData.push([ plotDataByCode[code].label, plotDataByCode[code].total ]);
				}
				jQuery.jqplot ('lcsPopupStatAllActionsPie', [ plotData ], {
					seriesDefaults: {
						renderer: jQuery.jqplot.PieRenderer
					,	rendererOptions: {
							showDataLabels: true
						}
					}
				,	legend: { show:	true, location: 'e' }
				});
			} else {
				jQuery('#lcsPopupStatAllActionsNoData').show();
			}
		}
		g_lcsPieAllActionDone = true;
	}
}
function lcsUpdateAllShareChart() {
	if(!g_lcsPieAllShareDone) {
		if(typeof(lcsPopupAllShareStats) != 'undefined') {
			var plotData = [];
			for(var i = 0; i < lcsPopupAllShareStats.length; i++) {
				if(lcsPopupAllShareStats[i].sm_type) {
					plotData.push([ lcsPopupAllShareStats[i].sm_type.label, parseInt(lcsPopupAllShareStats[i].total_requests) ]);
				}
			}
			if(plotData.length) {
				jQuery.jqplot ('lcsPopupStatAllSharePie', [ plotData ], {
					seriesDefaults: {
						renderer: jQuery.jqplot.PieRenderer
					,	rendererOptions: {
							showDataLabels: true
						}
					}
					,	legend: { show:	true, location: 'e' }
				});
			} else {
				jQuery('#lcsPopupStatAllShareNoData').show();
			}
		} else
			jQuery('#lcsPopupStatAllShareNoData').show();
		g_lcsPieAllShareDone = true;
	}
}
function lcsUpdatePopupStatsGraph(chartType, params) {
	if(typeof(lcsPopupAllStats) != 'undefined') {
		params = params || {};
		chartType = chartType ? chartType : lcsPopupStatGetChartType();
		if(g_lcsCurrentChartType == chartType && !params.force) {
			// Just switching tabs - no need to redraw if it is already drawn
			return;
		}
		lcsPopupStatSetChartType( chartType );
		g_lcsCurrentChartType = chartType;
		var plotData = []
		,	seriesKeys = {}
		,	series = []
		,	plotParams = {}
		,	dateFrom = false
		,	dateTo = false
		,	firstInit = false
		,	group = lcsPopupStatGetGoup();	// Hour, Day, Week, Month
		
		if(g_lcsCurrentPlot) {
			dateFrom = jQuery('#lcsPopupEditForm').find('input[name=stat_from_txt]').val()
		,	dateTo = jQuery('#lcsPopupEditForm').find('input[name=stat_to_txt]').val();
			
			dateFrom = dateFrom && dateFrom != '' ? lcsStrToMs(dateFrom) : false;
			dateTo = dateTo && dateTo != '' ? lcsStrToMs(dateTo) : false;
		}
		var hourMs = 60 * 60 * 1000
		,	dayMs = 24 * hourMs
		,	weekMs = 7 * dayMs
		,	monthMs = 30 * dayMs;
		g_lcsCurrentStats = [];
		var hasPoints = (dateFrom || dateTo) ? false : true;	// If date not set by user - points will be available in any case
		for(var i = 0; i < lcsPopupAllStats.length; i++) {
			var currentData = jQuery.extend( true, {}, lcsPopupAllStats[ i ] );
			if(lcsPopupAllStats[i]['points'] && lcsPopupAllStats[i]['points'].length && (dateFrom || dateTo)) {
				currentData['points'] = [];
				for(var j = 0; j < lcsPopupAllStats[i]['points'].length; j++) {
					var currentDate = lcsStrToMs( lcsPopupAllStats[i]['points'][j]['date'] );
					if((dateFrom 
						&& (currentDate < dateFrom 
							&& !(group == 'week' && currentDate + weekMs > dateFrom)
							&& !(group == 'month' && currentDate + monthMs > dateFrom)
							))
						|| (dateTo 
						&& (currentDate > dateTo))
					) {
						continue;
					}
					currentData['points'].push( lcsPopupAllStats[i]['points'][j] );
					hasPoints = true;
				}
			}
			g_lcsCurrentStats[ i ] = currentData;
		}
		// We re-calculated data - so rebuild txt table
		lcsPopupStatRebuildTbl();
		if(g_lcsCurrentPlot) {
			g_lcsCurrentPlot.destroy();
		} else {
			firstInit = true;
		}
		if(!hasPoints)
			return;
		switch(chartType) {
			case 'bar':
				var ticksKeys = {}
				,	ticks = []
				,	tickId = 0
				,	sortByDateClb = function(a, b) {
					var aTime = ( new Date( str_replace((typeof(a) === 'string' ? a : a.date), '-', '/') ) ).getTime()	// should be no "-" as ff make it Date.parse() in incorrect way
					,	bTime = ( new Date( str_replace((typeof(b) === 'string' ? b : b.date), '-', '/') ) ).getTime();
					if(aTime > bTime)
						return 1;
					if(aTime < bTime)
						return -1;
					return 0;
				}
				,	plotDataToDate = [];
				for(var i = 0; i < g_lcsCurrentStats.length; i++) {
					if(g_lcsCurrentStats[i]['points'] && g_lcsCurrentStats[i]['points'].length) {
						plotDataToDate.push({});
						for(var j = g_lcsCurrentStats[i]['points'].length - 1; j >= 0; j--) {
							ticksKeys[ g_lcsCurrentStats[ i ]['points'][ j ]['date'] ] = 1;
							plotDataToDate[ tickId ][ g_lcsCurrentStats[ i ]['points'][ j ]['date'] ] = parseInt(g_lcsCurrentStats[ i ]['points'][ j ]['total_requests']);
						}
						seriesKeys[ tickId ] = g_lcsCurrentStats[i].label;
						tickId++;
					}
				}
				for(var key in ticksKeys) {
					ticks.push( key );
				}
				ticks.sort( sortByDateClb );
				tickId = 0;
				for(var i = 0; i < plotDataToDate.length; i++) {
					plotData.push([]);
					for(var j in ticks) {
						var dateStr = ticks[ j ];
						plotData[ tickId ].push( typeof(plotDataToDate[i][dateStr]) === 'undefined' ? 0 : plotDataToDate[i][dateStr] );
					}
					tickId++;
				}
				for(var i in seriesKeys) {
					series.push({label: seriesKeys[ i ]});
				}
				var tickFormat = lcsPopupStatGetDateFormat();
				for(var i in ticks) {
					var tickDate = new Date(lcsStrToMs(ticks[i]));
					ticks[ i ] = tickDate.format( tickFormat );	// Format ticks date
				}
				plotParams = {
					seriesDefaults:{
						renderer: jQuery.jqplot.BarRenderer
					,	rendererOptions: {fillToZero: true}
					,	pointLabels: { 
							show: true 
						}
					}
				,	series: series
				,	legend: { show:	true, location: 'ne', placement : 'outsideGrid' }
				,	axes: {
						xaxis: {
							renderer: jQuery.jqplot.CategoryAxisRenderer
						,	ticks: ticks
						},
						yaxis: {
							pad: 1.05
						,	tickOptions: {
								formatString: '%d'
							}
						}
					}
				,	highlighter: {
						show: true
					,	sizeAdjust: 3
					,	tooltipLocation: 'n'
					,	tooltipContentEditor: function(str, seriesIndex, pointIndex, jqPlot) {
							if(seriesKeys[ seriesIndex ]) {
								if(strpos(str, ',')) {
									str = str.split(',');
									str = str[1] ? str[1] : str[0];
									str = jQuery.trim(str);
								}
								return seriesKeys[ seriesIndex ]+ ' ['+ str+ ']';
							}
							return str;
						}
					}
				,	cursor: {
						show: true
					,	zoom: true
					}
				};
				g_lcsCurrentPlot = jQuery.jqplot('lcsPopupStatGraph', plotData, plotParams);
				break;
			case 'line':
			default:
				var tickId = 0;
				for(var i = 0; i < g_lcsCurrentStats.length; i++) {
					if(g_lcsCurrentStats[i]['points'] && g_lcsCurrentStats[i]['points'].length) {
						plotData.push([]);
						for(var j = 0; j < g_lcsCurrentStats[i]['points'].length; j++) {
							plotData[ tickId ].push([g_lcsCurrentStats[ i ]['points'][ j ]['date'], parseInt(g_lcsCurrentStats[ i ]['points'][ j ]['total_requests'])]);
						}
						seriesKeys[ tickId ] = g_lcsCurrentStats[i].label;
						tickId++;
					}
				}
				for(var i in seriesKeys) {
					series.push({label: seriesKeys[ i ]});
				}
				var tickFormat = lcsPopupStatGetDateFormat(true);
				plotParams = {
					axes: {
						xaxis: {
							label: toeLangLcs('Date')
						,	labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer
						,	renderer:	jQuery.jqplot.DateAxisRenderer
						,	tickOptions:{formatString: tickFormat}
						}
					,	yaxis: {
							label: toeLangLcs('Requests')
						,	labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer
						}
					}
				,	series: series
				,	legend: { show:	true, location: 'ne', placement : 'outsideGrid'}
				,	highlighter: {
						show: true
					,	sizeAdjust: 7.5
					,	tooltipContentEditor: function(str, seriesIndex, pointIndex, jqPlot) {
							if(seriesKeys[ seriesIndex ]) {
								return seriesKeys[ seriesIndex ]+ ' ['+ str+ ']';
							}
							return str;
						}
					}
				,	cursor: {
						show: true
					,	zoom: true
					}
				};
				g_lcsCurrentPlot = jQuery.jqplot('lcsPopupStatGraph', plotData, plotParams);
				break;
		}
		if(firstInit) {
			g_lcsCurrentPlot.target.bind('jqplotZoom', function(ev, gridpos, datapos, plot, cursor){
				jQuery('.lcsPopupStatGraphZoomReset').show();
			});
			// Double click jqplotDblClick didn't worked for me here - don't know - why..........
			// So, let's check it as in old times :)
			var lastClick = 0;
			jQuery('#lcsPopupStatGraph').bind('jqplotClick', function (ev, seriesIndex, pointIndex, data) {
				var currTime = (new Date()).getTime();
				if(currTime - lastClick <= 400) {
					jQuery('.lcsPopupStatGraphZoomReset').hide();
				}
				lastClick = currTime;
			});
		}
	}
}
function lcsPopupStatGetDateFormat(forPlot) {
	var tickFormat = '';
	switch(lcsPopupStatGetGoup()) {
		case 'hour':
			tickFormat = forPlot ? '%H, %#d' : 'H, d';
			break;
		case 'month':
			tickFormat = forPlot ? '%b %Y' : 'M Y';
			break;
		case 'week':
		case 'day':
		default:
			tickFormat = forPlot ? '%b %#d, %Y' : 'M d, Y';
			break;
	}
	return tickFormat;
}
function lcsPopupStatRebuildTbl() {
	var tblId = 'lcsPopupStatTbl';
	if(jQuery('#'+ tblId).jqGrid) {
		jQuery('#'+ tblId).jqGrid('GridUnload');
	}
	jQuery('#'+ tblId).jqGrid({ 
		datatype: 'local'
	,	autowidth: true
	,	shrinkToFit: true
	,	colNames:[toeLangLcs('Date'), toeLangLcs('Views'), toeLangLcs('Unique Views'), toeLangLcs('Actions'), toeLangLcs('Conversion')]
	,	colModel:[
			{name: 'date', index: 'date', searchoptions: {sopt: ['eq']}, align: 'center', sorttype: 'date'}
		,	{name: 'views', index: 'views', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'unique_requests', index: 'unique_requests', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'actions', index: 'actions', searchoptions: {sopt: ['eq']}, align: 'center'}
		,	{name: 'conversion', index: 'conversion', searchoptions: {sopt: ['eq']}, align: 'center'}
		]
	,	rowNum: 10
	,	rowList: [10, 20, 30, 1000]
	,	pager: '#'+ tblId+ 'Nav'
	,	sortname: 'date'
	,	viewrecords: true
	,	sortorder: 'desc'
	,	jsonReader: { repeatitems : false, id: '0' }
	,	caption: toeLangLcs('Current PopUp')
	,	height: '100%' 
	,	emptyrecords: toeLangLcs('You have no statistics to display here.')
	});
	
	if(g_lcsCurrentStats && g_lcsCurrentStats.length) {
		g_tblDataToDate = {};
		var	hasData = false;
		for(var i = 0; i < g_lcsCurrentStats.length; i++) {
			if(g_lcsCurrentStats[i]['points'] && g_lcsCurrentStats[i]['points'].length) {
				for(var j = 0; j < g_lcsCurrentStats[i]['points'].length; j++) {
					var date = g_lcsCurrentStats[ i ]['points'][ j ]['date'];
					var currentData = {
						date: date
					,	views: 0
					,	unique_requests: 0
					,	actions: 0
					,	conversion: 0
					};
					if(toeInArrayLcs(g_lcsCurrentStats[i]['code'], ['show'])) {
						currentData['views'] = parseInt( g_lcsCurrentStats[ i ]['points'][ j ]['total_requests'] );
					} else {
						currentData['actions'] = parseInt( g_lcsCurrentStats[ i ]['points'][ j ]['total_requests'] );
					}
					var uniqueRequests = parseInt( g_lcsCurrentStats[ i ]['points'][ j ]['unique_requests'] );
					if(uniqueRequests) {
						currentData['unique_requests'] = uniqueRequests;
					}
					if(g_tblDataToDate[ date ]) {
						currentData['views'] += g_tblDataToDate[ date ]['views'];
						currentData['actions'] += g_tblDataToDate[ date ]['actions'];
						currentData['unique_requests'] += g_tblDataToDate[ date ]['unique_requests'];
					}
					g_tblDataToDate[ date ] = currentData;
					hasData = true;
				}
			}
		}
		if(hasData) {
			var i = 1;
			for(var date in g_tblDataToDate) {	// Calculate conversion
				if(g_tblDataToDate[date]['unique_requests'])
					g_tblDataToDate[date]['conversion'] = g_tblDataToDate[date]['actions'] / g_tblDataToDate[date]['unique_requests'];
				g_tblDataToDate[date]['conversion'] = g_tblDataToDate[date]['conversion'].toFixed(3);
			}
			for(var date in g_tblDataToDate) {
				jQuery('#'+ tblId).jqGrid('addRowData', i, g_tblDataToDate[ date ]);
				i++;
			}
		}
	}
}