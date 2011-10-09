(function($) {
	var makeRequest = function( dataTable, dataSource ) {
		if ( dataTable != null && dataSource != null ) {
			var oState = dataTable.getState();

			if ( oState.pagination )
				oState.pagination.recordOffset = 0;

			var request = dataTable.get( 'generateRequest' )( oState, dataTable );

			dataSource.sendRequest( request, {
				success: dataTable.onDataReturnSetRows,
				failure: dataTable.onDataReturnSetRows,
				argument: oState,
				scope: dataTable
			});
		}
	};

	var buildRequest = function( oState, oSelf ) {
		var pagingString = '';
		if ( oState.pagination ) {
			pagingString = '&offset=' + oState.pagination.recordOffset + '&limit=' + oState.pagination.rowsPerPage;
		}

		var sortByString = '';
		if ( oState.sortedBy ) {
			sortByString = '&sortby=' + oState.sortedBy.key;
			var sortDirection = oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC ? 'desc' : 'asc';
			sortByString += '&sortdirection=' + sortDirection;
		}

		return pagingString + sortByString;
	};

	var initDataTable = function( base, settings, dataTable, dataSource ) {
		var customMenu = function( cell, record, column, data ) {
			var translationArray = [];

			$(record.getData('translations')).each(function(i, e) {
				translationArray.push( {
					'locale': e,
					'name': settings.languages[e] } );
			});

			var a = new YAHOO.util.Element( document.createElement( 'a' ) );
			a.on('click', function(e) {
				ezpopmenu_showTopLevel(e, 'TagMenu', {
					'%tagID%': record.getData( 'id' ),
					'%languages%': translationArray }, record.getData('keyword'), -1, -1 );
			});

			var div = new YAHOO.util.Element( document.createElement( 'div' ) );
			div.addClass( 'crankfield' );
			div.appendTo( a );

			a.appendTo( cell );
		}

		var translationView = function(cell, record, column, data) {
			var html = '';

			$(data).each(function(i, e) {
				html += '<a href="' + settings.editUrl + '/' + record.getData('id') + '/' + e + '">';
				html += '<img src="' + settings.icons[e] + '" width="18" height="12" style="margin-right: 4px;" alt="' + e + '" title="' + e + '"/>';
				html += '</a>'
			});

			cell.innerHTML = html;
		}

		var timeStampYuiParser = function ( oData ) {
			if ( oData != null )
				return new Date( oData * 1000 );
			else
				return null;
		};

		var dataTableColumns = [
			{ key: 'crank', label:'', sortable: false, resizeable: false, formatter: customMenu },
			{ key: 'id', label: 'ID', sortable: true, resizeable: true, formatter: 'text' },
			{ key: 'keyword', label: 'Tag name', sortable: true, resizeable: true, formatter: 'text' },
			{ key: 'translations', label: 'Translations', sortable: false, resizeable: true, formatter: translationView },
			{ key: 'modified', label: 'Modified', sortable: true, resizeable: true, formatter: 'date' }
		];

		var dataSourceFields = [
			{ key: 'id', parser: 'number' },
			{ key: 'keyword', parser: 'string' },
			{ key: 'modified', parser: timeStampYuiParser },
			{ key: 'translations' }
		];

		var dataSourceMetaFields = {
			totalRecords: 'count',
			recordOffset: 'offset'
		}

		var dataTablePaginator = new YAHOO.widget.Paginator({
			rowsPerPage: settings.rowsPerPage,
			containers: $(document).find( '#eztags-tag-children-paging' )[0]
		});

		var sortedBy = {
			key: 'keyword',
			dir: YAHOO.widget.DataTable.CLASS_ASC
		}

		var dataSource = new YAHOO.util.XHRDataSource( "/ezjscore/call/ezjsctagschildren::tagsChildren::" + settings.parentTagID + "?", {
			responseType: YAHOO.util.DataSource.TYPE_JSON,
			responseSchema: {
				resultsList: 'data',
				fields: dataSourceFields,
				metaFields: dataSourceMetaFields
			}
		});

		var dataTable = new YAHOO.widget.DataTable( base, dataTableColumns, dataSource, {
			dateOptions: { format: '%d.%m.%Y %H:%M' },
			generateRequest: buildRequest,
			dynamicData: true,
			initialLoad: false,
			sortedBy: sortedBy,
			paginator: dataTablePaginator,
		});

		dataTable.handleDataReturnPayload = function( oRequest, oResponse, oPayload ) {
			oPayload.totalRecords = oResponse.meta.totalRecords;
			oPayload.pagination.recordOffset = oResponse.meta.recordOffset;
			return oPayload;
		};

		makeRequest( dataTable, dataSource );
	};

	$.fn.eZTagsChildren = function(settings) {
		var defaults = {
			rowsPerPage: 10
		};
		settings = $.extend(defaults, settings);
		var base = this[0];

		var yuiLoader = new YAHOO.util.YUILoader({
			base: settings.YUI2BasePath,
			loadOptional: true
		});
		yuiLoader.require( ['connection', 'datasource', 'datatable', 'paginator', 'dragdrop'] );
		yuiLoader.onSuccess = function() {
			initDataTable( base, settings );
		};
		yuiLoader.insert();

		return this;
	};
})(jQuery);