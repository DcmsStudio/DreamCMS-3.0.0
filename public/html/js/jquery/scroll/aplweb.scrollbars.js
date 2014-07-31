(function($, window, document) {
    var DOMSCROLL, DOWN, DRAG, MOUSEDOWN, MOUSEMOVE, MOUSEUP, MOUSEWHEEL, NanoScroll, PANEDOWN, RESIZE, SCROLL, SCROLLBAR, UP, WHEEL, getScrollbarWidth;
    SCROLLBAR = 'scrollbar';
    SCROLL = 'scroll';
    MOUSEDOWN = 'mousedown';
    MOUSEMOVE = 'mousemove';
    MOUSEWHEEL = 'mousewheel';
    MOUSEUP = 'mouseup';
    RESIZE = 'resize';
    DRAG = 'drag';
    UP = 'up';
    PANEDOWN = 'panedown';
    DOMSCROLL = 'DOMMouseScroll';
    DOWN = 'down';
    WHEEL = 'wheel';
    getScrollbarWidth = function() {
        var noscrollWidth, outer, yesscrollWidth;
        outer = document.createElement('div');
        outer.style.position = 'absolute';
        outer.style.width = '100px';
        outer.style.height = '100px';
        outer.style.overflow = 'scroll';
        document.body.appendChild(outer);
        noscrollWidth = outer.offsetWidth;
        yesscrollWidth = outer.scrollWidth;
        document.body.removeChild(outer);
        return noscrollWidth - yesscrollWidth;
    };
    NanoScroll = (function() {

        function NanoScroll(el) {
            this.el = el;
            this.generate();
            this.createEvents();
            this.addEvents();
            this.reset();
        }

        NanoScroll.prototype.createEvents = function() {
            var _this = this;
            this.events = {
                down: function(e) {
                    _this.isDrag = true;
                    _this.offsetY = e.clientY - _this.slider.offset().top;
                    _this.pane.addClass('active');
                    $(document).bind(MOUSEMOVE, _this.events[DRAG]);
                    $(document).bind(MOUSEUP, _this.events[UP]);
                    return false;
                },
                drag: function(e) {
                    _this.sliderY = e.clientY - _this.el.offset().top - _this.offsetY;
                    _this.scroll();
                    return false;
                },
                up: function(e) {
                    _this.isDrag = false;
                    _this.pane.removeClass('active');
                    $(document).unbind(MOUSEMOVE, _this.events[DRAG]);
                    $(document).unbind(MOUSEUP, _this.events[UP]);
                    return false;
                },
                resize: function(e) {
                    _this.reset();
                },
                panedown: function(e) {
                    _this.sliderY = e.clientY - _this.el.offset().top - _this.sliderH * 0.5;
                    _this.scroll();
                    _this.events.down(e);
                },
                scroll: function(e) {
                    var content, top;
                    if (_this.isDrag === true) return;
                    content = _this.content[0];
                    top = content.scrollTop / (content.scrollHeight - content.clientHeight) * (_this.paneH - _this.sliderH);
                    _this.slider.css({
                        top: top + 'px'
                    });
                },
                wheel: function(e) {
                    _this.sliderY += -e.wheelDeltaY || -e.delta;
                    _this.scroll();
                    return false;
                }
            };
        };

        NanoScroll.prototype.addEvents = function() {
            var events, pane;
            events = this.events;
            pane = this.pane;
            $(window).bind(RESIZE, events[RESIZE]);
            this.slider.bind(MOUSEDOWN, events[DOWN]);
            pane.bind(MOUSEDOWN, events[PANEDOWN]);
            this.content.bind(SCROLL, events[SCROLL]);
            if (window.addEventListener) {
                pane = pane[0];
                pane.addEventListener(MOUSEWHEEL, events[WHEEL], false);
                pane.addEventListener(DOMSCROLL, events[WHEEL], false);
            }
        };

        NanoScroll.prototype.removeEvents = function() {
            var events, pane;
            events = this.events;
            pane = this.pane;
            $(window).unbind(RESIZE, events[RESIZE]);
            this.slider.unbind(MOUSEDOWN, events[DOWN]);
            pane.unbind(MOUSEDOWN, events[PANEDOWN]);
            this.content.unbind(SCROLL, events[SCROLL]);
            if (window.addEventListener) {
                pane = pane[0];
                pane.removeEventListener(MOUSEWHEEL, events[WHEEL], false);
                pane.removeEventListener(DOMSCROLL, events[WHEEL], false);
            }
        };

        NanoScroll.prototype.generate = function() {
            this.el.append('<div class="pane"><div class="slider"></div></div>');
            this.content = $(this.el.children('.content')[0]);
            this.slider = this.el.find('.slider');
            this.pane = this.el.find('.pane');
            this.scrollW = getScrollbarWidth();
            if (this.scrollbarWidth === 0) this.scrollW = 0;
            this.content.css({
                right: -this.scrollW + 'px'
            });
        };

        NanoScroll.prototype.reset = function() {
            var content;
            if (this.el.find('.pane').length === 0) {
                this.generate();
                this.stop();
            }
            if (this.isDead === true) {
                this.isDead = false;
                this.pane.show();
                this.addEvents();
            }
            content = this.content[0];
            if (content != undefined)
            {
                this.contentH = content.scrollHeight + this.scrollW;
                this.paneH = this.pane.outerHeight();
                this.sliderH = this.paneH / this.contentH * this.paneH;
                this.sliderH = Math.round(this.sliderH);
                this.scrollH = this.paneH - this.sliderH;
                this.slider.height(this.sliderH);
                this.diffH = content.scrollHeight - content.clientHeight;
                this.pane.show();
                if (this.paneH >= this.content[0].scrollHeight) this.pane.hide();
            }
        };

        NanoScroll.prototype.scroll = function() {
            var scrollValue;
            this.sliderY = Math.max(0, this.sliderY);
            this.sliderY = Math.min(this.scrollH, this.sliderY);
            scrollValue = this.paneH - this.contentH + this.scrollW;
            scrollValue = scrollValue * this.sliderY / this.scrollH;
            this.content.scrollTop(-scrollValue);
            return this.slider.css({
                top: this.sliderY
            });
        };

        NanoScroll.prototype.scrollBottom = function(offsetY) {
            var diffH, scrollTop;
            diffH = this.diffH;
            scrollTop = this.content[0].scrollTop;
            this.reset();
            if (scrollTop < diffH && scrollTop !== 0) return;
            this.content.scrollTop(this.contentH - this.content.height() - offsetY);
        };

        NanoScroll.prototype.scrollTop = function(offsetY) {
            this.reset();
            this.content.scrollTop(offsetY + 0);
        };

        NanoScroll.prototype.stop = function() {
            this.isDead = true;
            this.removeEvents();
            this.pane.hide();
        };

        return NanoScroll;

    })();
  
  
  
    $.fn.scrollbars00000 = function(options) {
        options || (options = {});
        if (!($.browser.msie && parseInt($.browser.version, 10) < 8)) {
            this.each(function() {
                var me, scrollbar;
                me = $(this);
                scrollbar = me.data(SCROLLBAR);
                if (scrollbar === void 0 || !scrollbar) {
                    scrollbar = new NanoScroll(me);
                    me.data(SCROLLBAR, scrollbar);
                }
                if (options.scrollBottom) {
                    return scrollbar.scrollBottom(options.scrollBottom);
                }
                if (options.scrollTop) return scrollbar.scrollTop(options.scrollTop);
                if (options.scroll === 'bottom') return scrollbar.scrollBottom(0);
                if (options.scroll === 'top') return scrollbar.scrollTop(0);
                if (options.stop) return scrollbar.stop();
                return scrollbar.reset();
            });
        }
    };
})(jQuery, window, document);

