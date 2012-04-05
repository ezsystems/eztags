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

        /* Custom display formatters definition */

        var tagMenu = function( cell, record, column, data ) {
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

        var tagCheckbox = function(cell, record, column, data) {
            cell.innerHTML = '<input type="checkbox" name="SelectedIDArray[]" value="' + record.getData( 'id' ) + '" />';
        }

        var tagTranslations = function(cell, record, column, data) {
            var html = '';

            $(data).each(function(i, e) {
                console.log(i);
                console.log(e);
                if( settings.permissions.edit )
                    html += '<a href="' + settings.editUrl + '/' + record.getData('id') + '/' + e + '">';

                html += '<img src="' + settings.icons[e] + '" width="18" height="12" style="margin-right: 4px;" alt="' + e + '" title="' + e + '"/>';

                if( settings.permissions.edit )
                    html += '</a>'
            });

            cell.innerHTML = html;
        }

        var tagName = function(cell, record, column, data) {
            var html = '<a href="' + settings.viewUrl + '/' + record.getData('id') + '">' + record.getData('keyword') + '</a>';

            cell.innerHTML = html;
        }

        /* Paginator definition */

        var dataTablePaginator = new YAHOO.widget.Paginator({
            rowsPerPage: settings.rowsPerPage,
            containers: [ 'bpg' ],
            firstPageLinkLabel: settings.i18n.first_page,
            lastPageLinkLabel: settings.i18n.last_page,
            previousPageLinkLabel: settings.i18n.previous_page,
            nextPageLinkLabel: settings.i18n.next_page,
            template: '<div class="yui-pg-backward"> {FirstPageLink} {PreviousPageLink} </div>' +
            '{PageLinks}' +
            '<div class="yui-pg-forward"> {NextPageLink} {LastPageLink} </div>'
        });

        dataTablePaginator.subscribe( 'render', function () {
            var prevPageLink, prevPageLink, prevPageLinkNode, nextPageLinkNode, tpg;

            tpg = YAHOO.util.Dom.get( 'tpg' );

            // Instantiate the UI Component
            prevPageLink = new YAHOO.widget.Paginator.ui.PreviousPageLink( this );
            nextPageLink = new YAHOO.widget.Paginator.ui.NextPageLink( this );

            // render the UI Component
            prevPageLinkNode = prevPageLink.render( tpg );
            nextPageLinkNode = nextPageLink.render( tpg );

            // Append the generated node into the container
            tpg.appendChild( prevPageLinkNode );
            tpg.appendChild( nextPageLinkNode );
        });

        /* Selection button */

        var selectItemsButtonAction = function( type, args, item ) {
            $( '#eztags-tag-children-table' ).find( ':checkbox' ).prop( 'checked', item.value );
        }

        var selectItemsButtonInvert = function( type, args, item ) {
            var checks = $( '#eztags-tag-children-table' ).find( ':checkbox' ).each(function(){
                this.checked = !this.checked;
            });
        }

        var selectItemsButtonActions = [
            { text: settings.i18n.select_visible, id: 'ezopt-menu-check', value: 1, onclick: { fn: selectItemsButtonAction } },
            { text: settings.i18n.select_none, id: 'ezopt-menu-uncheck', value: 0, onclick: { fn: selectItemsButtonAction } },
            { text: settings.i18n.select_toggle, id: 'ezopt-menu-toggle', onclick: { fn: selectItemsButtonInvert } }
        ];

        var selectItemsButton = new YAHOO.widget.Button({
            type: 'menu',
            id: 'ezbtn-items',
            label: settings.i18n.select,
            name: 'select-items-button',
            menu: selectItemsButtonActions,
            container: 'action-controls'
        });

        /* Create new tag button */

        var createNewButtonAction = function( type, args ) {
            var item = args[1];
            var form = $( '<form action="' + settings.addUrl + '/' + item.value + '">' );
            $('body').append( form );
            form.submit();
        }

        var createNewButton = new YAHOO.widget.Button({
            type: 'menu',
            id: 'ezbtn-new',
            label: settings.i18n.add_child,
            name: 'create-new-button',
            menu: settings.createOptions,
            container: 'action-controls'
        });

        if ( !settings.permissions.add ) {
            createNewButton.set( 'disabled', true );
        }

        var createNewButtonMenu  = createNewButton.getMenu();
        createNewButtonMenu.cfg.setProperty( 'scrollincrement', 5 );
        createNewButtonMenu.subscribe( 'click', createNewButtonAction );

        var createNewButtonGroupsLength = createGroups.length;
        for ( var i = 0, l = createNewButtonGroupsLength; i < l; i++ ) {
            var groupName = createGroups[i];
            createNewButtonMenu.setItemGroupTitle( groupName, i );
        }

        /* More actions button */

        var moreActionsButtonAction = function( type, args, item ) {
            if ( $( '#eztags-tag-children-table  input[name=SelectedIDArray[]]:checked' ).length == 0 )
                return;
/*
            if (item.value == 0) {
                $('form[name=children]').append($('<input type="hidden" name="RemoveButton" />')).submit();
            } else {
                $('form[name=children]').append($('<input type="hidden" name="MoveButton" />')).submit();
            }
*/
        }

        var moreActionsButtonActions = [
            { text: settings.i18n.remove_selected, id: "ezopt-menu-remove", value: 0, onclick: { fn: moreActionsButtonAction }, disabled: false },
            { text: settings.i18n.move_selected, id: "ezopt-menu-move", value: 1, onclick: { fn: moreActionsButtonAction }, disabled: false }
        ];

        var noMoreActionsButtonActions = [
            { text: settings.i18n.no_actions, disabled: true }
        ];

        var moreActionsButton = new YAHOO.widget.Button({
            type: 'menu',
            id: 'ezbtn-more',
            label: settings.i18n.more_actions,
            name: 'more-actions-button',
            menu: noMoreActionsButtonActions,
            container: 'action-controls'
        });

        //  enable 'more actions' when rows are checked
        moreActionsButton.getMenu().subscribe( 'beforeShow', function () {
            if ( $( '#eztags-tag-children-table  input[name=SelectedIDArray[]]:checked' ).length == 0 ) {
                this.clearContent();
                this.addItems( noMoreActionsButtonActions );
                this.render();
            } else {
                this.clearContent();
                this.addItems( moreActionsButtonActions );
                this.render();
            }
        });

        /* Data source definition */

        var timeStampYuiParser = function ( oData ) {
            if ( oData != null )
                return new Date( oData * 1000 );
            else
                return null;
        };

        var dataSourceFields = [
            { key: 'id', parser: 'number' },
            { key: 'keyword', parser: 'string' },
            { key: 'modified', parser: timeStampYuiParser },
            { key: 'translations' }
        ];

        var dataSource = new YAHOO.util.XHRDataSource( settings.dataSourceURI, {
            responseType: YAHOO.util.DataSource.TYPE_JSON,
            responseSchema: {
                resultsList: 'data',
                fields: dataSourceFields,
                metaFields: {
                    totalRecords: 'count',
                    recordOffset: 'offset'
                }
            }
        });

        /* Data table definition */

        var dataTableColumns = [
            { key: 'checkbox', label:'', sortable: false, resizeable: false, formatter: tagCheckbox },
            { key: 'crank', label:'', sortable: false, resizeable: false, formatter: tagMenu },
            { key: 'id', label: settings.i18n.id, sortable: true, resizeable: true, formatter: 'text' },
            { key: 'keyword', label: settings.i18n.tag_name, sortable: true, resizeable: true, formatter: tagName },
            { key: 'translations', label: settings.i18n.translations, sortable: false, resizeable: true, formatter: tagTranslations },
            { key: 'modified', label: settings.i18n.modified, sortable: true, resizeable: true, formatter: 'date' }
        ];

        var dataTable = new YAHOO.widget.DataTable( base, dataTableColumns, dataSource, {
            dateOptions: { format: '%d.%m.%Y %H:%M' },
            generateRequest: buildRequest,
            dynamicData: true,
            initialLoad: false,
            sortedBy: {
                key: 'keyword',
                dir: YAHOO.widget.DataTable.CLASS_ASC
            },
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
        yuiLoader.require( [ 'connection', 'datasource', 'datatable', 'paginator', 'dragdrop', 'button' ] );
        yuiLoader.onSuccess = function() {
            initDataTable( base, settings );
        };
        yuiLoader.insert( [], 'js' );

        return this;
    };
})(jQuery);
