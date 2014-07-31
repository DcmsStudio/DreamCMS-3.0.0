
var Panel = {
    lastMessengerCount: 0,
    panelMessengerUpdateTimeout: 60000,
    panelLogUpdateTimeout: 40000,
    isInited: false,
    init: function (initlogdata) {
        if (this.isInited) {
            return;
        }
        var self = this;

        this.isInited = true;
        this.initPanelLogUpdater(initlogdata);

        this.logUpdate = setInterval(function () {
            self.getLogData();
        }, this.panelLogUpdateTimeout);

        this.messageUpdate = setInterval(function () {
            self.getMessageData();
        }, this.panelMessengerUpdateTimeout);
    },

    unload: function () {
        clearInterval(this.logUpdate);
        clearInterval(this.messageUpdate);
    },

    initPanelLogUpdater: function (initlogdata) {
        if (typeof initlogdata == 'object') {
            this.logUpdater(initlogdata);
            return;
        }

        this.getLogData();
    },

    getLogData: function () {

        var self = this;
        $.get('admin.php', {adm: 'logs', action: 'getpanellogs'}, function (data) {
            if (Tools.responseIsOk(data)) {
                if (data.logs) {
                    self.logUpdater(data);
                }
            }
        }, 'json');
    },
    getMessageData: function () {

        var self = this;
        $.get('admin.php', {adm: 'messenger', action: 'getnew'}, function (data) {
            if (Tools.responseIsOk(data)) {
                self.updateMessenger(data);
            }
        }, 'json');
    },

    logUpdater: function (data) {

        if (typeof data.logs == 'undefined') {
            return;
        }


        var self = this, logContent = $('#logs-content div:first');

        if (logContent.find('ul').length == 0) {
            logContent.empty().append('<ul></ul>');
        }

        var ul = logContent.find('ul:first'), tmpUl = $('<ul>');

        var lastActiveID = (logContent.find('li.active').length ? logContent.find('li.active').attr('id') : false);


        logContent.attr('lastScrollPosY', $(logContent).get(0).offsetTop);
        for (var i = 0; i < data.logs.length; ++i) {
            var item = data.logs[i];
            var listTemplate = $('<li>').attr('id', 'log-' + item.id);


            var tpl = '<div><span>IP:</span><span>' + item.ip + '</span></div>';
            tpl += '<div><span>Browser:</span><span>' + item.browser + '</span></div>';
            tpl += '<div><span>Message:</span><span>' + item.message + '</span></div>';

            var icon;
            switch (item.logtype) {
                case 'warn':
                case 'critical':
                case 'error':
                    icon = 'critical';
                    break;
                case 'note':
                    icon = 'note';
                    break;
                case 'info':
                default:
                    icon = 'info';
                    break;
            }

            listTemplate.append('<span class="logicon logicon-' + icon + '"></span><span class="log-title">' + item.time + (item.username != '' && item.username != 0 && item.username != null ? ' - ' + item.username : '') + '</span>');
            listTemplate.append('<div class="log-data" style="display:none">' + tpl + '</div>');
            tmpUl.append(listTemplate);
        }


        logContent.empty().append(tmpUl);

        if (lastActiveID) {
            logContent.find('li#' + lastActiveID).addClass('active').find('div.log-data').show();
        }




        logContent.find('span.logicon').bind('click.log-advanced', function (e) {
            e.preventDefault();

            var id = $(this).parent().attr('id').replace('log-', '');

            $.get( 'admin.php?adm=logs&action=showfull&id=' + id, function ( data )
            {
                if ( Tools.responseIsOk( data ) ) {
                    self.getAdvancedLog( data, id );
                }
                else {
                    jAlert( data.msg, cmslang.error );
                }
            }, 'json' );
        });


        logContent.find('span.log-title').bind('click.log', function (e) {
            e.preventDefault();

            logContent.find('li.active').removeClass('active').find('div:first').hide();
            $(this).parent().addClass('active').find('div:first').slideDown(250);

            setTimeout(function () {
                self.enableScrollbar();
            }, 50);
        });

        setTimeout(function () {
            self.enableScrollbar();
        }, 50);
    },


    getAdvancedLog: function (d, id) {


        var winOpt = {
            title: 'Erweiterte Log Infos...',
            Resizable: true,
            WindowToolbar: false,
            Minimize: false,
            minWidth: 400,
            minHeight: 180,
            Height: 450,
            Width: 600,
            Controller: 'logs',
            app: 'logs',
            Action: 'index',
            WindowID: 'logs-advanced-window',
            enableContentScrollbar: true,
            nopadding: false,
            onAfterClose: function (e, wm, callback) {
                if (Tools.isFunction(callback)) {
                    callback();
                }
            },
            onResizeStart: function (e, e2, wm, size) {
                $(wm.win).addClass('popup-logs');
            },
            onBeforeShow: function (e, wm, callback) {
                wm.settings.enableContentScrollbar = true;
                if (Tools.isFunction(callback)) {
                    callback();
                    setTimeout(function () {
                        $(wm.win).addClass('popup-logs');
                    }, 150);
                }
            }
        };

        winOpt.opener = null;
        var genid = new Date().getTime();

        winOpt.WindowID = HashGen.md5( 'admin.php?adm=logs&amp;action=showfull&id=' + id );
        //winOpt.WindowID += '-' + genid;

        var container = $('<div class="advancedlog"/>');
        if (d.allow_add_ip) {
           // container.append('<button class="addbadip">{trans("Als Black-IP markieren")}</button>');
        }
        container.append('<div><span>Date</span>' + d.date + '</div>');
        container.append('<div><span>IP</span>' + d.ip + ' (' + d.host + ')</div>');
        container.append('<div><span>User Agent</span>' + d.browser + '</div>');
        container.append('<div><span>Country</span>' + d.country + '</div>');
        container.append('<div class="map" id="dcmsgmap-' + genid + '"></div>')
        container.append( '<div><span>Message</span>' + d.message + '</div>' );
        if ( d.data ) {
            if ( d.data.log ) {
                container.append( $( '<div></div>' ).append( '<span>Log</span>' + d.data.log ) );
            }

            if ( d.data.requestMethod ) {
                container.append( $( '<div></div>' ).append( '<span>Request Method</span>' + d.data.requestMethod ) );
            }
            if ( d.data.REQUEST_URI ) {
                container.append( $( '<div></div>' ).append( '<span>Request URI</span>' + d.data.REQUEST_URI ) );
            }
            if ( d.data.request ) {
                container.append( $( '<div></div>' ).append( '<span id="requestdata" class="link">Request Data</span><div style="display: none" id="toggle_requestdata">' + d.data.request +'</div>' ) );
            }
        }

        if (d.backtrace && d.backtrace.snipped ) {

            var t = d.backtrace;
            container.append('<div><span>Backtrace</span></div>');


            container.append('<div><span id="debugparams" class="link">Params</span></div>');
            if (t.args) {
                container.append('<div id="toggle_debugparams" style="display: none"><pre class="source"><code>' + t.args + '</code></pre></div>');
            }

            if (t.class || t.function || t.file || t.snipped) {
                container.append('<div id="debugcall" class="link"><span>Call</span></div>');


                var out = $('<div style="display: none" id="toggle_debugcall"/>');
                if (t.class) {
                    out.append('<div><span>Class</span>' + t.class + '</div>');
                }
                if (t.function) {
                    out.append('<div><span>Function</span>' + t.function + '</div>');
                }
                if (t.file) {
                    out.append('<div><span>File</span>' + t.file + '</div>');
                }
                if (t.line) {
                    out.append('<div><span>Line</span>' + t.line + '</div>');
                }
                if (t.snipped) {
                    out.append('<div><span>Code</span><div>' + t.snipped + '</div></div>');
                }


                container.append(out);

            }
        }
        var container2 = $('<div class="advancedlog"/>').append(container);

        winOpt.onAfterOpen = function(popup) {


            popup.find('#requestdata,#debugparams,#debugcall').click(function(){

                var id = $(this).attr('id');
                var el = $('#toggle_' + id, popup);
                if (!el.is(':visible')) {
                    el.show();
                }
                else {
                    el.hide();
                }
                Tools.scrollBar(popup.find( '.advancedlog' ));

            });


            popup.find('button.addbadip' ).click(function(){
                $.post('admin.php', {
                    adm: 'badips',
                    action: 'add',
                    send: true,
                    ip: d.ip
                }, function(data) {
                    if (Tools.responseIsOk(data)) {
                        Notifier.info(data.msg);
                    }
                    else {
                        if (data.msg) {
                            Notifier.error(data.msg);
                        }
                        else {
                            Notifier.error( '{trans("IP konnte nicht zur Blcklist hinzugefügt werden!")}');
                        }
                    }
                });
            });

            if ( typeof d.lat != 'undefined' && typeof d.lon != 'undefined' ) {
                $('#dcmsgmap-'+ genid, popup).show();

                var ipnote = '<div id="content" style="width: 200px;overflow:hidden"><div id="siteNotice"></div>'
                    + '<h1 id="firstHeading" class="firstHeading" style="margin-bottom: 10px">Herkunft Infos</h1>'
                    + '<div id="bodyContent" style="width: 200px;overflow:hidden"><p>IP: ' + d.ip + '<br/>Host: ' + d.host + '<br/>Land: ' + d.country + '</p></div>'
                    + '</div>';

                initMap({
                    //   mapType: 'TERRAIN',
                    mapElement: '#dcmsgmap-'+ genid,
                    lat: d.lat,
                    lon: d.lon,
                    useMarker: true,
                    infoWindow: ipnote,
                    markerTitle: 'IP'
                });

            }
            else {
                $('#dcmsgmap-'+ genid, popup).hide();
            }

            Tools.scrollBar(popup.find( '.advancedlog' ));
        }


        Tools.createPopup( container2.html(), winOpt );


    },


    updateMessenger: function (data) {
        if (typeof data.result == 'undefined') {
            return;
        }
        $('#panel-buttons li[rel=messages] .bubble').remove();

        if (data.result.length > 0) {
            $('#panel-buttons li[rel=messages]').append('<i class="bubble">' + data.result.length + '</i>');
        }
        else {
            $('#panel-buttons li[rel=messages] i').remove();
        }

        if (typeof this.lastMessengerCount == 'undefined') {
            this.lastMessengerCount = data.result.length;
            Tools.html5Audio('html/audio/messagebox');
        }
        else {
            if (data.result.length > this.lastMessengerCount && this.lastMessengerCount != 0) {
                this.lastMessengerCount = data.result.length;

                // Play audio 
                Tools.html5Audio('html/audio/messagebox');
                Notifier.info('Sie haben ' + (data.result.length - this.lastMessengerCount) + ' neue Nachrichten', true);
            }
            else if (data.result.length > this.lastMessengerCount && this.lastMessengerCount == 0) {
                this.lastMessengerCount = data.result.length;

                // Play audio 
                Tools.html5Audio('html/audio/messagebox');
                Notifier.info('Sie haben ' + data.result.length + ' neue ungelesene Nachrichten', true);
            }
        }

        var messengerContent = $('#messages-content div:first');


        if (messengerContent.length) {

            var lastActiveID = (messengerContent.find('li.active').length ? messengerContent.find('li.active').attr('id') : false);
            messengerContent.attr('lastScrollPosY', $(messengerContent).get(0).offsetTop);

            if (messengerContent.find('ul').length == 0) {
                messengerContent.empty().append('<ul></ul>');
            }


            var self = this, tmpUl = $('<ul>');
            var DELAY = 500, clicks = 0, timer = null;

            for (var i = 0; i < data.result.length; ++i) {
                var item = data.result[i];
                var listTemplate = $('<li>').attr('id', 'msg-' + item.id);
                listTemplate.append('<div class="item"><span class="fromuser">Von: ' + item.username + '</span><span class="time">' + item.time + '</span><span class="title">' + item.subject + '</span></div><div class="message" style="display:none">' + item.message + '</div>');
                tmpUl.append(listTemplate);
            }


            tmpUl.find('.message').hide();
            messengerContent.empty().append(tmpUl);

            if (!data.result.length) {
                messengerContent.find('ul').empty().append('<li>Keine neuen Nachrichten vorhanden</li>');
            }
            else {

                messengerContent.find('li').data('clicks', 0).bind('click', function (e) {

                    e.preventDefault();


                    var li = $(this);

                    clicks++;

                    if (clicks === 1) {
                        clearTimeout(timer);
                        timer = setTimeout(function () {
                            clicks = 0;
                            messengerContent.find('li.active').removeClass('active').find('div.message').hide();

                            li.addClass('active').find('div.message').slideDown(250, function () {
                                self.enableScrollbar();
                                $(window).trigger('resizescrollbar');
                            });

                        }, DELAY);
                    }
                    else {
                        clearTimeout(timer);
                        clicks = 0;

                        if ($('#NavItems li[controller=messenger]').length && $('#main-tabs').find('li[app=messenger]').length == 0)
                        {
                            var id = $(this).attr('id').replace('msg-', '');
                            Cookie.set('wg-read-messenger', id);
                            $('#NavItems li[controller=messenger]').trigger('click');
                        }
                        else if ($('#main-tabs').find('li[app=messenger]').length == 1)
                        {
                            // click the menu item

                            var hash = $('#main-tabs').find('li[app=messenger]').attr('id').replace('tab-', '');
                            if ($('#content-' + hash).length && $('#content-' + hash).data('messenger')) {
                                var id = $(this).attr('id').replace('msg-', '');
                                $('#main-tabs').find('li[app=messenger]').trigger('click');
                                $('#content-' + hash).data('messenger').readMessage(id);
                            }
                        }
                    }
                });
            }

            if (lastActiveID) {
                $('#messages-content li#' + lastActiveID).addClass('active').find('div.message').show();
            }
        }
    },
    /***
     * Update only the Visible Content Tab Scrollbar
     * @returns {undefined}
     */
    updatePanelSizeStop: function () {
        var self = this;

        $('#panel-content').find('div.panel-content').each(function () {
            var self = this, scollTo = parseInt($(this).attr('lastScrollPosY'));
            $(this).nanoScroller({scrollContent: $(this)});
            if (scollTo > 0) {
                setTimeout(function () {
                    $(self).nanoScroller({'scrollTo': scollTo});
                }, 50);
            }
        });

    },
    /**
     * Update only the Visible Content Tab Scrollbar
     * @returns {undefined}
     */
    updateScrollbar: function () {
        var self = this;
        var i = 0, length = $('#panel-content').find('div.panel-content').length;

        $('#panel-content').find('div.panel-content').each(function () {
            $(this).attr('lastScrollPosY', $(this).get(0).offsetTop);
            i++;
            if (i + 1 >= length) {
                self.updatePanelSizeStop();
            }
        });

    },
    /**
     *
     * @returns {undefined}
     */
    disableScrollbar: function () {
        return;
    },
    /**
     *
     * @returns {undefined}
     */
    enableScrollbar: function () {
        this.updatePanelSizeStop();
    }
};