jQuery.fn.extend({
    scrollbars: function(options) {

        var defaults = {
            wheelStep : 10,
            width : '100%',
            height : '100%',
            size : '7px',
            borderRadius: '7px',
            color: '#000',
            position : 'right',
            distance : '2px',
            start : 'top',
            opacity : .5,
            alwaysVisible : false,
            railVisible : false,
            railColor : '#555',
            railOpacity : '0.2',
            railClass : 'GuiScrollRail',
            barClass : 'GuiScrollBar',
            wrapperClass : 'GuiScrollDiv',
            allowPageScroll: false,
            scroll: 0,
            paddingBottom: 2,
            isDropDown: false,
            isDisabled: false
        };

        var o = $.extend( defaults , options );
        var ops =  o;

        // do it for every element that matches selector
        this.each(function(){
            
            var isOverPanel = false, isOverBar = false, isDragg = false, queueHide, barHeight, percentScroll,
            divS = '<div></div>',
            minBarHeight = 30,
            releaseScroll = true,
            wheelStep = parseInt(o.wheelStep),
            cwidth = o.width,
            cheight = o.height,
            size = o.size,
            borderRadius = o.borderRadius,
            color = o.color,
            position = o.position,
            distance = o.distance,
            start = o.start,
            opacity = o.opacity,
            alwaysVisible = o.alwaysVisible,
            railVisible = o.railVisible,
            railColor = o.railColor,
            railOpacity = o.railOpacity,
            allowPageScroll = o.allowPageScroll,
            scroll = o.scroll,
            isDropDown = o.isDropDown,
            isDisabled = o.isDisabled, forceHide = false;
            
            // used in event handlers and for better minification
            var me = $(this);
            me.isOverPanel = false;
            
            
            if (!isDropDown) {
                cheight = o.height = me.outerHeight() ;
            }

            //cwidth = o.width = me.width();

            //ensure we are not binding it again
            if (me.parent().hasClass(o.wrapperClass) )
            {
                

                // cwidth = o.width = me.width();
                if (isDisabled) {
                    scroll = 0;
                    isOverPanel = false;
                    me.isOverPanel = false;
                    me.scroll = 0;
                    me.isDisabled = true;
                }
                
                
                
                //find bar and rail
                wrapper = me.parent();                    
                bar = me.parent().find('.'+ o.barClass);                                
                rail = me.parent().find('.'+ o.railClass);                
                setWrapperHeight();
                
                
                
                if (isDisabled) {
                    scroll = 0;
                    isOverPanel = false;
                    return;
                }
                
                //check if we should scroll existing instance
                if (scroll)
                {
                    bar.show();
                    rail.show();
                    getBarHeight();
                    
                    //scroll by given amount of pixels
                    scrollContent( 0 + parseInt(scroll), true, false);
                }
                else
                {
                    bar.hide();
                    rail.hide();
                }

                return;
            }

            // wrap content
            var wrapper = $(divS)
            .addClass( o.wrapperClass )
            .css({
                position: 'relative',
                overflow: 'hidden',
                width: cwidth,
                height: cheight
            });
            
            if ( alwaysVisible)
            {
                wrapper.addClass( 'visible' );
            }
            
            

            // update style for the div
            me.css({
                overflow: 'hidden',
                // width: cwidth,
                height: cheight
            });

            // create scrollbar rail
            var rail  = $(divS)
            .addClass( o.railClass )
            .css({
                width: size,
                height: '100%',
                position: 'absolute',
                top: 0,
                display: (alwaysVisible && railVisible) ? 'block' : 'none',
                'border-radius': borderRadius,
                BorderRadius: borderRadius,
                MozBorderRadius: borderRadius,
                WebkitBorderRadius: borderRadius,
                background: railColor,
                opacity: railOpacity,
                zIndex: 90
            });

            // create scrollbar
            var bar = $(divS)
            .addClass( o.barClass )
            .css({
                background: color,
                width: size,
                position: 'absolute',
                top: 0,
                opacity: opacity,
                display: alwaysVisible ? 'block' : 'none',
                'border-radius' : borderRadius,
                BorderRadius: borderRadius,
                MozBorderRadius: borderRadius,
                WebkitBorderRadius: borderRadius,
                zIndex: 99
            });

            // set position
            var posCss = (position == 'right') ? {
                right: distance
            } : {
                left: distance
            };
            rail.css(posCss);
            bar.css(posCss);

            // wrap it
            me.wrap(wrapper);

            // append to parent div
            me.parent().append(bar);
            me.parent().append(rail);
            /*
            // make it draggable
            bar.draggable({ 
                axis: 'y', 
                containment: 'parent',
                start: function() {
                    isDragg = true;
                },
                stop: function() {
                    isDragg = false;
                    hideBar();
                },
                drag: function(e, ui) 
                { 
                    if ($(this).hasClass('mwindow'))
                        {
                            return;
                        }
                    
                    // scroll content
                    scrollContent(0, $(this).position().top, false);
                }
            });
*/
            // on rail over
            rail.hover(function(){
                showBar();
            }, function(){
                hideBar();
            });

            // on bar over
            bar.hover(function(){
                me.isOverBar = true;
            }, function(){
                me.isOverBar = false;
            });

            // show on parent mouseover
            /*
            me.hover(function(){
                if ( isOverPanel ){
                    return;
                }
                isOverPanel = true;
                showBar();
            // hideBar();
            }, function(){
                
                if ( !isOverPanel ){
                    return;
                }
                
                isOverPanel = false;
                hideBar();
            });
             */

            
            me.bind('mouseenter', function(){
                me.isOverPanel = true;
                disableOtherBars();
                showBar();
            });
            

            me.bind('mouseleave', function(){
                if (me.isOverPanel)
                {
                    me.isOverPanel = false;
                    hideBar();
                    enableOtherBars();
                }
            });
            
            /*
            me.resize(function() {
               // showBar();
            });*/
            
            
            
            
            /*
            $('textarea,select').bind('mouseenter', function()            
                {             
                    me.isOverPanel = false;
                    
                    disableOtherBars();
                    hideBar();
                    me.forceHide = true;
                });
            

            
            $('textarea,select').bind('mouseleave', function()            
                {  
                    if (me.forceHide)
                    {
                        me.isOverPanel = true;
                        disableOtherBars();
                        showBar();
                        me.forceHide = false;   
                    }
                });
            
*/

            var _onWheel = function(e)
            {
                // use mouse wheel only when mouse is over
                if (!me.isOverPanel || me.forceHide || me.parent().hasClass('stopScroll') ) {
                    return;
                }

                var e = e || window.event;

                var delta = 0;
                if (e.wheelDelta) {
                    delta = -e.wheelDelta/120;
                }
                if (e.detail) {
                    delta = e.detail / 3;
                }

                // scroll content
                scrollContent(delta, true);

                // stop window scroll
                if (e.preventDefault && !releaseScroll) {
                    e.preventDefault();
                }
                if (!releaseScroll) {
                    e.returnValue = false;
                }
            }
            
            function setWrapperHeight()
            {
                wrapper.css({
                    height: cheight 
                });
            }
            
            function disableOtherBars()
            {
                
                if ( me.parent() && me.parent().parents('.'+ o.wrapperClass).length )
                {
                
                    me.parent().parents('.'+ o.wrapperClass).each(function(){
                        $(this).addClass('stopScroll'); 
                    
                    // $('.'+ o.barClass , this).stop(true,true).fadeOut('fast');
                    // $('.'+ o.railClass , this).stop(true,true).fadeOut('fast');  
                    
                    });
                }
            }
            
            function enableOtherBars() {
                if ( me.parent() && me.parent().parents('.'+ o.wrapperClass).length )
                {
                    me.parent().parents('.'+ o.wrapperClass).each(function(){
                        $(this).removeClass('stopScroll');

                    // $('.'+ o.barClass , this).stop(true,true).fadeIn('fast');
                    // $('.'+ o.railClass , this).stop(true,true).fadeIn('fast');  

                    });
                }
            }

            function scrollContent(y, isWheel, isJump)
            {
                var delta = y;

                if (isWheel)
                {
                    // move bar with mouse wheel
                    delta = parseInt(bar.css('top') ) + y * wheelStep / 100 * bar.outerHeight();

                    // move bar, make sure it doesn't go out
                    var maxTop = me.outerHeight() - bar.outerHeight();
                    delta = Math.min(Math.max(delta, 0), maxTop);

                    
                    var bartop = delta;
                    


                    // scroll the scrollbar
                    bar.css({
                        top: bartop + 'px'
                    });
                }

                // calculate actual scroll amount
                percentScroll = parseInt(bar.css('top') ) / (me.outerHeight() - bar.outerHeight());
                delta = percentScroll * (me[0].scrollHeight - me.outerHeight());

                if (isJump)
                {
                    delta = y;
                    var offsetTop = delta / me[0].scrollHeight * me.outerHeight();
                    bar.css({
                        top: offsetTop + 'px'
                    });
                }

                // scroll content
                me.scrollTop(delta);

                // ensure bar is visible
                showBar();

            // trigger hide when scroll is stopped
            // hideBar();
            }

            var attachWheel = function()
            {
                if (window.addEventListener)
                {
                    this.addEventListener('DOMMouseScroll', _onWheel, false );
                    this.addEventListener('mousewheel', _onWheel, false );
                } 
                else
                {
                    document.attachEvent("onmousewheel", _onWheel)
                }
            }

            // attach scroll events
            attachWheel();

            function getBarHeight()
            {
                // calculate scrollbar height and make sure it is not too small
                var outerH = me.outerHeight();
                barHeight = Math.max((outerH / me[0].scrollHeight) * outerH, minBarHeight) ;
                
                if(barHeight == me[0].scrollHeight) {
                    barHeight = 0
                }
                
                if ( barHeight > cheight)
                {
                //barHeight = parseInt(cheight) ;
                }
                
                barHeight = barHeight ;
                
                bar.css({
                    height: barHeight + 'px'
                });
            }

            // set up initial height
            getBarHeight();

            function showBar()
            {
                // recalculate bar height
                getBarHeight();
                clearTimeout(queueHide);

                // release wheel when bar reached top or bottom
                releaseScroll = allowPageScroll && percentScroll == ~~ percentScroll;

                // show only when required
                if(barHeight >= me.outerHeight()) {
                    //allow window scroll
                    releaseScroll = true;
                    bar.stop(true,true).fadeOut('fast');
                    rail.stop(true,true).fadeOut('fast'); 
                    return;
                }
                bar.stop(true,true).fadeIn('fast');
                if (railVisible) {
                    rail.stop(true,true).fadeIn('fast');
                }
            }

            function hideBar()
            {
                // only hide when options allow it
                if (!alwaysVisible)
                {
                    queueHide = setTimeout(function(){
                        if (!isOverBar && !isDragg) 
                        { 
                            bar.stop(true,true).fadeOut('fast');
                            rail.stop(true,true).fadeOut('fast');
                        }
                    }, 100);
                }
                else
                {
                    getBarHeight();
                    
                    if (barHeight >= me.outerHeight() )
                    {
                        bar.stop(true,true).fadeOut('fast');
                        rail.stop(true,true).fadeOut('fast');  
                    }
                }
            }

            // check start position
            if (start == 'bottom') 
            {
                // scroll content to bottom
                bar.css({
                    top: me.outerHeight() - bar.outerHeight()
                });
                scrollContent(0, true);
            }
            else if (typeof start == 'object')
            {
                // scroll content
                scrollContent($(start).position().top, null, true);

                // make sure bar stays hidden
                if (!alwaysVisible) {
                    bar.hide();
                }
            }
        });
      
        // maintain chainability
        return this;
    }
});

