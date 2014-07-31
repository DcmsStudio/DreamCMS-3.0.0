
/*! nanoScrollerJS - v0.7.2
 * http://jamesflorentino.github.com/nanoScrollerJS/
 * Copyright (c) 2013 James Florentino; Licensed MIT 
 * 
 * modefied by Marcel Domke
 * 
 * */


(function ($, window, document) {
    "use strict";

    var BROWSER_IS_IE7, BROWSER_SCROLLBAR_WIDTH, DOMSCROLL, DOWN, DRAG, KEYDOWN, KEYUP, MOUSEDOWN, MOUSEMOVE, MOUSEUP, MOUSEWHEEL, NanoScroll, PANEDOWN, RESIZE, SCROLL, SCROLLBAR, TOUCHMOVE, UP, WHEEL, defaults, getBrowserScrollbarWidth;

    defaults = {
        /**
         a classname for the pane element.
         @property paneClass
         @type String
         @default 'pane'
         */

        paneClass: 'pane',
        /**
         a classname for the slider element.
         @property sliderClass
         @type String
         @default 'slider'
         */

        sliderClass: 'slider',
        /**
         a classname for the content element.
         @property contentClass
         @type String
         @default 'content'
         */

        contentClass: 'content',
        /**
         a setting to enable native scrolling in iOS devices.
         @property iOSNativeScrolling
         @type Boolean
         @default false
         */

        iOSNativeScrolling: false,
        /**
         a setting to prevent the rest of the page being
         scrolled when user scrolls the `.content` element.
         @property preventPageScrolling
         @type Boolean
         @default false
         */

        preventPageScrolling: false,
        /**
         a setting to disable binding to the resize event.
         @property disableResize
         @type Boolean
         @default false
         */

        disableResize: false,
        /**
         a setting to make the scrollbar always visible.
         @property alwaysVisible
         @type Boolean
         @default false
         */

        alwaysVisible: true,
        /**
         a default timeout for the `flash()` method.
         @property flashDelay
         @type Number
         @default 1500
         */

        flashDelay: 1500,
        /**
         a minimum height for the `.slider` element.
         @property sliderMinHeight
         @type Number
         @default 20
         */

        sliderMinHeight: 50,
        /**
         a maximum height for the `.slider` element.
         @property sliderMaxHeight
         @type Number
         @default null
         */

        sliderMaxHeight: null,
        onScroll: null, // on scroll event
        scrollSliderMargin: 3
    };
    /**
     @property SCROLLBAR
     @type String
     @static
     @final
     @private
     */

    SCROLLBAR = 'scrollbar';
    /**
     @property SCROLL
     @type String
     @static
     @final
     @private
     */

    SCROLL = 'scroll';
    /**
     @property MOUSEDOWN
     @type String
     @final
     @private
     */

    MOUSEDOWN = 'mousedown';
    /**
     @property MOUSEMOVE
     @type String
     @static
     @final
     @private
     */

    MOUSEMOVE = 'mousemove';
    /**
     @property MOUSEWHEEL
     @type String
     @final
     @private
     */

    MOUSEWHEEL = 'mousewheel';
    /**
     @property MOUSEUP
     @type String
     @static
     @final
     @private
     */

    MOUSEUP = 'mouseup';
    /**
     @property RESIZE
     @type String
     @final
     @private
     */

    RESIZE = 'resize';
    /**
     @property DRAG
     @type String
     @static
     @final
     @private
     */

    DRAG = 'drag';
    /**
     @property UP
     @type String
     @static
     @final
     @private
     */

    UP = 'up';
    /**
     @property PANEDOWN
     @type String
     @static
     @final
     @private
     */

    PANEDOWN = 'panedown';
    /**
     @property DOMSCROLL
     @type String
     @static
     @final
     @private
     */

    DOMSCROLL = 'DOMMouseScroll';
    /**
     @property DOWN
     @type String
     @static
     @final
     @private
     */

    DOWN = 'down';
    /**
     @property WHEEL
     @type String
     @static
     @final
     @private
     */

    WHEEL = 'wheel';
    /**
     @property KEYDOWN
     @type String
     @static
     @final
     @private
     */

    KEYDOWN = 'keydown';
    /**
     @property KEYUP
     @type String
     @static
     @final
     @private
     */

    KEYUP = 'keyup';
    /**
     @property TOUCHMOVE
     @type String
     @static
     @final
     @private
     */

    TOUCHMOVE = 'touchmove';
    /**
     @property BROWSER_IS_IE7
     @type Boolean
     @static
     @final
     @private
     */

    BROWSER_IS_IE7 = window.navigator.appName === 'Microsoft Internet Explorer' && /msie 7./i.test(window.navigator.appVersion) && window.ActiveXObject;
    /**
     @property BROWSER_SCROLLBAR_WIDTH
     @type Number
     @static
     @default null
     @private
     */

    BROWSER_SCROLLBAR_WIDTH = null;
    /**
     Returns browser's native scrollbar width
     @method getBrowserScrollbarWidth
     @return {Number} the scrollbar width in pixels
     @static
     @private
     */

    getBrowserScrollbarWidth = function () {
        var outer, outerStyle, scrollbarWidth;
        outer = document.createElement('div');
        outerStyle = outer.style;
        outerStyle.position = 'absolute';
        outerStyle.width = '100px';
        outerStyle.height = '100px';
        outerStyle.overflow = SCROLL;
        outerStyle.top = '-9999px';
        document.body.appendChild(outer);
        scrollbarWidth = outer.offsetWidth - outer.clientWidth;
        document.body.removeChild(outer);
        return scrollbarWidth;
    };


    /**
     @class NanoScroll
     @param element {HTMLElement|Node} the main element
     @param options {Object} nanoScroller's options
     @constructor
     */

    NanoScroll = (function () {

        function NanoScroll (el, options) {
            this.el = el;
            this.options = options;


            BROWSER_SCROLLBAR_WIDTH || (BROWSER_SCROLLBAR_WIDTH = getBrowserScrollbarWidth());


            this.scrollPosTop = 0;
            this.$el = $(this.el);
            this.doc = $(document);
            this.win = $(window);

            if (typeof options.scrollContent === 'string')
            {
                this.$content = this.$el.find(options.scrollContent);
            }
            else if (typeof options.scrollContent === 'object')
            {
                this.$content = options.scrollContent;
            }
            else
            {
                this.$content = this.$el.children("." + options.contentClass);
            }

            this.startContentOverflow = this.$content.css('overflow') || '';
            this.startContentHeight = this.$content.css('height') || '';

            this.$content.css('overflow', '').height('');
            this.$content.attr('tabindex', formTabIndex++);
            this.content = this.$content.get(0);


            this.baseMarginRight = parseInt(this.$content.css('marginRight'), 0);
            this.basePaddingRight = parseInt(this.$content.css('paddingRight'), 0) + (!this.baseMarginRight ? 0 : 0);


            if (this.options.iOSNativeScrolling && (this.el.style.WebkitOverflowScrolling !== null)) {
                this.nativeScrolling();
            } else {
                this.generate();
            }

            this.createEvents();
            this.addEvents();
            this.reset();
        }

        /**
         Prevents the rest of the page being scrolled
         when user scrolls the `.content` element.
         @method preventScrolling
         @param event {Event}
         @param direction {String} Scroll direction (up or down)
         @private
         */


        NanoScroll.prototype.preventScrolling = function (e, direction) {
            if (!this.isActive) {
                return;
            }
            if (e.type === DOMSCROLL) {
                if (direction === DOWN && e.originalEvent.detail > 0 || direction === UP && e.originalEvent.detail < 0) {
                    e.preventDefault();
                }
            } else if (e.type === MOUSEWHEEL) {
                if (!e.originalEvent || !e.originalEvent.wheelDelta) {
                    return;
                }
                if (direction === DOWN && e.originalEvent.wheelDelta < 0 || direction === UP && e.originalEvent.wheelDelta > 0) {
                    e.preventDefault();
                }
            }
        };

        /**
         Enable iOS native scrolling
         */


        NanoScroll.prototype.nativeScrolling = function () {
            this.$content.css({
                WebkitOverflowScrolling: 'touch'
            });
            this.iOSNativeScrolling = true;
            this.isActive = true;
        };

        /**
         Updates those nanoScroller properties that
         are related to current scrollbar position.
         @method updateScrollValues
         @private
         */


        NanoScroll.prototype.updateScrollValues = function () {
            var content;
            content = this.content;
            this.maxScrollTop = content.scrollHeight - content.clientHeight;
            this.contentScrollTop = content.scrollTop;
            if (!this.iOSNativeScrolling) {
                this.maxSliderTop = this.paneHeight - this.sliderHeight;
                this.sliderTop = this.contentScrollTop * this.maxSliderTop / this.maxScrollTop;
            }
        };

        /**
         Creates event related methods
         @method createEvents
         @private
         */


        NanoScroll.prototype.createEvents = function () {
            var to, _this = this;
            this.events = {
                down: function (e) {
                    _this.isBeingDragged = true;
                    _this.offsetY = e.pageY - _this.slider.offset().top;
                    _this.pane.addClass('active');
                    _this.doc.bind(MOUSEMOVE, _this.events[DRAG]).bind(MOUSEUP, _this.events[UP]);
                    return false;
                },
                drag: function (e) {
                    _this.sliderY = e.pageY - _this.$el.offset().top - _this.offsetY;
                    _this.scroll();
                    _this.updateScrollValues();
                    if (_this.contentScrollTop >= _this.maxScrollTop) {
                        _this.$el.trigger('scrollend');
                    } else if (_this.contentScrollTop === 0) {
                        _this.$el.trigger('scrolltop');
                    }
                    return false;
                },
                up: function (e) {
                    _this.isBeingDragged = false;
                    _this.pane.removeClass('active');
                    _this.doc.unbind(MOUSEMOVE, _this.events[DRAG]).unbind(MOUSEUP, _this.events[UP]);
                    return false;
                },
                resize: function (e) {
                    clearTimeout(to);
            //        console.log([e]);
                    to = setTimeout(function(){ _this.reset(); }, 300);
                },
                panedown: function (e) {
                    _this.sliderY = (e.offsetY || e.originalEvent.layerY) - (_this.sliderHeight * 0.5);
                    _this.scroll();
                    _this.events.down(e);
                    return false;
                },
                scroll: function (e) {
                    if (_this.isBeingDragged) {
                        return;
                    }
                    _this.updateScrollValues();

                    var scrollTop;

                    if (!_this.iOSNativeScrolling) {
                        _this.sliderY = _this.sliderTop;

                        _this.slider.css({
                            top: _this.sliderTop
                        });
                    }

                    if (e == null) {
                        return;
                    }

                    if (typeof _this.options.onScroll == 'function') {
                        // var sliderY = Math.max(0, _this.sliderY);
                        // sliderY = Math.min(_this.maxSliderTop, sliderY);
                        // scrollTop = (_this.paneHeight - _this.contentHeight + BROWSER_SCROLLBAR_WIDTH) * sliderY / _this.maxSliderTop * -1;

                        _this.options.onScroll(_this.contentScrollTop);
                    }
                    if (_this.contentScrollTop >= _this.maxScrollTop) {
                        if (_this.options.preventPageScrolling) {
                            _this.preventScrolling(e, DOWN);
                        }
                        _this.$el.trigger('scrollend');
                    } else if (_this.contentScrollTop === 0) {
                        if (_this.options.preventPageScrolling) {
                            _this.preventScrolling(e, UP);
                        }
                        _this.$el.trigger('scrolltop');
                    }





                },
                wheel: function (e) {
                    if (e == null) {
                        return;
                    }
                    _this.sliderY += -e.wheelDeltaY || -e.delta;
                    _this.scroll();
                    return false;
                }
            };
        };

        /**
         Adds event listeners with jQuery.
         @method addEvents
         @private
         */
        NanoScroll.prototype.addEvents = function () {
            var events;
            this.removeEvents();
            events = this.events;
            if (!this.options.disableResize) {
                this.win.bind(RESIZE, events[RESIZE]);
            }
            if (!this.iOSNativeScrolling) {
                this.slider.bind(MOUSEDOWN, events[DOWN]);
                this.pane.bind(MOUSEDOWN, events[PANEDOWN]).bind("" + MOUSEWHEEL + " " + DOMSCROLL, events[WHEEL]);
            }
            this.$content.bind("" + SCROLL + " " + MOUSEWHEEL + " " + DOMSCROLL + " " + TOUCHMOVE, events[SCROLL]);
        };

        /**
         Removes event listeners with jQuery.
         @method removeEvents
         @private
         */


        NanoScroll.prototype.removeEvents = function () {
            var events;
            events = this.events;
            this.win.unbind(RESIZE, events[RESIZE]);
            if (!this.iOSNativeScrolling) {
                this.slider.unbind();
                this.pane.unbind();
            }
            this.$content.unbind("" + SCROLL + " " + MOUSEWHEEL + " " + DOMSCROLL + " " + TOUCHMOVE, events[SCROLL]);
        };

        /**
         Generates nanoScroller's scrollbar and elements for it.
         @method generate
         @chainable
         @private
         */


        NanoScroll.prototype.generate = function () {
            var contentClass, cssRule, options, paneClass, sliderClass;
            options = this.options;
            paneClass = options.paneClass, sliderClass = options.sliderClass, contentClass = options.contentClass;



            if (!this.$el.find("" + paneClass).length && !this.$el.find("" + sliderClass).length) {

                if (this.$content.is('table'))
                {
                    this.$content.removeClass('scroll-content');
                    this.$content.wrap($('<div class="scroll-content" style="display:inline-block"/>').width('100%').height('auto'));
                    this.$content = this.$content.parent();
                    this.content = this.$content.get(0);
                }


                this.$el.append("<div class=\"" + paneClass + "\"><div class=\"" + sliderClass + "\"><div/></div></div>");
            }


            this.pane = this.$el.children("." + paneClass);
            this.slider = this.pane.find("." + sliderClass);
            this.sliderInner = this.slider.find("div");

            if (BROWSER_SCROLLBAR_WIDTH)
            {
                cssRule = this.$el.css('direction') === 'rtl' ? {
                    paddingLeft: BROWSER_SCROLLBAR_WIDTH
                } : {
                    paddingRight: BROWSER_SCROLLBAR_WIDTH
                };

                this.$el.addClass('has-scrollbar');
            }

            if (cssRule != null) {

                var space = parseInt(this.$content.css('paddingRight')) + BROWSER_SCROLLBAR_WIDTH;
                this.$content.css({paddingRight: space + this.basePaddingRight, marginRight: 0 - space + this.baseMarginRight - 3});
            }

            return this;
        };

        /**
         @method restore
         @private
         */


        NanoScroll.prototype.restore = function () {
            this.stopped = false;
            this.$content.css({paddingRight: (this.basePaddingRight ? this.basePaddingRight : ''), marginRight: this.baseMarginRight}).css('overflow', '');
            this.pane.show();
            this.addEvents();
        };

        /**
         Resets nanoScroller's scrollbar.
         @method reset
         @chainable
         @example
         $(".nano").nanoScroller();
         */

        



        NanoScroll.prototype.reset = function () {
            var content, contentHeight, contentStyle, contentStyleOverflowY, paneBottom, paneHeight, paneOuterHeight, paneTop, sliderHeight;
            /*
             if (typeof this.content == 'undefined')
             {
             this.stopForce();
             delete(this.nanoscroller);
             return;
             }
             */
            if (this.iOSNativeScrolling) {
                this.contentHeight = this.content.scrollHeight;
                return;
            }

            if (!this.$el.find("." + this.options.paneClass).length) {
                this.generate().stop();
            }

            if (this.stopped) {
                this.restore();
            }


            this.$content.css('overflow', 'auto');
            var maxHeight = this.$content.parent().height();
            var maxWidth = this.$content.parent().width();
            this.$content.css({width: maxWidth});

            content = this.content;
            contentStyle = this.$content.attr('style') ? this.$content.attr('style') : '';
            contentStyleOverflowY = typeof contentStyle.overflowY !== 'undefined' ? contentStyle.overflowY : null;

            if (BROWSER_IS_IE7) {
                this.$content.css({
                    height: this.$content.height()
                });
            }

            contentHeight = content.scrollHeight; // + BROWSER_SCROLLBAR_WIDTH - 2;
            paneHeight = this.pane.outerHeight(true);
            paneTop = parseInt(this.pane.css('top'), 10);
            paneBottom = parseInt(this.pane.css('bottom'), 10);
            paneOuterHeight = paneHeight + paneTop + paneBottom;
            sliderHeight = Math.round(paneOuterHeight / contentHeight * paneOuterHeight);

            if (sliderHeight < this.options.sliderMinHeight) {
                sliderHeight = this.options.sliderMinHeight;
            } else if ((this.options.sliderMaxHeight !== null) && sliderHeight > this.options.sliderMaxHeight) {
                sliderHeight = this.options.sliderMaxHeight;
            }
            if (contentStyleOverflowY === SCROLL && contentStyle.overflowX !== SCROLL) {
                sliderHeight += BROWSER_SCROLLBAR_WIDTH;
            }

            //sliderHeight -= (2 * this.options.scrollSliderMargin)


            this.maxSliderTop = paneOuterHeight - sliderHeight;
            this.contentHeight = contentHeight;
            this.paneHeight = paneHeight;
            this.paneOuterHeight = paneOuterHeight;
            this.sliderHeight = sliderHeight;
            this.slider.height(sliderHeight);

            this.sliderInner.height(sliderHeight - (2 * this.options.scrollSliderMargin));



            this.events.scroll();
            this.pane.show();
            this.isActive = true;

            var paneWidth = parseInt(this.pane.width(), 10);

            this.$content.css('overflow', '');

            if ((content.scrollHeight === content.clientHeight) || (this.paneHeight >= content.scrollHeight && contentStyleOverflowY !== SCROLL)) {
                this.pane.hide();
                this.isActive = false;

                this.$content.css({paddingRight: (this.basePaddingRight ? this.basePaddingRight : ''), marginRight: (this.baseMarginRight > 0 ? this.baseMarginRight : '')}).css({'overflow': 'hidden'});
                this.removeEvents();

                this.$content.css({width: '', height: maxHeight});

            } else if (this.el.clientHeight === content.scrollHeight && contentStyleOverflowY === SCROLL) {
                this.pane.hide();
                this.$content.css({paddingRight: (this.basePaddingRight ? this.basePaddingRight : ''), marginRight: (this.baseMarginRight > 0 ? this.baseMarginRight : '')}).css({'overflow': 'hidden'});
                this.slider.hide();

                this.$content.css({width: '', height: maxHeight});

                this.removeEvents();
            }
            else
            {


                var space = parseInt(BROWSER_SCROLLBAR_WIDTH, 10);

                this.pane.show();

                // this.$content.attr('style', 'padding-right:' + (parseInt(this.basePaddingRight, 10) + 3) + 'px!important;margin-right:' + '0px!important;height:' + paneHeight + 'px');

                //this.$content.css({paddingRight: parseInt(space + this.basePaddingRight, 10) +'px!important' , marginRight: parseInt(0 - (space + parseInt(this.baseMarginRight)), 10) +'px!important', 'overflow': ''});
                /*
                 if (this.$content.parents('.isWindowContainer:first').length && this.$content.parents('.isWindowContainer:first').data('windowGrid')) {
                 this.$content.css({paddingRight: parseInt(space + this.basePaddingRight) +'px!important' , marginRight: (0 - (space + this.baseMarginRight)) +'px!important' });
                 } */
                this.removeEvents();
                this.addEvents();




                if (this.$content.parents('div.grid-table-wrapper:first').length || 
                        this.$content.parents('div.select-multilined-box:first').length ||
                        this.$content.parents('div.select-box-opts-container:first').length) {
                    this.$content.css({width: (maxWidth), height: maxHeight, marginRight: '', paddingRight: ''});
                }
                else {

                    this.$content.css({
                        marginRight: (0 - space) + 'px!important', height: maxHeight,
                        width: '', // (maxWidth - space - parseInt(this.basePaddingRight, 10)),
                        paddingRight: (space + (parseInt(this.basePaddingRight, 10) * 2)) + 'px!important'
                    });



                    // this.$content.attr('style', 'padding-right:' + (space + parseInt(this.basePaddingRight, 10)) + 'px!important;margin-right:' + (0 - (space + parseInt(this.baseMarginRight, 10))) + 'px!important');
                    // this.$content.css({width: (maxWidth - ( !this.$content.parents('.isWindowContainer:first').hasClass('no-padding') ? paneWidth - 3 : 0) - this.basePaddingRight)});
                }


                this.slider.show();
            }





            this.pane.css({
                opacity: (this.options.alwaysVisible ? 1 : '')//,
                        //visibility: (this.options.alwaysVisible ? 'visible' : '')
            });
            return this;
        };
        
        
        NanoScroll.prototype.getScrollPosTop = function () {
            return this.contentScrollTop;
        };
        

        /**
         @method scroll
         @private
         @example
         $(".nano").nanoScroller({ scroll: 'top' });
         */
        NanoScroll.prototype.scroll = function () {
            if (!this.isActive) {
                return;
            }
            this.sliderY = Math.max(0, this.sliderY);
            this.sliderY = Math.min(this.maxSliderTop, this.sliderY);
            var scrollTop = (this.paneHeight - this.contentHeight + BROWSER_SCROLLBAR_WIDTH) * this.sliderY / this.maxSliderTop * -1;
            
            this.scrollPosTop = scrollTop;
            
            this.$content.scrollTop(scrollTop);
            if (!this.iOSNativeScrolling) {
                this.slider.css({
                    top: this.sliderY
                });
            }

            if (typeof this.options.onScroll == 'function') {
                this.options.onScroll(scrollTop);
            }
            return this;
        };

        /**
         Scroll at the bottom with an offset value
         @method scrollBottom
         @param offsetY {Number}
         @chainable
         @example
         $(".nano").nanoScroller({ scrollBottom: value });
         */


        NanoScroll.prototype.scrollBottom = function (offsetY) {
            if (!this.isActive) {
                return;
            }
            this.reset();
            this.$content.scrollTop(this.contentHeight - this.$content.height() - offsetY).trigger(MOUSEWHEEL);
            return this;
        };

        /**
         Scroll at the top with an offset value
         @method scrollTop
         @param offsetY {Number}
         @chainable
         @example
         $(".nano").nanoScroller({ scrollTop: value });
         */


        NanoScroll.prototype.scrollTop = function (offsetY) {
            if (!this.isActive) {
                return;
            }
            this.reset();



            this.$content.scrollTop(+offsetY).trigger(MOUSEWHEEL);
            return this;
        };

        /**
         Scroll to an element
         @method scrollTo
         @param node {Node} A node to scroll to.
         @chainable
         @example
         $(".nano").nanoScroller({ scrollTo: $('#a_node') });
         */


        NanoScroll.prototype.scrollTo = function (node)
        {
            if (!this.isActive) {
                return;
            }

            this.reset();

            if (typeof node === 'object')
            {
                this.scrollTop($(node).get(0).offsetTop);
            }
            else
            {
                this.scrollTop(node);
            }

            return this;
        };

        /**
         To stop the operation.
         This option will tell the plugin to disable all event bindings and hide the gadget scrollbar from the UI.
         @method stop
         @chainable
         @example
         $(".nano").nanoScroller({ stop: true });
         */


        NanoScroll.prototype.stop = function () {
            this.$content.css({paddingRight: (this.basePaddingRight ? this.basePaddingRight : ''), marginRight: this.baseMarginRight}).css('overflow', '');
            this.stopped = true;
            this.removeEvents();
            this.pane.hide();

            return this;
        };


        NanoScroll.prototype.stopForce = function () {
            this.$el.removeClass('has-scrollbar nano');
            this.$content.css({paddingRight: (this.basePaddingRight ? '' : ''), marginRight: ''})
                    .css('overflow', '').css('height', '')
                    .removeClass('scroll-content').removeAttr('tabindex');



            if (this.startContentHeight) {

            }





            this.removeEvents();
            this.pane.hide().remove();
            this.stopped = true;


            return this;
        };

        /**
         To flash the scrollbar gadget for an amount of time defined in plugin settings (defaults to 1,5s).
         Useful if you want to show the user (e.g. on pageload) that there is more content waiting for him.
         @method flash
         @chainable
         @example
         $(".nano").nanoScroller({ flash: true });
         */


        NanoScroll.prototype.flash = function () {
            var _this = this;
            if (!this.isActive) {
                return;
            }
            this.reset();
            this.pane.addClass('flashed');
            setTimeout(function () {
                _this.pane.removeClass('flashed');
            }, this.options.flashDelay);
            return this;
        };

        return NanoScroll;

    })();


    $.fn.removeNanoScroller = function (settings) {
        return this.each(function () {
            var options, scrollbar;
            if (!(scrollbar = this.nanoscroller)) {
                return this;
            }


            $(this).removeClass('nano').removeClass('has-scrollbar');

            scrollbar.stopForce();

            delete(this.nanoscroller);

            return this;
        });
    };


    $.fn.nanoScroller = function (settings) {

        return $(this).each(function () {

            var options, scrollbar;

            if (!(scrollbar = this.nanoscroller)) {
                options = $.extend({}, defaults, settings);
                this.nanoscroller = scrollbar = new NanoScroll(this, options);
            }

            if (settings && typeof settings === "object") {
                $.extend(scrollbar.options, settings);
                if (settings.scrollBottom) {
                    return scrollbar.scrollBottom(settings.scrollBottom);
                }
                if (settings.scrollTop) {

                    return scrollbar.scrollTop(settings.scrollTop);
                }
                if (settings.scrollTo) {

                    return scrollbar.scrollTo(settings.scrollTo);
                }
                if (settings.scroll === 'bottom') {

                    return scrollbar.scrollBottom(0);
                }
                if (settings.scroll === 'top') {

                    return scrollbar.scrollTop(0);
                }
                if (settings.scroll && settings.scroll instanceof $) {

                    return scrollbar.scrollTo(settings.scroll);
                }
                if (settings.stop) {
                    return scrollbar.stop();
                }
                if (settings.flash) {
                    return scrollbar.flash();
                }
            }


            if (settings && typeof settings === "string")
            {
                if (settings === 'stop') {
                    return scrollbar.stop();
                }
                
                if (settings === 'flash') {
                    return scrollbar.flash();
                }
                
                if (settings === 'scrollPosTop') {
                    return scrollbar.getScrollPosTop();
                }
            }
            return scrollbar.reset();
        });
    };
})(jQuery, window, document);