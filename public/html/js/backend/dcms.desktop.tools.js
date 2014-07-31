/******************************************
 * UnitConverter: Deal with em/px/% conversions
 ******************************************/
(function() {

    /**
     * Create a new converter instance. heightOrElem is used to establish the base height of a line of text, which allows
     * us to convert % or em into pixels and vice versa.
     *
     * @param {number,HTMLElement} heightOrElm the element we'll be converting props for
     * @param {string,number} [amt]
     * @param {string} [units]
     */
    var Converter = $.UnitConverter = function(heightOrElm, amt, units) {
        this.pxHeight = typeof(heightOrElm) === 'number' ? heightOrElm : _pxHeight($(heightOrElm));
        if (typeof(amt) === 'undefined') {
            this.load(0);
        }
        else {
            this.load(amt, units);
        }
    };

    /**
     * Create a copy of this Converter instance
     * @param {jQuery} [$e] an element to use as the base, if not provided, uses existing base
     * @return {Converter}
     */
    Converter.prototype.clone = function($e) {
        return new Converter($e || this.pxHeight, this.amt, this.units);
    };

    /**
     * Replace the current instance's measurement with a new one. Use the same base element.
     * @param {int|string} amt if this is an integer, units must be provided, otherwise units is ignored
     * @param {string} [units] px, em, or %
     */
    Converter.prototype.load = function(amt, units) {
        var t = typeof(amt);
        if (t === 'string') {
            if (amt.match(/[^0-9.-]/)) {
                units = _extractType(amt);
                amt = _extractVal(amt);
            }
            else if (amt === '') {
                amt = 0;
            }
            else {
                amt = parseFloat(amt);
            }
        }
        else if (t !== 'number') {
            throw new Error('not a valid number', amt);
        }
        this.amt = amt;
        this.units = units ? units : 'px';
        return this;
    };

    /**
     * Add any measurment (e.g. "50px" or "45%") to the current amount
     * @param {Converter|string} b
     * @return {Converter}
     */
    Converter.prototype.add = function(b) {
        if (!(b instanceof Converter)) {
            b = this.clone().load(b);
        }
        this.amt += b.convert(this.units);
        return this;
    };

    /**
     * @param {string} newType one of: px, %, or em
     * @return {int} the measurement
     */
    Converter.prototype.convert = function(newType) {
        return _convVal(this, newType);
    };

    /** @return {int} representing the px equivalent of this measurement */
    Converter.prototype.px = function() {
        return this.convert('px');
    };

    /**
     * Return a string representation of the current measurement (e.g. "20%" or "5px")
     * @return {string}
     */
    Converter.prototype.toString = function() {
        return '' + this.amt + this.units;
    };

    /**
     * Static method to convert anything into pixels quickly
     * @param $e
     * @param amt
     * @return {int}
     * @static
     */
    Converter.px = function($e, amt) {
        return (new Converter($e, amt)).convert('px');
    };


    function _pxHeight($e) {
        var h, $d = $('<div style="display: none; font-size: 1em; margin: 0; padding:0; height: auto; line-height: 1; border:0;">&nbsp;</div>').appendTo($e);
        h = $d.height();
        $d.remove();
        return h;
    }

    function _extractVal(v) {
        return parseFloat(v.replace(/[^0-9.-]/, ''));
    }

    function _extractType(v) {
        return v.replace(/.*(em|px|%)$/, '$1');
    }

    function _convVal(a, newUnits) {
        var amt = a.amt, px = a.pxHeight;
        if (amt === 0) {
            return 0;
        }
        switch (newUnits) {
            case 'px':
                switch (a.units) {
                    case 'px':
                        return amt;
                    case 'em':
                        return _emToPx(px, amt);
                    case '%':
                        return _percentToPx(px, amt);
                    default:
                        throw Error('I don\'t know what type ' + a.units + ' is');
                }
            case 'em':
                switch (a.units) {
                    case 'px':
                        return _pxToEm(px, amt);
                    case 'em':
                        return amt;
                    case '%':
                        return _percentToEm(px, amt);
                    default:
                        throw Error('I don\'t know what type ' + a.units + ' is');
                }
            case '%':
                switch (a.units) {
                    case 'px':
                        return _pxToPercent(px, amt);
                    case 'em':
                        return _emToPercent(px, amt);
                    case '%':
                        return amt;
                    default:
                        throw Error('I don\'t know what type ' + a.units + ' is');
                }
            default:
                throw Error('I don\'t know what type ' + a.units + ' is');
        }
    }

    function _pxToEm(h, px) {
        if (px > 0) {
            return _round(px / h, 3);
        }
        return 0;
    }

    function _emToPx(h, em) {
        if (em > 0) {
            return _round(em * h, 3);
        }
        return 0;
    }

    function _percentToPx(h, perc) {
        return _round(h * perc / 100, 3);
    }

    function _percentToEm(ph, perc) {
        return _pxToEm(ph, ph * perc / 100);
    }

    function _pxToPercent(h, px) {
        return _round(px / h * 100);
    }

    function _emToPercent(h, em) {
        return _pxToPercent(h, _emToPx(h, em));
    }

    function _round(number, decimals) {
        if (!decimals) {
            decimals = 0;
        }
        return Math.round(number * Math.pow(10, decimals)) / Math.pow(10, decimals);
    }
})();