jQuery.fn.extend({
    scrollbars: jQuery.fn.scrollbars
});















(function ($)
{

    $.scrollbaroptions = $.scrollbaroptions || {
    };

    $.scrollbaroptions.scrollbar =
    {
        options: {
            axis: 'y',
            // vertical or horizontal scrollbar? ( x || y ).
            wheel: 40,
            //how many pixels must the mouswheel scroll at a time.
            scroll: true,
            //enable or disable the mousewheel;
            size: 'auto',
            //set the size of the scrollbar to auto or a fixed number.
            sizethumb: 'auto' //set the size of the thumb to auto or a fixed number.
        }
    };

    $.fn.scrollbars0000 = function (_options)
    {
        var options = $.extend(
        {
            }, $.scrollbaroptions.scrollbar.options, _options);

        // load Css file
        Loader.loadCss( Config.get('backendImagePath') + '../css/dcms.scrollbar.css');



        return this.each(function ()
        {

            var elem = $(this);
            var Api = elem.data('tsb');

            if (elem.hasClass("isscrollable") && elem.children(':first').hasClass("scrollbar") && Api)
            {
                try {
                    Api.update('relative');
                }
                catch(e)
                {
                    Debug.error('Error in $.fn.scrollbars ' + e.toString() );
                }
            }
            else
            {
                try {
                    elem.addClass("isscrollable");
                    elem.removeData('tsb');
                    elem.data('tsb', new Scrollbar(elem, options));
                }
                catch(e)
                {
                    Debug.error('Error in $.fn.scrollbars (not isscrollable) ' + e.toString() );
                }
            }
        });
    };

    $.fn.getScrollbarPos = function ()
    {

        try {
            var obj = $(this).data('tsb').getScrollpos();
        }
        catch(e)
        {
            Debug.error('Error in $.fn.getScrollbarPos (obj is empty or not exists) ' + e.toString() );
        }


    };
    $.fn.scrollbarUpdate = function (sScroll)
    {


        if ( !$(this).data('tsb') )
        {
            return;
        }

        if (typeof sScroll == 'undefined')
        {
            sScroll = 'relative';
        }


        var wrapperHeight = $(this).height();



        $('.scrolltrack', $(this)).css({
            height: wrapperHeight - 10
        });
        $('.scrollbar', $(this)).css("height", wrapperHeight - 10);
        $('.viewport', $(this)).css("height", wrapperHeight - 10);

        try {
            var obj = $(this).data('tsb').update(sScroll, parseInt(wrapperHeight));
        }
        catch(e)
        {
            Debug.error('Error in $.fn.scrollbarUpdate (obj is empty or not exists) ' + e.toString() );
        }


        $('.scrolltrack', $(this)).css({
            height: wrapperHeight - 10
        });
        $('.scrollbar', $(this)).css("height", wrapperHeight - 10);
        $('.viewport', $(this)).css("height", wrapperHeight - 10);

        return obj;
    };




    function Scrollbar(root, options)
    {
        var oSelf = this;
        var oWrapper = $(root);
        oSelf.options = options;



        // create scrollbar containers
        if (!$(root).find('.viewport:first').length)
        {
            var firstChild = $(root).children('*:first-child');
            var width = $(root).innerWidth();
            var height = $(root).innerHeight();

            //marginLeft = $(root).marginLeft;
            ///marginRight = $(root).marginRight;

            var marginTop = $(root).outerHeight(true) - $(root).outerHeight();
            var marginLeft = $(root).outerWidth(true) - $(root).outerWidth();


            /*
			if ( !firstChild.hasClass("overview"))
			{
				firstChild.addClass("overview").css({width: '100%'});
			}
			overv = firstChild;
             */

            var overv = $('<div>').css(
            {
                // height: height - getMargin()
                }).addClass('overview');


            $(root).children().each(function(){

                if ($(this).text().trim()  ) {
                    $(this).appendTo(overv);
                }
            });




            var vp = $('<div>').css(
            {
                height: height - getMargin()
            }).addClass('viewport');

            vp.append(overv);
            vp.appendTo($(oWrapper));

            $(oWrapper).prepend( $('<div class="scrollbar"><div class="scrolltrack"><div class="scrollthumb"><div class="scrollend"></div></div></div></div>') );


            var oViewport =
            {
                obj: $('.viewport', root),
                canScoll: false
            };
            var oContent =
            {
                obj: $('.overview', root),
                scrollTop: 0,
                scrollLeft: 0,
                iScroll: 0
            };
            var oScrollbar =
            {
                obj: $('.scrollbar', root)
            };
            var oTrack =
            {
                obj: $('.scrolltrack', oScrollbar.obj)
            };
            var oThumb =
            {
                obj: $('.scrollthumb', oScrollbar.obj),
                scrollTop: 0,
                scrollLeft: 0,
                iScroll: 0
            };
            var sAxis = options.axis == 'x',
            sDirection = sAxis ? 'left' : 'top',
            sSize = sAxis ? 'Width' : 'Height';


            var iScroll, iPosition =
            {
                start: 0,
                now: 0
            },
            iMouse =
            {
            };



        }
        else
        {

            var oViewport =
            {
                obj: $('.viewport', root),
                canScoll: false
            };
            var oContent =
            {
                obj: $('.overview', root),
                scrollTop: 0,
                scrollLeft: 0,
                iScroll: 0
            };
            var oScrollbar =
            {
                obj: $('.scrollbar', root)
            };
            var oTrack =
            {
                obj: $('.scrolltrack', oScrollbar.obj)
            };
            var oThumb =
            {
                obj: $('.scrollthumb', oScrollbar.obj),
                scrollTop: 0,
                scrollLeft: 0,
                iScroll: 0
            };
            
            var sAxis = options.axis == 'x',
            sDirection = sAxis ? 'left' : 'top',
            sSize = sAxis ? 'Width' : 'Height';
            
            var iScroll, iPosition =
            {
                start: 0,
                now: 0
            },
            
            iMouse =
            {
            };


        }

        this.update = function (sScroll, trackSize)
        {
            calculateScrollBar(sScroll, trackSize);
            setSize(sScroll);
        }

        this.getScrollpos = function()
        {
            return iScroll;
        }

        this.setScrollpos = function(posTop, posLeft)
        {
            if (typeof posTop != 'undefined' )
            {

            }

            if (typeof posLeft != 'undefined' )
            {

        }
        }






        function initialize()
        {
            oSelf.update();
            setEvents();
            return oSelf;
        }

        function getMargin()
        {
            return 1;// parseInt($(oWrapper).innerHeight(true) - $(oWrapper).innerHeight()) * 1.5;
        }


        function calculateScrollBar(sScroll, trackSize)
        {
            if (trackSize)
            {
                oViewport[options.axis] = oViewport.obj[0]['offset' + sSize];
                oContent[options.axis] = oContent.obj[0]['scroll' + sSize];
            }
            else
            {
                oViewport[options.axis] = oViewport.obj[0]['offset' + sSize];
                oContent[options.axis] = oContent.obj[0]['scroll' + sSize];
            }

            oContent.ratio = (oViewport[options.axis] / oContent[options.axis]);

            if (oContent.ratio >= 1)
            {
                oScrollbar.obj.addClass('scrollbardisable');
            }
            else
            {
                oScrollbar.obj.removeClass('scrollbardisable');
            }


            if (trackSize)
            {
                oTrack[options.axis] = trackSize;
            }
            else
            {
                oTrack[options.axis] = (options.size == 'auto' ? oViewport[options.axis] : options.size);
            }


            //  barHeight = Math.max((oSelf.outerHeight() / oSelf[0].scrollHeight) * oSelf.outerHeight(), minBarHeight) ;

            oThumb[options.axis] = Math.min(oTrack[options.axis], Math.max(0, (options.sizethumb == 'auto' ? (oTrack[options.axis] * oContent.ratio) - 0.2 : options.sizethumb)));
            oScrollbar.ratio = options.sizethumb == 'auto' ? (oContent[options.axis] / oTrack[options.axis] ) : (oContent[options.axis] - oViewport[options.axis]) / (oTrack[options.axis] - oThumb[options.axis]);


            oViewport[options.axis] = oViewport[options.axis];
            oTrack[options.axis] = oTrack[options.axis];
            oContent[options.axis] = oContent[options.axis];

            iScroll = (sScroll == 'relative' && oContent.ratio <= 1) ? Math.min((oContent[options.axis] - oViewport[options.axis]), Math.max(0, iScroll)) : 0;
            iScroll = (sScroll == 'bottom' && oContent.ratio <= 1) ? Math.min(oContent[options.axis] - oViewport[options.axis]) : isNaN(parseInt(sScroll)) ? iScroll : parseInt(sScroll);

        }
        




        function setSize(sScroll)
        {


            if (sScroll == 'relative' && (oThumb.iScroll || oContent.iScroll) )
            {

                iScroll = oContent.iScroll;

                if (sDirection == 'top') {
                    oThumb.obj.css(sDirection, oThumb.scrollTop);
                    oContent.obj.css(sDirection, oContent.scrollTop);
                }
                else
                {
                    oThumb.obj.css(sDirection, oThumb.scrollLeft);
                    oContent.obj.css(sDirection, oContent.scrollLeft);
                }



                iMouse['start'] = oThumb.obj.offset()[sDirection];

                var sCssSize = sSize.toLowerCase();

                    
                oTrack.obj.css(sCssSize, oTrack[options.axis] -10);//
                oThumb.obj.css(sCssSize, oThumb[options.axis] -10);//
                    
                oScrollbar.obj.css(sCssSize, oTrack[options.axis] - 10 ); //
            }
            else
            {
                oThumb.obj.css(sDirection, iScroll / oScrollbar.ratio);
                oContent.obj.css(sDirection, -iScroll);
                iMouse['start'] = oThumb.obj.offset()[sDirection];

                var sCssSize = sSize.toLowerCase();

                    
                    
                oTrack.obj.css(sCssSize, oTrack[options.axis] -10);//
                oThumb.obj.css(sCssSize, oThumb[options.axis] -10);//
                    
                oScrollbar.obj.css(sCssSize, oTrack[options.axis] - 10 ); //
                    
            }
        }




        function doResize(oEvent)
        {
            oSelf.update('relative');
        }


        function hasScrollBar(e)
        {
            return e.get(0).scrollHeight > e.height();
        }

        function disableParentScrolls(obj)
        {
            obj.parents('.isscrollable').each(function ()
            {
                //$(this).data("tsb").oViewport.canScroll = false;
                $(this).removeClass('canScroll');
                $('.scrollthumb',$(this)).fadeOut(300);
            });
        }

        function enableParentScrolls(obj)
        {
            obj.parents('.isscrollable').each(function ()
            {
                //$(this).data("tsb").oViewport.canScroll = true;
                $(this).addClass('canScroll');
                $('.scrollthumb',$(this)).fadeIn(300);
            });
        }

        function setEvents()
        {

            oThumb.obj.unbind('mousedown.dcmsScroller').bind('mousedown.dcmsScroller', start);
            oThumb.obj[0].ontouchstart = function (oEvent)
            {
                oEvent.preventDefault();
                oThumb.obj.unbind('mousedown.dcmsScroller');
                start(oEvent.touches[0]);
                return false;
            };




            oTrack.obj.unbind('mouseup.dcmsScroller').bind('mouseup.dcmsScroller', drag);
            var vo = $('.viewport,.scrollbar', oWrapper).get(0);
            var ot = $('.scrolltrack', oWrapper).get(0);
            var ow = oWrapper.get(0);
            var axis = oSelf.options.axis;
                
            $('*:first-child', oWrapper).unbind('resize.dcmsScroller').bind('resize.dcmsScroller', function (e)
            {
                var self = this;

                $('.viewport,.scrollbar', $(this)).css('height', $(this).height());
                $(self).scrollbarUpdate('relative');
            });

            $(window).unbind('resize.dcmsScroller').bind('resize.dcmsScroller', function (e)
            {

                $('.isscrollable').each(function ()
                {
                    var self = this;

                    $('.viewport,.scrollbar', $(this)).css('height', $(this).height());
                    $(self).scrollbarUpdate('relative');
                });

                /*
                    wrapperHeight = oWrapper.height();


                    ot.style.height = wrapperHeight - getMargin();
                    $('.viewport,.scrollbar', oWrapper).css("height", wrapperHeight - getMargin());

                    Debug.info('Run window event resize.dcmsScroller ');

                    oSelf.update('relative', parseInt(wrapperHeight));

                    ot.style.height = wrapperHeight - getMargin();
                    $('.viewport,.scrollbar', oWrapper).css("height", wrapperHeight - getMargin());
                 */
                return false;
            });

            // add scroll enable if mouse enter
            oWrapper.unbind('mouseenter.dcmsScroller').bind('mouseenter.dcmsScroller', function (e)
            {

                //$('.canScroll').removeClass('canScroll');

                //var objData = $(this).data("tsb");
                //objData.oViewport.canScroll = true;
                disableParentScrolls($(this));

                $(this).addClass('canScroll');
                if (!$(this).hasClass('scrollbardisable')) {
                    $('.scrollthumb',$(this)).fadeIn(300);
                }
                e.preventDefault();
                return false;

            //oContent.obj.addClass('canScroll');
            });
                
                
            /*
                oWrapper.unbind('click.dcmsScroller').bind('click.dcmsScroller', function (e)
                {

                    //$('.canScroll').removeClass('canScroll');

                    //var objData = $(this).data("tsb");
                    //objData.oViewport.canScroll = true;
                    disableParentScrolls($(this));

                    $(this).addClass('canScroll');
                    if (!$(this).hasClass('scrollbardisable')) { $('.scrolltrack',$(this)).fadeIn(300); }

                    //oContent.obj.addClass('canScroll');
                    e.preventDefault();
                    return false;
                });
                
             */


            // remove scroll enable if mouse leave
            oWrapper.unbind('mouseleave.dcmsScroller').bind('mouseleave.dcmsScroller', function (e)
            {
                e.preventDefault();

                //var objData = $(this).data("tsb");
                //objData.oViewport.canScroll = false;
                enableParentScrolls($(this));
                $(this).removeClass('canScroll');
                $('.scrollthumb',$(this)).fadeOut(300);
                    
                return false;
            })

            if (options.scroll )
            {

                if ( this.addEventListener )
                {
                    oWrapper[0].addEventListener('DOMMouseScroll', wheel, false);
                    oWrapper[0].addEventListener('mousewheel', wheel, false);
                }
                else if ( Tools.isOpera )
                {
                    oWrapper[0].addEventListener('MouseEvent', wheel, false);
                }
                else if ( Tools.isChrome || Tools.isSafari )
                {
                    oWrapper[0].addEventListener('WheelEvent', wheel, false);
                }
                else
                {
                    oWrapper[0].onmousewheel = wheel;
                }
            }
        }

        function start(oEvent)
        {

            et = $(oEvent.target).get(0);
            if (et.tagName)
            {
                // do nothing if clicked on handle
                if (et.tagName.toLowerCase() == 'input' || et.tagName.toLowerCase() == 'textarea' || et.tagName.toLowerCase() == 'select')
                {
                    return false;
                }
            }

            //if ( !hasScrollBar( $(oEvent.target) ) ){ return false; }

            iMouse.start = sAxis ? oEvent.pageX : oEvent.pageY;
            var oThumbDir = parseInt(oThumb.obj.css(sDirection));
            iPosition.start = oThumbDir == 'auto' ? 0 : oThumbDir;
            $(document).bind('mousemove.dcmsScroller', drag);
            document.ontouchmove = function (oEvent)
            {
                $(document).unbind('mousemove.dcmsScroller');
                drag(oEvent.touches[0]);
            };
            $(document).bind('mouseup.dcmsScroller', end);
            oThumb.obj.bind('mouseup.dcmsScroller', end);

            oThumb.obj[0].ontouchend = document.ontouchend = function (oEvent)
            {
                $(document).unbind('mouseup.dcmsScroller');
                oThumb.obj.unbind('mouseup.dcmsScroller');
                end(oEvent.touches[0]);
            };

            return false;
        }

        function wheel(oEvent)
        {
            if (!(oContent.ratio >= 1) &&
                $(this).hasClass('canScroll') &&
                $(oEvent.target).get(0).tagName != 'select' &&
                $(oEvent.target).parent().get(0).tagName != 'select' &&
                $(oEvent.target).get(0).tagName != 'textarea'
                ){


                oEvent = $.event.fix(oEvent || window.event);
                var iDelta = oEvent.wheelDelta ? oEvent.wheelDelta / 120 : -oEvent.detail / 3;
                iScroll -= iDelta * options.wheel;
                iScroll = Math.min((oContent[options.axis] - oViewport[options.axis]), Math.max(0, iScroll));
                oThumb.obj.css(sDirection, iScroll / oScrollbar.ratio );
                oContent.obj.css(sDirection, -iScroll);


                if ( sDirection == 'top') {
                    oThumb.scrollTop = iScroll / oScrollbar.ratio;
                    oContent.scrollTop = -iScroll;

                    oThumb.iScroll = iScroll;
                    oContent.iScroll = iScroll;
                }
                else {
                    oThumb.scrollLeft = iScroll / oScrollbar.ratio;
                    oContent.scrollLeft = -iScroll;

                    oThumb.iScroll = iScroll;
                    oContent.iScroll = iScroll;
                }

                oEvent.preventDefault();

                return false;
            }

        }


        function end(oEvent)
        {
            $(document).unbind('mousemove.dcmsScroller', drag);
            $(document).unbind('mouseup.dcmsScroller', end);
            oThumb.obj.unbind('mouseup.dcmsScroller', end);

            oThumb.obj.removeClass('scrollthumbDrag');

            document.ontouchmove = oThumb.obj[0].ontouchend = document.ontouchend = null;


            return false;
        }


        function drag(oEvent)
        {
            if (!(oContent.ratio >= 1))
            {

                oThumb.obj.addClass('scrollthumbDrag');

                iPosition.now = Math.min((oTrack[options.axis] - oThumb[options.axis]), Math.max(0, (iPosition.start + ((sAxis ? oEvent.pageX : oEvent.pageY) - iMouse.start))));
                iScroll = iPosition.now * oScrollbar.ratio;
                oContent.obj.css(sDirection, -iScroll);
                oThumb.obj.css(sDirection, iPosition.now);

                if ( sDirection == 'top') {
                    oThumb.scrollTop = iScroll / oScrollbar.ratio;
                    oContent.scrollTop = -iScroll;

                    oThumb.iScroll = iScroll;
                    oContent.iScroll = iScroll;


                }
                else {
                    oThumb.scrollLeft = iScroll / oScrollbar.ratio;
                    oContent.scrollLeft = -iScroll;

                    oThumb.iScroll = iScroll;
                    oContent.iScroll = iScroll;
                }


            }
            return false;
        };

        return initialize();
    }

})(jQuery);