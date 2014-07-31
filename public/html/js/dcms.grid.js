var Grid = function(){
    
    return {
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
            onAfterLoad: false,
            table: '',        
            updatecolsettingsUrl: null
        },
        settings: { },
    
    
    
        page: 1,
        pages: 1,
        count: 0,
        query: '', // search query
        no_data_msg:  cmslang.emptygriddata,
        results_from_to:  cmslang.resultsfromto,
        visibleCols:  0,
        searchParams:  false,
        perpages:  [10,20,30,40,50,75,100],
        allFields:  [],
        sendChange:  false,
        gridActions:  false,
        onAfterLoad:  false,
        processing: false,
    
        // events
        renderers: false,
        selectionChecker: false,
        doubleClickHandler: false,
        rowDecorator: false,
        postDataFetchHandler: false,
        currentActiveFilter: false,
        onAfterLoad: false,
        searchParams: {},
    
    
        // column change event
        sendChange: false,

    

        // in this container will show the grid
        element: null,
        inWindow: false,
    
    
    
        visibleCols: 0,
        wrapper: null,    
        searchbar: null,
        gridNavi: null,
        toolbarfooter: null,
        toolbarinited: false,   // buildGridNavipanel
        searchbarcreated: false, // buildGridToolbar
    
    
    
    
        /**
     *  Grid Header
     */
        headerTableWrapper: null,
        headerTable: null,
        headerThead: null,
        headerTh: null,

        /**
     *  DATA
     */
        dataTableWrapper: null,
        dataTable: null,
        dataTbody: null,
    
    
        gridForm: null,
    
    
        getOption: function(key)
        {
            var ns = this.settings;
        },
    
        create: function(container, win, options)
        {
            var self = this;
            this.element = $(container);
    
            if (Config.get('useWindowStyledTabs', false) && win)
            {
                this.inWindow = win;
            }

            this.settings = $.extend(this.defaults, options);

            // Form detector
            var form = this.element.parents('form:first');
            if ( form.children().find('#' + container.attr('id') ).length == 1 )
            {
                this.gridForm = form;
            }


            this.griddataurl = this.settings.url;
            this.updatecolsettingsUrl = '&table='+ this.settings.table +'&allfields=%s&visiblefields=%s';
        
            this.filterformfields = this.settings.filterfields;        
        
            this.perpage = this.settings.perpage;
            this.datarows = this.settings.datarows || false; // Table rows
            this.count = this.settings.total || 0;
            this.pages = Math.ceil(this.count/this.perpage);        
            this.key = this.settings.key || 'id'; // primary key
            this.sort = this.settings.sort || 'asc'; // Table sortorder name if is sorted
            this.orderby = this.settings.orderby || this.key; // The Table sortorder orderby
        
        
        
            this.currentActiveFilter = this.settings.currentFilter || false;

            if ( this.currentActiveFilter !== false )
            {
                this.searchParams = {};
                $.extend(this.searchParams, this.currentActiveFilter);
            }

            this.selectionChecker = this.settings.selectionChecker || false; // optional callback to handle external stuff when a row is selected.
            this.doubleClickHandler = this.settings.doubleClick || false; // optional callback to handle external stuff when a row is double clicked
            this.rowDecorator = this.settings.rowDecorator || false; // optional callback used to decorate rows after they've been built
            this.postDataFetchHandler = this.settings.postDataFetchHandler || false; // optional callback used to handle data after rows have been built
            this.onAfterLoad = this.settings.onAfterLoad || false;

        
        
            // 
            this.wrapper = $('<div>').addClass('grid-table-wrapper');
            this.searchbar = $('<div>').addClass('tablegrid-searchbar');        
            this.gridNavi = $('<div>').addClass('tablegrid-toolbar');        
        
        
            this.Viewport = $('<div>').addClass('tablegrid-viewport');
        
            $(this.element).empty().append( this.wrapper );
        
            // count all visible cols
            for(var col in this.settings.colModel) {
                this.allFields.push(this.settings.colModel[col].name);

                // skip invisible cols
                if ( !this.settings.colModel[col].isvisible )
                {
                    continue;
                }
                this.visibleCols++;
            }
        
        
        
        
        
            /**
         * Grid Header Table
         */
            this.headerTableWrapper = $('<div>').css({
                width: '100%'
            }).addClass('grid-header');
            this.headerTable = $('<table>').addClass('header-table');
            this.headerThead = $('<thead>');
        

            $(this.headerTableWrapper).append(this.headerTable);
            $(this.headerTable).append(this.headerThead);
        
            this.Viewport.append(this.headerTableWrapper);
        
            /**
         * Grid Data table
         */        
            this.dataTableWrapper = $('<div>').css({
                width: '100%'
            }).addClass('grid-data gui-scroll');
        
            this.dataTable = $('<table>').addClass('data-table');
            this.dataTbody = $('<tbody>');
            $(this.dataTable).append(this.dataTbody);
            $(this.dataTableWrapper).append(this.dataTable);
        
        
            this.Viewport.append(this.dataTableWrapper);
        
        
            //$(this.wrapper).append(this.Viewport);

            // -------------------------- START ------------------------------



            /**
         *  Build the search panel if exists
         */
            this.buildGridToolbar();
        
            /**
         *  Build the table header
         */
            this.buildGridTableHeader();
        
        
            //  adding search panel to wrapper
            if (this.searchbarcreated) {
                $(this.wrapper).append(this.searchbar);
            }
        
            //  table header to wrapper
            $(this.wrapper).append(this.Viewport);
        
            /**
         *  create grid navigation panel
         */
            this.buildGridNavipanel();
        
            //  data table to wrapper
            $(this.wrapper).append(this.Viewport);
        
            //  gridNavi to wrapper
            this.wrapper.append(this.gridNavi);
        
        
        

            // building data
            if (this.datarows == 'object' && this.datarows != false )
            {
                this.buildGridTableData({
                    datarows: this.datarows
                });
            }
            else
            {
                this.getData();
            }
        
        
        
            // resizing?
            this.updateDataTableSize(this.inWindow);
            
            var winbody = WindowCreator.getWinbodyFromWindow(this.inWindow);
            winbody.on('resize', function(){
                //self.updateDataTableSize(this);
            });
            
        },
    
    
        updateDataTableSize: function(inWin)
        {        
       
            if (inWin)
            {
                var headerHeight = this.headerTable.outerHeight();
                var searchbarHeight = this.searchbar.outerHeight();
                var naviHeight = this.gridNavi.outerHeight(); 
                var bodySize = WindowCreator.getWindowBodySize($(inWin));
                var winSize = WindowCreator.getWindowSize($(inWin));
                var body = $(inWin).find('.mwindow-body-content:first');
                
                body.css({
                    margin: 0
                });
                body.parent().css({
                    margin: 0
                });

                $(this.Viewport, $(inWin) ).css({
                    width: winSize.width,
                    height: (bodySize.height - headerHeight - naviHeight - searchbarHeight)
                });
                
                $(this.dataTableWrapper, $(inWin) ).css({
                    width: winSize.width,
                    height: (bodySize.height - headerHeight - naviHeight - searchbarHeight)
                });
                
                // set width by px not by percent
                this.updateAllColumnWidths(inWin);
            
                $(this.dataTableWrapper, $(inWin)).scrollbars();
            }
        },
    
    
    
    
    
        buildGridToolbar: function()
        {
            var self = this;
        
            if ( this.searchbarcreated )
            {
                return;
            }
        
        
            if (
                typeof this.settings.searchitems == 'undefined' ||
                this.settings.searchitems == false ||
                this.settings.searchitems == null ||
                this.settings.searchitems.length == 0
                )
                {
                return;
            }

            var container = $('<div>').addClass('searchbar-row');
            for (var i in this.settings.searchitems)
            {
                var item = this.settings.searchitems[i];

                switch (item.type)
                {
                    case 'seperator':
                        container.append( $('<span>').addClass('seperator') );
                        break;

                    // create new row
                    case 'wrap':
                        this.searchbar.append( container );
                        container = $('<div>').addClass('searchbar-row');
                        break;

                    default:
                        container.append( item.htmlcode );
                        break;
                }
            }


            if ( this.searchbar.find('button.submit').length == 0 )
            {
                container.append( $('<button>').attr('type', 'button').append('Filtern').addClass('submit action-button').click(function() {
                    self.sendSearch();
                }));
            }


            this.searchbar.append(container);
            this.searchbarcreated = true;
        

        },
    
        buildGridNavipanel: function()
        {
            if ( this.toolbarinited )
            {
                return;
            }
        
            this.gridNavi.empty();
        
            var navi = $('<div>').addClass('paging-container');
            var self = this;

            // add search toggle icon
            if ( typeof this.settings.searchitems != 'undefined' && 
                this.settings.searchitems != false && 
                this.settings.searchitems != null &&
                this.settings.searchitems.length > 0
                )
                {
                
        
                navi.append( 
                    $('<span>').addClass('remove-search').click(function(){
                        self.removeSearch();
                        GUI.updateScrollSize();
                    } ) 
                    );
            
                navi.append( 
                    $('<span>').addClass('toggle-searchpanel').click(function(){
                        self.toggleSearchbar();
                        GUI.updateScrollSize();
                    } ) 
                    );
            
                navi.append( $('<span>').addClass('seperator') );
            }

            navi.append( $('<span>').addClass('first-page') );
            navi.append( $('<span>').addClass('prev-page') );
            navi.append( $('<span>').addClass('seperator') );
            navi.append( $('<span>').addClass('page-label').append('Seite') );

            var input = $('<input name="page" type="text" value="1"/>');
            input.bind('blur', function() {
                if(parseInt($(this).val()) > self.pages) {
                    $(this).val(self.pages);
                }

                if(parseInt($(this).val()) < 1) {
                    $(this).val(1);
                }

                if(isNaN($(this).val())) {
                    $(this).val(1);
                }

                if( parseInt($(this).val()) <= self.page) {
                    self.page = $(this).val();
                    self.getData();
                }
            });

            navi.append( $('<span>').addClass('pageinput').append( input ) );
            navi.append( $('<span>').addClass('page-of-counter').append('von 1') );
            navi.append( $('<span>').addClass('next-page') );
            navi.append( $('<span>').addClass('last-page') );
            navi.append( $('<span>').addClass('seperator') );
        
        
        
        
            // rows per page
            var rowsPerPage = $('<select>').attr('name', 'perpage');
            for(var i =0; i<self.perpages.length;i++)
            {
                var opt = $('<option>').val(self.perpages[i]).append( sprintf(cmslang.perpage, self.perpages[i] ) );
                if ( self.perpages[i] == self.settings.perpage )
                {
                    opt.attr('selected', 'selected');
                }

                rowsPerPage.append( opt );
            }
        
            rowsPerPage.unbind('change').bind('change', function() {
                if($(this).val() != self.perpage) {
                    self.perpage = $(this).val();
                    Cookie.set('perpage', self.perpage);
                    self.getData();
                    GUI.updateScrollSize();
                }
            });
        
            navi.append(rowsPerPage);
        
            navi.append( $('<span>').addClass('seperator') );
            navi.append( $('<span>').addClass('refresh-grid').click(function(){
                self.getData();
            } ) );
        
        
            this.gridNavi.append(navi);
            this.gridNavi.append($('<span>').addClass('grid-load-indicator').hide());
        
        
        
            var button = $('<span>').attr({
                'title': cmslang.columnsettings,
                style:'display:inline;float:right;width:18px;height:18px;'
            }).append( $('<span>') );
        
        
            var colsettingsBtn = $('<div>').attr('id', 'grid_settings_container').css({
                'overflow': 'visible',
                position: 'relative'
            }).append( button );
        
        
        
            var griddropdown = $('<div>').addClass('grid-dropdown').hide();
        
        
            this.initColsettingsDopdown(griddropdown, colsettingsBtn);
        
            this.gridNavi.append( colsettingsBtn ).append( griddropdown );
        
        
            this.toolbarinited = true;
        },
    
        initColsettingsDopdown: function(griddropdown, colsettingsBtn) {
            var self = this;

            if (griddropdown)
            {
                var dd = griddropdown;
                dd.empty();

                var ul = $('<ul>');
                for(var col in self.settings.colModel) {
                    var column = self.settings.colModel[col];
                
                    var li = $('<li>');
                    var label = $('<label>').attr({
                        'for': 'f-'+ column.name
                    });
                
                    var cbk = $('<input>').attr({
                        type: 'checkbox',
                        'value': '1'
                    }).attr('name', 'f_'+ column.name).attr('id', 'f-'+ column.name);
                
                    li.attr('id', 'field-'+ column.name);

                    if( column.isvisible )
                    {
                        cbk.attr('checked', true);
                    }

                    cbk.change(function(){
                        self.updateCols(this);
                    });

                    label.append(cbk).append(column.label);
                    li.append(label);
                    ul.append(li);
                }

                ul.appendTo(dd);

                colsettingsBtn.click(function(){
                    dd.slideToggle(350, function(){
                        // send the data when the dropdown closed
                        // make more speed and slower server load :)
                        if ( !$(this).is(':visible') )
                        {
                        //self.sendUpdateCols();
                        }
                    });
                });
            }
        },
    
    
    
        updateGridNavi: function()
        {
            var self = this;        
            var nav = this.gridNavi;


            // button 'first'
            if(this.page <= 1) {
                nav.find('.first-page').addClass('disabled').unbind('click').css({
                    cursor: 'default'
                });
            } else {
                nav.find('.first-page').removeClass('disabled').bind('click', function(){
                    self.firstPage(self)
                }).css({
                    cursor: 'pointer'
                });
            }

            // button 'previous'
            if(this.page <= 1) {
                nav.find('.prev-page').addClass('disabled').unbind('click').css({
                    cursor: 'default'
                });
            } else {
                nav.find('.prev-page').removeClass('disabled').bind('click', function(){
                    self.previousPage(self)
                }).css({
                    cursor: 'pointer'
                });
            }

            // button 'next'
            if(this.page >= this.pages) {
                nav.find('.next-page').addClass('disabled').unbind('click').css({
                    cursor: 'default'
                });
            } else {
                nav.find('.next-page').removeClass('disabled').bind('click', function(){
                    self.nextPage(self)
                }).css({
                    cursor: 'pointer'
                });
            }

            // button 'last'
            if(this.page >= this.pages) {
                nav.find('.last-page').addClass('disabled').unbind('click').css({
                    cursor: 'default'
                });
            } else {
                nav.find('.last-page').removeClass('disabled').bind('click', function(){
                    self.lastPage(self)
                }).css({
                    cursor: 'pointer'
                });
            }

            // update the counter
            if(this.settings.displayCounter)
            {
                var counter = nav.find('.grid-load-indicator');
                counter.show();

                var first = (parseInt(this.perpage) * (parseInt(this.page) - 1)) + 1;
                var last = first + parseInt(this.perpage) - 1;
                last = last < this.count ? last : this.count ;

                var counter_text = sprintf( cmslang.resultsfromto, first, last, this.count);
                counter.empty().append(counter_text).show();
            }
            else
            {
                nav.find('.grid-load-indicator').empty().hide();
            //this.toolbarfooter.find('.grid-load-indicator').empty().hide();
            }
        },

        firstPage: function() {
            this.page = 1;
            this.getData();
        },

        previousPage: function() {
            this.page = this.page - 1;
            this.getData();
        },

        nextPage: function(self) {
            self.page = self.page + 1;
            self.getData();
        },

        lastPage: function() {
            this.page = this.pages;
            this.getData();
        },
    
    
    
    
    
    
    
        
    
    
    
    

        buildGridTableHeader: function()
        {
            var self = this;
        
            if ( this.settings.selectable )
            {
                var td =   $('<th>').css({
                    width: 22,
                    paddingLeft: 0
                }).appendTo(this.headerThead);
        
        
                var checkbox = $('<input>').attr({
                    type: 'checkbox'
                }).hide().appendTo(td);
            
            
            
            
                checkbox.bind('click', function() {
                    if($(this).is(':checked')) {
                        self.dataTbody.find(':checkbox').attr('checked', true);
                        self.dataTbody.find('tr').addClass('selected-row');
                        $(this).siblings('span').attr('title', cmslang.deselectall).addClass('checkbox checked');
                        self.dataTbody.find('.checkbox').addClass('checkbox checked');

                        self.element.find('#btn-grid-action').enableButton();

                    //$(this).siblings('img').attr({src: settings.base_url + 'asset/img/icon/cancel.png', title: strings.select_none});
                    } else {
                        self.dataTbody.find(':checkbox').attr('checked', false);
                        self.dataTbody.find('tr').removeClass('selected-row');
                        self.dataTbody.find('.checkbox').removeClass('checked').addClass('checkbox');
                        $(this).siblings('span').attr('title', cmslang.selectall).removeClass('checked').addClass('checkbox');


                        self.element.find('#btn-grid-action').disableButton();
                    }

                    $('.grid-items-selected').text( sprintf(cmslang.totalitems_selected,
                        self.dataTbody.find('.selected-row').length > 0 ? self.dataTbody.find('.selected-row').length : 0
                        ));
                    
                    if(self.selectionChecker) {
                        self.selectionChecker(self.getSelected());

                        $('.grid-items-selected').text( sprintf(cmslang.totalitems_selected,
                            self.dataTbody.find('.selected-row').length > 0 ? self.dataTbody.find('.selected-row').length : 0
                            ));
                    }


                });
            }
        
            var x = 1;
            var skipNextWidth = false;
            var len = this.settings.colModel.length;
        
            for(var col in this.settings.colModel) {
            
                var column = this.settings.colModel[col];
            
                // skip invisible cols
                if ( column.isvisible === false )
                {
                    skipNextWidth = true;
                    continue;
                }
            
            
            
            
            
                var td = $('<th>').addClass('dragaccept').attr('rel', column.name);
                td.append($('<div>').addClass('tablecolsort') );
            
                var label = $('<span>').addClass('label').append(column.label);
                td.append(label);
            
            
                if(column.sortable)
                {
                    td.addClass('sorting');
                    var sign = $('<span>').addClass('sign');

                    if (typeof column.type != 'undefined' && column.type != '' )
                    {
                        sign.addClass( column.type );
                    }

                    // set Default order
                    if(column.sortby == this.settings.orderby) {
                        sign.addClass( this.sort );
                    }

                    td.append( sign );
                    sign.removeClass('label');
                    this.setSortable(td, column);
                }
            
                if(column.align!='left' && x != len) {
                    td.css({
                        textAlign: column.align
                    }).addClass(column.align);
                }

                if (x >= this.visibleCols)
                {
                    td.css({
                        textAlign: 'center'
                    });
                }

                if(column.width != 'auto') {
                    td.css({
                        width: column.width
                    });
                }
            
                td.css({
                    borderRight: 0
                });
            
                x++;
                td.appendTo(this.headerThead);
            }
        },
    
        // helper for buildGridTableHeader
        setSortable: function(td, colModel) {
            var self = this;
        
            td.addClass('grid-sorter');
            td.attr('id', 'sort-' + colModel.sortby);

            // set type of field (date, numeric, string ....)
            if ( typeof colModel.sorttype != 'undefined' )
            {
            // td.addClass( colModel.sorttype );
            }

            td.bind('click', function(e) {
                self.orderby = $(this).attr('id').replace('sort-', '');
                var old = self.sort;
                self.sort = (self.sort == 'asc' ? 'desc' : 'asc');

                $(this).parent().find('.grid-sorter .sign').removeClass('asc').removeClass('desc');
                $(this).find('.sign').removeClass(old).addClass(self.sort);

                self.getData();
            });
        },
    
        updateCols: function( object ) {

            var ischecked = $(object).is(':checked');
            var name = $(object).parents('li:first').attr('id').replace('field-', '');


            for(var col in this.settings.colModel) {
                if ( this.settings.colModel[col].name == name )
                {
                    this.settings.colModel[col].isvisible = (ischecked ? true : false);
                    break;
                }
            }


            this.sendChange = true;
            if (ischecked)
            {
                //this.tbody.find('td[rel='+ name +']').show();
                //this.thead.find('th[rel='+ name +']').show();
                this.visibleCols = this.visibleCols + 1;
                this.redrawCols(name);
                this.sendUpdateCols(name);
            }
            else
            {
                this.headerTable.find('th[rel='+ name +']:first').remove().next().css({
                    'width': 'auto'
                });
            
                this.dataTbody.find('td[rel='+ name +']').each(function() {
                    $(this).remove();
                });
            
                this.visibleCols = this.visibleCols - 1;
                this.sendUpdateCols();
            }

        //  this.tfoot.find('td:first').attr('colspan', this.thead.find('th').length );

        },
    
    
    
        redrawDataCols: function(name) {
            var colorder = -1;
            var headers = this.headerTable.find('th').length;
            var self = this;
            var removeNextWidth = false;
        },
    
    
        redrawCols: function(name) {
            var colorder = -1;
            var headers = this.headerTable.find('th').length;
            var self = this;
            var removeNextWidth = false;

            var x = 0;
            for(var col in this.settings.colModel)
            {
                var column = this.settings.colModel[col];

                if (column.isvisible && removeNextWidth )
                {
                //this.thead.find( 'th[rel='+ this.settings.colModel[col].name +']').css({width: 'auto'});
                }


                if(column.isvisible )
                {
                    if(column.width != 'auto') {

                        if ( x > 0)
                        {
                            this.headerTable.find( 'th[rel='+ column.name +']').css({
                                width: column.width
                            });
                        
                        
                            this.dataTbody.find( 'th[rel='+ column.name +']').css({
                                width: column.width
                            });
                        }
                    }
                    else
                    {
                        this.headerTable.find( 'th[rel='+ column.name +']').attr('style', '');
                        this.dataTbody.find( 'th[rel='+ column.name +']').attr('style', '');
                    }

                    colorder++;
                
                    removeNextWidth = false;
                }




                if ( name == column.name && column.isvisible )
                {
                    removeNextWidth = true;

                    var td = $('<th>').attr('rel', column.name).insertAfter( this.headerTable.find( 'th:eq('+ colorder +')') );
                    this.headerTable.find( 'th:eq('+ colorder +')').next().next().css('width', 'auto');

                    var label = $('<span>').addClass('label').append(this.settings.colModel[col].label);
                    td.append(label);

                    if(column.sortable) {
                        td.addClass('sorting');
                        var sign = $('<span>').addClass('sign');

                        if (typeof column.type != 'undefined' && column.type != '' )
                        {
                            sign.addClass( column.type );
                        }

                        // set Default order
                        if(column.sortby == this.settings.orderby) {
                            sign.addClass( this.sort );
                        }

                        td.append( sign );
                        sign.removeClass('label');
                    
                        this.setSortable(td, column);
                    }

                    if(x > 0 && column.align!='left') {
                        td.css({
                            textAlign: column.align
                        }).addClass(column.align);
                    }


                    if(x > 0 && column.width != 'auto') {
                        td.css({
                            width: column.width
                        });
                    }



                    // redraw data rows
                    this.dataTbody.find('tr').each(function()
                    {
                        $('<td>').addClass('redraw').attr('rel', column.name).insertAfter( $(this).find( 'td:eq('+ colorder +')') );
                    });
                }

                x++;
            }
        },
    
    
        /**
     * get updated column data if visible
     */
        sendUpdateCols: function(name) {

            if ( this.sendChange )
            {
                var self = this;
                var all = this.allFields.join(',');
                var visiblecols = [];
            
                for(var col in this.settings.colModel) {
                    if (this.settings.colModel[col].isvisible)
                    {
                        visiblecols.push( this.settings.colModel[col].name );
                    }
                }

                // do update table settings
                var url = sprintf( this.updatecolsettingsUrl, all, visiblecols.join(','));

                if ( typeof name == 'string' )
                {
                    url += '&getcoldata='+ name;
                }


                $.post( this.griddataurl + url +'&ajax=1', this.getParams() , function(data){
                    if(responseIsOk(data)){

                        //self.getData();

                        if ( typeof data.rows != 'undefined' )
                        {
                            for (var i in data.rows)
                            {
                                var c = data.rows[i];
                                var row = this.dataTbody.find('tr:eq('+ i +')');
                                row.find('td[rel='+ name +']').removeClass('redraw').html( c );
                            }
                        }

                        makeAjaxUrls();
                        self.registerDelConfirm();
                        self.sendChange = false;
                    }
                    else
                    {
                        if (Tools.exists(data, 'sessionerror') )
                        {
                            alert(data.msg);

                            top.location.href = 'admin.php';
                        }
                        self.sendChange = false;
                        alert(data.msg);
                    }
                }, 'json');
            }
        
        
        
        },

    
    
    
    
    
    
    



        /**
     *  Draw data rows
     */
        buildGridTableData: function(data)
        {
            var rowCount = data.datarows.length;
            var tr;
        
            this.dataTbody.empty();
        
            if(rowCount>0)
            {
                for(var i=0; i<rowCount; i++)
                {
                    tr = this.drawGridTableDataRow(data.datarows[i], this.settings.colModel, i);
            
                    if(typeof this.rowDecorator == 'function')
                    {
                        tr = this.rowDecorator(tr, data.datarows[i]);
                    }
                
                    this.dataTbody.append(tr);
                }
            
            
                this.dataTable.append(this.dataTbody);
            
            
        
                this.initRows();
                this.registerDelConfirm();
            //this.openTabs();
            
            //  gridNavi to wrapper
            }
            else
            {
                this.page = 1;
                var tr = $('<tr>').appendTo(this.dataTbody);
        
                $('<td>').attr({
                    colspan: this.visibleCols + 1
                }).appendTo(tr).append(this.no_data_msg);
            }
        

    

        },
    
        // helper for buildGridTableData
        drawGridTableDataRow: function(data, colModel, rowIndex)
        {
            var self = this;
            var className = ((rowIndex % 2 != 1) ? 'firstrow' : 'secondrow');
            var tr = $('<tr>').attr('id', 'data-' + data[this.key]).addClass(className);        
            var td;
        
            if ( this.settings.selectable )
            {
                td = $('<td>').addClass('selection-column').appendTo(tr);
                td.css({
                    'text-align': 'left!important',
                    width: 22
                });
            
                var checkbox = $('<input>').attr({
                    type: 'checkbox'
                }).val(data[this.key]).css({
                    marginLeft: '2px'
                }).appendTo(td);
        
                checkbox.hide();
        
                var toggle = $('<span>').addClass('checkbox').css({
                    verticalAlign: '-4px'
                }).appendTo(td);
            }
        
            var x = 1;
            var cx = 1;
            var len = colModel.length;
        
            for(var col in colModel) 
            {
                var column = colModel[col];

                if (!column.isvisible)
                {
                    continue;
                }
            
                td = $('<td>').attr('rel', column.name).appendTo(tr);
                if ( !this.settings.selectable )
                {
                    td.css({
                        'text-align': 'left!important'
                    });
                }
            
                if(column.width != 'auto') {
                    td.css({
                        width: column.width
                    });
                }
            
                if (typeof column.css != 'undefined')
                {
                    td.addClass(column.css);
                }

                try {
                    if (typeof data[ column.name ].css == 'string' && data[ column.name ].css != '')
                    {
                    //  td.addClass(data[ column.name ].css);
                    }
                }
                catch(e)
                {
                    Debug.error(e.toString());
                }
            
                /**
         * 
         * 
         * 
         * 
         * 
         */
                if(typeof this.renderers == 'array' && this.renderers[ column.name ])
                {
                    td.append(   this.renderers[ column.name ]( data[ column.name ], data )      );
                } 
                else 
                {
                    td.append(data[ column.name ].data);
                }
            
            
                if( column.align != 'left' && len > cx ) {
                    td.css({
                        textAlign: column.align
                    }).addClass(column.align);
                }

                // the last col set to right
                if( len == cx ) {
                    td.css({
                        textAlign: 'center'
                    });
                    cx = 0;
                }

                if(this.confirmText == column.name) {
                    td.attr('id', 'confirm-' + data[this.key]);
                }
                cx++;
                x++;
            }
        
            /**
     * add doubleClickHandler to the first column
     */
            if(typeof this.doubleClickHandler == 'function') {
                tr.find('td:first').siblings().bind('dblclick', function(e) {
                    self.doubleClickHandler(e);
                } );
            }
        
        
        
            return tr;
        
        },
    
    
    
        // helper for buildGridTableData
        initRows: function() {
            var self = this;

            if ( !this.settings.selectable ) {
                return;
            }

            if(self.selectOnClick)
            {
                this.dataTbody.find('tr').click( function(e) {

                    $(this).find('td:first span.checkbox').click(function()
                    {
                        var row = $(this).parents('tr:first');


                        if( !row.find('td:first :checkbox').is(':checked') ) {
                            row.find('td:first :checkbox').attr('checked', true);
                            row.addClass('selected-row');
                            row.find('td:first span.checkbox').addClass('checked');
                        }
                        else {
                            row.find('td:first :checkbox').attr('checked', false);
                            row.removeClass('selected-row');
                            row.find('td:first span.checkbox').removeClass('checked');
                        }

                        self.checkSelection();
                        if(self.selectionChecker) {
                            self.selectionChecker(self.getSelected());
                        }
                    });
                });
            }
            else {

                var rows = this.dataTbody.find('tr');


                rows.find('td:eq(1)').each(function(){
                    $(this).click(function()
                    {
                        var row = $(this).parents('tr:first');


                        if( !row.find('td:first :checkbox').is(':checked') ) {
                            row.find('td:first :checkbox').attr('checked', true);
                            row.addClass('selected-row');
                            row.find('td:first span.checkbox').addClass('checked');
                        }
                        else {
                            row.find('td:first :checkbox').attr('checked', false);
                            row.removeClass('selected-row');
                            row.find('td:first span.checkbox').removeClass('checked');
                        }

                        self.checkSelection();
                        if(self.selectionChecker) {
                            self.selectionChecker(self.getSelected());
                        }
                    });
                });


                rows.find('td:eq(0) .checkbox').each(function(){
                    $(this).click(function()
                    {
                        var row = $(this).parents('tr:first');


                        if( !row.find('td:first :checkbox').is(':checked') ) {
                            row.find('td:first :checkbox').attr('checked', true);
                            row.addClass('selected-row');
                            row.find('td:first span.checkbox').addClass('checked');
                        }
                        else {
                            row.find('td:first :checkbox').attr('checked', false);
                            row.removeClass('selected-row');
                            row.find('td:first span.checkbox').removeClass('checked');
                        }

                        self.checkSelection();

                        if(self.selectionChecker) {
                            self.selectionChecker(self.getSelected());
                        }
                    });
                });

            }
        },
    
    
    

    
        // other data table helpers
        // used in initRows
        getSelected: function() {
            var ids = [];
            this.dataTbody.find('.selected-row :checkbox:checked').each(function() {
                ids.push($(this).val());
            });
            return ids;
        },

        checkSelection: function() {
            var self = this;
            var selected = this.dataTbody.find('.selected-row').length;


            if(this.count==selected){
                this.headerTable.find(':checkbox').attr('checked', true);

                this.headerTable.find('span.checkbox').attr('title', cmslang.deselectall).addClass('checked');
            } else {
                this.headerTable.find(':checkbox').attr('checked', false);

                this.headerTable.find('span.checkbox').attr('title', cmslang.selectall).removeClass('checked');
            }

            if ( selected > 0 )
            {
                this.element.find('button#btn-grid-action').enableButton();
            }
            else
            {
                this.element.find('button#btn-grid-action').disableButton();
            }

            this.element.find('.grid-items-selected').text( sprintf(cmslang.totalitems_selected, selected) );

            if(this.selectionChecker) {
                this.selectionChecker(this.getSelected());
            }
        
            return true;
        },
    
        registerDelConfirm: function() {
            var self = this;

            this.dataTbody.find('.delconfirm').each(function(){
                $(this).unbind('click').bind('click', function(e) {
                    e.preventDefault();

                    var isAjaxRequest = false;
                    if ( $(this).hasClass('ajax') )
                    {
                        isAjaxRequest = true;
                    }

                    var href = $(this).attr('href');

                    jConfirm('Möchtest du diesen Inhalt wirklich löschen?', 'Bestätigung...', function(r) {
                        if(r) {

                            //unnotify();

                            $.get(href +'&send=1', {}, function(data) {
                                if(responseIsOk(data))
                                {
                                    if ( data.msg )
                                    {
                                        setFormStatusOk(data.msg);

                                        setTimeout(function(){
                                            removeFormStatus();
                                        }, 3000);
                                    }
                                    else
                                    {
                                        setFormStatusOk('Daten wurden erfolgreich gelöscht...');
                                
                                        setTimeout(function(){
                                            removeFormStatus();
                                        }, 3000);
                                    }

                                    // refresh
                                    self.gridNavi.find('.refresh-grid').click();
                                }
                                else
                                {

                                    if (Tools.exists(data, 'sessionerror') )
                                    {
                                        top.location.href = 'admin.php';
                                    }

                                    jAlert(data.msg);
                                }
                            },'json');
                        }

                    });
                    return false;
                });
            });
        },
    
    
    
    
    
    
    
    
        /**
     * prepare form data before send the
     * ajax request to server
     * 
     */
        getParams: function() {
            var params = {};
            var url = this.griddataurl;
        
        
            params.adm = $.getURLParam("adm", url);
            params.filter = false;
        
            if($('.tablegrid-search').val()!='') {
                this.query = $('.tablegrid-search').val();
            }
            else
            {
                this.query = null;
            }

            params.q = (typeof this.query == 'string' ? this.query : '');

            if ( this.gridForm !== null )
            {
                $.extend(params, this.gridForm.serialize() );
            }

            if ( this.searchParams !== false )
            {
                $.extend(params, this.searchParams);

                if ( typeof this.searchParams.removefilter != 'undefined' )
                {
                    this.searchParams = false;
                }
            }

            params.page = this.page;
            params.perpage = this.perpage;
            params.sort = this.sort;
            params.orderby = this.orderby;


            return params;
        },
    
        /**
     *  getting grid data via Ajax
     * 
     */
        getData: function(isFirstRun, loadurlParams, callbackGetData)
        {
            if ( isFirstRun && typeof this.datarows != 'undefined' && this.datarows.length > 0  )
            {
                var rows = {
                    datarows: this.datarows
                };            
            
                this.buildGridTableData(rows);
                this.updateGridNavi();
                this.gridNavi.find('.page-of-counter').html((this.pages>0 ? this.pages : 1));
                this.gridNavi.find('.pageinput input').val(this.page);
                this.checkSelection();

                if(this.selectionChecker) 
                {
                    this.selectionChecker(this.getSelected());
                }
            
                return;
            }
        
            this.processing = true;
            this.gridNavi.find('.refresh-grid').addClass('load');
        
            var self = this;

            $(this.headerThead).find('span.checkbox').removeClass('checked').addClass('checkbox');
            $(this.dataTbody).find('span.checkbox').removeClass('checked').addClass('checkbox');
        
        
            //this.maskTimer = window.setTimeout(function() {if(self.processing) {self.element.mask('laden ...'); }}, 10);
            if(this.processing && !$(this.wrapper).parents().find('.masked:first').length ) {
                $(this.wrapper).mask(cmslang.gettingData);
            }
    
    
    
    
            var params = this.getParams();
            if ( typeof loadurlParams != 'undefined' )
            {
                $.extend(params, loadurlParams );
            }
        
            params.getGriddata = true;
            params.ajax = 1; 
    
            $.post( this.griddataurl, params, function(data){
            
            
                self.gridNavi.find('.refresh-grid').removeClass('load');
            
                if(responseIsOk(data))
                {
                    if (typeof data.debugoutput == 'string' )
                    {
                        GUI_Console.setDebug(data.debugoutput);
                    }

                    self.buildGridTableData(data);
                    self.count = data.total;
                    self.pages = Math.ceil(self.count/self.perpage);

                    self.updateGridNavi();
                    self.gridNavi.find('.page-of-counter').html((self.pages>0 ? self.pages : 1));
                    self.gridNavi.find('.pageinput input').val(self.page);
                    self.checkSelection();
                
                
                    if(self.selectionChecker) {
                        self.selectionChecker(self.getSelected());
                    }


                    setTimeout(function(){
                        GUI.updateScrollSize();
                    }, 100);


                    if ( typeof callbackGetData == 'function')
                    {
                        setTimeout(function(){
                            eval( callbackGetData(data, self) );
                        }, 100);
                    }
                    else {
                        if ( typeof self.onAfterLoad == 'function' )
                        {
                            setTimeout(function(){
                                eval( self.onAfterLoad(data, self) );
                            }, 100);
                        }
                    }
                    self.updateDataTableSize();
                    self.processing = false;
                    $(self.wrapper).unmask();
                }
                else
                {

                    $(self.wrapper).unmask();
                    alert(data.msg);

                    // Session error?
                    if (Tools.exists(data, 'sessionerror') )
                    {
                        top.location.href = 'admin.php';
                    }
                
                    self.processing = false;

                }
            
            });
        },
        
        
        getHeaderCell: function(colNum)
        {
            return this.headerThead.find('th:eq('+ colNum +')');
        },
        
        getDataTableRows: function()
        {
            return this.dataTbody.find('tr');
        },
        
        getDataTableRow: function(rowNum)
        {
            return this.dataTbody.find('tr:eq('+ rowNum +')');
        },
        
        getNumberOfRows: function()
        {
            return this.dataTbody.find('tr').length;
        },
        
        getFirstRow: function()
        {
            return (this.getNumberOfRows() ? this.dataTbody.find('tr:first') : null);
        },
        
        getGridWidth: function()
        {
            return this.dataTable.width();
        },
        
        getColumnWidth: function(colNum)
        {
            return this.getHeaderCell(colNum).width();
        },
        
        getColumnCount: function()
        {
            return this.headerThead.find('th').length;
        },
        
        
        /**
        * @private
        * Updates the size of every column and cell in the grid
        */
        updateAllColumnWidths : function(inWin) {
            var totalWidth = this.getGridWidth(),
            colCount   = this.getColumnCount(),
            rowCount   = this.getNumberOfRows(),
            widths     = [],
            row, i, j;
        
            for (i = 0; i < colCount; i++) {
                widths[i] = this.getColumnWidth(i);
                this.getHeaderCell(i).css('width', widths[i] );
            }
        
            //this.updateHeaderWidth();
        
            for (i = 0; i < rowCount; i++) {
                row = this.getDataTableRow(i);
                row.css('width', totalWidth);

                if ( row.find('td:first') ) {
                    for (j = 0; j < colCount; j++) {
                        row.find('td:eq('+ j +')').css('width', widths[j]);
                    }
                }
            }
        
            this.onAllColumnWidthsUpdated(widths, totalWidth);
        },
        
        onAllColumnWidthsUpdated: function(widths, totalWidth)
        {
            
        }
        
    
    }
};


function dcmsGrid(el, options) {
    
    
    var element, self = this;
    
    setTimeout(function(){
        self.element = $('#'+el);
        self.init(el, options);
    }, 10);
}

dcmsGrid.prototype.init = function(el, options) {
    var element = $('#'+el);
    var inWindow = false, _containerElement, self = this;
    var activeWn = false;
    
    
    
    if (Config.get('useWindowStyledTabs', false))
    {
        activeWn = WindowCreator.getActiveWin();
        if ( activeWn.length == 1 )
        {
            // search the container in window
            _containerElement = $('#'+el, activeWn);

            // has found only one element
            if (_containerElement.length == 1)
            {
                inWindow = activeWn;
            }
            else
            {
            // error
            }
        }
        else
        {
            _containerElement = $('#'+el);
        }
    }
    else
    {
        _containerElement = $('#'+el);
    }
    
    self.element = _containerElement;
    var g = new Grid();
    g.create(_containerElement, inWindow, options);


    if ( activeWn )
    {
        WindowCreator.setWindowGrid(activeWn, g, _containerElement);
    }
};