var DataGrid = function () {

    return {
        runnerTimer: null,
        processing: false,
        isInited: false,
        isExcecuted: false,
        runner: false,
        inWindow: null, // the jquery window object
        defaults: {
            sort: "asc",
            perpage: 20,
            datarows: false,
            height: 'auto',
            width: 'auto',
            key: 'id',
            selectable: true,
            title: '',
            counter: '%s - %s (%s)',
            displayCounter: true,
            selectOnClick: true,
            footertoolbar: false,
            footersearchpanel: false,
            colModel: null,
            cachelIcon: '',
            checkboxIcon: '',
            checkboxIconChecked: '',
            searchitems: false,
            table: '',
            updatecolsettingsUrl: null,
            filterformfields: null,
            // 
            gridActions: false,
            // Events
            onAfterLoad: false,
            onAfterSearchToggle: false,
            onAfterDelConfirm: false,
            selectionChecker: false,
            doubleClick: false,
            rowDecorator: false,
            postDataFetchHandler: false,

            labelColumn: false,
            convertOptionsColumn: false,

            onError: function (XMLHttpRequest, textStatus, errorThrown) {
                Debug.log(textStatus);
            }
        },
        settings: {
            labelColumn: false,
            convertOptionsColumn: false,
            perpage: 20,
            total: 0,
            displayCounter: true,
            selectable: true,
            gridActions: false
        },
        displayCounter: false,
        visibleCols: 0,
        widthPatch: 15,
        page: 1,
        pages: 1,
        count: 0,
        query: '', // search query
        no_data_msg: cmslang.emptygriddata,
        results_from_to: cmslang.resultsfromto,
        perpages: [10, 20, 30, 40, 50, 75, 100],
        allFields: [], // store all table column names
        allVisibleFields: [], // store all visible table column names
        // events
        renderers: false,
        selectionChecker: false,
        doubleClickHandler: false,
        rowDecorator: false,
        postDataFetchHandler: false,
        currentActiveFilter: false,
        // 
        searchParams: {},
        // column change event
        sendChange: false,
        hasGridAction: false,
        // Grid header and Footer height
        gridFooterHeight: 0,
        gridHeaderHeight: 0,
        //
        element: null,
        gridForm: null,
        griddataurl: null,
        updatecolsettingsUrl: null,
        // table default sorting
        sort: 'asc',
        //
        dataCache: false,
        //
        firstDataRowCache: [],
        // 
        headerColumnCache: [],
        /**
         *  Grid objects
         */
        actionContainer: null,
        wrapper: null,
        viewport: null,
        gridSearchbar: null,
        gridNavi: null,
        gridFooter: null,
        toolbarfooter: null,
        toolbarinited: false, // buildGridNavipanel
        searchbarcreated: false, // buildGridToolbar

        /**
         *  Grid Header
         */
        gridHeader: null,
        headerTable: null,
        headerThead: null,
        headerTh: null,
        /**
         *  DATA
         */
        dataTableWrapper: null,
        dataTable: null,
        dataTableTbody: null,

        /**
         *
         *
         */
        columnSelector: null,

        /**
         *
         * Create the Grid
         *
         * @returns {undefined}
         */
        create: function (container, win, options) {
            var self = this;

            if ($(container).hasClass('gc') || this.isExcecuted) {
                this.runner = false;
                this.isExcecuted = true;
                clearTimeout(this.runnerTimer);
                return;
            }

            if (this.processing || this.runner || Desktop.windowWorkerOn || $(container).length === 0) {
                this.runnerTimer = setTimeout(function () {
                    self.create(container, win, options);
                }, 1);
            }
            else {
                clearTimeout(this.runnerTimer);
                this.isExcecuted = true;
                this.runner = true;
                this.isInited = false;
                this.settings = $.extend({}, options);
                this.element = $(container);

                if (win) {
                    this.inWindow = win;
                }

                if (typeof this.inWindow != 'object') {
                    Debug.log('create Grid out of window');
                    return false;
                }

                this.inWindow.addClass('grid-window');
                this.element.addClass('grid-window');

                // Form detector
                var form = this.element.parents('form:first');
                if (form.children().find('#' + container.attr('id')).length === 1) {
                    this.gridForm = form;
                }

                // preparing urls
                this.griddataurl = Tools.prepareAjaxUrl(this.settings.url);

                this.filterformfields = this.settings.filterfields;
                this.perpage = this.settings.perpage;
                this.datarows = this.settings.datarows || false; // Table rows
                this.count = 0;
                this.pages = Math.ceil(this.count / this.perpage);

                if (!this.pages) {
                    this.pages = 1;
                }

                if (this.settings.total > 0) {
                    this.count = this.settings.total;
                }

                this.key = this.settings.key || 'id'; // primary key
                this.sort = this.settings.sort || 'asc'; // Table sortorder name if is sorted
                this.orderby = this.settings.orderby || this.key; // The Table sortorder orderby

                this.displayCounter = this.settings.displayCounter || true;

                this.currentActiveFilter = this.settings.currentFilter || false;

                if (this.currentActiveFilter !== false) {
                    this.searchParams = {};
                    $.extend(this.searchParams, this.currentActiveFilter);
                }

                this.selectionChecker = this.settings.selectionChecker || false; // optional callback to handle external stuff when a row is selected.
                this.doubleClickHandler = this.settings.onDoubleClick || false; // optional callback to handle external stuff when a row is double clicked
                this.rowDecorator = this.settings.rowDecorator || false; // optional callback used to decorate rows after they've been built
                this.postDataFetchHandler = this.settings.postDataFetchHandler || false; // optional callback used to handle data after rows have been built
                this.onAfterLoad = this.settings.onAfterLoad || false;
                this.gridActions = this.settings.gridActions || false;

                // init column counter
                this.countVisibleColumns();

                this.wrapper = $('<div>').addClass('grid-table-wrapper');
                this.viewport = $('<div>').addClass('tablegrid-viewport');
                this.gridNaviState = $('<div>').addClass('selection-state');
                this.gridNavi = $('<div>').addClass('tablegrid-toolbar');
                this.searchbar = $('<div>').addClass('tablegrid-searchbar').hide();
                this.gridFooter = $('<div>').addClass('grid-footer');

                this.columnSelector = $('<div>').addClass('column-selector').append('<span class="fa fa-sort"></span>').hide();
                this.columnSelectorMenu = $('<div></div>').addClass('column-selector-menu');

                this.gridFooter.append(this.gridNavi);

                this.element.empty().append(this.wrapper);

                this.gridHeader = $('<div>').css({
                    width: '100%'
                }).addClass('grid-header');

                this.headerTable = $('<table>').addClass('header-table');
                this.headerThead = $('<thead>');
                $(this.gridHeader).append(this.headerTable);
                $(this.headerTable).append(this.headerThead);
                //  table header to wrapper
                this.viewport.append(this.gridHeader);

                /**
                 * Grid Data table
                 */
                this.dataTableWrapper = $('<div>').css({
                    width: '100%', height: this.element.parents('.window-body-content:first').outerHeight() + 20
                }).addClass('grid-data gui-scroll');

                this.dataTable = $('<table cellspacing="1">').addClass('table table-striped table-hover data-table');
                this.dataTable.addClass('nodata');
                this.dataTableTbody = $('<tbody>');
                $(this.dataTable).append(this.dataTableTbody);

                var d = $('<div>').append(this.dataTable);
                $(this.dataTableWrapper).append($('<div id="grid-scroll">').append(d));

                // append data table to viewport
                this.viewport.append(this.dataTableWrapper);

                this.tfooter = $('<tfooter>');

                /**
                 *  Build the Footer
                 */
                this.buildGridActions();
                this.buildGridSearchBar();
                this.buildGridNavigation();
                this.updateGridNavi(this.displayCounter);

                //  adding search panel to wrapper
                if (this.searchbarcreated)
                {

                    if (typeof Desktop.getActiveWindowToolbar === 'function')
                    {
                        Desktop.getActiveWindowToolbar().append(this.searchbar);
                    }
                    if (typeof Core.getToolbar === 'function') {
                        Core.getToolbar().append(this.searchbar);
                    }

                }

                /**
                 *  Add the selction checker status
                 *
                 */
                if (this.gridActions !== false && this.settings.selectable || this.hasGridAction) {
                    this.gridFooter.prepend(this.gridNaviState);
                    this.gridNaviState.show().append($('<span>').addClass('grid-items-selected').html(sprintf(cmslang.totalitems_selected, 0)));
                }

                /**
                 *  Build the table header
                 */
                this.buildGridHeader();

                this.bindCheckAllEvent();

                //  viewport & gridFooter to wrapper
                this.wrapper.append(this.viewport).append(this.gridFooter);

                if (this.inWindow) {
                    this.inWindow.data('windowGrid', this);
                }

                this.runner = false;
            }
        },
        createGrid: function (grid, callback) {
            if (!grid || typeof grid.element !== 'object') {
                // Debug.log('Invalid Data Grid!');
                return false;
            }

            var self = grid;

            if (grid.element.hasClass('gc') && $(self.inWindow).data('WindowManager')) {
                setTimeout(function () {
                    self.checkSelection();
                    $(self.inWindow).data('WindowManager').set('gridloaded', true);

                    if (typeof callback === 'function') {
                        callback();
                    }
                }, 10);

                return;
            }
            else if (grid.element.hasClass('gc')) {

                self.checkSelection();
                if (typeof callback === 'function') {
                    callback();
                }

                return;
            }

            grid.isExcecuted = true;
            //	grid.isInited = true;
            grid.element.addClass('gc');

            // building data
            if (typeof grid.datarows == 'object' && grid.datarows && grid.datarows.length > 0) {
                //   Debug.log('createGrid() buildGridTableData');
                grid.buildDataTable({
                    datarows: grid.datarows
                });

                grid.pages = Math.ceil(grid.count / grid.perpage);

                grid.updateGridNavi();

                if (grid.selectionChecker) {
                    grid.selectionChecker(grid.getSelected());
                }

            }
            else {
                //        Debug.log('createGrid() getData');
                grid.getData(true);
            }

            if (!grid.hasGridAction) {
                //$(this.headerTable).find('th:first').remove();
                //$(this.dataTable).find('tr').find('td:first').remove();
            }
            else {
                /*
                 $(grid.headerTable).find('th:first').css({
                 maxWidth: '22px',
                 width: '22px',
                 minWidth: '22px'
                 }).attr('width', '22');

                 $(grid.dataTable).find('tr').find('td:first').css({
                 maxWidth: '22px',
                 width: '22px',
                 minWidth: '22px'
                 }).attr('width', '22');
                 */
            }

            grid.dataCellHeight = $(grid.dataTable).find('td:first').height();

            // 

            /*
             var cDrag = $('<div>').addClass('cDrag').css({
             top: 1,
             position: 'absolute',
             zIndex: 5
             });

             cDrag.insertAfter($(grid.headerTableWrapper));
             */

            $(grid.dataTableWrapper).scroll(function () {
                $(self.headerTableWrapper).css({
                    position: 'relative',
                    left: 0 - self.dataTableWrapper.scrollLeft()
                });
            });

            /*
             $(grid.headerTable).find('.grid-resizer').css({
             height: $(grid.headerTable).outerHeight() - 2,
             width: 2
             });
             */

            grid.dataTableWrapper.width('100%').height($(self.inWindow).find('.window-body').height());

            $(grid.headerTable).find('th').each(function (i) {
                var ow = $(this).attr('owidth');
                if (i == 0 && (grid.hasGridAction || grid.settings.forceselectable)) {

                    $(this).css({
                        width: '22px'
                    });
                }
                else {
                    if (ow && !ow.match(/%/) && !ow.match(/auto/)) {
                        var percent = self.toUnit('%', {
                            w: $(this).attr('owidth'),
                            scope: grid.headerTable
                        });

                        if (percent) {
                            $(this).css({
                                width: percent + '%'
                            });
                        }
                        else {
                            $(this).css({
                                width: 'auto'
                            });
                        }
                    }
                }
            });


            //	self.updateDataTableSize( self.inWindow );
            // self.initHeaderColWidth(self.inWindow);
            self.updateDataTableSize(self.inWindow, true);

            setTimeout(function () {
                grid.isInited = true;
                self.isInited = true;
                self.runner = false;

                //  $(self.inWindow).data('WindowManager').set('gridloaded', true);
                self.checkSelection();

                // self.initHeaderColWidth(grid.headerTable, grid.dataTable);

                if (typeof callback === 'function') {
                    callback();
                }
            }, 10);

        },

        getPercent: function (elem) {
            var width = elem.width(),
                parentWidth = elem.offsetParent().width(),
                percent = Math.round(100 * width / parentWidth);
            return percent;
        },

        toUnit: function (unit, settings) {
            if (settings.w === 'auto' || !settings.w) {
                return '';
            }
            settings = $.extend({
                scope: 'body'
            }, settings);

            var that = parseInt(settings.w, 10);
            var scopeTest = jQuery('<div style="display: none; width: 10000' + unit + '; padding:0; border:0;"></div>').appendTo(settings.scope);
            var scopeVal = scopeTest.width() / 10000;
            scopeTest.remove();

            return (that / scopeVal).toFixed(8);
        },


        isFixedWidth: function(name) {
            var col = this.settings.colModel[name];

            if ( col ) {
                if ( col.fixedwidth ) {
                    return col.width ? col.width : true;
                }
            }

            return false;
        },





        /**
         * ------------------ Data Table Header ------------------
         */

        /**
         *
         * @returns {undefined}
         */
        buildGridHeader: function () {

            var self = this, x = 1, extraColumn = 0, columnCache = [];

            if (this.hasGridAction || this.settings.forceselectable) {

                columnCache['selector'] = $('<th>').css({
                    width: '22px',
                    paddingLeft: 0,
                    textAlign: 'center'
                }).attr('owidth', '22').attr('width', '1%').attr('rel', 'selector').addClass('selection-columnHead');

                columnCache['selector'].get(0).visible = true;

                var div = $('<div>').css({
                    position: 'relative',
                    textAlign: 'center'
                });
                var ts = new Date().getTime();
                var checkbox = $('<label for="check-' + ts + '">').append($('<input>').attr({
                    type: 'checkbox',
                    id: 'check-' + ts,
                    name: 'check-' + ts
                }).addClass('chk-all')).appendTo(div);

                div.appendTo(columnCache['selector']);
                x++;
                extraColumn = 1;

                this.headerThead.append(columnCache['selector']);
            }

            var columnCounter = 0;

            for (var col in this.settings.colModel) {
                var column = this.settings.colModel[col];
                if (!column.name) {
                    continue;
                }
                columnCounter++;
            }

            var columnIndex = 0, activeSort = false;

            for (var col in this.settings.colModel) {
                var column = this.settings.colModel[col];
                if (!column.name) {
                    continue;
                }

                columnCache[column.name] = $('<th>').attr('rel', column.name);

                var div = $('<div>').css({
                    position: 'relative'
                });

                var resizer = $('<div class="resize-handle">');

                var label = $('<span>').addClass('label').append(column.label);
                div.append(label);

                this.headerThead.append(columnCache[column.name]);

                if (column.sortable) {
                    columnCache[column.name].attr('sort', 1);
                    div.addClass('sorting');
                    var sign = $('<span>').addClass('sign');

                    if (typeof column.type != 'undefined' && column.type != '') {
                        sign.addClass(column.type);
                    }

                    // set Default order
                    if (column.name == this.settings.orderby && !activeSort) {
                        sign.addClass(this.sort);
                        columnCache[column.name].addClass('active-sort');
                        activeSort = true;
                    }

                    div.append(sign);
                    sign.removeClass('label');

                    // add sortable event
                    this.setSortableEvent(columnCache[column.name], column);
                }

                if (column.align != 'left' && x != this.settings.colModel.length + extraColumn) {
                    div.css({
                        textAlign: column.align
                    }).addClass(column.align);
                }

                // set last center text
                if (x >= this.settings.colModel.length + extraColumn) {
                    div.css({
                        textAlign: 'center'
                    });
                }

                if (typeof column.width != 'undefined') {
                    columnCache[column.name].attr('owidth', column.width);	// px
                    columnCache[column.name].css({
                        width: column.width
                    });
                }
                else {
                    columnCache[column.name].attr('owidth', 'auto');
                    columnCache[column.name].css({
                        width: 'auto'
                    });
                }

                $(columnCache[column.name]).get(0).visible = true;

                // hide column if not visible
                if (this.settings.convertOptionsColumn && this.settings.labelColumn) {

                    if (column.name == 'options') {
                        columnCache[column.name].hide();
                        $(columnCache[column.name]).get(0).visible = false;
                    }
                    else {
                        if (!column.isvisible && column.name != this.settings.labelColumn) {
                            columnCache[column.name].hide();
                            $(columnCache[column.name]).get(0).visible = false;
                        }
                    }
                }
                else {
                    if (!column.isvisible) {

                        columnCache[column.name].hide();
                        $(columnCache[column.name]).get(0).visible = false;
                    }
                }

                div.appendTo(columnCache[column.name]);
                if (columnIndex + 1 < columnCounter) {
                    resizer.appendTo(columnCache[column.name]);
                }
                x++;
                columnIndex++;

                this.headerThead.append(columnCache[column.name]);

            }

            var tableWidth = $('#content-container').length ? $('#content-container').width() : this.headerTable.parent().outerWidth(true);
            this.dataTable.width(tableWidth);

            console.log('tableWidth: ' + tableWidth);

            var tmp = [];
            this.headerThead.find('th').each(function (i) {
                var name = $(this).attr('rel');
                var isVisible = $(this).css('display');

                if (isVisible == 'none') {
                    $(this).show();
                }

                tmp[i] = Math.round((100 * $(this).width()) / tableWidth);

                if (isVisible == 'none') {
                    $(this).hide();
                }

            });
            /*
             // convert px to %
             this.headerThead.find( 'th' ).each( function ( i )
             {
             if ($( this ).attr('owidth') == 'auto' ) {
             $( this ).css( 'width', 'auto' ).attr( 'width', '' );
             }
             else {
             $( this ).css( 'width', '' ).attr( 'width', tmp[i] + '%' );
             }
             } );
             */
            this.headerColumnCache = columnCache;

            this.bindColumnResizeEvent();

            /**
             * Column visible toggler menu
             */

            this.getViewOptions(this.columnSelectorMenu);
            this.columnSelectorMenu.hide().appendTo(this.element);

            var t;
            this.columnSelectorMenu.bind('mouseleave', function (e) {
                if (!$(e.target).hasClass('column-selector') && !$(e.target).parents('div.column-selector').length) {
                    var c = this;
                    t = setTimeout(function () {
                        $(c).hide();
                        self.columnSelector.removeClass('active').hide();
                    }, 150);
                }
            });

            this.columnSelector.bind('mouseover', function () {
                clearTimeout(t);
            });

            var isInTable = this.element.parents('table').length;


            this.columnSelector.bind('click', function (e) {
                e.preventDefault();
                clearTimeout(t);

                var menu = self.columnSelectorMenu;

                if ($(this).hasClass('active')) {
                    menu.hide();
                    $(this).removeClass('active');
                    return;
                }

                var menuWidth = menu.outerWidth();
                var tableWidth = self.dataTable.width();

                $(this).addClass('active');

                var columnWidth = $(this).parents('th:first').outerWidth(true);
                var height = $(this).parents('div.grid-header:first').height();
                var left = (isInTable ? $(this).offset().left - self.element.offset().left : $(this).offset().left - $('#panel-content').width());

                if (columnWidth > menuWidth) {
                    menu.css({
                        top: $(this).height() - 2,
                        left: (left + $(this).width()) - menuWidth + 2
                    });
                }
                else {

                    if (left + menuWidth > tableWidth) {
                        menu.css({
                            top: height - 1,
                            left: (left + $(this).width()) - menuWidth + 2
                        });
                    }
                    else {
                        menu.css({
                            top: height - 1,
                            left: left
                        });
                    }
                }
                menu.show();
            });
        },
        dcolt: null,
        colresize: false,
        colmove: false,
        moveColumnName: null,
        hset: false,
        updateColumnSizesAndOrdersTime: null,

        // helper for buildGridHeader
        setSortableEvent: function (th, colModel) {
            var self = this;

            th.addClass('grid-sorter');
            th.attr('id', 'sort-' + colModel.sortby);

            // set type of field (date, numeric, string ....)
            if (typeof colModel.sorttype != 'undefined') {
                // td.addClass( colModel.sorttype );
            }

            th.hover(function () {

                if (self.colmove) {
                    var n = $(this).index();
                    self.dcolt = n;
                }
                else if (!self.colresize) {

                    if (!self.columnSelector.hasClass('active') && $(this).attr('sort')) {
                        self.columnSelector.appendTo($(this));
                        self.columnSelector.show();
                    }
                    else if (self.columnSelector.hasClass('active') && self.columnSelector.parent() !== $(this)) {
                        self.columnSelector.removeClass('active').hide();
                        self.columnSelectorMenu.hide();
                    }
                }

                if (self.colresize) {
                    self.columnSelector.removeClass('active').hide();
                    self.columnSelectorMenu.hide();
                }

            },function () {
                if (!self.columnSelector.hasClass('active')) {
                    self.columnSelector.hide();
                }

                if (self.colmove) {
                    //  $(g.cdropleft).remove();
                    //  $(g.cdropright).remove();
                    //  self.dcolt = null;
                }

            }).mouseup(function (e) {
                    if (self.colresize || self.colmove) {
                        e.preventDefault();
                        self.dragEnd(e);
                    }
                    else {

                        // skip if is click from column resizehandler
                        if ($(e.target).hasClass('resize-handle') || $(e.target).hasClass('column-selector') || $(e.target).parents().hasClass('column-selector') || self.colmove || self.dcolt) {
                            return true;
                        }

                        $(this).parent().find('.active-sort').removeClass('active-sort');
                        $(this).addClass('active-sort');

                        self.orderby = $(this).attr('rel');
                        var old = self.sort;
                        self.sort = (self.sort == 'asc' ? 'desc' : 'asc');

                        $(this).parent().find('.grid-sorter .sign').removeClass('asc').removeClass('desc');
                        $(this).find('.sign').removeClass(old).addClass(self.sort);

                        self.getData();
                        $(self.inWindow).resize();
                    }
                });

        },

        bindColumnResizeEvent: function () {

            this.resizeSpace = $('div.resize-spacer', this.viewport);
            if (this.resizeSpace.length !== 1) {
                this.resizeSpace = $('<div class="resize-spacer">').hide();
                this.viewport.append(this.resizeSpace);




                /*

                if ($('#fullscreenContainer').length) {
                    $('#fullscreenContainer').append(this.resizeSpace);
                }
                else {
                    $('body').append(this.resizeSpace);
                }

                */

            }
            var self = this, t;

            this.headerThead.find('th:not(.selection-columnHead)').each(function () {
                $(this).bind('mousedown',function (e) {
                    if (!$(e.target).hasClass('resize-handle') && !self.colmove && !self.colresize) {
                        var c = this;
                        // little timeout
                        t = setTimeout(function () {
                            e.preventDefault();
                            self.dragStart('colMove', e, $(c));
                        }, 200);
                    }
                }).bind('mouseup', function (e) {
                        clearTimeout(t);
                        return false;
                    });
            });

            this.headerThead.find('div.resize-handle').each(function () {
                $(this).bind('mousedown',function (e) {
                    if (!self.colmove) {
                        var c = this;
                        // little timeout
                        t = setTimeout(function () {
                            e.preventDefault();
                            self.dragStart('colresize', e, $(c));
                        }, 50);
                    }
                }).bind('mouseup', function (e) {
                        clearTimeout(t);
                        return false;
                    });
            });

            this.wrapper.unbind('mousemove.grid').bind('mousemove.grid',function (e) {
                    if (self.colresize || self.colmove ) { self.dragMove(e); }
            }).unbind('mouseup.grid').bind('mouseup.grid', function (e) {
                    e.preventDefault();
                    if (self.colresize || self.colmove) { self.dragEnd(e); }
                });
        },

        dragStart: function (dragtype, e, obj) {
            // 
            clearTimeout(this.updateColumnSizesAndOrdersTime);

            if (dragtype == 'colresize')
            {
                //this.resizeSpace = $('div.resize-spacer', this.element );

                var nextWidth = parseInt($(obj).parent().nextAll('th:visible:first').width(), 10);
                var index = $(obj).parent().index();
                var offsetLeft = $(obj).parent().offset().left;
                var col = $(obj).parent().get(0);
                var originalWidth = parseInt($(obj).parent().width(), 10);
                var columnName = $(obj).parent().attr('rel');

                this.moveColumnName = columnName;
                var n = index;
                var ow = originalWidth;
                this.colresize = {
                    nextWidth: nextWidth,
                    handlerLeft: offsetLeft,
                    startX: e.pageX,
                    ol: parseInt(col.style.left),
                    ow: ow,
                    n: n
                };

                var resizer = this.resizeSpace.get(0);
                resizer.style.top = this.dataTableWrapper.offset().top;
                resizer.style.height = this.dataTableWrapper.height();
                resizer.style.left = e.pageX /*- this.inWindow.offset().left*/ - (this.resizeSpace.width() / 2);
                this.resizeSpace.show();
                $('body').addClass('col-resize');

            }
            else if (dragtype == 'colMove') {

                document.body.style.cursor = 'move';

                this.colmove = document.createElement("div");
                this.colmove.className = "colCopy";
                this.colmove.innerHTML = $(obj).get(0).innerHTML;

                this.colmove.index = $(obj).index();

                this.hset = this.headerTable.offset();
                this.hset.index = $(obj).index();
                this.hset.right = this.hset.left + this.headerTable.width();
                this.hset.bottom = this.hset.top + this.headerTable.height();

                this.hset.startX = e.pageX;

                $(this.colmove).css({
                    height: $(obj).height(),
                    width: $(obj).width(),
                    top: $(obj).offset().top,
                    position: 'absolute',
                    zIndex: 100,
                    'float': 'left',
                    display: 'none',
                    textAlign: $(obj).css('textAlign')
                });

                if ($('#fullscreenContainer').length) {
                    $('#fullscreenContainer').append(this.colmove);
                }
                else {
                    $('body').append(this.colmove);
                }

            }
        },
        dragMove: function (e) {
            if (this.colresize) {
                //this.resizeSpace = $('div.resize-spacer', this.element );
                var n = this.colresize.n;
                var diff = e.pageX - this.colresize.startX;
                var nleft = this.colresize.ol + diff;

                var newWidth;
                var headerColumn = this.headerThead.find('th:eq(' + n + ')');
                var nextWidth = (this.colresize.nextWidth ? this.colresize.nextWidth : headerColumn.nextAll('th:visible:first').width()), setNextWidth = 0;

                if (e.pageX > this.colresize.startX) {

                    diff = e.pageX - this.colresize.startX;
                    newWidth = this.colresize.ow + diff;
                    setNextWidth = nextWidth - diff;

                }
                else {
                    diff = this.colresize.startX - e.pageX;
                    newWidth = this.colresize.ow - diff;
                    setNextWidth = nextWidth + diff;
                }

                this.colresize.xpos = e.pageX;
                this.colresize.nLeft = nleft;

                // min width
                if ((newWidth >= 22 && setNextWidth >= 22) ) {
                    this.resizeSpace.get(0).style.left = e.pageX /*- this.inWindow.offset().left*/ - (this.resizeSpace.width() / 2);
                }
                else {
                    this.wrapper.trigger('mouseup.grid');
                }
            }
            else if (this.colmove) {

                var setLeft = e.pageX - this.inWindow.offset().left;

                if (e.pageX > this.hset.right || e.pageX < this.hset.left || e.pageY > this.hset.bottom || e.pageY < this.hset.top) {
                    //this.dragEnd();
                    $('body').css('cursor', 'move').addClass('move');
                } else {
                    $('body').css('cursor', 'pointer').addClass('move');
                }

                if ($(e.target).parents('th').length) {

                    this.headerThead.find('th.moveinto').removeClass('moveinto after before');
                    if (e.pageX > this.hset.startX) {
                        $(e.target).parents('th').addClass('moveinto after');
                    }
                    else {
                        $(e.target).parents('th').addClass('moveinto before');
                    }

                }
                if ($(e.target).is('th')) {

                    this.headerThead.find('th.moveinto').removeClass('moveinto after before');
                    if (e.pageX > this.hset.startX) {
                        $(e.target).addClass('moveinto after');
                    }
                    else {
                        $(e.target).addClass('moveinto before');
                    }

                }

                //	console.log( e.target);

                $(this.colmove).css({
                    top: e.pageY,
                    left: e.pageX + 20
                }).show();

            }
        },
        dragEnd: function (e) {

            if (this.colresize) {
                //this.resizeSpace = $('div.resize-spacer', this.element );
                var n = this.colresize.n;
                var nw = this.colresize.nw;
                var ow = this.colresize.ow;
                var startX = this.colresize.startX;
                var x = this.colresize.xpos, diff = 0, newWidth;

                var headerColumn = this.headerThead.find('th:eq(' + n + ')');
                var setPos = e.pageX - this.inWindow.offset().left - this.resizeSpace.width();
                var currentLeft = headerColumn.get(0).offsetLeft;
                var nextWidth = (this.colresize.nextWidth ? this.colresize.nextWidth : headerColumn.nextAll('th:visible:first').width());

                if (e.pageX > startX) {
                    diff = e.pageX - this.colresize.startX;
                    nextWidth = nextWidth - diff;
                }
                else {
                    diff = this.colresize.startX - e.pageX;
                    nextWidth = nextWidth + diff;
                }


                if (e.pageX > startX) {
                    if (nextWidth >= 30) {

                        // next column must make smaller
                        newWidth = ow + diff;

                        headerColumn.width(newWidth);
                        headerColumn.nextAll('th:visible:first').width(nextWidth);

                        /*
                         var percent = this.toUnit('%', {
                         w: newWidth,
                         scope: this.headerTable
                         });

                         if (percent) {
                         headerColumn.css('width', percent + '%');
                         }


                         percent = this.toUnit('%', {
                         w: nextWidth,
                         scope: this.headerTable
                         });

                         if (percent) {
                         headerColumn.nextAll('th:visible:first').width(percent + '%');
                         }
                         */
                    }
                }
                else {
                    newWidth = ow - diff;
                    headerColumn.width(newWidth);
                    headerColumn.nextAll('th:visible:first').width(nextWidth);
                }

                this.prepareTableSizes();
                this.headerThead.find('th.moveinto').removeClass('moveinto after before');


                this.resizeSpace.hide();
                this.colresize = false;
                //		this.updateDataTableSize( this.inWindow, true );

                $('body').removeClass('col-resize');

                this.updateColumnSizesAndOrders();


            }
            else if (this.colmove) {

                $('body').css('cursor', '').removeClass('move').removeClass('col-resize');

                if (this.dcolt) {
                    e.preventDefault();

                    this.headerThead.find('th.moveinto').removeClass('moveinto after before');

                    var self = this, index = this.colmove.index;
                    var moveOverIndex = this.dcolt;

                    var headerColumn = this.headerThead.find('th:eq(' + index + ')');
                    var dataColumn = this.dataTable.find('td:eq(' + index + ')');

                    // var headerColumnOver = this.headerThead.find('th:eq(' + moveOverIndex + ')');

                    var datatr = this.dataTable.find('tr'), trlen = datatr.length;

                    if (index > moveOverIndex) {
                        this.headerThead.find('th:eq(' + moveOverIndex + ')').before(headerColumn);

                        //this.dataTable.find('tr').each(function () {
                        //    $(this).find('td:eq(' + moveOverIndex + ')').before($(this).find('td:eq(' + index + ')'));
                        //});


                        for (var y = 0; y<trlen; ++y) {
                            $(datatr[y]).find('td:eq(' + moveOverIndex + ')').before($(datatr[y]).find('td:eq(' + index + ')'));
                        }
                    }
                    else {
                        this.headerThead.find('th:eq(' + moveOverIndex + ')').after(headerColumn);
                        /*
                        this.dataTable.find('tr').each(function () {
                            $(this).find('td:eq(' + moveOverIndex + ')').after($(this).find('td:eq(' + index + ')'));
                        });
                        */
                        for (var y = 0; y<trlen; ++y) {
                            $(datatr[y]).find('td:eq(' + moveOverIndex + ')').after($(datatr[y]).find('td:eq(' + index + ')'));
                        }
                    }
                    /*
                     var self = this, tmp = [];
                     this.headerThead.find('th').each(function(){
                     var rel = $(this).attr('rel');
                     tmp[rel] = self.headerColumnCache[ rel ];
                     });

                     this.headerColumnCache = tmp;*/
                    $(this.colmove).remove();

                    //		this.dataTableWrapper.mask( cmslang.gettingData );

                    setTimeout(function () {

                        //	self.dataTableTbody.empty();
                        //	self.buildDataTable( self.dataCache );
                        self.updateDataTableSize(self.inWindow);
                        //	self.dataTableWrapper.unmask();
                        self.updateColumnSizesAndOrders();

                        self.colmove = false;
                        self.dcolt = false;

                    }, 50);

                }
                else {
                    this.headerThead.find('th.moveinto').removeClass('moveinto after before');
                    e.preventDefault();

                    this.headerThead.find('th.moveinto').removeClass('moveinto after before');
                    $(this.colmove).remove();
                    this.colmove = false;
                    this.dcolt = false;
                }

            }
        },
        updateColumnSizesAndOrders: function () {
            var self = this;
            clearTimeout(this.updateColumnSizesAndOrdersTime);

            this.updateColumnSizesAndOrdersTime = setTimeout(function () {
                var allFields = [], allFieldWidth = [], visiblecols = [];
                self.headerThead.find('th').each(function () {
                    if ($(this).attr('rel')) {
                        allFields.push($(this).attr('rel'));
                        allFieldWidth.push(parseInt($(this).innerWidth(), 10));

                        if ($(this).is(':visible') || $(this).is(':visible') == true) {
                            visiblecols.push($(this).attr('rel'));
                        }
                    }
                });

                //    var parms = self.getParams();

                var parms = {};
                var url = self.griddataurl;

                var p = Tools.convertUrlToObject(url);
                parms = $.extend(parms, p);
                parms.adm = $.getURLParam("adm", url);

                var action = $.getURLParam("action", url);

                if (action) {
                    parms.action = action;
                }

                parms.saveColumns = true;
                parms.ajax = true;
                parms.allfields = allFields.join(',');
                parms.widths = allFieldWidth.join(',');
                parms.visiblefields = visiblecols.join(',');

                if (typeof parms.token == 'undefined') {
                    parms.token = Config.get('token');
                }
                $.post(self.griddataurl + '&ajax=1', parms, function (data) {
                    if (Tools.responseIsOk(data)) {
                        delete parms;
                    }
                });
            }, 800);
        },
        /**
         * ------------------ End Data Table Header ------------------
         */


        prepareTableSizes: function () {
            var xself = this, cache = {},
                tableWidth = $('#content-container').length ? $('#content-container').width() : this.headerTable.parent().outerWidth(true),
                fixed = false, allColumnWidth = 0;


            for (var n in this.headerColumnCache) {
                if (typeof this.headerColumnCache[n] == 'object') {




                    if (n != 'selector' && !this.isFixedWidth(n)) {
                        var tw = this.headerColumnCache[n].width();
                        allColumnWidth += tw;
                        cache[n] = Math.round(100 * tw / tableWidth);
                        this.headerColumnCache[n].width(tw + 'px');
                    }
                    else if ( this.isFixedWidth(n) ) {

                        var tw = this.headerColumnCache[n].width();
                        allColumnWidth += tw;
                        cache[n] = Math.round(100 * tw / tableWidth);
                        this.headerColumnCache[n].width(tw + 'px');
                    }
                    else if (n == 'selector') {
                        this.headerColumnCache[n].width('21px');

                        if (typeof this.firstDataRowCache[n] != 'undefined') {
                            this.firstDataRowCache[n].style.width = '22px'
                        }
                    }
                }
            }

            var selectorColumnFix = tableWidth - allColumnWidth;


            // convert px to %
            for (var n in this.headerColumnCache) {
                if (typeof this.headerColumnCache[n] == 'object') {
                    if (n != 'selector' && n != this.key && !this.isFixedWidth(n) ) {


                        if (!fixed && selectorColumnFix > 22) {
                            this.headerColumnCache[n].css('width', '').attr('width', '');
                            if (typeof this.firstDataRowCache[n] != 'undefined') {
                                this.firstDataRowCache[n].style.width = ''
                            }
                            fixed = true;
                        }
                        else if (fixed && selectorColumnFix > 22) {
                            this.headerColumnCache[n].css('width', '').attr('width', cache[n] + '%');
                            if (typeof this.firstDataRowCache[n] != 'undefined') {
                                this.firstDataRowCache[n].style.width = cache[n] + '%'
                            }
                            fixed = true;
                        }
                        else {
                            this.headerColumnCache[n].css('width', '').attr('width', cache[n] + '%');
                            if (typeof this.firstDataRowCache[n] != 'undefined') {
                                this.firstDataRowCache[n].style.width = cache[n] + '%'
                            }
                        }
                    }
                    else if ( this.isFixedWidth(n) ) {
                        this.headerColumnCache[n].css('width', '').attr('width', cache[n] + '%');
                        if (typeof this.firstDataRowCache[n] != 'undefined') {
                            this.firstDataRowCache[n].style.width = cache[n] + '%'
                        }
                    }
                    else if (n == this.key) {
                        this.headerColumnCache[n].css('width', '').attr('width', cache[n] + '%');
                        if (typeof this.firstDataRowCache[n] != 'undefined') {
                            this.firstDataRowCache[n].style.width = cache[n] + '%'
                        }
                    }
                    else if (n == 'selector') {
                        this.headerColumnCache[n].width('21px');

                        if (typeof this.firstDataRowCache[n] != 'undefined') {
                            this.firstDataRowCache[n].style.width = '22px'
                        }
                    }
                }
            }

        },


        /**
         *
         * @returns {undefined}
         */
        buildDataTable: function (data) {

            if (!data) {
                Debug.log('Undefined data for buildDataTable()');
                return;
            }

            var self = this, rowCount = data.datarows.length;
            var tmp = [], firstRowColumnsCache = [], isEmpty = false, headerColumns = this.headerTable.find('th');

            this.dataCache = data;

            if (rowCount > 0) {
                this.dataTable.removeClass('nodata');

                var columnCache = [];
                headerColumns.each(function () {
                    columnCache[$(this).attr('rel')] = $(this);
                })
                this.headerColumnCache = columnCache;
                var i = 0;
                //for ( var i = 0; i < rowCount; i++ ) {

                //	this.dataTableTbody.find('td').empty();
                this.dataTableTbody.find('#empty-grid').remove();

                var trs = this.dataTableTbody.find('tr');

                while (i < rowCount) {
                    if (typeof data.datarows[i] !== 'undefined') {
                        var t = trs.eq(i);

                        if (!t.length) {
                            var tr = this.buildDataTableRow(headerColumns, data.datarows[i], this.settings.colModel, i);

                            if (typeof this.rowDecorator === 'function') {
                                tr = this.rowDecorator(tr, data.datarows[i]);
                            }

                            if (i === 0) {
                                tr.find('td').each(function () {
                                    var rel = $(this).attr('rel');
                                    firstRowColumnsCache[rel] = this;
                                });
                            }

                            this.dataTableTbody.append(tr);

                            //tmp.push( tr );
                        }
                        else {
                            // fast update
                            t[0].style.display = '';
                            this.buildDataTableRow(headerColumns, data.datarows[i], this.settings.colModel, i, t);


                            if (i === 0) {
                                t.find('td').each(function () {
                                    var rel = $(this).attr('rel');
                                    firstRowColumnsCache[rel] = this;
                                });
                            }

                        }

                        i++;
                    }

                }

                if (rowCount < trs.length) {
                    for (var y = rowCount; y < trs.length; ++y) {
                        if (trs.eq(y).length) {
                            trs.eq(y).remove();
                        }
                        else {
                            break;
                        }
                    }
                }

                this.firstDataRowCache = firstRowColumnsCache;
                //this.dataTableTbody.empty().append( tmp );

                this.initDataTableRows();
                this.checkSelection();
                this.bindCheckAllEvent();
                this.bindGridDataEvents();

            }
            else {
                this.firstDataRowCache = [];
                this.dataTableTbody.empty().append($('<div id="empty-grid">').append(cmslang.emptygriddata));
                this.dataTable.addClass('nodata');
            }

        },

        changePublish: function (id, url) {

            var actTabContentHash = Core.getContent().attr('id');
            var spinner = $('#' + actTabContentHash + ' #' + id);
            if (spinner.hasClass('fa-spin')) {
                return;
            }

            spinner.addClass('fa-spin');

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                async: true,
                cache: false,
                success: function (data) {
                    spinner.removeClass('fa-spin');

                    if (Tools.responseIsOk(data)) {

                        if (typeof data.msg != 'undefined' && data.msg == '0') {
                            spinner.removeClass('published').addClass('unpublished').parents('a:first').find('.pub-label').text(cmslang.unpublished);
                            return false;
                        }

                        if (data.msg != 'undefined' && data.msg == '1') {
                            spinner.removeClass('unpublished').addClass('published').parents('a:first').find('.pub-label').text(cmslang.published);
                            return false;
                        }

                        if (spinner.hasClass('published')) {
                            spinner.removeClass('published').addClass('unpublished').parents('a:first').find('.pub-label').text(cmslang.unpublished);
                        }
                        else {
                            spinner.removeClass('unpublished').addClass('published').parents('a:first').find('.pub-label').text(cmslang.published);
                        }

                        if (typeof data.msg != "undefined") {
                            Notifier.info(data.msg);
                        }
                    }
                    else {
                        if (typeof data.msg != "undefined") {
                            Notifier.error(data.msg);
                        }
                    }
                }
            });

        },
        buildDataTableRow: function (headerColumns, data, columnModel, rowcounter, fasttr) {
            var self = this;
            var className = ((rowcounter % 2 !== 1) ? 'firstrow' : 'secondrow');
            var keyValue = false;

            if (Tools.exists(data[self.key], 'data')) {
                keyValue = data[self.key].data;
            }
            else if (typeof data[self.key] === 'string') {
                keyValue = data[self.key];
            }

            var td, x = 1, extraColumn = 0, div, tr = $('<tr>').attr('id', 'data-' + keyValue).addClass(className);

            if ( fasttr ) {
                fasttr.attr('id', 'data-'+keyValue);
            }


            if (this.hasGridAction || this.settings.forceselectable) {
                if (fasttr) {
                    fasttr.find('td[rel=selector]').empty().append(
                        $('<div>').css({
                            'text-align': 'left!important'
                        }).append($('<input>').attr({
                                type: 'checkbox',
                                name: 'ids[]'
                            }).val(keyValue).css({
                                    marginLeft: '2px'
                                }))

                    );
                }
                else {
                    $('<td>').attr('rel', 'selector').addClass('selection-column').append(

                        $('<div>').css({
                            'text-align': 'left!important'
                        }).append($('<input>').attr({
                                type: 'checkbox',
                                name: 'ids[]'
                            }).val(keyValue).css({
                                    marginLeft: '2px'
                                }))

                    ).appendTo(tr);
                }
                extraColumn = 1;
            }

            var len = columnModel.length;

            //headerColumns.each(function () {
            for (var rel in self.headerColumnCache) {
                //var rel = $(this).attr('rel');

                if (rel && rel != 'selector') {
                    var column = self.headerColumnCache[rel][0]; //self.findColumnByName(rel, columnModel);

                    if (column) {

                        if (fasttr) {
                            td = fasttr.find('td[rel=' + rel + ']').empty();
                            td[0].innerHTML = '';
                        }
                        else {
                            td = $('<td>').attr('rel', rel);
                        }


                        // hide column if not visible
                        if (fasttr) {

                            if (this.settings.convertOptionsColumn && this.settings.labelColumn) {

                                if (rel == 'options') {
                                    td.hide();
                                }
                                else {
                                    if (!column.visible && rel != this.settings.labelColumn) {
                                        td.hide();
                                    }
                                }
                            }
                            else {
                                if (!column.visible) {
                                    td.hide();
                                }
                            }

                            if (!self.settings.selectable) {
                                td.css({
                                    'text-align': 'left!important'
                                });
                            }

                            if (typeof column.css !== 'undefined') {
                                td.addClass(column.css);
                            }
                        }
                        else {
                            if (!column.visible) {
                                td.hide();
                            }
                            if (!self.settings.selectable) {
                                td.css({
                                    'text-align': 'left!important'
                                });
                            }

                            if (typeof column.css !== 'undefined') {
                                td.addClass(column.css);
                            }
                        }

                        var span = $('<span>'), treeSpace = 0;

                        if (typeof self.renderers === 'array' && self.renderers[ rel ]) {
                            span.append(self.renderers[ rel ](data[ rel ], data));
                        }
                        else {
                            var d = data[ rel ];

                            if (typeof d !== 'undefined') {

                                if (data[ rel ].data) {
                                    var ts = data[ rel ].data.match(/#ts_([\d]+?)#/ig);
                                    if (ts) {

                                        ts = ts[0].replace(/#/g, '').split('ts_');

                                        if (parseInt(ts[1]) > 0) {
                                            treeSpace = parseInt(ts[1]);
                                        }
                                        data[ rel ].data = data[ rel ].data.replace(/#ts_([\d]+?)#/ig, '');
                                    }
                                }

                                span.append(data[ rel ].data);
                            }
                        }

                        if (treeSpace > 0) {
                            var space = $('<div class="tree-space" style="margin-left: ' + (treeSpace * 15) + 'px"></div>');
                            span.appendTo(space);
                            space.appendTo(td);
                        }
                        else {
                            span.appendTo(td);
                        }

                        if (this.settings.convertOptionsColumn) {
                            if (this.settings.labelColumn === rel && typeof data[ rel ].actions !== 'undefined') {

                                // create options
                                var span2 = $('<div class="opt"></div>');

                                for (var y = 0; y < data[ rel ].actions.length; y++) {
                                    var a = data[ rel ].actions[y];
                                    var extraCssClass = '';

                                    if (a.disabled) {
                                        extraCssClass += ' disabled';
                                    }

                                    if (a.ajax) {
                                        extraCssClass += ' ajax';
                                    }


                                    if (a.isdraft) {
                                        span2.append('<span class="draftmode"><span class="fa fa-flash"></span></span>').append('<i></i>');
                                    }
                                    else {


                                        if (a.type === 'edit' && a.url && !a.url.match(/javascript:/g)) {
                                            span2.append('<a href="admin.php?' + a.url + '" class="edit doTab' + extraCssClass + '"><span class="fa fa-cog"></span><span class="pub-label">' + cmslang.edit + '</span></a>').append('<i></i>');
                                        }
                                        else if (a.type === 'delete' && a.url && !a.url.match(/javascript:/g)) {
                                            span2.append('<a href="admin.php?' + a.url + '" class="delete delconfirm' + extraCssClass + '"><span></span><span class="pub-label">' + cmslang.delete + '</span></a>').append('<i></i>');
                                        }
                                        else if (a.type === 'uninstall' && a.url && !a.url.match(/javascript:/g)) {
                                            span2.append($('<a href="admin.php?' + a.url + '" class="uninstall delconfirm' + extraCssClass + '"><span></span></a>').append($('<span class="pub-label">').append(a.label))).append('<i></i>');
                                        }
                                        else if (a.type === 'publish' && a.url && !a.url.match(/javascript:/g)) {
                                            if (a.published == 0) {
                                                // unpublished

                                                var link = $('<a href="javascript:void(0)" rel="pub' + rowcounter + '" url="' + a.url + '" class="unpublish' + extraCssClass + '"><span class="unpublished fa fa-refresh" id="pub' + rowcounter + '"></span><span class="pub-label">' + cmslang.unpublished + '</span></a>');
                                                link.click(function (e) {
                                                    self.changePublish($(this).attr('rel'), $(this).attr('url'));
                                                });
                                                span2.append(link).append('<i></i>');
                                            }
                                            else if (a.published == 1 && a.url && !a.url.match(/javascript:/g)) {
                                                // published
                                                var link = $('<a href="javascript:void(0)" rel="pub' + rowcounter + '" url="' + a.url + '" class="publish' + extraCssClass + '"><span class="published fa fa-refresh" id="pub' + rowcounter + '"></span><span class="pub-label">' + cmslang.published + '</span></a>');
                                                link.click(function (e) {
                                                    self.changePublish($(this).attr('rel'), $(this).attr('url'));
                                                });

                                                span2.append(link).append('<i></i>');

                                            }
                                            else if (a.published == 2) {
                                                // time controlled
                                                span2.append('<span class="timecontrol" data-tooltip="Hello World"><span class="fa fa-clock-o"></span><span class="pub-label">' + cmslang.timecontrolled + '</span></span>').append('<i></i>');
                                            }
                                        }
                                        else {
                                            if (a.url && !a.url.match(/javascript:/g) && a.label) {

                                                if (a.type == 'download') {
                                                    span2.append($('<a href="admin.php?' + a.url + '" class="dl download' + extraCssClass + '"><span class="fa fa-cog"></span></a>').append($('<span class="pub-label">').append(a.label))).append('<i></i>');
                                                }
                                                else {
                                                    span2.append($('<a href="admin.php?' + a.url + '" class="doTab' + extraCssClass + '"><span class="fa fa-cog"></span></a>').append($('<span class="pub-label">').append(a.label))).append('<i></i>');
                                                }
                                            }
                                        }

                                    }
                                }

                                if (treeSpace > 0) {
                                    span2.appendTo(space);
                                }
                                else {
                                    span2.appendTo(td);
                                }
                            }
                        }

                        if (span.text().trim().length > 10) {
                            span.attr('title', span.text().trim().replace('"', '').replace("'", '´'));
                        }

                        // display draft icon?
                        if (span.find('span.fa-pause')) {
                            span.find('span.fa-pause').attr('title', cmslang.isDraft);
                        }

                        if (!fasttr) {
                            if (column.align != null && column.align !== 'left' && x < len + extraColumn) {
                                td.css({
                                    textAlign: column.align
                                }).addClass(column.align);
                            }

                            /*
                             // the last col set to right
                             if ( x >= len + extraColumn ) {
                             td.css( {
                             textAlign: 'center'
                             } );
                             }

                             if ( self.confirmText === column.name ) {
                             //      td.attr('id', 'confirm-' + (typeof data[self.key] == 'object' ? data[self.key].data : data[self.key]));
                             }
                             */

                            tr.append(td);	// add column to row
                        }
                        else {
                            if (column.align != null && column.align !== 'left' && x < len + extraColumn) {
                                td.css({
                                    textAlign: column.align
                                }).addClass(column.align);
                            }
                        }
                        x++;

                    }

                }

            }
            //});

            return tr;
        },
        /**
         * ------------------ Data table row tools ------------------
         */

        /**
         *
         * @returns {undefined}
         */
        initDataTableRows: function () {

            if (typeof Core != 'undefined') {
                Core.BootstrapInit($(this.dataTableTbody));
            }

            var trs = $(this.dataTableTbody).find('tr');

            if (this.gridActions.length || this.settings.forceselectable) {
                var self = this;
                var cbks = trs.find(':checkbox'), clen = cbks.length;



                if (this.selectOnClick && this.gridActions) {


                    for (var x = 0; x < clen; ++x) {
                        $(cbks[x]).unbind('change.datarow').bind('change.datarow', function () {
                            var row = $(this).parents('tr:first');
                            if (!$(this).is(':checked') || !$(this).prop('checked')) {
                                row.addClass('selected-row');
                            }
                            else {
                                row.removeClass('selected-row');
                            }

                            setTimeout(function () {
                                self.checkSelection();

                                if (self.selectionChecker) {
                                    self.selectionChecker(self.getSelected());
                                }
                            }, 100);
                        });
                    }


                }
                else {
                    for (var x = 0; x < clen; ++x) {
                        $(cbks[x]).unbind('change.datarow').bind('change.datarow', function () {
                            var row = $(this).parents('tr:first');
                            if (!$(this).is(':checked') || !$(this).prop('checked')) {
                                row.addClass('selected-row');
                            }
                            else {
                                row.removeClass('selected-row');
                            }

                            setTimeout(function () {
                                self.checkSelection();

                                if (self.selectionChecker) {
                                    self.selectionChecker(self.getSelected());
                                }
                            }, 100);
                        });
                    }
                    /*
                    trs.find(':checkbox').each(function () {
                        $(this).unbind('change.datarow').bind('change.datarow', function () {
                            var row = $(this).parents('tr:first');
                            if (!$(this).is(':checked') || !$(this).prop('checked')) {
                                row.addClass('selected-row');

                            }
                            else {
                                row.removeClass('selected-row');

                            }

                            setTimeout(function () {
                                self.checkSelection();

                                if (self.selectionChecker) {
                                    self.selectionChecker(self.getSelected());
                                }
                            }, 100);
                        });
                    });
                        */
                }
            }

            var clicks = 0, self = this;

            trs.data('clicks', 0).data('clicksTimer', false);

            if (this.gridActions.length || this.settings.forceselectable) {


                var tds = trs.find('td'), tdlen = tds.length;

                for (var y = 0; y < tdlen; ++y) {
                //trs.find('td').each(function () {
                    var td = $(tds[y]);
                    if (!td.find('a').length && !td.find(':checkbox').length) {
                        td.unbind('click.datarowev').bind('click.datarowev', function (e) {
                            var row = $(this).parents('tr:first');
                            var rowCbk = row.find(':checkbox');
                            var thisClicks = row.data('clicks');
                            self.dataTableTbody.find('tr').data('clicks', 0); // reset all counters

                            thisClicks++;

                            if (thisClicks == 1) {

                                row.data('clicks', thisClicks);
                                row.data('clicksTimer', setTimeout(function () {

                                    row.data('clicks', 0);

                                    if (!rowCbk.is(':checked') || !rowCbk.prop('checked')) {
                                        rowCbk.attr('checked', true);
                                        rowCbk.prop('checked', true);
                                        rowCbk.trigger('change');
                                        row.addClass('selected-row');
                                    }
                                    else {
                                        rowCbk.prop('checked', false);
                                        rowCbk.prop('checked', false);
                                        rowCbk.trigger('change');
                                        rowCbk.removeAttr('checked');
                                        row.removeClass('selected-row');
                                    }

                                    setTimeout(function () {
                                        self.checkSelection();
                                        if (self.selectionChecker) {
                                            self.selectionChecker(self.getSelected());
                                        }
                                    }, 100);

                                }, 200)

                                );
                            }
                            else {
                                clearTimeout(row.data('clicksTimer'));

                                if (typeof self.doubleClickHandler === 'function') {
                                    self.doubleClickHandler(row);
                                }

                                row.data('clicks', 0);
                            }
                        });
                    }
               // });
            }
            }

            /**
             * add doubleClickHandler to the first column
             */
            if (typeof this.doubleClickHandler === 'function') {
                trs.siblings().bind('dblclick', function (e) {
                    e.preventDefault();
                });
            }

        },
        bindGridDataEvents: function () {
            var self = this;

            setTimeout(function () {

                self.registerDelConfirm();
                self.dataTable.find('a.doTab:not(.ajax)').each(function () {
                    $(this).unbind('click');
                    $(this).bind('click', function (e) {
                        e.preventDefault();
                        if ($(this).hasClass('disabled')) {
                            return;
                        }
                        $(this).find('.fa').addClass('fa-spin');

                        var self = this, href = $(this).attr('href');
                        var rel = $(this).attr('rel');
                        var url = '';

                        if (typeof href !== 'undefined' && href.match(/(^admin\.php|\/admin\.php\?)/)) {
                            url = href;
                        }
                        if (url === '' && typeof rel !== 'undefined' && rel.match(/(^admin\.php|\/admin\.php\?)/)) {
                            url = rel;
                        }

                        var l = $(this).text().trim();
                        if (l !== '') {
                            var label = l;
                        }

                        if (url !== '') {
                            Win.openerWindow = this.inWindow;
                            setTimeout(function () {
                                openTab( {url: url, obj: self, label: label, useOpener: true});
                            }, 50);
                        }

                        return false;
                    });
                });

                self.dataTable.find('a.ajax').each(function () {

                    $(this).unbind('click');
                    $(this).bind('click', function (e) {
                        e.preventDefault();
                        if ($(this).hasClass('disabled')) {
                            return;
                        }

                        $(this).find('.fa').addClass('fa-spin');

                        var xself = this, href = $(this).attr('href');
                        var rel = $(this).attr('rel');
                        var url = '';

                        if (typeof href !== 'undefined' && href.match(/(^admin\.php|\/admin\.php\?)/)) {
                            url = href;
                        }
                        if (url === '' && typeof rel !== 'undefined' && rel.match(/(^admin\.php|\/admin\.php\?)/)) {
                            url = rel;
                        }

                        if (url !== '') {
                            self.dataTableWrapper.mask('please wait...');
                            setTimeout(function () {

                                $.ajax({
                                    url: url,
                                    type: 'GET',
                                    dataType: 'json',
                                    async: true,
                                    cache: false,
                                    success: function (data) {
                                        $(xself).find('.fa').removeClass('fa-spin');
                                        self.dataTableWrapper.unmask();
                                        if (Tools.responseIsOk(data)) {
                                            if (data.msg) {
                                                Notifier.info(data.msg);
                                            }
                                        }
                                        else {
                                            if (data && data.msg) {
                                                Notifier.warn(data.msg);
                                            }
                                            else {
                                                console.log(data);
                                            }
                                        }
                                    }
                                });

                            }, 50);
                        }

                        return false;

                    });

                });

                self.dataTable.find('a.doTab-applicationMenu:not(.ajax)').each(function () {

                    $(this).unbind('click');
                    $(this).bind('click', function (e) {
                        e.preventDefault();
                        if ($(this).hasClass('disabled')) {
                            return;
                        }
                        $(this).find('.fa').addClass('fa-spin');

                        var self = this, href = $(this).prop('href');
                        var rel = $(this).prop('rel');
                        var url = '';

                        if (typeof href !== 'undefined' && href.match(/(^admin\.php|\/admin\.php\?)/)) {
                            url = href;
                        }

                        if (url === '' && typeof rel !== 'undefined' && rel.match(/(^admin\.php|\/admin\.php\?)/)) {
                            url = rel;
                        }

                        var l = $(this).text().trim();
                        if (l !== '') {
                            var label = l;
                        }

                        if (url !== '') {

                            Win.openerWindow = this.inWindow;
                            Application.setActiveUrl(url);
                            setTimeout(function () {
                                openTab({url: url, obj: self, label: label, useOpener: true});
                            }, 50);
                        }

                        return false;
                    });
                });

                if (typeof self.onAfterLoad === 'function') {
                    self.onAfterLoad(null, self);
                }

            }, 10);
        },
        registerDelConfirm: function () {
            var self = this;

            this.dataTable.find('a.delconfirm').each(function () {
                $(this).unbind('click');
                $(this).bind('click', function (e) {
                    e.preventDefault();

                    if ($(this).hasClass('disabled')) {
                        return;
                    }

                    var href = $(this).attr('href');

                    jConfirm('Möchtest du diesen Inhalt wirklich löschen?', 'Bestätigung...', function (r) {
                        if (r) {

                            //unnotify();

                            $.get(Tools.prepareAjaxUrl(href + '&send=1'), {}, function (data) {
                                if (Tools.responseIsOk(data)) {
                                    if (data.msg) {
                                        Notifier.display('info', data.msg);
                                    }
                                    else {
                                        Notifier.display('info', 'Daten wurden erfolgreich gelöscht...');
                                    }

                                    if (typeof self.settings.onAfterDelConfirm == 'function') {
                                        self.settings.onAfterDelConfirm(e, data);
                                    }

                                    // refresh
                                    self.gridNavi.find('.refresh-grid').trigger('click');

                                }
                                else {
                                    jAlert(data.msg);
                                }
                            }, 'json');
                        }

                    });

                    return false;
                });
            });
        },
        /**
         * ------------------ End Data table row tools ------------------
         */


        /**
         * ------------------ Selection Tools ------------------
         */
        bindCheckAllEvent: function () {
            var self = this;

            var trs = this.dataTable.find('tr'), trlen = trs.length;


            this.headerThead.find('.chk-all').each(function () {
                if ($(this).next().hasClass('Zebra_TransForm_Checkbox')) {

                    $(this).next().unbind('click.chkall').bind('click.chkall', function () {
                        if ($(this).prev().is(':checked')) {


                            for (var x = 0; x < trlen; ++x) {
                                var td = $(trs[x]).find('td:first');
                                var cbk = td.find('input:checkbox:not(:checked)');
                                cbk.prop('checked', true);
                                cbk.attr('checked', true);
                                cbk.trigger('change');

                                $(trs[x]).addClass('selected-row');
                            }

                        }
                        else {
                            for (var x = 0; x < trlen; ++x) {
                                var td = $(trs[x]).find('td:first');
                                var cbk = td.find('input:checkbox:checked');
                                cbk.prop('checked', false);
                                cbk.attr('checked', false);
                                cbk.trigger('change');
                                cbk.removeAttr('checked');
                                $(trs[x]).removeClass('selected-row');
                            }
                        }

                        setTimeout(function () {
                            var selectedTotal = $(self.dataTable).find('tr.selected-row').length;

                            self.checkSelection();

                            self.gridNaviState.find('.grid-items-selected').text(sprintf(cmslang.totalitems_selected,
                                selectedTotal > 0 ? selectedTotal : 0
                            ));

                            if (self.selectionChecker) {
                                self.selectionChecker(self.getSelected());

                                self.gridNaviState.find('.grid-items-selected').text(sprintf(cmslang.totalitems_selected,
                                    selectedTotal > 0 ? selectedTotal : 0
                                ));
                            }
                        }, 100);

                    });
                }
            });

            this.headerThead.find('.chk-all').unbind('change.chkall').bind('change.chkall', function (e) {

                if ($(this).is(':checked')) {

                    if (!$(this).next().hasClass('Zebra_TransForm_Checkbox_Checked')) {
                        $(this).next().addClass('Zebra_TransForm_Checkbox_Checked')
                    }

                    for (var x = 0; x < trlen; ++x) {
                        var td = $(trs[x]).find('td:first');
                        var cbk = td.find('input:checkbox:not(:checked)');
                        cbk.prop('checked', true);
                        cbk.attr('checked', true);
                        cbk.trigger('change');

                        $(trs[x]).addClass('selected-row');
                    }


                }
                else {
                    if ($(this).next().hasClass('Zebra_TransForm_Checkbox_Checked')) {
                        $(this).next().removeClass('Zebra_TransForm_Checkbox_Checked')
                    }
                    for (var x = 0; x < trlen; ++x) {
                        var td = $(trs[x]).find('td:first');
                        var cbk = td.find('input:checkbox:checked');
                        cbk.prop('checked', false);
                        cbk.attr('checked', false);
                        cbk.trigger('change');
                        cbk.removeAttr('checked');
                        $(trs[x]).removeClass('selected-row');
                    }
                }

                setTimeout(function () {
                    var selectedTotal = $(self.dataTable).find('tr.selected-row').length;

                    self.gridNaviState.find('.grid-items-selected').text(sprintf(cmslang.totalitems_selected,
                        selectedTotal > 0 ? selectedTotal : 0
                    ));

                    self.checkSelection();

                    if (self.selectionChecker) {
                        self.selectionChecker(self.getSelected());

                        self.gridNaviState.find('.grid-items-selected').text(sprintf(cmslang.totalitems_selected,
                            selectedTotal > 0 ? selectedTotal : 0
                        ));
                    }

                    e.preventDefault();
                }, 100);
            });

        },
        removeCheckall: function () {
            if ($('.chk-all:checkbox', this.headerTableWrapper).is(':checked')) {
                $('.chk-all:checkbox', this.headerTableWrapper).removeAttr('checked').trigger('change');
            }
        },
        getSelected: function () {
            var ids = [];
            this.dataTableTbody.find('.selected-row :checkbox:checked').each(function () {
                ids.push($(this).val());
            });
            return ids;
        },
        checkSelection: function () {
            var self = this;
            var rows = this.dataTableTbody.find('tr').length, selected = this.dataTableTbody.find('tr.selected-row').length;

            var btn = this.gridActionButton;

            if (selected > 0) {
                if (btn) {
                    btn.enableButton();
                }

                setTimeout(function () {

                    if (Desktop.isWindowSkin) {
                        $('#' + Win.windowID).find('#grid-action,.subaction select,.subaction input').enableContext().change();
                        if ($('#' + Win.windowID).find('#grid-action').data('selectbox')) {
                            $('#' + Win.windowID).find('#grid-action').data('selectbox').isOpen = false;
                        }
                    }
                    else {

                        var tb = Core.getToolbar();
                        if (tb.length == 1) {
                            tb.find('#grid-action,.subaction select,.subaction input').enableContext().change();
                            if (tb.find('#grid-action').data('selectbox')) {
                                tb.find('#grid-action').data('selectbox').isOpen = false;
                            }
                        }
                    }
                }, 10);

            }
            else {
                if (btn) {
                    btn.disableButton();
                }

                setTimeout(function () {
                    if (Desktop.isWindowSkin) {
                        $('#' + Win.windowID).find('#grid-action,.subaction select,.subaction input').disableContext().change();

                        if ($('#' + Win.windowID).find('#grid-action').data('selectbox')) {
                            $('#' + Win.windowID).find('#grid-action').data('selectbox').isOpen = true;
                        }

                    }
                    else {

                        var tb = Core.getToolbar();
                        if (tb.length == 1) {
                            tb.find('#grid-action,.subaction select,.subaction input').disableContext().change();
                            if (tb.find('#grid-action').data('selectbox')) {
                                tb.find('#grid-action').data('selectbox').isOpen = true;
                            }
                        }
                    }

                }, 10);
            }

            $('.grid-items-selected', $('#' + Win.windowID)).html(sprintf(cmslang.totalitems_selected, selected));

            if (this.selectionChecker) {
                this.selectionChecker(this.getSelected());
            }

            return false;
        },
        /**
         * ------------------ End Selection Tools ------------------
         */



        /**
         * Building the extra window Toolbar
         * for grid filters
         */
        gridSearch: false,
        toggleSearchbar: function () {
            if (!this.searchbarcreated) {
                return;
            }
            var self = this;

            if (!this.searchbar.is(':visible')) {

                this.searchbar.show();

                if ($(self.inWindow).data('WindowManager')) {
                    $(self.inWindow).data('WindowManager').Toolbar.trigger('changeHeight');
                }

                Win.redrawWindowHeight($(self.inWindow).attr('id'), false);
                self.updateDataTableSize($(self.inWindow));

                $('.toggle-searchpanel', $(self.inWindow)).addClass('open');

                if (typeof self.settings.onAfterSearchToggle === 'function') {
                    self.settings.onAfterSearchToggle();
                }

            }
            else {
                this.searchbar.hide();

                if ($(self.inWindow).data('WindowManager')) {
                    $(self.inWindow).data('WindowManager').Toolbar.trigger('changeHeight');
                }

                Win.redrawWindowHeight($(self.inWindow).attr('id'), false);
                self.updateDataTableSize($(self.inWindow));

                $('.toggle-searchpanel', $(self.inWindow)).removeClass('open');

                if (typeof self.settings.onAfterSearchToggle === 'function') {
                    self.settings.onAfterSearchToggle();
                }

            }

        },
        buildGridSearchBar: function () {
            var self = this;

            if (this.searchbarcreated) {
                return;
            }

            if (
                typeof this.settings.searchitems == 'undefined' ||
                    this.settings.searchitems == false ||
                    this.settings.searchitems == null ||
                    this.settings.searchitems.length == 0
                ) {
                this.searchbarcreated = true;
                return;
            }

            var container = $('<div>').addClass('searchbar-row');
            for (var i in this.settings.searchitems) {
                var item = this.settings.searchitems[i];

                switch (item.type) {
                    case 'seperator':
                        container.append($('<span>').addClass('seperator'));
                        break;

                    // create new row
                    case 'wrap':
                        this.searchbar.append(container);
                        container = $('<div>').addClass('searchbar-row');
                        break;

                    default:
                        container.append(item.htmlcode);
                        break;
                }
            }

            if (this.searchbar.find('button.submit').length == 0) {
                container.append($('<button>').attr('type', 'button').append('Filtern').addClass('submit action-button filter').bind('click.gridsearch', function () {
                    self.sendSearch();
                }));
            }

            this.searchbarcreated = true;
            this.gridSearch = true;
            var winid = $(this.inWindow).attr('id');

            if (!$('#toolbar-' + winid).is(':visible') && this.isInited) {
                $('#toolbar-' + winid).show();
                Win.redrawWindowHeight(winid, false);
                this.updateDataTableSize($(this.inWindow));
            }

            this.searchbar.append(container);
        },
        /**
         * Building the extra Actions (select and button)
         * append to the window toolbar
         *
         */

        gridActionSelect: null,
        gridSubAction: null,
        buildGridActions: function () {

            if (this.gridActions !== false && this.gridActions.length > 0) {

                this.hasAction = true;
                this.hasGridAction = true;

                this.actionContainer = $('<div>').addClass('grid-actions');
                var self = this, selectbox = $('<select>').attr('id', 'grid-action');
                selectbox.appendTo(this.actionContainer);
                selectbox.append($('<option>').attr('value', '').text('----------------'));

                var addChangeState = false;
                for (var i = 0; i < this.gridActions.length; i++) {
                    var item = this.gridActions[i];
                    if (item.label) {
                        var option = $('<option>').attr('value', item.adm).text(item.label);

                        if (typeof item.msg === 'string' && item.msg !== '') {
                            option.attr('msg', item.msg);
                        }

                        if (typeof item.newtab !== 'undefined') {
                            option.attr('newtab', 'true');
                        }

                        selectbox.append(option);

                        if (typeof item.subaction !== 'undefined') {
                            addChangeState = true;

                            var subaction = $('<div>').css({
                                'display': 'inline',
                                marginLeft: '3px'
                            }).attr({
                                    id: 'subaction-' + item.adm
                                }).addClass('subaction').hide();
                            subaction.append($(item.subaction));
                            subaction.appendTo(this.actionContainer)
                        }
                    }
                }

                if (addChangeState) {

                    selectbox.change(function () {
                        var selectedval = $(this).find('option:selected').val();

                        if (!selectedval) {
                            self.actionContainer.find('.subaction').hide();
                            return;
                        }

                        if (selectedval) {
                            if (self.actionContainer.find('#subaction-' + selectedval).length) {
                                self.actionContainer.find('#subaction-' + selectedval).show().css({
                                    'display': 'inline-block'
                                });
                            }
                            else {
                                self.actionContainer.find('.subaction').hide();
                            }
                        }
                    });
                }

                this.gridActionSelect = selectbox;
                this.gridSubAction = subaction;

                var button = $('<button>').attr({
                    'type': 'button',
                    id: 'btn-grid-action'
                }).addClass('action-button');
                button.append('<span class="icn"></span><span class="label">Ausführen</span>');

                button.appendTo(this.actionContainer);
                button.bind('click.gridaction', function () {
                    if (!$(this).hasClass('button-disabled')) {
                        self.runGridAction();
                    }
                });

                button.disableButton();

                this.gridActionButton = button;

                if (Desktop.isWindowSkin) {
                    $('.window-toolbar', this.inWindow).append(this.actionContainer);
                }
                else {
                    var tb = Core.getToolbar();

                    if (tb.length) {
                        tb.append(this.actionContainer);
                    }

                }

            }

        },
        runGridAction: function () {
            var self = this;

            var gridAction = (Desktop.isWindowSkin ? $(this.inWindow).find('#grid-action') : Core.getToolbar().find('#grid-action'));
            var gridSelected = this.gridActionSelect.children("option").filter(":selected");
            var value = this.gridActionSelect.children("option").filter(":selected").val();

            if (!value) {
                console.log([value]);
            }

            var deleteMessage = this.gridActionSelect.children("option").filter(":selected").attr('msg');
            var selectedItems = this.getSelected();

            var extraParams = {};
            var subAct = this.actionContainer.find('#subaction-' + value);

            if (subAct.length === 1 && subAct.is(':visible')) {

                subAct.find('select,.inputS').each(function () {
                    var name = $(this).attr('name');
                    $(this).children("option").filter(":selected").each(function () {
                        if (name) {
                            extraParams[name] = $(this).val();
                        }
                    });
                });

                subAct.find('input').each(function () {
                    if ($(this).attr('name')) {
                        extraParams[$(this).attr('name')] = $(this).val();
                    }
                });
            }

            if (value != '' && selectedItems.length > 0) {
                var selected = selectedItems.join(',');
                var params = this.getParams();
                params.filter = null;
                params.page = null;
                params.perpage = null;
                params.sort = null;
                params.orderby = null;
                params.saveColumns = null;

                params.send = 1;
                params.ajax = 1;
                params.action = value;
                params.ids = selected;
                params.token = Config.get('token');
                eval('params.' + this.key + ' = ' + selected);

                $.extend(params, extraParams);
                if (value.match(/^del/i) || value.match(/^remove/i)) {
                    if (typeof deleteMessage === 'string' && deleteMessage !== '') {
                        jConfirm(deleteMessage, 'Bestätigung...', function (r) {
                            if (r) {
                                self.sendGridAction(params, true);
                            }
                        });
                    }
                    else {
                        jConfirm('Möchtest du die ausgewählten Inhalte wirklich löschen?', 'Bestätigung...', function (r) {
                            if (r) {
                                self.sendGridAction(params, true);
                            }
                        });
                    }
                }
                else {
                    if (typeof gridSelected.attr('newtab') != 'undefined' && gridSelected.attr('newtab')) {

                        var url = this.griddataurl;
                        url = url.replace(/([\?&]?)getGriddata=1/g, '');
                        params.getGriddata = null;
                        var url = this.griddataurl + '&' + $.param(params);

                        url = url.replace(/([\?&]?)send=1/g, '');
                        openTab({url: url, label: $.trim(gridSelected.text()), obj: null});
                    }
                    else {
                        self.sendGridAction(params);
                    }
                }
            }
        },
        sendGridAction: function (params, delmode) {
            var self = this;
            this.processing = true;
            this.gridNavi.find('.refresh-grid').hide().next().show();

            $(this.thead).find('span.checkbox').removeClass('checked').prev().attr('checked', false);
            $(this.tbody).find('span.checkbox').removeClass('checked').prev().attr('checked', false);

            if (this.processing) {
                this.viewport.mask('laden');
            }

            var url = this.griddataurl;
            url = url.replace(/([\?&]?)getGriddata=1/g, '');
            params.getGriddata = null;

            for (i in params) {
                if (params[i] === null || params[i] === "") {
                    delete params[i];
                }
            }
            if (typeof params.token == 'undefined') {
                params.token = Config.get('token');
            }
            setTimeout(function () {

                //alert(url + $.param(params));
                $.post(url.replace(/\?.*/, ''), params, function (data) {

                    self.processing = false;
                    $('.chk-all', self.headerTableWrapper).removeAttr('checked').trigger('change');

                    self.viewport.unmask();

                    if (Tools.responseIsOk(data)) {
                        if (Tools.isString(data.msg) && data.msg !== '0' && data.msg !== '1') {
                            Notifier.display('info', data.msg);
                        }
                        else {
                            if (delmode === true) {
                                Notifier.display('Daten wurden erfolgreich gelöscht...');
                            }
                        }

                        self.getData();
                    }
                    else {
                        jAlert(data.msg);
                    }
                }, 'json');
            }, 1);
        },
        /**
         * ------------------ Navigation ----------------------
         */

        /**
         * Building the Grid Navigation bar
         *
         */
        gridNaviCreated: false,
        gridNaviInputField: null,
        gridNaviPageOfPage: null,
        gridNaviRefreshButton: null,
        gridNaviLoadIndicator: null,
        gridNaviLoaded: null,
        gridNaviFirst: null,
        gridNaviLast: null,
        gridNaviNext: null,
        gridNaviPrev: null,
        buildGridNavigation: function () {

            if (this.gridNaviCreated) {
                return;
            }

            var self = this;

            this.gridNavi.empty();

            var navi = $('<div>').addClass('paging-container');
            var self = this;

            // add search toggle icon
            if (this.gridSearch) {
                navi.append(
                    $('<span>').addClass('remove-search').click(function () {
                        self.removeSearch();
                        // extra for the scrollbar
                        self.updateDataTableSize(self.inWindow);
                    })
                );

                navi.append(
                    $('<span>').addClass('toggle-searchpanel').click(function () {
                        self.toggleSearchbar();
                        // extra for the scrollbar

                    })
                );

                navi.append($('<span>').addClass('seperator'));
            }

            this.gridNaviFirst = $('<span>').addClass('first-page');
            this.gridNaviFirst.on('click', function () {
                if (!$(this).hasClass('disabled')) {
                    self.firstPage();
                }
            });

            this.gridNaviPrev = $('<span>').addClass('prev-page');
            this.gridNaviPrev.on('click', function () {
                if (!$(this).hasClass('disabled')) {
                    self.prevPage();
                }
            });

            navi.append(this.gridNaviFirst);
            navi.append(this.gridNaviPrev);
            navi.append($('<span>').addClass('seperator'));
            navi.append($('<span>').addClass('page-label').append('Seite'));

            this.gridNaviInputField = $('<input name="page" type="text" value="1"/>');
            this.gridNaviInputField.bind('blur',function () {

                var val = parseInt($(this).val());

                if (val > self.pages) {
                    $(this).val(self.pages);
                }

                if (val < 1) {
                    $(this).val(1);
                    val = 1;
                }

                if (isNaN(val)) {
                    $(this).val(1);
                    val = 1;
                }

                if (val != self.page) {
                    self.page = $(this).val();
                    self.getData();
                }
            }).bind('keyup', function (e) {
                    var val = parseInt($(this).val());

                    if (e.keyCode == 13 && val > 0 && val <= self.pages && val != self.page) {
                        self.page = val;
                        self.getData();
                    }
                });

            navi.append($('<span>').addClass('pageinput').append(this.gridNaviInputField));

            this.gridNaviPageOfPage = $('<span>').addClass('page-of-counter').append(sprintf(cmslang.ofPages, this.pages));

            navi.append(this.gridNaviPageOfPage);

            this.gridNaviNext = $('<span>').addClass('next-page');
            this.gridNaviNext.on('click', function () {
                if (!$(this).hasClass('disabled')) {
                    self.nextPage();
                }
            });

            this.gridNaviLast = $('<span>').addClass('last-page');
            this.gridNaviLast.on('click', function () {
                if (!$(this).hasClass('disabled')) {
                    self.lastPage();
                }
            });

            navi.append(this.gridNaviNext);
            navi.append(this.gridNaviLast);
            navi.append($('<span>').addClass('seperator'));

            // rows per page
            var rowsPerPage = $('<select>').attr('name', 'perpage');
            for (var i = 0; i < this.perpages.length; i++) {
                var opt = $('<option>').val(this.perpages[i]).append(sprintf(cmslang.perpage, this.perpages[i]));
                if (self.perpages[i] == this.settings.perpage) {
                    opt.attr('selected', 'selected');
                }

                rowsPerPage.append(opt);
            }

            rowsPerPage.unbind('change').bind('change', function () {
                if ($(this).val() != self.perpage) {
                    self.perpage = $(this).val();
                    Cookie.set('perpage', self.perpage);
                    self.getData();
                }
            });

            navi.append(rowsPerPage);

            navi.append($('<span>').addClass('seperator'));

            this.gridNaviRefreshButton = $('<span>').addClass('refresh-grid').click(function () {
                self.getData();
            });

            navi.append(this.gridNaviRefreshButton);

            this.gridNaviLoadIndicator = $('<span>').addClass('load').hide();
            navi.append(this.gridNaviLoadIndicator);
            this.gridNavi.append(navi);

            this.gridNaviLoaded = $('<span>').addClass('grid-load-indicator').hide();
            this.gridNavi.append(this.gridNaviLoaded);

            var button = $('<span>').attr({
                'title': cmslang.columnsettings,
                style: 'display:inline-block;width:18px;height:18px;'
            }).append($('<span>'));

            var colsettingsBtn = $('<div>').attr('id', 'grid_settings_container').css({
                'overflow': 'visible',
                position: 'relative'
            }).append(button);

            var pulldownId = 'gridcols-' + $(this.inWindow).attr('id');
            var griddropdown = $('<div>').attr('id', pulldownId).addClass('grid-dropdown').hide();
            this.gridNaviCreated = true;

        },
        /**
         *
         *
         */

        updateGridNavi: function () {
            this.gridNaviInputField.val(this.page);
            this.gridNaviPageOfPage.text(sprintf(cmslang.ofPages, this.pages));

            if (this.page <= 1) {
                this.gridNaviFirst.addClass('disabled');
                this.gridNaviPrev.addClass('disabled');
            }
            else {
                this.gridNaviFirst.removeClass('disabled');
                this.gridNaviPrev.removeClass('disabled');
            }

            if (this.page >= this.pages) {
                this.gridNaviLast.addClass('disabled');
                this.gridNaviNext.addClass('disabled');
            }
            else {
                this.gridNaviLast.removeClass('disabled');
                this.gridNaviNext.removeClass('disabled');
            }

            if (this.displayCounter) {
                this.gridNaviLoaded.show();

                var first = (this.count ? (parseInt(this.perpage) * (parseInt(this.page) - 1)) + 1 : 0);
                var last = first + parseInt(this.perpage) - 1;
                last = last < this.count ? last : this.count;

                var counter_text = sprintf(cmslang.resultsfromto, first, last, this.count);
                this.gridNaviLoaded.empty().html(counter_text);

            }
            else {
                this.gridNaviLoaded.empty().hide();
            }
        },
        /**
         * Grid Navigation button Events
         *
         */
        firstPage: function () {
            this.page = 1;
            this.getData();
        },
        prevPage: function () {
            this.page = (this.page > 1 ? this.page - 1 : 1);
            this.getData();
        },
        nextPage: function () {
            this.page = (this.pages > this.page ? this.page + 1 : this.page);
            this.getData();
        },
        lastPage: function () {
            this.page = this.pages;
            this.getData();
        },
        /**
         *
         * ------------------ End Navigation ------------------
         */









        /**
         * Refresh the Grid
         *
         */
        rto: null,
        refresh: function (callback) {
            var self = this;
            var win = this.headerTable.parents('.isWindowContainer');

            if (win.length && win !== this.inWindow) {
                this.inWindow = win;
            }

            this.getData(false, false, callback);
            /*
             if (typeof callback === 'function')
             {
             this.rto = setTimeout(function () {
             clearTimeout(self.rto);
             self.refreshCallBack(callback);
             }, 100);
             }
             */
        },
        refreshCallBack: function (callback) {
            var self = this;
            if (this.processing) {
                this.rto = setTimeout(function () {
                    self.refreshCallBack(callback);
                }, 50);
            }
            else {
                clearTimeout(this.rto);
                callback();
            }
        },
        /**
         * Update the grid if the window is resize
         *
         */
        updateDataTableSize: function (inWin, removeScroll, uiContent) {
            if (!this.isInited) {
                return;
            }
            if (inWin) {
                var self = this, grid = this;
                var contentHeight = 0, contentWidth = 0, winID = $(inWin).attr('id');

                if (uiContent && uiContent.width) {
                    contentHeight = uiContent.contentHeight;
                    contentWidth = uiContent.width;
                }
                else {

                    if (Desktop.isWindowSkin || Core.isWindowSkin ) {

                        if (inWin) {
                            contentWidth = parseInt($(inWin).width(), 10);
                            contentHeight = parseInt($('#body-content-' + winID).height(), 10);

                            if (!contentHeight || isNaN(contentHeight)) {
                                contentHeight = Core.getWindowContentHeight();
                            }

                            grid = this; //$(inWin).data('windowGrid');
                        }
                        else {

                            contentWidth = parseInt($(inWin).width(), 10);
                            contentHeight = parseInt($('#body-content-' + winID).height(), 10);

                            if (!contentHeight || isNaN(contentHeight)) {
                                contentHeight = Core.getWindowContentHeight();
                            }
                        }
                    }
                    else {
                        contentWidth = parseInt($('#content-container').width(), 10);
                        contentHeight = parseInt($('#content-container').height(), 10);
                    }
                }

                if (typeof grid != 'object') {
                    return;
                }

                if (typeof grid.wrapper == 'undefined') {
                    return;
                }

                if (grid.wrapper.parents('td:first').length) {
                    contentWidth = parseInt(grid.wrapper.parents('td:first').width(), 10);
                }

                grid.headerTable.width('100%');
                grid.dataTable.width('100%');

                var gridFooterHeight = parseInt(grid.gridFooter.outerHeight(), 10);
                var gridHeaderHeight = parseInt(grid.gridHeader.outerHeight(true), 10);
                var viewportHeight = contentHeight - gridFooterHeight;

                grid.viewport.height(viewportHeight).width('100%');
                grid.dataTableWrapper.height(viewportHeight - gridHeaderHeight).width('100%');
                $('#grid-scroll', $('#' + winID)).height(viewportHeight - gridHeaderHeight);


                if (removeScroll !== true) {
                    Tools.scrollBar($('#grid-scroll', $('#' + winID)));
                }


                // -------------- NEW VERSION
                // is faster as the old version

                var xself = grid, fixed = false, allColumnWidth = 0, cache = {}, tableWidth = grid.headerTable.parent().outerWidth(true);

                for (var n in grid.headerColumnCache) {
                    if (typeof grid.headerColumnCache[n] == 'object' && grid.headerColumnCache[n].is(':visible')) {
                        var tw = grid.headerColumnCache[n].width();
                        var atw = grid.headerColumnCache[n].attr('width');

                        if (typeof atw != 'undefined' && atw.match(/%/g)) {
                            cache[n] = atw.replace(/%/g, '');
                            grid.headerColumnCache[n].width('');
                            if (n != 'selector') {
                                allColumnWidth += grid.headerColumnCache[n].width();
                            }
                        }
                        else {
                            cache[n] = Math.round(100 * tw / tableWidth);
                            grid.headerColumnCache[n].width(tw);

                            if (n != 'selector') {
                                allColumnWidth += tw;
                            }
                        }
                    }
                }

                var selectorColumnFix = tableWidth - allColumnWidth;

                // convert px to %
                for (var n in grid.headerColumnCache) {

                    if (typeof grid.headerColumnCache[n] == 'object' && typeof cache[n] != 'undefined') {

                        var headCol = grid.headerColumnCache[n], dataCol = grid.firstDataRowCache[n];


                        if (n != 'selector'&&!grid.isFixedWidth(n)){
                            if (
                                headCol.attr('width') && headCol.attr('width').match(/%/) &&
                                    typeof dataCol != 'undefined' && typeof dataCol.attributes.width != 'undefined' && dataCol.attributes.width.match(/%/)) {
                                console.log('continue col: ', n);

                                continue;
                            }
                        }


                        if (n != 'selector' && !grid.isFixedWidth(n)) {
                            if (!fixed && selectorColumnFix > 22 && n != grid.key) {
                                // do not add width if the selector column is larger as 22px
                                headCol.css('width', '').attr('width', '');
                                if (typeof dataCol != 'undefined') {
                                    dataCol.style.width = ''
                                }
                                fixed = true;
                            }
                            else if (fixed && selectorColumnFix > 22 && n != grid.key) {
                                headCol.css('width', cache[n] + '%').attr('width', cache[n] + '%');
                                if (typeof dataCol != 'undefined') {
                                    dataCol.style.width = cache[n] + '%'
                                }
                            }
                            else {
                                headCol.css('width', cache[n] + '%').attr('width', cache[n] + '%');
                                if (typeof dataCol != 'undefined') {
                                    dataCol.style.width = cache[n] + '%'
                                }
                            }
                        }
                        else if ( grid.isFixedWidth(n) ) {
                            headCol.css('width', cache[n] + '%').attr('width', cache[n] + '%');
                            if (typeof dataCol != 'undefined') {
                                dataCol.style.width = cache[n] + '%'
                            }
                        }
                        else {
                            headCol.css('width', '22px').attr('width', '22');

                            if (typeof dataCol != 'undefined') {
                                dataCol.style.width = '22px'
                            }
                        }
                    }
                }

                return;


                // ----------------- OLD VERSION


                //if ( !Desktop.isWindowSkin ) {
                //    grid.dataTable.find('tr:eq(0)').find('td').width('');
                //}

                if (removeScroll !== true) {

                    Tools.scrollBar($('#grid-scroll', $('#' + winID)));

                    $('#grid-scroll', $('#' + winID)).width('');

                    if (grid.viewport.find('div.pane').is(':visible')) {
                        var w = contentWidth - parseInt(grid.viewport.find('div.pane').width(), 10);
                        //$('.grid-data table,.header-table', $('#' + winID)).width(w);
                        if (Desktop.isWindowSkin) {
                            grid.headerTable.width('100%');
                            grid.dataTable.width('100%');
                        }
                        else {
                            grid.headerTable.width('100%');
                            grid.headerTable.parent().css({
                                marginRight: $('#grid-scroll', $('#' + winID)).css('margin-right')
                            });
                        }
                    }
                    else {

                        // grid.headerTable.width('100%');
                        // grid.dataTable.width(contentWidth);
                        //$('.grid-data table,.header-table,table.data-table', $('#' + winID)).width(contentWidth);
                    }

                    var xself = this, cache = [], tableWidth = this.headerTable.parent().outerWidth(true);
                    this.headerThead.find('th:not([rel=selector])').each(function (i) {
                        cache[i] = Math.round(100 * $(this).width() / tableWidth);
                        $(this).width($(this).width() + 'px');
                    });


                    // convert px to %
                    this.headerThead.find('th:not([rel=selector])').each(function (i) {
                        $(this).css('width', '').attr('width', cache[i] + '%');
                        if (typeof xself.firstDataRowCache[$(this).attr('rel')] != 'undefined') {
                            xself.firstDataRowCache[$(this).attr('rel')].style.width = cache[i] + '%'
                        }
                    });

                    grid.updateColumnSizes(inWin);
                    //refresh table row events

                    /*
                     $('#' + winID).find('#grid-scroll').css({
                     marginRight: ''
                     });
                     */
                    // Win.prepareWindowFormUi(self.inWindow);

                }
                else {
                    $('#grid-scroll', $('#' + winID)).width('');

                    if (grid.viewport.find('div.pane').is(':visible')) {
                        var w = contentWidth - parseInt(grid.viewport.find('div.pane').width(), 10);
                        if (Desktop.isWindowSkin) {
                            grid.headerTable.width('100%');
                            grid.dataTable.width('100%');
                        } else {
                            grid.headerTable.width('100%');
                            grid.headerTable.parent().css({
                                marginRight: $('#grid-scroll', $('#' + winID)).css('margin-right')
                            });
                        }
                    }
                    else {
                        // grid.headerTable.width('100%');
                        // grid.dataTable.width(contentWidth);
                    }
                    var xself = this, cache = [], tableWidth = this.headerTable.parent().outerWidth(true);
                    this.headerThead.find('th:not([rel=selector])').each(function (i) {
                        cache[i] = Math.round(100 * $(this).width() / tableWidth);
                        $(this).width($(this).width() + 'px');
                    });


                    // convert px to %
                    this.headerThead.find('th:not([rel=selector])').each(function (i) {
                        $(this).css('width', '').attr('width', cache[i] + '%');
                        if (typeof xself.firstDataRowCache[$(this).attr('rel')] != 'undefined') {
                            xself.firstDataRowCache[$(this).attr('rel')].style.width = cache[i] + '%'
                        }
                    });
                    grid.updateColumnSizes(inWin);
                }
            }
        },
        refreshTables: function (ui, winID, grid) {
            if (!winID)
                return;

            var winObj = $('#' + winID), winData = $('#' + winID).data('WindowManager');

            if (!winData || !winObj.length) {
                return;
            }

            updateDataTableSize(winObj);
            return;
        },
        /**
         * Table column tools
         *
         */
        setDataColumnWidth: function (columnName, width) {

        },
        /**
         * Data Scrollbar
         *
         */
        enableDataScrollbar: function (win, scrollToItem) {
            if (!win) {
                win = $('#' + Win.windowID);
            }
            Tools.scrollBar(win.find('#grid-scroll'));
        },
        disableDataScrollbar: function (win) {
            if (!win) {
                win = $('#' + Win.windowID);
            }
            Tools.scrollBar(win.find('#grid-scroll'));
        },
        addDataScrollbar: function (ui, winID, grid) {
            if (!winID) {
                return;
            }
            Tools.scrollBar($('#' + winID).find('#grid-scroll'));
        },
        removeDataScrollbar: function (winID) {
            return;
        },
        /**
         * ------------------ Tools ------------------
         *
         */

        getHeaderCellDimensions: function (column, columnName, x) {
            if (typeof column != 'undefined') {
                var width = (x == 0 ? column.outerWidth(true) : column.innerWidth());
                width = parseInt(width, 10);
                return width;
            }
        },
        useTable: false,
        updateColumnSizes: function (inWindow) {
            var self = this;
            return;

            /*
             if (this.useTable === false) {
             this.useTable = self.dataTable; //$(inWindow).find('table.data-table');
             }
             */
            for (var columnName in self.headerColumnCache) {
                if (typeof self.firstDataRowCache[columnName] != 'undefined') {
                    var column = self.headerColumnCache[columnName][0];

                    if (column && column.visible) {
                        self.firstDataRowCache[columnName].style.width = column.offsetWidth; //self.headerColumnCache[columnName].find(':first-child').width() + 1;
                    }
                }
            }

            return;

            this.useTable.each(function () {
                var container = $(this).parents('div.sub-content:first');

                if (container.length) {
                    if (container.is(':visible')) {
                        var x = 0;
                        for (var columnName in self.headerColumnCache) {
                            if (typeof self.firstDataRowCache[columnName] != 'undefined') {
                                var column = self.headerColumnCache[columnName];

                                if (column && $(column).is(':visible')) {
                                    // var w = parseInt(column.innerWidth(), 10);

                                    self.firstDataRowCache[columnName].style.width = (columnName === 'selector' ? '22' : self.getHeaderCellDimensions($(column), columnName, x)) + 'px';

                                    //   $(self.firstDataRowCache[columnName]).attr('width', (columnName === 'selector' ? '22' : self.getHeaderCellDimensions($(column), columnName, x)));
                                    x++;
                                }
                            }
                        }
                    }
                }
                else {
                    for (var columnName in self.headerColumnCache) {
                        if (typeof self.firstDataRowCache[columnName] != 'undefined') {
                            var column = self.headerColumnCache[columnName];

                            if (column && $(column).is(':visible')) {
                                // var w = parseInt(column.innerWidth(), 10);
                                self.firstDataRowCache[columnName].style.width = (columnName === 'selector' ? '22' : self.getHeaderCellDimensions($(column))) + 'px';
                                //$(self.firstDataRowCache[columnName]).attr('width', (columnName === 'selector' ? '22' : self.getHeaderCellDimensions($(column))));
                            }
                        }
                    }
                }
            });

        },
        getViewOptions: function (objectToInsert) {
            this.initColsettingsDopdown(objectToInsert);
        },
        initColsettingsDopdown: function (griddropdown, colsettingsBtn) {
            var self = this;

            if (griddropdown) {
                var dd = griddropdown;
                dd.empty();

                var ul = $('<ul>');
                for (var col in self.settings.colModel) {
                    var column = self.settings.colModel[col];

                    if (column && column.name) {

                        // skip fore visible colums & not label columns
                        if (column.forcevisible || !column.label || column.label == '&nbsp;') {
                            continue;
                        }

                        var li = $('<li>');
                        var label = $('<label>').attr({
                            'for': 'f-' + column.name
                        });

                        var cbk = $('<input>').attr({
                            type: 'checkbox',
                            'value': '1'
                        }).attr('name', 'f_' + column.name).attr('id', 'f-' + column.name);

                        li.attr('id', 'field-' + column.name);

                        if (column.isvisible) {
                            cbk.attr('checked', true);
                        }

                        cbk.bind('change.columns', function () {
                            // self.updateCols(this);
                            if ($(this).parent().next().hasClass('InputCheckboxContent')) {
                                if ($(this).is(':checked')) {
                                    $(this).parent().next().removeClass('checkboxFalse').addClass('checkboxTrue');
                                }
                                else {
                                    $(this).parent().next().removeClass('checkboxTrue').addClass('checkboxFalse');
                                }
                            }

                            if ($(this).is(':checked')) {
                                self.showColumn($(this).attr('name').replace('f_', ''));
                            }
                            else {
                                self.hideColumn($(this).attr('name').replace('f_', ''));
                            }

                        });

                        li.click(function () {
                            var ischecked = true;
                            if ($('input:checkbox', $(this)).is(':checked') || $('input:checkbox', $(this)).checked) {
                                ischecked = false;
                            }

                            $('input:checkbox', $(this)).prop('checked', ischecked).trigger('change');
                            $('#App-Menu li.active').removeClass('active');
                            $('.submenu', $('#App-Menu')).removeClass('active').hide();
                        });

                        li.append(cbk).append(column.label);
                        ul.append(li);

                    }
                }

                ul.appendTo(dd);

                if (typeof colsettingsBtn != 'undefined') {
                    $(colsettingsBtn).click(function () {
                        var offset = $(colsettingsBtn).position();

                        $(griddropdown).css({
                            //top: $(this).parent().position().top,
                            left: $(colsettingsBtn).position().left - $(griddropdown).width()
                        }).slideToggle(350, function () {
                                // send the data when the dropdown closed
                                // make more speed and slower server load :)
                                if (!$(this).is(':visible')) {
                                    //self.sendUpdateCols();
                                }
                            });
                    });
                }
            }
        },
        showColumn: function (columnName) {
            this.headerColumnCache[columnName].show();
            $(this.headerColumnCache[columnName]).get(0).visible = true;

            var self = this, cache = [], tableWidth = $('#content-container').length ? $('#content-container').width() : this.headerTable.parent().outerWidth(true);

            this.dataTableTbody.find('td[rel="' + columnName + '"]').css('display', 'table-cell');

            var firstRowCols = this.dataTableTbody.find('tr:first td');
            this.headerThead.find('th').each(function (i) {
                var percent = Math.round(100 * $(this).width() / tableWidth);
                $(this).width('').attr('width', percent + '%');
                firstRowCols.eq(i).width($(this).width());
            });

            // update table column sizes
            //	this.updateColumnSizes( this.inWindow );

            var tmp = [];
            for (var k in this.allVisibleFields) {
                if (this.allVisibleFields[k] != columnName) {
                    tmp.push(this.allVisibleFields[k]);
                }
            }
            tmp.push(columnName);

            this.allVisibleFields = tmp;
            this.visibleCols++;

            $(window).trigger('resize');

            setTimeout(function () {
                self.updateColumnSizesAndOrders();
            }, 1000);
        },
        hideColumn: function (columnName) {
            this.headerColumnCache[columnName].hide();
            $(this.headerColumnCache[columnName]).get(0).visible = false;

            var self = this, cache = [], tableWidth = $('#content-container').length ? $('#content-container').width() : this.headerTable.parent().outerWidth(true);

            this.dataTableTbody.find('td[rel="' + columnName + '"]').css('display', 'none');

            var firstRowCols = this.dataTableTbody.find('tr:first td');
            this.headerThead.find('th').each(function (i) {
                var percent = Math.round(100 * $(this).width() / tableWidth);
                $(this).width('').attr('width', percent + '%');
                firstRowCols.eq(i).width($(this).width());
            });

            // update table column sizes
            //	this.updateColumnSizes( this.inWindow );

            var tmp = [];
            for (var k in this.allVisibleFields) {
                if (this.allVisibleFields[k] != columnName) {
                    tmp.push(this.allVisibleFields[k]);
                }
            }

            this.allVisibleFields = tmp;
            this.visibleCols++;

            $(window).trigger('resize');

            setTimeout(function () {
                self.updateColumnSizesAndOrders();
            }, 1000);

        },
        countVisibleColumns: function () {

            this.allFields = [];
            this.allVisibleFields = [];
            this.visibleCols = 0;

            for (var col in this.settings.colModel) {
                var column = this.settings.colModel[col];
                if (column.name) {
                    this.allFields.push(column.name);

                    if (column.isvisible) {
                        this.allVisibleFields.push(column.name);
                        this.visibleCols++;
                    }
                }
            }
        },
        findColumnByName: function (name, columnModel) {
            for (var col in columnModel) {
                var column = columnModel[col];

                if (column.name === name) {
                    return column;
                }
            }

            return null;
        },
        getParams: function () {
            var params = {};
            var url = this.griddataurl;

            if (this.gridForm !== null) {
                $.extend(params, this.gridForm.serialize());

            }

            if (this.searchParams !== false) {
                $.extend(params, this.searchParams);

                if (typeof this.searchParams.removefilter !== 'undefined') {
                    this.searchParams = false;
                }
            }

            var p = Tools.convertUrlToObject(url);
            params = $.extend(params, p);
            params.adm = $.getURLParam("adm", url);
            params.filter = false;

            if (!this.filterOn) {
                params.q = '';
            }
            else {
                params.filter = true;
            }

            params.page = this.page;
            params.perpage = this.perpage;
            params.sort = this.sort;
            params.orderby = this.orderby;

            if (typeof params.saveColumns != 'undefined') {
                delete params.saveColumns;
            }
            if (typeof params.saveColumns != 'undefined') {
                delete params.saveColumns;
            }
            if (typeof params.widths != 'undefined') {
                delete params.widths;
            }

            return params;
        },
        filterOn: false,
        sendSearch: function (e) {
            var self = this;
            this.searchParams = {};
            var items;

            if (Desktop.isWindowSkin) {
                var items = $('.tablegrid-searchbar', $('#' + Win.windowID)).find('input,select');
            }
            else {
                var tb = Core.getToolbar();
                if (!tb) {
                    return;
                }
                var items = tb.find('.tablegrid-searchbar').find('input,select');
            }

            items.each(function () {
                var type = $(this).attr('type');
                var name = $(this).attr('name');
                var val = null;

                if (typeof type !== 'undefined' && (type.toLowerCase() == 'radio' || type.toLowerCase() == 'checkbox')) {
                    val = ($(this).is(':checked') || $(this).prop('checked') || $(this).get(0).checked ? $(this).val() : '');
                }

                if (typeof type !== 'undefined' && type.toLowerCase() == 'text') {
                    val = $(this).val();
                }

                if ($(this).get(0).tagName.toLowerCase() == 'select') {
                    val = $(this).find(':selected').val();
                }

                self.searchParams[name] = val;
            });

            this.searchParams['filter'] = true;

            $('.remove-search').addClass('active');
            this.filterOn = true;
            this.getData();
        },
        removeSearch: function () {
            if (this.filterOn) {

                this.filterOn = false;
                this.searchParams = {};
                this.searchParams['filter'] = false;
                $('.remove-search').removeClass('active');
                this.getData();

            }
        },
        /**
         * ------------------ END Tools ------------------
         */

        /**
         * Load Data
         *
         */
        getData: function (isFirstRun, loadurlParams, callbackGetData) {
            var self = this;

            /// $(this.inWindow).data('WindowManager').enableLoading();

            this.viewport.unmask();

            this.dataTableWrapper.mask(cmslang.gettingData);

            // hide the navi refresh button
            $(this.gridNavi).find('.refresh-grid').css({
                display: 'none'
            });

            // show the navi loading button
            $(this.gridNavi).find('.load').css({
                display: 'block'
            }).each(function () {

                    // load from existing data
                    if (isFirstRun === true && typeof self.datarows === 'object') {
                        self.processing = true;

                        self.removeCheckall();

                        var rows = {
                            datarows: self.datarows
                        };
                        self.buildDataTable(rows);

                        self.pages = Math.ceil(self.count / self.perpage);

                        if (!self.pages) {
                            self.pages = 1;
                        }

                        self.gridNavi.find('.page-of-counter').html((self.pages > 0 ? self.pages : 1));
                        self.gridNavi.find('.pageinput input').val(self.page);

                        self.updateGridNavi();

                        if (self.selectionChecker) {
                            self.selectionChecker(self.getSelected());
                        }

                        if (typeof callbackGetData === 'function') {
                            callbackGetData(null, self);
                        }

                        Win.prepareWindowFormUi(self.inWindow);

                        self.gridNavi.find('.refresh-grid').show().next().hide();

                        self.processing = false;

                        self.dataTableWrapper.unmask();


                        // extra for the scrollbar
                        self.updateDataTableSize(self.inWindow);

                    }
                    else {
                        // get data by ajax request
                        self.processing = true;

                        var dataparams = self.getParams();
                        if (loadurlParams !== null && loadurlParams !== false && typeof loadurlParams !== 'undefined') {
                            $.extend(dataparams, loadurlParams);
                        }

                        dataparams.getGriddata = true;
                        dataparams.ajax = 1;
                        dataparams.token = Config.get('token');

                        var preparedUrl = Tools.prepareAjaxUrl(self.griddataurl);

                        self.removeCheckall();

                        $.ajax({
                            url: preparedUrl,
                            type: 'POST',
                            dataType: 'json',
                            data: dataparams,
                            async: true,
                            cache: false,

                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                try {
                                    if (self.settings.onError) {
                                        self.settings.onError(XMLHttpRequest, textStatus, errorThrown);
                                    }
                                } catch (e) {
                                }
                            },
                            success: function (data) {
                                if (Tools.responseIsOk(data)) {



                                    //  $(self.inWindow).data('WindowManager').disableLoading();

                                    self.buildDataTable(data);

                                    self.count = data.total;
                                    self.pages = Math.ceil(self.count / self.perpage);
                                    if (!self.pages) {
                                        self.pages = 1;
                                    }
                                    self.updateGridNavi();

                                    if (self.selectionChecker) {
                                        self.selectionChecker(self.getSelected());
                                    }

                                    self.gridNavi.find('.refresh-grid').show().next().hide();

                                    Win.prepareWindowFormUi(self.inWindow);

                                    self.processing = false;


                                    // extra for the scrollbar
                                    if (self.page <= 1) {
                                        self.updateDataTableSize(self.inWindow, true);
                                    }
                                    if (typeof data.debugoutput === 'string') {
                                        DesktopConsole.setDebug(data.debugoutput);
                                    }
                                    Tools.scrollBar(self.inWindow.find('#grid-scroll'), 'top');

                                    if (typeof callbackGetData === 'function') {
                                        callbackGetData(null, self);
                                    }

                                    self.dataTableWrapper.unmask();

                                }
                                else {
                                    self.dataTableWrapper.unmask();
                                }
                            }
                        });

                    }

                });

        }
    };

};
function dcmsGrid(el, options) {
    Application.Grid(el, options, Win.windowID);
}