Desktop.Tools = {
    bootTimeout: 1200,
    // tooltip event cache
    toolTip: {},
    rebuildTooltips: function()
    {
        var tips = $('body').find('.infoicon');
        var tipIndex = 0;
        var self = this;

        $('body').unbind('click.tooltiphide');


        if (!$('#tip').length)
        {
            var Balloon = Template
                    .setTemplate(Desktop.Templates.FormNotification)
                    .process(
                    {
                        id: 'tip',
                        message: '',
                        title: ''
                    });

            Template.reset();
            $(Balloon).appendTo($('body'));
            $('#tip').hide();
        }

        tips.each(function()
        {
            var alt = $(this).attr('alt');
            $(this).attr('id', 'tip' + tipIndex + '_' + $(this).attr('alt').replace('|', '_'));
            $(this).unbind('click.tooltip').bind('click.tooltip', function(e)
            {
                $('#tip').hide();



                self.toolTip.alt = alt;
                self.toolTip.obj = $(this);

                self.showTip();

                var iconPos = $(this).offset();
                var leftpos = iconPos.left;

                if (leftpos < 80)
                {
                    leftpos = iconPos.left;
                }
                $('#tip').css(
                        {
                            zIndex: 99999,
                            left: (leftpos) + 'px',
                            top: (iconPos.top + 23) + 'px',
                            'position': 'absolute',
                            width: 300
                        }).fadeIn(200, function()
                {
                    $(this).show();
                });
            });
            tipIndex++;
        });



        if (tipIndex)
        {
            $('body').unbind('click.tooltiphide').bind('click.tooltiphide', function(ev)
            {
                if ($('#tip:visible').length && !$(ev.target).hasClass('infoicon') && $(ev.target).attr('id') != 'tip')
                {
                    $('#tip').fadeOut(300);
                }
                //
            });
        }
    },
    showTip: function()
    {
        if (this.toolTip.alt == undefined || this.toolTip.alt == '')
        {
            return;
        }

        var isAlt = this.toolTip.alt;
        isAlt = isAlt.replace('|', '_');

        $('.x-panel-body', $('#tip')).empty();
        $('.x-panel-header-text', $('#tip')).empty();

        this.toolTip.obj.attr('src', Config.get('loadingImgSmall'));

        var post = 'adm=tooltip&ajax=1&tip=' + Utf8.encode(this.toolTip.alt);
        var url = 'admin.php?adm=tooltip&ajax=1&tip=' + Utf8.encode(this.toolTip.alt);
        var self = this;

        $.get(url, {}, function(data)
        {

            $('.x-panel-header-text', $('#tip')).append(data.title);
            $('.x-panel-body', $('#tip')).append(data.content);



            self.toolTip.obj.attr('src', Config.get('backendImagePath') + 'info.png')


        }, 'json');
    },
    extractCssImages: function() {
        var loaded = 0, allImgs = [];//new array for all the image urls  
        var k = 0; //iterator for adding images
        var sheets = document.styleSheets;//array of stylesheets

        for (var i = 0; i < sheets.length; i++) {//loop through each stylesheet
            var cssPile = '';//create large string of all css rules in sheet
            var csshref = (sheets[i].href) ? sheets[i].href : 'window.location.href';
            var baseURLarr = csshref.split('/');//split href at / to make array
            baseURLarr.pop();//remove file path from baseURL array
            var baseURL = baseURLarr.join('/');//create base url for the images in this sheet (css file's dir)
            if (baseURL != "")
                baseURL += '/'; //tack on a / if needed
            if (document.styleSheets[i].cssRules) {//w3
                var thisSheetRules = document.styleSheets[i].cssRules; //w3
                for (var j = 0; j < thisSheetRules.length; j++) {
                    cssPile += thisSheetRules[j].cssText;
                }
            }
            else {
                cssPile += document.styleSheets[i].cssText;
            }

            //parse cssPile for image urls and load them into the DOM
            var imgUrls = cssPile.match(/[^\(]+\.(gif|jpg|jpeg|png)/g);//reg ex to get a string of between a "(" and a ".filename"
            if (imgUrls != null && imgUrls.length > 0 && imgUrls != '') {//loop array
                var arr = $.makeArray(imgUrls);//create array from regex obj	 
                $(arr).each(function() {

                    allImgs[k] = new Image(); //new img obj
                    allImgs[k].src = (this[0] == '/' || this.match('http://')) ? this : baseURL + this;	//set src either absolute or rel to css dir

                    k++;
                });
            }
        }//loop


        return allImgs;
    },
    preLoadCSSImages: function(callback)
    {


        // Desktop.Tools.preloadImages({}, callback);


        var loaded = 0, loadedImgs = Desktop.Tools.extractCssImages(), total = loadedImgs.length;
        //console.log([loadedImgs]);
        for (var i = 0; i < total; i++) {
            var src = loadedImgs[i].src;
            
            loadedImgs[i].onload = function() {
                loaded++;
               // console.log('loaded Image: ' + src);

                if (total === loaded && Tools.isFunction(callback))
                {
                    $('body .preloaded').remove();
                    callback();
                }

            };

            loadedImgs[i].onerror = function()
            {
                loaded++;
            //    console.log('could not load Image: ' + src);

                if (total === loaded && Tools.isFunction(callback))
                {
                    $('body .preloaded').remove();
                    callback();
                }
            };


            $('body').append('<img width="0" height="0" class="preloaded" src=\"' + src + '\" />');
        }
        
        if (total == 0 && Tools.isFunction(callback))
        {
         //   console.log('Skip Image loader');
            callback();
        }


        /**
         var self = this, pic = [], i, imageList = [], loaded = 0, total, regex = /url\((?:"|')?(?!data:)([^)"']+)(?:"|')?\)/i, spl;
         var cssSheets = document.styleSheets, path, myRules = '', Rule, match, txt, img, sheetIdx, ruleIdx;
         
         for (sheetIdx = 0; sheetIdx < cssSheets.length; sheetIdx++)
         {
         var sheet = cssSheets[sheetIdx];
         
         if (typeof sheet.href == 'string' && sheet.href.length > 0) {
         spl = sheet.href.split('/');
         spl.pop();
         path = spl.join('/') + '/';
         } else {
         path = './';
         }
         
         
         
         try {
         
         
         if (sheet.cssRules) {
         myRules = sheet.cssRules;
         } else if (sheet.rules) {
         myRules = sheet.rules;
         }
         }
         catch (e)
         {
         console.error(e);
         }
         
         if (myRules.length > 0)
         {
         for (ruleIdx = 0; ruleIdx < myRules.length; ruleIdx++)
         {
         Rule = myRules[ruleIdx];
         txt = Rule.cssText ? Rule.cssText : Rule.style.cssText;
         txt = $.trim(txt);
         
         if ('@' === txt.substr(0, 1)) {
         continue;
         }
         
         match = regex.exec(txt);
         
         if (match != null) {
         img = match[1];
         if (img.substring(0, 4) == 'http') {
         imageList[imageList.length] = img;
         } else if (match[1].substring(1, 2) == '/') {
         var p2 = path.split('/');
         p2.pop();
         p2.pop();
         p2x = p2.join("/");
         imageList[imageList.length] = p2x + img;
         } else {
         imageList[imageList.length] = path + img;
         }
         }
         }
         }
         }
         
         total = imageList.length; // used later
         if (total > 0)
         {
         console.log(total + ' Images found');
         
         for (i = 0; i < total; i++)
         {
         var imgUrl = imageList[i];
         console.log('load Image ' + imgUrl);
         
         
         setTimeout(function() {
         pic[i] = new Image();
         pic[i].onload = function() {
         loaded++; // should never hit a race condition due to JS's non-threaded nature
         if (loaded == total) {
         if ($.isFunction(callback)) {
         callback();
         }
         }
         };
         pic[i].src = imgUrl;
         
         pic[i].onerror = function()
         {
         var self = this;
         
         setTimeout(function() {
         loaded++; // should never hit a race condition due to JS's non-threaded nature
         
         // console.log('Could not load the Image: ' + imgUrl);
         
         if (loaded == total) {
         if ($.isFunction(callback)) {
         callback();
         }
         }
         }, 1);
         };
         
         }, this.bootTimeout);
         }
         
         }
         else if ($.isFunction(callback)) {
         //nothing found, but we have a callback.. so run this now
         //thanks to Evgeni Nobokov
         console.log('No Images found');
         callback();
         }
         
         */
    },
    preloadImages: function(settings, callback)
    {
        settings = $.extend({
            statusTextEl: null,
            statusBarEl: null,
            errorDelay: 999, // handles 404-Errors in IE
            simultaneousCacheLoading: 2
        }, settings);

        var allImgs = [],
                loaded = 0,
                imgUrls = [],
                thisSheetRules,
                errorTimer, errorCount = 0;

        function onImgComplete() {
            clearTimeout(errorTimer);


            if (imgUrls && imgUrls.length && imgUrls[loaded]) {
                loaded++;



                if (settings.statusTextEl) {
                    var nowloading = (imgUrls[loaded]) ?
                            'Now Loading: <span>' + imgUrls[loaded].split('/')[imgUrls[loaded].split('/').length - 1] :
                            'Loading complete'; // wrong status-text bug fixed
                    jQuery(settings.statusTextEl).html('<span class="numLoaded">' + loaded + '</span> of <span class="numTotal">' + imgUrls.length + '</span> loaded (<span class="percentLoaded">' + (loaded / imgUrls.length * 100).toFixed(0) + '%</span>) <span class="currentImg">' + nowloading + '</span></span>');
                }


                if (settings.statusBarEl) {
                    var barWidth = jQuery(settings.statusBarEl).width();
                    jQuery(settings.statusBarEl).css('background-position', -(barWidth - (barWidth * loaded / imgUrls.length).toFixed(0)) + 'px 50%');
                }



                loadImgs();
            }
        }



        function loadImgs() {
            //only load 1 image at the same time / most browsers can only handle 2 http requests, 1 should remain for user-interaction (Ajax, other images, normal page requests...)
            // otherwise set simultaneousCacheLoading to a higher number for simultaneous downloads
            if (imgUrls && imgUrls.length && imgUrls[loaded]) {
                var img = new Image(); //new img obj
                img.src = imgUrls[loaded];	//set src either absolute or rel to css dir

                // console.log('Load image: '+ img.src);


                if (errorCount > 3)
                {
                    //  errorCount = 0;
                    //  img.complete = true;
                }


                if (!img.complete) {
                    errorCount++;
                    // jQuery(img).bind('error load onreadystatechange', onImgComplete);
                    onImgComplete();
                } else {
                    onImgComplete();
                }

                errorTimer = setTimeout(onImgComplete, settings.errorDelay); // handles 404-Errors in IE
            }


            if (imgUrls && imgUrls.length && imgUrls[loaded] && loaded === imgUrls.length && typeof callback === 'function')
            {
                return callback();
            }
        }


        function parseCSS(sheets, urls)
        {
            var w3cImport = false,
                    imported = [],
                    importedSrc = [],
                    baseURL;
            var sheetIndex = sheets.length;

            while (sheetIndex--) {//loop through each stylesheet

                var cssPile = '';//create large string of all css rules in sheet

                if (urls && urls[sheetIndex]) {
                    baseURL = urls[sheetIndex];
                } else {
                    var csshref = (sheets[sheetIndex].href) ? sheets[sheetIndex].href : 'window.location.href';
                    var baseURLarr = csshref.split('/');//split href at / to make array
                    baseURLarr.pop();//remove file path from baseURL array
                    baseURL = baseURLarr.join('/');//create base url for the images in this sheet (css file's dir)
                    if (baseURL) {
                        baseURL += '/'; //tack on a / if needed
                    }
                }


                if (sheets[sheetIndex].cssRules || sheets[sheetIndex].rules) {
                    thisSheetRules = (sheets[sheetIndex].cssRules) ? //->>> http://www.quirksmode.org/dom/w3c_css.html
                            sheets[sheetIndex].cssRules : //w3
                            sheets[sheetIndex].rules; //ie 
                    var ruleIndex = thisSheetRules.length;
                    while (ruleIndex--) {
                        if (thisSheetRules[ruleIndex].style && thisSheetRules[ruleIndex].style.cssText) {
                            var text = thisSheetRules[ruleIndex].style.cssText;
                            if (text.toLowerCase().indexOf('url') != -1) { // only add rules to the string if you can assume, to find an image, speed improvement
                                cssPile += text; // thisSheetRules[ruleIndex].style.cssText instead of thisSheetRules[ruleIndex].cssText is a huge speed improvement
                            }
                        } else if (thisSheetRules[ruleIndex].styleSheet) {
                            imported.push(thisSheetRules[ruleIndex].styleSheet);
                            w3cImport = true;
                        }

                    }
                }


                //parse cssPile for image urls
                var tmpImage = cssPile.match(/[^\(]+\.(gif|jpg|jpeg|png)/g);//reg ex to get a string of between a "(" and a ".filename" / '"' for opera-bugfix
                if (tmpImage) {
                    var i = tmpImage.length;
                    while (i--) { // handle baseUrl here for multiple stylesheets in different folders bug

                        if (tmpImage[i].charAt(0) == '"' || tmpImage[i].charAt(0) == '\'')
                        {
                            tmpImage[i] = tmpImage[i].substr(1);
                        }

                        var imgSrc = (tmpImage[i].charAt(0) == '/' || tmpImage[i].match('://')) ? // protocol-bug fixed
                                tmpImage[i] :
                                baseURL + tmpImage[i];

                        if (jQuery.inArray(imgSrc, imgUrls) == -1) {
                            imgUrls.push(imgSrc);
                        }
                    }
                }

                if (!w3cImport && sheets[sheetIndex].imports && sheets[sheetIndex].imports.length) {
                    for (var iImport = 0, importLen = sheets[sheetIndex].imports.length; iImport < importLen; iImport++) {
                        var iHref = sheets[sheetIndex].imports[iImport].href;
                        iHref = iHref.split('/');
                        iHref.pop();
                        iHref = iHref.join('/');
                        if (iHref) {
                            iHref += '/'; //tack on a / if needed
                        }
                        var iSrc = (iHref.charAt(0) == '/' || iHref.match('://')) ? // protocol-bug fixed
                                iHref :
                                baseURL + iHref;

                        importedSrc.push(iSrc);
                        imported.push(sheets[sheetIndex].imports[iImport]);
                    }


                }
            }//loop

            if (imported.length) {
                parseCSS(imported, importedSrc);
                return false;
            }

            var downloads = settings.simultaneousCacheLoading;

            while (downloads--) {
                setTimeout(loadImgs, downloads);
            }

        }

        parseCSS(document.styleSheets);

        return imgUrls;
    },
    // mask the desktop
    maskDesktop: function(message)
    {
        if (!$('#fullscreenContainer').find('#desktop-mask').lenght)
        {
            $('#fullscreenContainer').append($(Desktop.Templates.DesktopMask));


            $('#desktop-mask').css({
                zIndex: 999998,
                width: '100%'
            }).hide();


            $('#desktop-mask-message').css({
                zIndex: 999999
            }).hide();
        }


        $('#desktop-mask').css({
            top: $('#Taskbar').outerHeight(),
            height: $(window).height() - $('#Taskbar').outerHeight()
        }).show();


        if (typeof message == 'string') {
            $('#desktop-mask-message').html(message).show();
        }

    },
    // unmask desktop
    unmaskDesktop: function()
    {
        $('#desktop-mask').hide();
        $('#desktop-mask-message').hide();
    },
    maskWindow: function(win, message)
    {

        if (!$('.window-mask', $(win)).lenght)
        {
            $(win).find('.table-mm-content').append($(Desktop.Templates.WindowMask));


            $('.window-mask', $(win)).css({
                zIndex: 999998,
                width: '100%',
                top: 0
            }).hide();


            $('.window-mask-message', $(win)).css({
                zIndex: 999999
            }).hide();
        }
        $('.window-mask', $(win)).show();
        if (typeof message == 'string') {
            $('.window-mask-message', $(win)).html(message).show();
        }



    },
    unmaskWindow: function()
    {
        $('.window-mask').remove();
        $('.window-mask-message').remove();
    }
};