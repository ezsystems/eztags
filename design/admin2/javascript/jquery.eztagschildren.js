(function( $ ) {
    var makeRequest = function( dataTable, dataSource ) {
        if ( dataTable != null && dataSource != null ) {
            var oState = dataTable.getState();

            if ( oState.pagination )
                oState.pagination.recordOffset = 0;

            dataTable.filterString = $( '#action-filter-input' ).val();

            var request = dataTable.get( 'generateRequest' )( oState, dataTable );

            dataSource.sendRequest(request, {
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

        var filterString = '';
        if ( oSelf.filterString ) {
            filterString = '&filter=' + encodeURIComponent( oSelf.filterString );
        }

        return pagingString + sortByString + filterString;
    };

    var initDataTable = function( base, settings ) {

        /* Custom display formatter definition */

        var tagMenu = function( cell, record, column, data ) {
            var translationArray = [];

            $(record.getData( 'translations' )).each(function( i, e ) {
                translationArray.push({
                    locale: e,
                    name: settings.languages[e].name
                });
            });

            var a = new YAHOO.util.Element( document.createElement( 'a' ) );
            a.on('click', function(e) {
                ezpopmenu_showTopLevel(e, 'TagMenu', {
                    '%tagID%': record.getData( 'id' ),
                    '%languages%': translationArray
                },
                record.getData( 'keyword' ), -1, -1 );
            });

            var div = new YAHOO.util.Element( document.createElement( 'div' ) );
            div.addClass( 'crankfield' );
            div.appendTo( a );

            a.appendTo( cell );
        };

        var tagCheckbox = function( cell, record, column, data ) {
            cell.innerHTML = '<input type="checkbox" name="SelectedIDArray[]" value="' + record.getData( 'id' ) + '" />';
        };

        var tagTranslations = function( cell, record, column, data ) {
            var html = '';

            $(data).each(function(i, e) {
                if( settings.permissions.edit )
                    html += '<a href="' + settings.urls.edit + '/' + record.getData( 'id' ) + '/' + e + '">';

                html += '<img src="' + settings.languages[e].flag + '" width="18" height="12" style="margin-right: 4px;" alt="' + settings.languages[e].name + '" title="' + settings.languages[e].name + '"/>';

                if( settings.permissions.edit )
                    html += '</a>'
            });

            cell.innerHTML = html;
        };

        var tagName = function( cell, record, column, data ) {
            cell.innerHTML = '<a href="' + settings.urls.view + '/' + record.getData( 'id' ) + '">' + record.getData( 'keyword' ) + '</a>';
        };

        /* Paginator definition */

        var dataTablePaginator = new YAHOO.widget.Paginator({
            rowsPerPage: settings.rowsPerPage,
            containers: [ 'bpg' ],
            firstPageLinkLabel: settings.i18n.first_page,
            lastPageLinkLabel: settings.i18n.last_page,
            previousPageLinkLabel: settings.i18n.previous_page,
            nextPageLinkLabel: settings.i18n.next_page,
            template: '<div class="yui-pg-backward">{FirstPageLink}{PreviousPageLink}</div>{PageLinks}<div class="yui-pg-forward">{NextPageLink}{LastPageLink}</div>'
        });

        dataTablePaginator.subscribe('render', function () {
            var prevPageLink, nextPageLink, prevPageLinkNode, nextPageLinkNode, tpg;

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
        };

        var selectItemsButtonInvert = function( type, args, item ) {
            var checks = $( '#eztags-tag-children-table' ).find( ':checkbox' ).each(function(){
                this.checked = !this.checked;
            });
        };

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
            $('form[id=eztags-children-actions]').prop( 'action', settings.urls.add + '/' + args[1].value ).submit();
        };

        var createNewButtonOptions = [];
        for ( var l in languages ) {
            if ( languages.hasOwnProperty( l ) ) {
                createNewButtonOptions.push( { text: languages[l].name, value: l } );
            }
        }

        var createNewButton = new YAHOO.widget.Button({
            type: 'menu',
            id: 'ezbtn-new',
            label: settings.i18n.add_child,
            name: 'create-new-button',
            menu: createNewButtonOptions,
            container: 'action-controls'
        });

        if ( !settings.permissions.add ) {
            createNewButton.set( 'disabled', true );
        }

        var createNewButtonMenu  = createNewButton.getMenu();
        createNewButtonMenu.cfg.setProperty( 'scrollincrement', 5 );
        createNewButtonMenu.subscribe( 'click', createNewButtonAction );
        createNewButtonMenu.setItemGroupTitle( settings.i18n.add_child_group, 0 );

        /* More actions button */

        var moreActionsButtonAction = function( type, args, item ) {
            if ( $( '#eztags-tag-children-table input[name=\"SelectedIDArray[]\"]:checked' ).length == 0 )
                return;

            if ( item.value == 0 && settings.permissions.remove ) {
                $( 'form[id=eztags-children-actions]' ).prop( 'action', settings.urls.deletetags ).submit();
            }
            else if ( item.value == 1 && settings.permissions.edit ) {
                $( 'form[id=eztags-children-actions]' ).prop( 'action', settings.urls.movetags ).submit();
            }
        };

        var moreActionsButtonActions = [];
        if ( settings.permissions.remove ) {
            moreActionsButtonActions.push({
                text: settings.i18n.remove_selected, id: 'ezopt-menu-remove',
                value: 0,
                onclick: { fn: moreActionsButtonAction },
                disabled: false
            });
        }

        if ( settings.permissions.edit ) {
            moreActionsButtonActions.push({
                text: settings.i18n.move_selected, id: 'ezopt-menu-move',
                value: 1,
                onclick: { fn: moreActionsButtonAction },
                disabled: false
            });
        }

        if ( moreActionsButtonActions.length == 0 ) {
            moreActionsButtonActions.push({
                text: settings.i18n.more_actions_denied,
                disabled: true
            });
        }

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
        moreActionsButton.getMenu().subscribe('beforeShow', function () {
            if ( $( '#eztags-tag-children-table input[name=\"SelectedIDArray[]\"]:checked' ).length == 0 ) {
                this.clearContent();
                this.addItems( noMoreActionsButtonActions );
                this.render();
            } else {
                this.clearContent();
                this.addItems( moreActionsButtonActions );
                this.render();
            }
        });

        /* Table options button & dialog */

        // Shows dialog, creating one when necessary
        var colLayoutHasChanged = true;
        var showTableOptionsDialog = function( e ) {
            YAHOO.util.Event.stopEvent( e );

            if ( colLayoutHasChanged ) {
                // Populate Dialog
                var tableOptionsHTML = '<fieldset>';
                tableOptionsHTML += '<legend>' + settings.i18n.number_of_items + '</legend><div class="block">';

                var rowsPerPageDefinition = [
                    { id: 1, count: 10 },
                    { id: 2, count: 25 },
                    { id: 3, count: 50 }
                ];

                for ( var i = 0, l = rowsPerPageDefinition.length; i < l ; i++ ) {
                    var rowDefinition = rowsPerPageDefinition[i];
                    tableOptionsHTML += '<div class="table-options-row"><span class="table-options-key">'+ rowDefinition.count + '</span>';
                    tableOptionsHTML += '<span class="table-options-value"><input id="table-option-row-btn-' + rowDefinition.id + '" type="radio" name="TableOptionValue" value="' + rowDefinition.count + '"' + ( settings.rowsPerPage == rowDefinition.count ? ' checked="checked"' : '' ) + ' /></span></div>';

                    YAHOO.util.Event.on('table-option-row-btn-' + rowDefinition.id, 'click', function( e, a ) {
                        dataTablePaginator.setRowsPerPage( a.count );
                        $.ez.setPreference( 'admin_eztags_list_limit', a.id );
                    }, rowDefinition);
                }

                tableOptionsHTML += '</div></fieldset>';

                tableOptionsDialog.setBody( tableOptionsHTML );
                colLayoutHasChanged = false;
            }

            tableOptionsDialog.show();
        };

        var hideTableOptionsDialog = function( e ) {
            this.hide();
        };

        var tableOptionsButton = new YAHOO.widget.Button({
            label: settings.i18n.table_options,
            id: 'ezbtn-options',
            container: 'action-controls',
            onclick: { fn: showTableOptionsDialog, obj: this, scope: true }
        });

        var tableOptionsDialog = new YAHOO.widget.SimpleDialog('to-dialog-container', {
            width: '25em',
            visible: false,
            modal: true,
            buttons: [{
                text: settings.i18n.close_table_options,
                handler: function( e ){
                    this.hide();
                }
            }],
            fixedcenter: 'contained',
            constrainToViewport: true
        });

        var escKeyListener = new YAHOO.util.KeyListener(
            document,
            { keys: 27 },
            { fn: tableOptionsDialog.hide, scope: tableOptionsDialog, correctScope: true }
        );

        tableOptionsDialog.cfg.queueProperty( 'keylisteners', escKeyListener );
        tableOptionsDialog.setHeader( settings.i18n.table_options );
        tableOptionsDialog.render();

        /* Filter box */

        var filterTextBox = new YAHOO.util.Element( document.createElement( 'input' ) );
        filterTextBox.set( 'type', 'text' );
        filterTextBox.set( 'size', '40' );
        filterTextBox.set( 'id', 'action-filter-input' );
        filterTextBox.addClass( 'action-filter-input' );

        var filterContainer = new YAHOO.util.Element( document.getElementById( 'action-filter' ) );
        filterContainer.appendChild( filterTextBox );

        // stupid IE
        var eventToBind = navigator.userAgent.match( /MSIE/ ) ? 'keydown' : 'input';
        var filterTimeoutHandler;

        $( '#action-filter-input' ).bind(eventToBind, function(){
            if ( filterTimeoutHandler )
                clearTimeout( filterTimeoutHandler );

            filterTimeoutHandler = setTimeout( function() {
                makeRequest( dataTable, dataSource );
            }, 400 );
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

        var dataSource = new YAHOO.util.XHRDataSource(settings.urls.data, {
            responseType: YAHOO.util.DataSource.TYPE_JSON,
            responseSchema: {
                resultsList: 'content.data',
                fields: dataSourceFields,
                metaFields: {
                    totalRecords: 'content.count',
                    recordOffset: 'content.offset',
                    filterString: 'content.filter'
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

        var dataTable = new YAHOO.widget.DataTable(base, dataTableColumns, dataSource, {
            dateOptions: { format: '%d.%m.%Y %H:%M' },
            generateRequest: buildRequest,
            dynamicData: true,
            initialLoad: false,
            sortedBy: {
                key: 'keyword',
                dir: YAHOO.widget.DataTable.CLASS_ASC
            },
            paginator: dataTablePaginator,
            MSG_LOADING: settings.i18n.loading,
            MSG_EMPTY: settings.i18n.no_tags
        });

        dataTable.handleDataReturnPayload = function( oRequest, oResponse, oPayload ) {
            oPayload.totalRecords = oResponse.meta.totalRecords;
            oPayload.pagination.recordOffset = oResponse.meta.recordOffset;
            dataTable.filterString = oResponse.meta.filterString;
            $( '#eztags-children-count' ).html( oResponse.meta.totalRecords );
            return oPayload;
        };

        makeRequest( dataTable, dataSource );
    };

    $.fn.eZTagsChildren = function( settings ) {
        var defaults = {
            rowsPerPage: 10
        };
        settings = $.extend( defaults, settings );
        var base = this[0];

        var yuiLoader = new YAHOO.util.YUILoader({
            base: settings.urls.yui2,
            loadOptional: true
        });
        yuiLoader.require( [ 'connection', 'datasource', 'datatable', 'paginator', 'dragdrop', 'button', 'container' ] );
        yuiLoader.onSuccess = function() {
            initDataTable( base, settings );
        };
        yuiLoader.insert( [], 'js' );

        return this;
    };
})(jQuery);
