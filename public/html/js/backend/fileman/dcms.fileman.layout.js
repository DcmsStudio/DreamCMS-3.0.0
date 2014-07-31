/**
 * Sets a request for the next animation frame
 */

var renderTimeout = -1;

window.requestAnimFrame = (function () {
    return window.requestAnimationFrame
        || window.webkitRequestAnimationFrame
        || window.mozRequestAnimationFrame
        || window.oRequestAnimationFrame
        || window.msRequestAnimationFrame
        || function (callback) {
        renderTimeout = window.setTimeout(callback, 1000 / 60);
    };
}());

/**
 * Cancels a request for a scheduled repaint of the window for the next animation frame
 */
window.cancelAnimFrame = (function () {
    return window.cancelAnimationFrame
        || window.webkitCancelRequestAnimationFrame
        || window.mozCancelRequestAnimationFrame
        || window.oCancelRequestAnimationFrame
        || window.msCancelRequestAnimationFrame
        || window.clearTimeout(renderTimeout);
}());

(function ($) {
    Fileman.prototype.Coverflow = function (fm, el) {
        var self = this;

        /**
         * Fileman instance
         *
         * @type Fileman
         */
        this.fm = fm;
        this.container = null;

        // Local variables
        var _index = 0, container = null,
            _coverflow = null,
            _prevLink = null,
            _nextLink = null,
            _albums = []; //

        var scrollerWrapper = null, viewWidth = null, halfViewWidth = null, leftMargin = null, currentOffset = null;
        var scrollTarget = 1, list = null;
        var scrollSnapped = 0;
        var item = null;
        var itemOffset = null;
        var normOffset = null;
        var opacity = null;
        var itemClass = null;
        var scrollLeft = null;
        var currentId = null;

        var delta = 1, currentId, numItems = 0, run = true, timer, mouseWheelSpeed = 30, scrollDirection, baseCoverWidth = 0, coverWidth = 0;
        var moveLeft = false, moveRight = false;

        var scroll = {
            x: 0,
            y: 0
        };
        var coverLabel, flowContent, currentSelected, sc;

        /**
         * Get selector from the dom
         **/
        function get(selector) {
            return document.querySelector(selector);
        }

        function _getCenterPosition(image) {

            var imgWidth = image.offsetWidth;
            var flowWrapperWidth = _coverflow.offsetWidth;
            var left = image.offsetLeft - (flowWrapperWidth / 2) + (imgWidth / 2);

            return {left: left + 10, itemleft: (flowWrapperWidth / 2) - (image.offsetWidth / 2) };
        }

        var t;

        /**
         * Renders the CoverFlow based on the current _index
         **/
        function render() {
            var i = 0;
            clearTimeout(t);

            if (!_albums[_index]) {
                return;
            }

            t = setTimeout(function () {
                var baseCoverSize = container.data('coverBaseSize');
                var coverSize = container.data('coverSize');
                currentSelected = $('.body', self.fm.layout.foldercontentContainer).find('[hash=' + _albums[_index].getAttribute("hash") + ']');
                self.fm.select(currentSelected, true, true);

                coverLabel.innerHTML = _albums[_index].getAttribute("data-album");
                coverWidth = _albums[_index].width;

                var i = 0, opacity = 1, pos = _getCenterPosition(_albums[_index]);
                // list.style.left = (0 - pos.left) + 'px';

                if (moveRight) {
                    //pos.left += 70;
                }
                if (moveLeft) {
                    pos.left -= 70;
                }

                var labelHalfWidth = parseInt(coverLabel.offsetWidth / 2, 10);
                var containerLeft = (0 - pos.left - labelHalfWidth);
                var labelLeft = containerLeft;

                if (list.offsetWidth < _coverflow.offsetWidth) {
                    var d = _coverflow.offsetWidth - list.offsetWidth;
                    containerLeft -= d / 2;
                }

                if (coverLabel.offsetWidth > _albums[_index].offsetWidth) {
                    labelLeft -= parseInt((coverLabel.offsetWidth - _albums[_index].offsetWidth) / 2, 10);
                }
                else {
                    labelLeft += parseInt((_albums[_index].offsetWidth / 2), 10) - labelHalfWidth;
                }

                list.style.left = containerLeft + 'px';

                if (moveLeft) {
                    console.log('move left');
                    coverLabel.style.left = (0 + labelLeft + _albums[_index].offsetLeft ) + 'px';
                }
                if (moveRight) {
                    console.log('move right');
                    coverLabel.style.left = (0 + labelLeft + _albums[_index].offsetLeft + 30) + 'px';
                }
                if (!moveLeft && !moveRight) {
                    console.log('no move');
                    coverLabel.style.left = (0 + labelLeft + _albums[_index].offsetLeft + 10) + 'px';
                }

                var opacityAfter = 0.980;

                // loop through albums & transform positions

                while (i < numItems) {

                    item = _albums[i];
                    itemClass = item.className;

                    if (itemClass.length) {
                        item.classList.remove('prev');
                        item.classList.remove('next');
                        item.classList.remove('current');
                        //item.style[_transformName] = 'scale(1)';
                    }
                    var diff = (_index - i - 1), opacity = (0.095 * diff);
                    // before 
                    if (i < _index) {
                        item.classList.add('prev');
                        item.style.zIndex = _index + i;
                        item.style.opacity = (1 - opacity) - 0.050;
                    }

                    // current
                    if (i === _index) {
                        item.classList.add('current');
                        item.style.zIndex = 99999;
                        //    item.style.left = '-70px';
                        item.style.margin = '';
                        item.style.opacity = 1;
                    }

                    // after
                    if (i > _index) {
                        item.style.zIndex = numItems - i;
                        item.classList.add('next');
                        item.style.opacity = opacityAfter;
                        opacityAfter -= 0.090;
                    }
                    ++i;
                }
            });

        }

        /**
         * Flow to the right
         **/
        function flowRight(fromWheel) {

            // check if has albums 
            // on the right side
            if (_index) {
                _index--;
                moveRight = true;
                moveLeft = false;
                //  clearTimeout(renderTimeout);
                //  self.loopAnim();
                render(scrollerWrapper, list);
            }

        }

        /**
         * Flow to the left
         **/
        function flowLeft(fromWheel) {

            // check if has albums 
            // on the left side
            if (numItems > (_index + 1)) {
                _index++;
                moveRight = false;
                moveLeft = true;
                //clearTimeout(renderTimeout);
                // self.loopAnim();
                render(scrollerWrapper, list);

            }

        }

        /**
         * Enable left & right keyboard events
         **/
        function keyDown(event) {

            switch (event.keyCode) {
                case 37:
                    flowRight();
                    break; // left
                case 39:
                    flowLeft();
                    break; // right
            }

        }

        function scrollHandle(e, delta) {
            if (debounce) {
                clearTimeout(debounce);
            } else {
                scrolling = true;
                if (delta > 0) {
                    if (_index) {
                        _index--;
                    }
                }
                else {
                    if (numItems > (_index + 1)) {
                        _index++;
                    }
                }

                render(scrollerWrapper, list);
            }

            scroll.x = e.someValueX;
            scroll.y = e.someValueY;

            debounce = setTimeout(debounceCallback, 300);
        }

        function debounceCallback() {
            scrolling = false;
            debounce = null;
        }

        function bindMouseWheel() {
            var maxLeft = container.find('.wrapper div:first').css({left: 0}).width();

            container.off('mousewheel').on('mousewheel', function (ev) {
                ev.preventDefault();

                clearTimeout(sc);

                if (ev.originalEvent.wheelDelta > 0) {
                    flowRight();
                }
                else {
                    flowLeft();
                }

                if (currentSelected) {
                    sc = setTimeout(function () {
                        self.fm.scrollTo(currentSelected);
                    }, 300);
                }
            });
        }

        /**
         * Register all events
         **/
        function registerEvents() {
            $(document).unbind('keydown.cf').bind('keydown.cf', keyDown);
        }

        this.loopAnim = function () {
            if (this.fm.opts.coverflow && _albums.length > 0) {
                render();
            }
        };
        this.init = function () {

        };

        this.getCover = function (hash, noScroll) {
            if (_albums.length > 0 && this.fm.opts.coverflow) {
                var itm = $('span[hash=' + hash + ']', container).index();

                if (_index !== itm) {
                    _index = itm;
                    clearTimeout(sc);
                    clearTimeout(renderTimeout);
                    render();

                    if (currentSelected && !noScroll) {
                        sc = setTimeout(function () {
                            self.fm.scrollTo(currentSelected);
                        }, 300);
                    }
                }
            }
        };

        this.reCenterCoverflow = function () {
            if (this.fm.opts.coverflow && _albums.length) {
                render();
            }
        };

        var mymargin = 48;
        this.setSizes = function (s, sizeLarger) {
            numItems = _albums.length;
            if (numItems) {
                var y = 0;
                var margin = parseInt($(_albums[0]).css('marginLeft'), 10);
                var marginDiff = s.width / numItems;

                if (sizeLarger) {
                    mymargin += (mymargin < 48 ? 0.5 : 0);
                }
                else {
                    mymargin -= (mymargin > 25 ? 0.5 : 0);
                }

                //    console.log('New margin: 0 ' + (0 - mymargin));

                if (margin < -25) {

                    while (y < numItems) {

                        _albums[y].style.width = s.width;
                        _albums[y].style.height = s.height;

                        if (baseCoverWidth > s.width) {
                            _albums[y].style.margin = '0 ' + (0 - mymargin) + 'px';

                        }
                        else {
                            _albums[y].style.margin = '0 -48px';
                        }

                        ++y;
                    }
                }

            }
        };

        this.refresh = function (containerIn) {
            _albums = (_albums && _albums.length ? _albums : $('span', $('.flow-content', containerIn)));
            numItems = _albums.length;
            list = containerIn.find('.flow-content').show().get(0);

            if (numItems) {

                var setWidth = (containerIn.data('coverSize').width * numItems / 2);
                container.find('.wrapper div:first').width(setWidth);

                for (var i = 0; i < numItems; i++) {
                    itemClass = _albums[i].className;

                    if (itemClass.length) {
                        _albums[i].classList.remove('prev');
                        _albums[i].classList.remove('next');
                        _albums[i].classList.remove('current');
                        //item.style[_transformName] = 'scale(1)';
                    }
                    _albums[i]._offsetLeft = _albums[i].offsetLeft - 10;
                }

                clearTimeout(sc);
                clearTimeout(renderTimeout);

                render();
                if (currentSelected) {
                    sc = setTimeout(function () {
                        self.fm.scrollTo(currentSelected);
                    }, 300);
                }

            }
        };

        /**
         * Initalize
         **/
        this.initCoverflow = function (containerIn) {

            clearTimeout(sc);
            clearTimeout(renderTimeout);

            if (!this.fm.opts.coverflow)
                return;

            containerIn.find('.scroller-wrapper').remove();

            _albums = $('span', $('.flow-content', containerIn));
            numItems = _albums.length;
            _index = 0; //Math.floor(itm.length / 2);
            //
            // get dom stuff
            _coverflow = $(containerIn).find('div:first').addClass('wrapper').get(0);

            list = containerIn.find('.flow-content').css({
                left: 0
            }).show().get(0);

            container = containerIn;
            var baseCoverSize = containerIn.data('coverBaseSize');

            coverLabel = containerIn.find('.cover-label').get(0);

            if (numItems) {

                var setWidth = (_albums[0].offsetWidth * numItems) / 2;
                list.style.width = setWidth;

                for (var i = 0; i < numItems; i++) {
                    if (i == 0 && !baseCoverSize) {
                        containerIn.data('coverBaseSize', {width: _albums[i].offsetWidth, height: _albums[i].offsetHeight });
                        containerIn.data('coverSize', containerIn.data('coverBaseSize'));
                    }
                    $(_albums[i]).filter('ui-draggable').draggable('destroy');
                    _albums[i]._index = i;
                    _albums[i].offsetLeft = _albums[i].offsetLeft - 10;

                    $(_albums[i]).unbind().click(function (e) {
                        self.getCover(this.getAttribute('hash'));
                    });
                }

                leftMargin = _albums[0]._offsetLeft;

                if (!baseCoverWidth)
                    baseCoverWidth = containerIn.data('coverBaseSize').width;
                if (!coverWidth)
                    coverWidth = containerIn.data('coverSize').width;
            }
            else {
                var setWidth = (100 * numItems / 2);
                list.style.width = setWidth;

                containerIn.data('coverBaseSize', {width: 100, height: 100});
                containerIn.data('coverSize', {width: 100, height: 100});

                if (!baseCoverWidth)
                    baseCoverWidth = 100;
                if (!coverWidth)
                    coverWidth = 100;

            }

            viewWidth = (containerIn.data('coverBaseSize').width * numItems);
            halfViewWidth = Math.floor(setWidth);

            self.fm.layout.coverflowHeight = self.fm.layout.coverflowContainer.outerHeight(true);

            if (numItems > 0) {
                // self.fm.layout.updatePanelSizes();

                render();

                // do important stuff
                registerEvents();
                bindMouseWheel();

                self.fm.layout.coverflowHeight = self.fm.layout.coverflowContainer.outerHeight(true);

            }
            else {

                self.fm.layout.coverflowHeight = self.fm.layout.coverflowContainer.outerHeight(true);
                self.fm.layout.updatePanelSizes();

            }
        };

    };

    Fileman.prototype.Layout = function (fm, el) {
        var self = this;

        /**
         * Fileman instance
         *
         * @type Fileman
         */
        this.fm = fm;
        this.doTreeUpdateHash = false;
        this.clicktimeout; // used for dblclick
        this.dblClickCounter = 0;
        this.lastCwd = null;
        this.tableHeaderHeight = 0;
        this.treeHeight = 0;
        this.coverflowHeight = 0;
        this.lastViewMode = this.fm.opts.view;

        this.el = el;
        var opt = fm.opt;
        this.isUploadMode = false;

        this.init = function () {

        }

        this.initLayout = function () {

            this.fm.toolbarContainer.empty().append($('<ul class="fm-toolbar"/>'));
            this.toolbar = this.fm.toolbarContainer.find('ul:first').empty();

            this.err = $('<div class="el-finder-err"><strong/></div>').hide().click(function () {
                $(this).hide();
            });
            this.filemanContainer = $('<div class="fileman">');
            this.pathway = $('<div class="pathway">');
            this.body = null, this.header = null;   // tables

            //        

            if (!this.fm.opts.treePanel) {
                this.leftSide = $('<div class="left-side"/>').width(200);

                // Tree container
                this.treeContainerInner = $('<div class="treelistInner">');
                this.treeContainer = $('<div class="treelist">').append(this.treeContainerInner);
            }
            else {
                this.leftSide = this.fm.opts.treePanel;
                this.leftSide.parent().addClass('fm');

                // Tree container
                this.treeContainerInner = $('<div class="treelistInner">');
                this.treeContainer = $('<div class="treelist">').append(this.treeContainerInner);
            }

            // Info container
            if (!this.fm.opts.treePanelInfo) {
                this.fileinfoContainer = $('<div class="file-info"/>');
            }
            else {
                this.fileinfoContainer = $('<div class="file-info"/>');
            }

            this.fileinfoPreview = $('<div class="preview-container"/>');
            this.fileinfo = $('<div class="fileinfos"/>');
            this.fileinfoContainer.append(this.fileinfoPreview).append(this.fileinfo);

            // Upload container
            this.upload = $('<div class="upload-container"></div>').hide();
            this.upload.append('<div class="uploadpath"></div>');
            this.upload.append('<div class="upload-drop-container"><div></div></div>');

            var uplTpl = '<form id="upload-fileman" name="upload-fileman" class="upload-form" action="admin.php" method="post" enctype="multipart/form-data">'
                + ' <div id="upload-drop" style="display:inner-block">'
                + '    <span class="drop-here">Drop Files here...</span>'
                + '    <span class="browse">Browse</span>'
                + '    <span class="allowed-info">'
                + '        <span class="allowed-extensions"></span>'
                + '        <span class="allowed-filesize"></span>'
                + '    </span>'
                + '    <input type="file" name="Filedata" multiple="multiple" />'
                + ' </div>'
                + ' <ul class="dropped-files">'
                + '    <!-- The file uploads will be shown here -->'
                + ' </ul>'
                + '</form>';

            this.upload.find('.upload-drop-container').append(uplTpl);

            this.fileinfoContainer.append(this.upload);

            if (!this.fm.opts.treePanel) {
                this.leftSide.append(this.treeContainer).append(this.fileinfoContainer);
            }
            else {
                if (!this.fm.opts.treePanelInfo) {
                    this.fm.opts.treePanel.append(this.treeContainer).append(this.fileinfoContainer);
                }
                else {
                    this.fm.opts.treePanel.append(this.treeContainer);
                    this.fm.opts.treePanelInfo.append(this.fileinfoContainer);
                }
            }

            this.uploadPath = this.leftSide.find('div.uploadpath');
            this.uploadDropContainer = this.leftSide.find('div.upload-drop-container');
            this.rightSide = $('<div class="right-side"/>');

            // cwd
            this.coverflowContainer = $('<div class="coverflow-container"/>').append(
                $('<div class="wrapper"/>').append('<div class="flow-content"/>')
            ).append($('<div class="flow-scroll"/>').append('<div/>')).append($('<div class="cover-label"/>'));

            this.foldercontentContainer = $('<div class="foldercontent"/>');
            this.foldercontentContainerInner = $('<div class="foldercontentInner"/>');
            this.foldercontentContainer.append(this.foldercontentContainerInner);

            this.spinnerIcon = $('<div class="spinner"/>').hide();
            this.statusbar = $('<div class="statusbar"/>').append(this.spinnerIcon);

            this.pager = $('<div class="pager"/>');
            this.statusbar.append(this.pager);
            this.rightSide.append(this.coverflowContainer).append(this.foldercontentContainer).append(this.statusbar).append(this.err);

            if (this.fm.opts.treePanel) {
                this.filemanContainer.append(this.pathway).append(this.rightSide);
            }
            else {
                this.filemanContainer.append(this.pathway).append(this.leftSide).append(this.rightSide);
            }

            $(el).empty().append(this.filemanContainer);


            // this.fileinfoContainer = $( this.leftSide ).find('div.file-info');
            //  this.treeContainer = $( this.leftSide ).find('div.treelist');


            this.statusbar = $(el).find('div.statusbar');
            this.foldercontentContainer = $(el).find('div.foldercontent');
            this.foldercontentContainerInner = $(el).find('div.foldercontentInner');

            this.coverflowContainer.data('baseHeight', this.coverflowContainer.height());


            if (self.fm.opts.coverflow == true) {
                this.coverflowContainer.show();
            }
            else {
                this.coverflowContainer.hide();
            }

            // this.updatePanelSizes();
        };

        /**
         *
         * @param bool show
         */
        this.spinner = function (show) {
            if (show) {
                this.pager.hide();
                this.spinnerIcon.show();
            }
            else {
                this.spinnerIcon.hide();
                this.pager.show();
            }
        };

        /*
         * Display ajax error
         */
        this.fatal = function (t) {
            self.error(t.status != '404' ? 'Invalid backend configuration' : 'Unable to connect to backend');
        };

        /*
         * Render error
         */
        this.error = function (err, data) {
            //this.fm.lock();
            this.err.children('strong').html(err + '!' + this.formatErrorData(data));
            this.err.fadeIn(200);

            setTimeout(function () {
                self.err.fadeOut('slow');
                //self.fm.unlock();
            }, 4000);
        };

        /*
         * Return error message
         */
        this.formatErrorData = function (data) {
            var i, err = ''
            if (typeof (data) == 'object') {
                err = '<br />';
                for (i in data) {
                    err += i + ' ' + self.fm.i18n(data[i]) + '<br />';
                }
            }
            return err;
        };

        // find the Path by giving node
        this.getCwdPath = function (node) {
            var arr = [], parents = this.fm.parents(this.fm.cwd.hash);

            for (var x = 0; x < parents.length; ++x) {
                arr.push(parents[x].name);
            }

            return arr.join(this.fm.opts.dirSep);
        };

        this.pathwayToPath = function () {
            return this.getCwdPath();
        };

        this.toggleUpload = function () {
            if (!this.upload.is(':visible')) {
                this.fileinfoPreview.hide();
                this.fileinfo.hide();
                this.upload.show();
                this.isUploadMode = true;
            }
            else {
                this.upload.hide();
                this.fileinfoPreview.show();
                this.fileinfo.show();
                this.isUploadMode = false;
            }
        };

        this.updateUploadInfos = function () {
            if (this.isUploadMode) {
                var path = this.getCwdPath();
                this.uploadPath.empty().append('Upload Path: ' + (path == '' ? '/' : '/' + path));

                this.fm.command.isCmdAllowed('upload');
            }
        };

        this.updatePathWay = function () {
            var parents = this.fm.parents(this.fm.cwd.hash);
            this.pathway.empty();

            var root = this.treeContainer.find('a.tree-root:first');
            var item = $('<div hash="' + root.attr('hash') + '"/>').html('<div class="home"></div><div></div>');

            if (this.fm.cwd.hash != root.attr('hash')) {
                item.click(function () {
                    self.fm.open(this, $(this).attr('hash'), 'directory');
                    self.doTreeUpdateHash = true;
                    self.fm.setSelected($(this).attr('hash'));
                    self.updateTreeSelection();
                });

                if (item.width() > 150) {
                    item.on('mouseover', function () {
                        $(this).addClass('over');
                    });

                    item.on('mouseleave', function () {
                        $(this).removeClass('over');
                    });
                }
                else {
                    item.addClass('over');
                }
            }
            else {
                item.addClass('current');
            }

            this.pathway.append(item);

            for (var x = 0; x < parents.length; ++x) {
                var item = $('<div hash="' + parents[x].hash + '"/>').html('<div></div><span>' + parents[x].name + '</span><div></div>');

                if (this.fm.cwd.hash != parents[x].hash) {
                    item.click(function () {

                        self.fm.open(this, $(this).attr('hash'), 'directory');
                        self.doTreeUpdateHash = true;
                        self.fm.setSelected($(this).attr('hash'));
                        self.updateTreeSelection();

                    });

                    item.addClass('over');
                    if (item.width() > 150) {
                        item.removeClass('over');

                        item.on('mouseover', function () {
                            $(this).addClass('over');
                        });

                        item.on('mouseleave', function () {
                            $(this).removeClass('over');
                        });

                    }
                    else {
                        item.addClass('over');
                    }
                }
                else {
                    item.addClass('current');
                    /**
                     * For init Filemanager last selected path
                     *
                     */
                    var current = this.leftSide.find('.treelistInner').find('[hash=' + this.fm.cwd.hash + ']');
                    current.parents('.item').each(function () {
                        $(this).removeClass('selected').find('a:first').removeClass('selected');
                        if (!$(this).find('.subtree:first').is(':visible')) {
                            $(this).find('.subtree:first').show();
                        }
                    });
                    current.addClass('selected').parent().addClass('ui-selected selected');
                }

                this.pathway.append(item);
            }

        };

        /**
         *  Update the statusbar if selected a file/dir in dirlist
         *
         */
        this.updateStatusbar = function () {
            if (!this.statusbar.find('.info').length) {
                this.statusbar.total = $('<div class="total-items">' + this.fm.dirInfo.items + ' Items</div>');
                this.statusbar.size = $('<div class="total-size">' + this.fm.formatSize(this.fm.dirInfo.size) + '</div>');

                this.statusbar.append($('<div class="info">').append(this.statusbar.total).append(this.statusbar.size));
            }
            else {
                this.statusbar.total.text(this.fm.dirInfo.items + ' Items');
                this.statusbar.size.text(this.fm.formatSize(this.fm.dirInfo.size));
            }

        };

        this.updatePager = function () {
            if (this.fm.dircontent_pages > 1) {
                this.pager.empty();
                var ul = $('<ul>');
                for (var i = 1; i <= this.fm.dircontent_pages; i++) {
                    var li = $('<li>').text(i);
                    if (i == this.fm.dircontent_page) {
                        li.addClass('active');
                    }

                    ul.append(li);
                }

                ul.appendTo(this.pager);

                ul.find('li').click(function () {
                    var page = $(this).text();
                    if (page != self.fm.dircontent_page) {
                        var args = {
                            action: 'open',
                            type: 'dir',
                            pathHash: self.fm.cwd.hash,
                            target: self.fm.cwd.hash,
                            tree: false,
                            page: page
                        };
                        self.fm.ajax(args, function (data) {
                            self.fm.reload(false);
                            self.fm.lockShortcuts();
                        });
                    }
                });

                this.pager.show();
            }
            else {
                this.pager.empty();
            }
        };

        this.updateTreeSelection = function () {
            var selection = self.fm.getSelected();

            if (selection.length == 0 && this.fm.selected[0]) {
                selection = []
                if (this.fm._files[this.fm.selected[0]])
                    selection.push(this.fm._files[this.fm.selected[0]]);
            }

            if (selection.length == 1) {
                var current = this.leftSide.find('.treelistInner').find('[hash=' + selection[0].hash + ']');

                current.parents('.item').each(function () {
                    $(this).removeClass('selected').find('a:first').removeClass('selected');
                    if (!$(this).find('.subtree:first').is(':visible')) {
                        $(this).find('.subtree:first').show();
                    }
                });

                current.addClass('selected').parent().addClass('ui-selected selected');
                //current.parents('.item:first').attr('id', selection[0].hash );

                // scroll to element
                if (this.doTreeUpdateHash && current.length && this.fm.opts.externalScrollbarContainer && this.leftSide.find('.treeDiv').height() > this.leftSide.find('.treelist').height()) {
                    if (typeof self.fm.opts.onAfterLoad == 'function') {
                        self.fm.opts.onAfterLoad();
                        self.addResizeable();
                    }

                    setTimeout(function () {

                        //var treeHeight = self.leftSide.find('.treelist').outerHeight();

                        if (typeof self.fm.opts.scrollTo == 'function') {
                            //  console.log('scrollTo: ' + current.offset().top);
                            scrollTo('tree', current);
                            //Tools.scrollBar(0 + current.offset().top);
                        }

                        /*
                         $(self.fm.opts.externalScrollbarContainer, self.treeContainer).stop(true, true).animate({
                         top: 0 - current.offset().top - current.outerHeight() + treeHeight
                         }, 250);

                         */

                        self.doTreeUpdateHash = false;
                    }, 100);

                }

            }

        };

        /**
         *  Render the tree
         */
        this.renderTree = function (tree) {

            var subs = tree.subdirs;
            delete tree.subdirs;

            tree.phash = false;

            this.fm.treeSingleDimension = [];
            this.fm.treeSingleDimension[tree.hash] = tree;

            var d = subs.length ? this._traverse(subs, tree.hash, 1) : false;

            var div = '<div class="treeDiv"><div class="item"><a href="#" class="tree-root" hash="' + tree.hash + '"><span' + (d ? ' class="collapsed expanded"' : '') + '/><div></div><span class="perms"></span>' + tree.name + '</a>' + d + '</div></div>';

            this.leftSide.find('.treelistInner').empty();
            this.leftSide.find('.treelistInner').html(div);
            this.leftSide.find('.subtree:first').show();

            this.updatePathWay();
        };

        /**
         *  Bind the tree events
         */
        this.bindTreeEvents = function () {

            $('a:not(.noaccess,.readonly)', this.treeContainer).droppable({
                tolerance: 'pointer',
                accept: 'div[hash],tr[hash]',
                over: function () {
                    $(this).addClass('el-finder-droppable');
                },
                out: function () {
                    $(this).removeClass('el-finder-droppable');
                },
                drop: function (e, ui) {
                    e.preventDefault();

                    $(this).removeClass('el-finder-droppable');
                    self.fm.drop(e, ui, $(this).attr('hash'));

                }
            });

            this.treeContainer.find('.collapsed').each(function () {
                $(this).unbind('click.' + self.fm.id).bind('click.' + self.fm.id, function (e) {
                    e.preventDefault(); // cancel default behavior
                    self.doTreeUpdateHash = true;
                    if ($(this).parents('.item:first').find('.subtree:first').length == 1) {
                        self.fm.select($(this), true);
                        if (!$(this).parents('.item:first').find('.subtree:first').is(':visible')) {
                            $(this).addClass('expanded');
                            $(this).parents('.item:first').find('.subtree:first').show();
                        }
                        else {
                            $(this).removeClass('expanded');
                            $(this).parents('.item:first').find('.subtree:first').hide();
                        }

                        self.treeContainer.find('.selected').removeClass('selected');
                        $(this).parents('.item:first').addClass('selected');
                        $(this).parents('a:first').addClass('selected');

                        if (typeof self.fm.opts.onAfterLoad == 'function') {
                            self.fm.opts.onAfterLoad();
                            self.addResizeable();
                            self.fm.setSelected($(this).attr('hash'));
                            self.updateTreeSelection();
                        }

                        return false;
                    }
                });
            });

            var clicks = 0;
            var dblClickTimer;
            this.treeContainer.find('a').each(function () {
                $(this).unbind('click.' + self.fm.id).bind('click.' + self.fm.id, function (e) {
                    e.preventDefault();

                    if ($(e.target).hasClass('collapsed')) {
                        return true;
                    }

                    var _self = this;
                    clicks++;

                    if (clicks === 1) {
                        dblClickTimer = setTimeout(function () {

                            self.treeContainer.find('.selected').removeClass('selected');
                            self.treeContainer.find('.ui-selected').removeClass('ui-selected');

                            self.doTreeUpdateHash = true;
                            self.fm.select($(_self), true);

                            $(_self).addClass('selected');
                            $(_self).parents('.item:first').addClass('selected');
                            clicks = 0;
                        }, 300);
                    }
                    else if (clicks >= 2) {

                        clearTimeout(dblClickTimer);
                        self.doTreeUpdateHash = true;
                        self.fm.select($(_self), true);
                        self.fm.open($(_self), $(_self).attr('hash'), 'dir');

                        self.treeContainer.find('.selected').removeClass('selected');
                        self.treeContainer.find('.ui-selected').removeClass('ui-selected');

                        $(_self).addClass('selected');
                        $(_self).parents('.item:first').addClass('selected');

                        clicks = 0;
                    }
                    return false;
                    //self.updateTreeSelection();
                });
            });
        };

        /**
         *  END tree
         */


        this.addItemToCoverflow = function (data) {
            if (!data.mime || !self.fm.opts.coverflow) {
                return;
            }

            if (typeof this.flowContent == 'undefined') {
                this.flowContent = this.coverflowContainer.find('div.flow-content:first').get(0);

            }
            if (data.mime.match(/^image\//)) {
                this.flowContent.innerHTML += '<span style="background-image:url(' + data.coverflow + ')" hash="' + data.hash + '" data-album="' + data.name + '"></span>';
            }
            else {
                this.flowContent.innerHTML += '<span class="cwd-icon ' + (data.mime == 'directory' ? 'directory ' : '') + this.fm.mime2class(data.mime) + '" hash="' + data.hash + '" data-album="' + data.name + '"></span>';
            }

        };

        /**
         *  Render Current work dir
         */
        this.renderCWD = function () {
            var dragContainer;

            if (typeof self.flowContent == 'undefined') {
                self.flowContent = self.coverflowContainer.find('div.flow-content:first').get(0);
            }

            if (self.fm.opts.coverflow) {
                self.flowContent.style.display = 'none';
                self.coverflowContainer.show();
                self.flowContent.innerHTML = '';

                if (self.fm.allowLocalStorage()) {
                    self.fm.localStorage('coverflow', true);
                }
            }
            else {
                self.coverflowContainer.hide();
                self.coverflowHeight = null;
                self.flowContent.style.display = 'none';

                if (self.fm.allowLocalStorage()) {
                    self.fm.localStorage('coverflow', false);
                }
            }

            var selectableFilter;

            this.pager.hide();

            if (self.fm.opts.view == 'list') {
                self.rightSide.mask('laden...');
                self.renderList();
                self.fixTableSize();

                selectableFilter = 'tr[hash]';
                dragContainer = self.foldercontentContainer.find('tbody');

                if (self.fm.allowLocalStorage()) {
                    self.fm.localStorage('view', 'list');
                }

            }
            else if (self.fm.opts.view == 'icons') {
                self.rightSide.mask('laden...');
                self.renderIconList();

                if (self.fm.canThumb()) {
                    self.fm.tmb();
                }

                selectableFilter = 'div[hash]';
                dragContainer = self.foldercontentContainer.find('.iconview .scroll-inner');

                if (self.fm.allowLocalStorage()) {
                    self.fm.localStorage('view', 'icons');
                }
            }

            self.updatePathWay();

            if (self.fm.opts.coverflow == true) {
                self.coverflowContainer.show();

                if (self.fm.canThumb() && self.fm.opts.view != 'icons') {
                    self.flowContent.innerHTML = '';
                    self.flowContent.style.display = 'none';
                    self.fm.tmb();
                }
                else {
                    self.fm.coverflow.initCoverflow(self.coverflowContainer);
                }

            }
            else {
                self.coverflowContainer.hide();
            }

            // Update history
            if (this.lastCwd != this.fm.cwd.hash && this.lastCwd !== null) {
                this.fm.history.push(this.fm.cwd.hash);
            }

            this.lastCwd = this.fm.cwd.hash;

            this.updateUploadInfos();
            this.updatePager();

            this.dblClickCounter = 0;
            this.bindTreeEvents();
            this.addResizeable();

            if (this.fm.opts.view == 'list') {
                this.bindRowEvents(this.foldercontentContainerInner.find('.listview tbody'));
            }
            else if (this.fm.opts.view == 'icons') {

                // bind row events
                $('div[hash]', this.foldercontentContainerInner.find('.iconview')).each(function () {
                    var clicks = 0;

                    $(this).unbind('dblclick.fm').unbind('mousedown.fm').unbind('mouseup.fm')
                        .bind('dblclick.fm',function (e) {
                            e.preventDefault();  // cancel system double-click event

                        }).bind('mousedown.fm',function (e) {
                            //if ( !$( this ).hasClass( 'ui-selected' ) ) {
                            self.disableDraggable();
                            //}
                        }).bind('mouseup.fm', function (e) {
                            if (self.selecting) {
                                return true;
                            }
                            self.enableDraggable();
                            self.dblClickCounter++;
                            var _self = $(this);

                            if (e.metaKey || e.altKey) {
                                self.fm.select(_self, false);
                                self.dblClickCounter = 0;
                                self.selectedCount = 1;
                                return;
                            }

                            if (self.dblClickCounter === 1) {

                                self.clicktimeout = setTimeout(function () {
                                    self.dblClickCounter = 0;
                                    self.selectedCount = 1;
                                    self.fm.select(_self, true);
                                    // self.fm.command.exec('select');
                                }, 200);
                            }
                            else {
                                clearTimeout(self.clicktimeout);
                                self.handleDblClick(e, _self);
                            }

                        });
                });
            }


            setTimeout(function () {
                self.rightSide.unmask();
                self.updatePanelSizes();
                self.selecting = false;
                self.selectedCount = 0;


                if (self.fm.opts.view == 'icons') {
                    $('.iconview', self.foldercontentContainer).filter('ui-selectable').selectable('destroy');
                    $('.iconview', self.foldercontentContainer).selectable({
                        appendTo: ($('#fullscreenContainer').length == 1 ? $('#fullscreenContainer') : $('body') ),
                        filter: 'div[hash]',
                        opacity: '1',
                        start: function (event, ui) {
                            $(selectableFilter, self.foldercontentContainer).draggable('disable');
                            self.selecting = true;
                        },
                        selected: function (event, ui) {
                            if (self.dblClickCounter > 1) {
                                //$( selectableFilter, self.foldercontentContainer ).draggable( 'enable' );
                            }
                            else {
                                self.fm.select($(ui.selected), false);
                                self.selectedCount++;
                            }

                        },
                        unselected: function (event, ui) {
                            self.dblClickCounter = 0;
                            self.fm.unselect($(ui.unselected));
                            self.selectedCount--;
                        },
                        stop: function (event, ui) {
                            /*
                             var selcount = 0;
                             $( 'div[hash].ui-selected', self.foldercontentContainer ).each( function ()
                             {
                             self.fm.select( $( this ), false );
                             selcount++;
                             } );
                             */
                            if (self.selectedCount) {
                                $(selectableFilter, self.foldercontentContainer).draggable('enable');
                            }
                            self.selecting = false;
                        }
                    });
                }
                else {
                    $('.body > :first-child', self.foldercontentContainer).not('.pane').filter('ui-selectable').selectable('destroy');
                    $('.body > :first-child', self.foldercontentContainer).not('.pane').selectable({
                        appendTo: ($('#fullscreenContainer').length == 1 ? $('#fullscreenContainer') : $('body') ),
                        filter: 'tr[hash]',
                        opacity: '1',
                        autoRefresh: true,
                        start: function (event, ui) {
                            self.selecting = true;
                            $(selectableFilter, self.foldercontentContainer).draggable('disable');
                        },
                        selected: function (event, ui) {

                            if (self.dblClickCounter > 1) {
                                //$( selectableFilter, self.foldercontentContainer ).draggable( 'enable' );
                            }
                            else {

                                self.fm.select($(ui.selected), false);
                                self.selectedCount++;
                            }
                        },
                        unselected: function (event, ui) {
                            self.dblClickCounter = 0;
                            self.selectedCount--;
                            self.fm.unselect($(ui.unselected));
                        },
                        stop: function (event, ui) {
                            /*
                             var selcount = 0;
                             $( 'tr[hash].ui-selected', self.foldercontentContainer ).each( function ()
                             {
                             self.fm.select( $( this ), false );
                             selcount++;
                             } );
                             */

                            if (self.selectedCount) {
                                //self.selectedCount = selcount;
                                $(selectableFilter, self.foldercontentContainer).draggable('enable');
                            }
                            self.selecting = false;
                        }
                    });
                }


                /**
                 * add drad and drop events
                 */
                $(selectableFilter, self.foldercontentContainer).draggable({
                    delay: 200,
                    addClasses: false,
                    appendTo: ($('#fullscreenContainer').length == 1 ? $('#fullscreenContainer') : $('body') ),
                    revert: true,
                    revertDuration: 200,
                    scroll: false,
                    cursorAt: {left: 0, top: 0},
                    drag: function (e, ui) {
                        if (!e.metaKey && !e.altKey && self.selectedCount == 1) {
                            return false;
                        }

                        ui.helper.toggleClass('drag-copy', e.shiftKey || e.ctrlKey);
                    },
                    helper: function () {
                        var t = $(this),
                            h = $('<div class="drag-helper">'),
                            c = 0;

                        !t.hasClass('ui-selected') && self.fm.select(t, true);

                        var files = dragContainer.find('.ui-selected').length;

                        if (self.selectedCount > 1) {
                            var inner = $('<div class="drag-helper-inner"></div>');
                            h.addClass('multi');
                            h.append(inner);
                        }
                        else {
                            var inner = $('<div class="drag-helper-inner"></div>');
                            h.append(inner);
                        }

                        var singleName = null, cwd = self.getCwdPath();

                        dragContainer.find('.ui-selected').each(function (i) {

                            var el = self.fm.opts.view == 'icons' ? $(this).clone(false).removeClass('ui-selected') : $(self.renderIcon(self.fm.dircontent[$(this).attr('hash')], cwd, true));

                            el.unbind().removeData();

                            if (c++ == 0 || c % 12 == 0) {
                                //	el.css( 'margin-left', 0 );
                            }

                            if (self.selectedCount > 1) {
                                inner.append(el);
                            }
                            else {
                                singleName = el.find('label').text();
                                el.find('.filename-label').hide();
                                inner.append(el);
                            }
                        });

                        h.append('<div class="drag-as-copy"></div>');

                        if (self.selectedCount > 1) {
                            h.append('<div class="multi-file-info"><span class="multi-file-drag"></span><span class="multi-file-drag-num">' + files + ' Objects</span></div>');
                        }
                        else {
                            h.append('<div class="multi-file-info"><span class="multi-file-drag-num">' + singleName + '</span></div>');
                        }
                        return h.css('width', 85 + 'px');

                        return h.css('width', (c <= 12 ? 85 + (c - 1) * 29 : 387) + 'px');
                    },
                    stop: function () {
                        $(selectableFilter, self.foldercontentContainer).draggable('disable');
                    }
                }).filter('[hash]').droppable({
                        tolerance: 'pointer',
                        accept: '[hash]',
                        over: function () {
                            $(this).addClass('el-finder-droppable');
                        },
                        out: function () {
                            $(this).removeClass('el-finder-droppable');
                        },
                        drop: function (e, ui) {
                            $(this).removeClass('el-finder-droppable');
                            self.fm.drop(e, ui, $(this).attr('hash'));
                        }
                    });


                $(selectableFilter, self.foldercontentContainer).draggable('disable');


            }, 100);

        }; // END renderCWD

        this.disableDraggable = function () {
            if (this.fm.opts.view == 'list') $('tr[hash]', self.foldercontentContainer).filter('ui-draggable').draggable('disable');
            else {
                $('div[hash]', self.foldercontentContainer).filter('ui-draggable').draggable('disable');
            }
        };

        this.enableDraggable = function () {
            if (this.fm.opts.view == 'list') $('tr[hash]', self.foldercontentContainer).filter('ui-draggable').draggable('enable');
            else {
                $('div[hash]', self.foldercontentContainer).filter('ui-draggable').draggable('disable');
            }
        };

        /**
         *
         * @param {type} data
         * @returns {undefined}
         */
        this.updateFile = function (data) {
            var hash = data.hash;

            if (this.fm.opts.view == 'list') {
                var fileinfos = this.foldercontentContainer.find('tr[hash="' + hash + '"]');
                if (fileinfos.length) {
                    fileinfos.find('.d span').empty().append(this.fm.formatDate(data.date));
                    fileinfos.find('.s span').empty().append(this.fm.formatSize(data.size));

                    this.fm.dircontent[hash] = data;

                }
            }
            else {

            }

        };

        /**
         *
         */
        this.renderList = function () {
            this.foldercontentContainerInner.find('.iconview').hide();
            var tBody, listview = this.foldercontentContainerInner.find('.listview').show();

            if (listview.length == 0) {
                listview = $('<div class="listview"/>');

                var tableHeader = $('<table cellspacing="0" cellpadding="0" class="headerTable">');
                tableHeader.html(
                    '<thead><tr><th>&nbsp;</th>\n\
    <th order="name" class="f"><span class="order"></span>Dateiname</th>\n\
    <th order="size" class="s"><span class="order"></span>Größe</th>\n\
<th order="kind" class="t"><span class="order"></span>Typ</th>\n\
<th order="date" class="d"><span class="order"></span>Geändert</th>\n\
<th order="perm" class="p"><span class="order"></span>Rechte</th>\n\
<th order="dimensions" class="dim"><span class="order"></span>Abmessungen</th></tr></thead>');

                var tableBody = $('<div class="body"><table cellspacing="0" cellpadding="0"><tbody></tbody></table></div>');
                tableHeader.find('th').each(function () {
                    if ($(this).attr('order')) {
                        if (self.fm.sortType == $(this).attr('order')) {
                            $(this).addClass('s' + self.fm.sortOrder);
                        }
                    }
                    $(this).bind('click.' + self.fm.id, function (e) {

                        if ($(this).attr('order')) {
                            var sort = ($(this).hasClass('sasc') ? 'desc' : 'asc');

                            self.fm.setSort($(this).attr('order'), sort, true);
                            self.renderCWD();

                            $(this).parents('thead').find('th').removeClass('sasc').removeClass('sdesc'); // remove other sorts
                            $(this).removeClass('sasc').removeClass('sdesc').addClass('s' + sort);
                        }
                    });

                });

                listview.append($('<div class="header"/>').append(tableHeader)).append(tableBody);

                this.foldercontentContainerInner.append(listview);

                tBody = $('tbody', tableBody);
                if (!this.body)
                    this.body = $('.body', tableBody);
                if (!this.header)
                    this.header = $('.header', tableBody);
            }
            else {
                tBody = $('tbody', listview);

                if (!this.body)
                    this.body = $('.body', listview);
                if (!this.header)
                    this.header = $('.header', listview);
            }

            if (this.fm.dircontent) {

                tBody.empty();

                // set sort
                var dircontentMaped = $.map(this.fm.dircontent, function (f) {
                    return true || f.hash == this.fm.cwd.hash ? f : null;
                });
                dircontentMaped = this.fm.sortFiles(dircontentMaped);

                var x, cwd = self.getCwdPath();

                // for (var x=0; x<dircontentMaped.length; ++x)
                // {
                for (x in dircontentMaped) {
                    var hash = dircontentMaped[x].hash;
                    tBody.append(this.renderRow(dircontentMaped[x], x, cwd));
                }

                delete dircontentMaped;
            }


        }; // END renderList

        this.bindRowEvents = function (tBody) {
            /*
             $('tr', tBody).unbind('dblclick.' + self.fm.id).bind('dblclick.' + self.fm.id, function(e) {

             var _self = $(this);
             var key = _self.attr('hash');
             var type = _self.hasClass('directory') ? 'dir' : 'file';

             if (key)
             {
             self.doTreeUpdateHash = true;
             self.fm.select(_self, true);
             self.fm.command.exec('open');
             self.fm.setSelected(key);
             self.updateTreeSelection();
             }


             });



             return;
             */

            var clicks = 0;
            // bind row events
            $('tr', tBody)
                .unbind('click.filerow')
                .unbind('dblclick.fm')
                .unbind('mousedown.fm')
                .bind('mousedown.fm', function (e) {
                    //if ( !$( this ).hasClass( 'ui-selected' ) ) {
                    self.disableDraggable();
                    //}
                })
                .unbind('mouseup.fm')
                .bind('mouseup.fm', function (e) {
                    if (self.selecting) {
                        return;
                    }

                    e.preventDefault();
                    self.enableDraggable();

                    self.dblClickCounter++;
                    var _self = $(this);

                    if (e.metaKey || e.altKey) {
                        self.fm.select(_self, false);
                        self.dblClickCounter = 0;
                        self.selectedCount = 1;
                        return;
                    }

                    if (self.dblClickCounter === 1) {

                        self.clicktimeout = setTimeout(function () {
                            self.dblClickCounter = 0;
                            self.selectedCount = 1;
                            self.fm.select(_self, true);

                            // self.fm.command.exec('select');
                        }, 200);
                    }
                    else {
                        clearTimeout(self.clicktimeout);
                        self.handleDblClick(e, _self);
                    }

                })
                .bind('dblclick.fm', function (e) {
                    e.preventDefault(); // cancel system double-click event
                });
        };

        this.handleDblClick = function (event, obj) {
            if (self.dblClickCounter > 1) {
                self.dblClickCounter = 0;
                self.selectedCount = 0;

                var key = obj.attr('hash');
                var type = obj.hasClass('directory') ? 'dir' : 'file';

                if (key) {
                    self.doTreeUpdateHash = true;
                    self.fm.select(obj, true);

                    if (self.fm && typeof self.fm.extraEvents.onSelectFile === 'function' && type !== 'dir') {
                        self.fm.open($(obj), key, type, self.fm.extraEvents.onSelectFile);
                    }
                    else {
                        self.fm.open($(obj), key, type);
                        self.fm.setSelected(key);

                        if (type === 'dir') {
                            self.updateTreeSelection();
                        }
                    }
                }
            }

        };

        this.renderRow = function (f, x, cwd) {
            var str = f.alias || f.mime == 'symlink-broken' ? '<em class="symlink"/>' : '';

            if (!f.name) {
                return '';
            }

            if (!f.read && !f.write) {
                str += '<em class="noaccess"/>';
            } else if (f.read && !f.write) {
                str += '<em class="readonly"/>';
            } else if (!f.read && f.write) {
                str += '<em class="' + (f.mime == 'directory' ? 'dropbox' : 'noread') + '" />';
            }

            if (f.mime != 'directory' && f.mime != 'symlink-broken' && this.fm.dircontent[f.hash]) {
                this.fm.dircontent[f.hash].url = this.fm.opts.connectorUrl + '&action=open&type=file&fpathHash=' + f.hash + '&cwd=' + encodeURIComponent(cwd);
            }

            if (f.mime == 'directory' && this.fm.dircontent[f.hash]) {
                var file = self.fm.file(f.hash);
                if (file && file.phash) {
                    this.fm.dircontent[f.hash].phash = file.phash;
                }

                if (this.fm.opts.coverflow == true) {
                    this.addItemToCoverflow({coverflow: 'folder.png', hash: f.hash, name: f.name, mime: f.mime});
                }
            }
            else {
                if (this.fm.dircontent[f.hash])
                    this.fm.dircontent[f.hash].phash = self.fm.cwd.hash;

                if (f.coverflow && this.fm.opts.coverflow == true) {
                    this.addItemToCoverflow({coverflow: f.coverflow, hash: f.hash, name: f.name, mime: f.mime});

                }
            }

            var permClass = this.fm.perms2class(f);

            this.fm.dirInfo.items++;
            this.fm.dirInfo.size += f.size;

            return $('<tr hash="' + f.hash + '" class="' + (f.mime != 'directory' ? ' file ' : ' directory ' ) + (x % 2 ? ' odd ' : '')

                // + this.fm.mime2class(f.mime) 
                + (permClass ? ' ' + permClass : '')
                + '"' + (f.mime != 'directory' ? ' file="1"' : '') + '><td><div class="cwd-icon ' + this.fm.mime2class(f.mime) + '">'
                + str

                + '</div></td><td class="f"' + '><span>'
                + f.name

                + '</span></td><td class="s"><span>'
                + this.fm.formatSize(f.size)

                + '</span></td><td class="t"><span>'
                + this.fm.mime2kind(f.link ? 'symlink' : f.mime)

                + '</span></td><td class="d"><span>'
                + this.fm.formatDate(f.date)

                + '</span></td><td class="p"><span>'
                // + this.fm.formatPermissions(f.read, f.write, f.rm)
                + f.perms_human + ' (' + f.perms_octal1 + ')'

                + '</span></td><td class="dim"><span>'
                + (f.dimensions && f.dimensions != '---' ? f.dimensions : '') + '</span></td></tr>');
        };

        this.renderIconList = function () {
            this.foldercontentContainerInner.find('.listview').hide();
            var listview = this.foldercontentContainerInner.find('.iconview');

            if (listview.length == 0) {
                listview = $('<div class="iconview body"/>').show();
                this.foldercontentContainerInner.append(listview);
            }

            listview.empty().show();

            if (this.fm.dircontent) {

                listview.append('<div class="scroll-inner"></div>');

                // set sort
                var dircontentMaped = $.map(this.fm.dircontent, function (f) {
                    return true || f.hash == this.fm.cwd.hash ? f : null;
                });

                dircontentMaped = this.fm.sortFiles(dircontentMaped);

                var x, cwd = self.getCwdPath();

                for (x in dircontentMaped) {
                    var hash = dircontentMaped[x].hash;
                    var f = dircontentMaped[x];
                    listview.find('.scroll-inner').append(self.renderIcon(f, cwd));
                }

                delete dircontentMaped;
            }


        }; // END renderIconList

        this.renderIcon = function (f, cwd, fromDrag) {

            if (!f.name) {
                return '';
            }
            if (f.mime != 'directory' && f.mime != 'symlink-broken' && this.fm.dircontent[f.hash] && !this.fm.dircontent[f.hash].url) {
                this.fm.dircontent[f.hash].url = this.fm.opts.connectorUrl + '&action=open&type=file&fpathHash=' + f.hash + '&cwd=' + encodeURIComponent(cwd);
            }

            if (f.mime != 'directory' && f.mime != 'symlink-broken' && this.fm.dircontent[f.hash] && f.mime.match(/^image\//) && this.fm.canThumb()) {
                // this.fm.dircontent[f.hash].tmb = this.fm.opts.connectorUrl + '&action=thumb&type=file&fpathHash='+ f.hash +'&cwd='+ encodeURIComponent( cwd );
            }

            if (f.mime == 'directory' && this.fm.dircontent[f.hash]) {
                var file = self.fm.file(f.hash);
                if (file && file.phash) {
                    this.fm.dircontent[f.hash].phash = file.phash;
                }

                if (this.fm.opts.coverflow == true && !fromDrag) {
                    this.addItemToCoverflow({coverflow: 'folder.png', hash: f.hash, name: f.name, mime: f.mime});
                }

            }
            else {

                if (f.coverflow && this.fm.opts.coverflow == true && !fromDrag) {
                    this.addItemToCoverflow({coverflow: f.coverflow, hash: f.hash, name: f.name, mime: f.mime});
                }

                if (this.fm.dircontent[f.hash])
                    this.fm.dircontent[f.hash].phash = self.fm.cwd.hash;
            }

            var p = $('<p' + (f.tmb ? ' style="' + "background-image:url('" + f.tmb + "')" + '"' : '') + ' class="' + (f.tmb ? 'found ' : '') + 'cwd-icon ' + this.fm.mime2class(f.mime) + '"/>');
            var image = $('<div class="image"></div>');

            this.fm.dirInfo.items++;
            this.fm.dirInfo.size += f.size;

            var str = '';
            if (f.alias) {
                str += '<span class="symlink"/>';
            }
            else if (f.mime == 'symlink-broken') {
                str += '<span class="symlink broken"/>';
            }

            if (str != '') {
                p.append($(str));
            }

            str = '';
            if (!f.read && !f.write) {
                str += '<em class="noaccess"/>';
            } else if (f.read && !f.write) {
                str += '<em class="readonly"/>';
            } else if (!f.read && f.write) {
                str += '<em class="' + (f.mime == 'directory' ? 'dropbox' : 'noread') + '" />';
            }

            p.append($(str));
            image.append(p);

            var label = $('<div class="filename-label"><label>' + this.fm.formatName(f.name) + '</label></div>');
            image.append(label);

            str = image.html();

            return '<div class="icon' + (f.mime != 'directory' ? ' file ' : ' directory ' ) + '" hash="' + f.hash + '">' + str + '</div>';
        };

        // Display the file info if selected in the file/dir list
        this.displayFileInfos = function (hash) {

            if (hash !== false) {
                var f = this.fm.dircontent[hash];
            }

            // single file is selected
            if (f) {
                var container = this.fileinfo, info = $('<div>'), tpl = '<table width="210">';
                tpl += '<tr>';
                tpl += '    <td class="col">Name</td>';
                tpl += '    <td class="name"><div>' + f.name + '</div></td>';
                tpl += '</tr>';
                tpl += '<tr>';
                tpl += '    <td class="col">Art</td>';
                tpl += '    <td class="art"><div>' + (f.mime != 'directory' ? 'Datei' : 'Ordner') + '</div></td>';
                tpl += '</tr>';
                tpl += '<tr>';
                tpl += '    <td class="col">Ort</td>';
                tpl += '    <td class="rel"><div>' + this.fm.cwd.rel + '</div></td>';
                tpl += '</tr>';

                tpl += '<tr>';
                tpl += '    <td class="col">Datum</td>';
                tpl += '    <td class="date"><div>' + f.date + '</div></td>';
                tpl += '</tr>';
                tpl += '<tr>';
                tpl += '    <td class="col">Dateigröße</td>';
                tpl += '    <td class="size"><div>' + this.fm.formatSize(f.size) + '</div></td>';
                tpl += '</tr>';
                tpl += '<tr>';
                tpl += '    <td class="col">Rechte</td>';
                tpl += '    <td class="perms"><div>' + this.fm.formatPermissions(f.read, f.write, f.rm) + '</div></td>';
                tpl += '</tr>';
                tpl += '<tr>';
                tpl += '    <td class="col">Mime Typ</td>';
                tpl += '    <td class="perms"><div>' + this.fm.mime2kind(f.link ? 'symlink' : f.mime) + '</div></td>';
                tpl += '</tr>';

                if (f.dimensions && f.dimensions != '---') {
                    tpl += '<tr>';
                    tpl += '    <td class="col">Abmessungen</td>';
                    tpl += '    <td class="perms"><div>' + f.dimensions + '</div></td>';
                    tpl += '</tr>';
                }

                info.html(tpl);

                this.fileinfoPreview.removeAttr('class')
                    .addClass('preview-container').empty().append(
                        $('<p/>').addClass('cwd-icon ' + self.fm.mime2class(f.mime))
                    );

                if (!this.isUploadMode) {
                    this.fileinfoPreview.show();
                }

                container.empty().append(info);
            }
            else {
                this.fileinfoPreview.hide();
                var container = this.fileinfo;
                container.empty();
            }
        };

        this.updateSizeOnly = function (callback) {
            var height;
            if (Desktop.isWindowSkin) {
                if (this.fm.opts.isInlineFileman) {
                    //width = (thewin.width() - 50);
                    height = parseInt($(el).parents('.inline-window-slider:first').height(), 10);
                }
                else {
                    height = parseInt($(el).parents('.window-body-content:first').height(), 10);
                }
            }
            else {
                if (this.fm.opts.isInlineFileman) {
                    //width = (thewin.width() - 50);
                    height = parseInt($(el).parents('.inline-window-slider:first').height(), 10);
                }
                else {
                    height = parseInt($(el).parents('.core-tab-content:first').height(), 10);
                }
            }

            var rightHeight = parseInt(this.fm.opts.treePanel.parent().height(), 10), rightWidth = parseInt($(el).width(), 10);

            if (typeof self.statusbarHeight == 'undefined') {
                self.foldercontentInner = self.foldercontentContainer.find('.foldercontentInner');
                self.treelistInner = this.fm.opts.treePanel.find('.treelistInner');
                self.pathwayHeight = parseInt(self.pathway.outerHeight(true), 10);
                self.statusbarHeight = parseInt(self.statusbar.outerHeight(true), 10);
            }

            if (self.fm.opts.view == 'list' && typeof self.listBodyScrollContent == 'undefined') {
                self.listHeader = self.foldercontentContainer.find('.header');
                self.listHeaderHeight = parseInt(self.listHeader.outerHeight(true), 10);
                self.listBody = self.foldercontentContainer.find('.body');
                self.listBodyScrollContent = self.foldercontentContainer.find('.scroll-content');
            }

            if (self.fm.opts.view == 'icons' && typeof self.iconBody == 'undefined') {
                self.iconBody = self.foldercontentContainer.find('.iconview');
                self.listIconBodyScrollContent = self.iconBody;
            }

            if (self.fm.opts.coverflow) {
                self.fm.coverflow.reCenterCoverflow(true);
            }

            if (height > 0) {
                $(el).find('.right-side').height(sidesHeight);
            }

            var extraH = 0, sidesHeight = height - self.pathway.outerHeight(true);
            var folderContentHeight = height - self.statusbarHeight - self.pathwayHeight - self.coverflowHeight;

            if (self.treeHeight === 0) {
                self.treeHeight = parseInt(self.treeContainer.outerHeight(true), 10);
            }

            var h = rightHeight - self.treeHeight;
            if (self.fm.opts.isInlineFileman && extraH > 0) {
                h = h - extraH;
            }

           // self.fileinfoContainer.height(h);

            if (self.fm.opts.view == 'list') {
                self.listBodyScrollContent.height('').width('');
                self.foldercontentInner.height(folderContentHeight).css('overflow', '');
                //	self.listBody.height( folderContentHeight - self.listHeaderHeight );
                self.listHeader.width(rightWidth);
                self.listBody.width(rightWidth).height(parseInt(folderContentHeight - self.listHeaderHeight, 10));
            }
            else {
                self.listIconBodyScrollContent.height('').width('');
                self.iconBody.height(folderContentHeight);
                self.foldercontentInner.height(folderContentHeight).css('overflow', '');
                self.iconBody.width(rightWidth);
            }

            if (callback !== false) {
                self.fixTableSize();
            }

            if (typeof callback == 'function') {
                callback();
            }

        };

        this.updatePanelSizes = function (callback, force) {
            if (!force) {
                if (self.fm.opts.view == 'list' && !$(el).find('.header').length) {
                    return;
                }
                else if (self.fm.opts.view == 'icons' && !$(el).find('.iconview').length) {
                    return;
                }
            }


            if (this.fm.opts.treePanel) {

                this.updateSizeOnly(callback);
                return;
            }

            var height;
            if (Desktop.isWindowSkin) {
                if (this.fm.opts.isInlineFileman) {
                    //width = (thewin.width() - 50);
                    height = parseInt($(el).parents('.inline-window-slider:first').height(), 10);
                }
                else {
                    height = parseInt($(el).parents('.window-body-content:first').height(), 10);
                }
            }
            else {
                if (this.fm.opts.isInlineFileman) {
                    //width = (thewin.width() - 50);
                    height = parseInt($(el).parents('.inline-window-slider:first').height(), 10);
                }
                else {
                    height = parseInt($(el).parents('.core-tab-content:first').height(), 10);
                }
            }

            var width = parseInt($(el).outerWidth(), 10);
            var extraH = 0, sidesHeight = height - self.pathway.outerHeight(true);

            if (self.fm.opts.isInlineFileman) {
                extraH = parseInt($(el).parents('.inline-window-slider:first').find('.fm-footer').outerHeight(true), 10);

                if (extraH > 0) {
                    sidesHeight = sidesHeight - extraH;
                }
                var extraH2 = parseInt($(el).parents('.inline-window-slider:first').find('.fm-toolbar').outerHeight(true), 10);
                if (extraH2 > 0) {
                    sidesHeight = sidesHeight - extraH2;
                    extraH += extraH2;
                }
            }

            if (typeof self.foldercontentInner == 'undefined') {
                self.foldercontentInner = self.foldercontentContainer.find('.foldercontentInner');
                self.treelistInner = self.leftSide.find('.treelistInner');

                self.pathwayHeight = self.pathway.outerHeight(true);
                self.statusbarHeight = self.statusbar.outerHeight(true)
            }

            if (self.fm.opts.view == 'list' && typeof self.listHeader == 'undefined') {
                self.listHeader = self.foldercontentContainer.find('.header');
                self.listHeaderHeight = self.listHeader.outerHeight(true);
                self.listBody = self.foldercontentContainer.find('.body');
                self.listBodyScrollContent = self.foldercontentContainer.find('.scroll-content');
            }

            if (self.fm.opts.view == 'icons' && typeof self.iconBody == 'undefined') {
                self.iconBody = self.foldercontentContainer.find('.iconview');
                self.listBodyScrollContent = self.iconBody;
            }

            if (self.fm.opts.coverflow) {
                self.fm.coverflow.reCenterCoverflow(true);
            }

            if (height > 0) {
                $(el).find('.left-side,.right-side').height(sidesHeight);
            }

            if (self.tableHeaderHeight === 0 && self.header) {
                self.tableHeaderHeight = self.header.outerHeight(true);
            }

            if (self.treeHeight === 0) {
                self.treeHeight = self.treeContainer.outerHeight(true);
            }

            var h = sidesHeight - self.treeHeight;

            if (self.fm.opts.isInlineFileman && extraH > 0) {
                h = h - extraH;
            }

            var leftWidth = self.leftSide.outerWidth(true);

            self.treeContainer.width(leftWidth);
            self.treelistInner.width(leftWidth);


            self.fileinfoContainer.height(h);

            self.rightSide
                .css('marginLeft', leftWidth)
                .height(sidesHeight);

            var rightWidth = self.rightSide.width();
            var folderContentHeight = height - self.statusbarHeight - self.pathwayHeight - self.coverflowHeight;

            if (self.fm.opts.isInlineFileman && extraH > 0) {
                folderContentHeight -= extraH;
            }

            self.foldercontentContainer
                .width(rightWidth)
                .height(folderContentHeight);

            if (self.fm.opts.view == 'list') {
                self.listBodyScrollContent.height('').width('');
                self.foldercontentInner.height(folderContentHeight).css('overflow', '');
                self.listBody.height(folderContentHeight - self.listHeaderHeight);

                self.listHeader.width(rightWidth);
                self.listBody.width(rightWidth);
            }
            else {
                self.listBodyScrollContent.height('').width('');
                self.iconBody.height(folderContentHeight);
                self.foldercontentInner.height(folderContentHeight).css('overflow', '');

                self.iconBody.width(rightWidth);
            }

            if (callback !== false) {
                self.fixTableSize();
            }

            if (typeof callback == 'function') {
                callback();
            }

            // self.addResizeable();
        };

        this.addResizeable = function () {

            this.leftSide.filter(':ui-resizable').resizable('destroy');
            this.leftSide.resizable({
                handles: 'e',
                autoHide: true,
                minWidth: 170,
                maxWidth: 400,
                start: function () {
                    if (self.fm.opts.onResizeStart) {
                        self.fm.opts.onResizeStart();
                    }
                },
                resize: function (event, ui) {
                    if (!self.fm.opts.treePanel) {
                        self.treeContainerInner.css({overflow: ''});
                        self.treeContainerInner.width(ui.size.width);
                    }
                    self.updatePanelSizes(null, true);
                    $(window).trigger('resizescrollbar');
                },
                stop: function (event, ui) {
                    if (!self.fm.opts.treePanel) {
                        self.treeContainerInner.css({overflow: ''});
                        self.treeContainerInner.width(ui.size.width);
                    }
                    self.updatePanelSizes(self.fm.opts.onResizeStop, true);
                    $(window).trigger('resizescrollbar');
                }
            });

            var infoContainerMinHeight = this.fileinfoContainer.css('min-height');


            if (!self.fm.opts.treePanel) {

                this.treeContainer.filter(':ui-resizable').resizable('destroy');
                this.treeContainer.resizable({
                    handles: 's',
                    minHeight: 200,
                    autoHide: true,
                    start: function () {
                        if (self.fm.opts.onResizeStart) {
                            self.fm.opts.onResizeStart();
                        }

                        if (self.fm.opts.coverflow)
                            self.fm.coverflow.reCenterCoverflow(true);
                    },
                    resize: function (event, ui) {
                        var h = self.leftSide.height() - ui.size.height;
                        self.treeContainerInner.css({overflow: ''});

                        if (self.fm.opts.coverflow)
                            self.fm.coverflow.reCenterCoverflow();

                        if (infoContainerMinHeight && h < infoContainerMinHeight) {
                            self.treeHeight = $(this).height();

                            if (!self.fm.opts.treePanel) {
                                self.treeContainer.height(ui.size.height).width(ui.size.width);
                                self.leftSide.find('.treelistInner').width(ui.size.width);
                            }
                            else {
                                self.treeContainer.height(ui.size.height)

                            }

                            self.fileinfoContainer.height(h);
                            self.updatePanelSizes(null, true);
                        }
                        else {
                            self.treeHeight = $(this).height();

                            var h = self.leftSide.height() - ui.size.height;
                            if (!self.fm.opts.treePanel) {
                                self.treeContainer.height(ui.size.height).width(ui.size.width);
                                self.leftSide.find('.treelistInner').width(ui.size.width);
                            }
                            else {
                                self.treeContainer.height(ui.size.height);
                            }

                            self.fileinfoContainer.height(h);
                            self.updatePanelSizes(null, true);

                            $(window).trigger('resizescrollbar');
                        }
                    },
                    stop: function (event, ui) {
                        if (self.fm.opts.coverflow)
                            self.fm.coverflow.reCenterCoverflow();


                        var h = self.leftSide.height() - ui.size.height;


                        if (!self.fm.opts.treePanel) {
                            self.treeContainer.height(ui.size.height).width(ui.size.width);
                            self.leftSide.find('.treelistInner').width(ui.size.width);

                            self.treeContainerInner.css({overflow: ''});
                            self.fileinfoContainer.height(h);
                            self.updatePanelSizes(self.fm.opts.onResizeStop, true);

                            $(window).trigger('resizescrollbar');
                        }
                        else {

                            self.treeContainer.height(ui.size.height);
                            self.treeContainerInner.css({overflow: ''});
                            self.fileinfoContainer.height(h);
                            self.updatePanelSizes(self.fm.opts.onResizeStop, true);

                            $(window).trigger('resizescrollbar');
                        }

                    }
                });


            }
            else {

                self.fm.opts.treePanel.filter(':ui-resizable').resizable('destroy');


                var h = self.fm.opts.treePanel.parent().height() - 110;
                self.fileinfoContainer.height(110);
                self.fm.opts.treePanel.height(h);


                self.fm.opts.treePanel.resizable({
                    handles: 's',
                    minHeight: 200,
                    maxHeight: h,
                    // autoHide: true,
                    start: function () {
                        if (self.fm.opts.onResizeStart) {
                            self.fm.opts.onResizeStart();
                        }
                    },
                    resize: function (event, ui) {
                        var h = self.fm.opts.treePanel.parent().height() - ui.size.height;
                        self.treeContainerInner.css({overflow: ''});

                        $(this).find('div.treelist:first').height(ui.size.height);

                        if (infoContainerMinHeight && h < infoContainerMinHeight) {
                            self.treeHeight = $(this).height();
                            self.treeContainer.height(ui.size.height)
                            self.fileinfoContainer.height(h);
                            self.updatePanelSizes(null, true);
                        }
                        else {
                            self.treeHeight = $(this).height();
                            self.treeContainer.height(ui.size.height);
                            self.fileinfoContainer.height(h);
                            self.updatePanelSizes(null, true);

                            $(window).trigger('resizescrollbar');
                        }
                    },
                    stop: function (event, ui) {

                        var h = self.fm.opts.treePanel.parent().height() - ui.size.height;
                        $(this).find('div.treelist:first').height(ui.size.height);

                        self.treeContainer.height(ui.size.height);
                        self.treeContainerInner.css({overflow: ''});
                        self.fileinfoContainer.height(h);
                        self.updatePanelSizes(self.fm.opts.onResizeStop, true);

                        $(window).trigger('resizescrollbar');

                    }
                });

            }

        };

        this.fixTableSize = function () {
            //if ( this.fm.dircontent.length > 300) return;
            if (self.body) {
                var width = self.body.children('table:first').width();
            }

            if (self.header) {

                if (self.body && self.body.find('.pane:visible').length > 0) {
                    width -= self.body.find('.pane:visible').width();
                }

                $('table', self.header).width(width);
            }

            if (self.header) {
                $('table tr:first td', self.body).each(function (i) {
                    $('th:eq(' + i + ')', self.header).width($(this).width());
                    // $(this).width($(this).innerWidth());
                });
            }
        };

        this._traverse = function (tree, phash, level) {
            self.fm.treeSingleDimension[tree.hash] = tree;

            var i, hash, c, html = '<div class="subtree">';
            for (i = 0; i < tree.length; i++) {
                if (!tree[i].name || !tree[i].hash) {
                    continue;
                }
                c = '';
                if (!tree[i].read && !tree[i].write) {
                    c = 'noaccess';
                } else if (!tree[i].read) {
                    c = 'dropbox';
                } else if (!tree[i].write) {
                    c = 'readonly';
                }

                // add parent hash
                tree[i].phash = phash;

                var sub = tree[i].subdirs;
                delete tree[i].subdirs;

                self.fm.treeSingleDimension[tree[i].hash] = tree[i];

                var levelPlace = '';
                if (level) {
                    levelPlace = ' style="margin-left:' + (self.fm.opts.deepPlaceholderSize * level) + 'px"';
                }

                html += '<div class="item"><a' + levelPlace + ' href="#"' + (c ? ' class="' + c + '"' : '') + ' hash="' + tree[i].hash + '"><span' + (sub.length ? ' class="collapsed"' : '') + '/><div></div><span class="perms"></span>' + tree[i].name + '</a>';

                if (sub.length) {
                    html += this._traverse(sub, tree[i].hash, (level + 1));
                }

                html += '</div>';
            }

            return html + '</div>';
        };

    }

})(jQuery, window);