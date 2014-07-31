function URLParser(u) {
    var path = "", query = "", hash = "", params;
    if (u.indexOf("#") > 0) {
        hash = u.substr(u.indexOf("#") + 1);
        u = u.substr(0, u.indexOf("#"));
    }
    if (u.indexOf("?") > 0) {
        path = u.substr(0, u.indexOf("?"));
        query = u.substr(u.indexOf("?") + 1);
        params = query.split('&');
    } else
        path = u;
    return {
        getHost: function () {
            var hostexp = /\/\/([\w.-]*)/;
            var match = hostexp.exec(path);
            if (match != null && match.length > 1)
                return match[1];
            return "";
        },
        getPath: function () {
            var pathexp = /\/\/[\w.-]*(?:\/([^?]*))/;
            var match = pathexp.exec(path);
            if (match != null && match.length > 1)
                return match[1];
            return "";
        },
        getHash: function () {
            return hash;
        },
        getParams: function () {
            return params
        },
        getQuery: function () {
            return query;
        },
        setHash: function (value) {
            if (query.length > 0)
                query = "?" + query;
            if (value.length > 0)
                query = query + "#" + value;
            return path + query;
        },
        setParam: function (name, value) {
            if (!params) {
                params = new Array();
            }
            params.push(name + '=' + value);
            for (var i = 0; i < params.length; i++) {
                if (query.length > 0)
                    query += "&";
                query += params[i];
            }
            if (query.length > 0)
                query = "?" + query;
            if (hash.length > 0)
                query = query + "#" + hash;
            return path + query;
        },
        getParam: function (name) {
            if (params) {
                for (var i = 0; i < params.length; i++) {
                    var pair = params[i].split('=');
                    if (decodeURIComponent(pair[0]) == name)
                        return decodeURIComponent(pair[1]);
                }
            }
            console.log('Query variable %s not found', name);
        },
        hasParam: function (name) {
            if (params) {
                for (var i = 0; i < params.length; i++) {
                    var pair = params[i].split('=');
                    if (decodeURIComponent(pair[0]) == name)
                        return true;
                }
            }
            console.log('Query variable %s not found', name);
        },
        removeParam: function (name) {
            query = "";
            if (params) {
                var newparams = new Array();
                for (var i = 0; i < params.length; i++) {
                    var pair = params[i].split('=');
                    if (decodeURIComponent(pair[0]) != name)
                        newparams.push(params[i]);
                }
                params = newparams;
                for (var i = 0; i < params.length; i++) {
                    if (query.length > 0)
                        query += "&";
                    query += params[i];
                }
            }
            if (query.length > 0)
                query = "?" + query;
            if (hash.length > 0)
                query = query + "#" + hash;
            return path + query;
        },
    }
}


// HTML Truncator for jQuery
// by Henrik Nyh <http://henrik.nyh.se> 2008-02-28.
// Free to modify and redistribute with credit.

(function ($) {

    var trailing_whitespace = true;

    $.fn.truncate = function (options) {

        var opts = $.extend({}, $.fn.truncate.defaults, options);

        $(this).each(function () {

            var content_length = $.trim(squeeze($(this).text())).length;
            if (content_length <= opts.max_length)
                return;  // bail early if not overlong

            var actual_max_length = opts.max_length - opts.more.length - 3;  // 3 for " ()"
            var truncated_node = recursivelyTruncate(this, actual_max_length);
            var full_node = $(this).hide();

            truncated_node.insertAfter(full_node);

            findNodeForMore(truncated_node).append(' (<a href="#show more content">' + opts.more + '</a>)');
            findNodeForLess(full_node).append(' (<a href="#show less content">' + opts.less + '</a>)');

            truncated_node.find('a:last').click(function () {
                truncated_node.slideUp(300);
                full_node.slideDown(300);
                return false;
            });
            full_node.find('a:last').click(function () {
                truncated_node.slideDown(300);
                full_node.slideUp(300);
                return false;
            });

        });
    }

    // Note that the " (…more)" bit counts towards the max length – so a max
    // length of 10 would truncate "1234567890" to "12 (…more)".
    $.fn.truncate.defaults = {
        max_length: 100,
        more: '…more',
        less: 'less'
    };

    function recursivelyTruncate(node, max_length) {
        return (node.nodeType == 3) ? truncateText(node, max_length) : truncateNode(node, max_length);
    }

    function truncateNode(node, max_length) {
        var node = $(node);
        var new_node = node.clone().empty();
        var truncatedChild;
        node.contents().each(function () {
            var remaining_length = max_length - new_node.text().length;
            if (remaining_length == 0)
                return;  // breaks the loop
            truncatedChild = recursivelyTruncate(this, remaining_length);
            if (truncatedChild)
                new_node.append(truncatedChild);
        });
        return new_node;
    }

    function truncateText(node, max_length) {
        var text = squeeze(node.data);
        if (trailing_whitespace)  // remove initial whitespace if last text
            text = text.replace(/^ /, '');  // node had trailing whitespace.
        trailing_whitespace = !!text.match(/ $/);
        var text = text.slice(0, max_length);
        // Ensure HTML entities are encoded
        // http://debuggable.com/posts/encode-html-entities-with-jquery:480f4dd6-13cc-4ce9-8071-4710cbdd56cb
        text = $('<div/>').text(text).html();
        return text;
    }

    // Collapses a sequence of whitespace into a single space.
    function squeeze(string) {
        return string.replace(/\s+/g, ' ');
    }

    // Finds the last, innermost block-level element
    function findNodeForMore(node) {
        var $node = $(node);
        var last_child = $node.children(":last");
        if (!last_child)
            return node;
        var display = last_child.css('display');
        if (!display || display == 'inline')
            return $node;
        return findNodeForMore(last_child);
    }
    ;

    // Finds the last child if it's a p; otherwise the parent
    function findNodeForLess(node) {
        var $node = $(node);
        var last_child = $node.children(":last");
        if (last_child && last_child.is('p'))
            return last_child;
        return node;
    }
    ;

})(jQuery);

var isMobile = false;

(function ($) {
    $.fn.dropmenu = function (custom) {
        var defaults = {
            openAnimation: "slide",
            closeAnimation: "slide",
            openSpeed: 400,
            closeSpeed: 250,
            closeDelay: 200,
            onHide: function () {
            },
            onHidden: function () {
            },
            onShow: function () {
            },
            onShown: function () {
            }
        };
        var settings = $.extend({}, defaults, custom);

        var menu = $(this);
        var currentPage = 0;
        var delayTimer = "";

        // Trigger init
        init();

        /**
         * Do preparation work
         */
        function init() {

            // Add open button and class to parent of a submenu
            var items = menu.find(">:has(li,div) > a");
            $.each(items, function (i, val) {
                if (items.eq(i).parent().is("li")) {

                    items.eq(i).next().addClass("submenu").parent().addClass("haschildren");

                } else {
                    items.eq(i).parent().find("ul").show();
                }
            });

            // Add open button and class to parent of a submenu
            var items = menu.find(">:has(li,div) > span");
            $.each(items, function (i, val) {
                if (items.eq(i).parent().is("li")) {

                    items.eq(i).next().addClass("submenu").parent().addClass("haschildren");


                } else {
                    items.eq(i).parent().find("ul").show();
                }
            });

            menu.find('.menuitem-container').find('ul').removeClass("submenu").show();

            menu.find(">li").bind("mouseleave", handleHover).bind("mouseenter", handleHover);
            menu.bind("mouseleave", function (e) {
                closeAllMenus();
            });
            /*
             menu.find(">li > a").bind("mouseenter", handleHover).parent().bind("mouseleave", handleHover).bind("mouseenter", function() {
             window.clearInterval(delayTimer);
             });

             menu.find(">li > span").bind("mouseenter", handleHover).parent().bind("mouseleave", handleHover).bind("mouseenter", function() {
             window.clearInterval(delayTimer);
             });
             */


        }

        /**
         * Handle mouse hover action
         */
        function handleHover(e) {
            if (e.type == "mouseenter" || e.type == "click") {
                window.clearInterval(delayTimer);


                var currentItem = $(e.target);
                var current_submenu = $(e.target).find(".submenu:not(:animated):not(.open)");

                if (current_submenu.length == 0) {
                    current_submenu = $(e.target).next(".submenu:not(:animated):not(.open)");
                }

                if (current_submenu.length == 1) {
                    settings.onShow.call(current_submenu);
                    closeAllMenus();
                    currentItem.find('>a,>span').addClass("selected");
                    current_submenu.css("z-index", "");

                    if (current_submenu.hasClass('submenu')) {
                        current_submenu.stop().hide();
                    }
                    current_submenu.css({height: '', width: '', opacity: '1', margin: '', padding: ''});
                    menu.find('.menuitem-container').find('ul').removeClass("submenu").show();
                    openMenu(current_submenu);
                }
                else {
                    menu.find("a.selected").removeClass("selected");
                    menu.find("span.selected").removeClass("selected");
                    currentItem.children('a:first,span:first').addClass("selected");
                    closeAllMenus();
                    menu.find('.menuitem-container').find('ul').removeClass("submenu").show();
                }
            }

            if (e.type == "mouseleave" || e.type == "mouseout") {
                current_submenu = $(e.target);


                if (current_submenu.length == 1) {

                    menu.find("a.selected").removeClass("selected");
                    menu.find("span.selected").removeClass("selected");

                    if (settings.closeDelay == 0) {

                        closeMenu(current_submenu);
                        current_submenu.css({height: '', width: '', opacity: '1', margin: '', padding: ''});
                    } else {
                        if (current_submenu.hasClass('submenu')) {
                            window.clearInterval(delayTimer);
                            delayTimer = setInterval(function () {
                                window.clearInterval(delayTimer);
                                closeMenu(current_submenu);

                                current_submenu.css({height: '', width: '', opacity: '1', margin: '', padding: ''});
                            }, settings.closeDelay);
                        }
                    }
                }
            }
        }

        function openMenu(object) {
            switch (settings.openAnimation) {
                case "slide":
                    openSlideAnimation(object);
                    break;
                case "fade":
                    openFadeAnimation(object);
                    break;
                default:
                    openSizeAnimation(object);
                    break;
            }
        }

        function openSlideAnimation(object) {
            object.stop(true).css({visible: 'hidden'}).show();
            var height = object.outerHeight(true);
            object.css({visible: ''}).hide();


            object.addClass("open").css({height: 0});

            object.show().animate({
                height: height
            }, {
                duration: settings.openSpeed,
                complete: function () {
                    $(this).prev().addClass("selected");
                    settings.onShown.call(this);
                }
            });
            return;

            object.addClass("open").stop(true).slideDown(settings.openSpeed, function () {
                $(this).prev().addClass("selected");
                settings.onShown.call(this);
            });
        }

        function openFadeAnimation(object) {
            object.addClass("open").stop(true).fadeIn(settings.openSpeed, function () {
                $(this).prev().addClass("selected");
                settings.onShown.call(this);
            });
        }

        function openSizeAnimation(object) {
            object.addClass("open").stop(true).show(settings.openSpeed, function () {
                $(this).prev().addClass("selected");
                settings.onShown.call(this);
            });
        }

        function closeMenu(object) {
            settings.onHide.call(object);
            switch (settings.closeAnimation) {
                case "slide":
                    closeSlideAnimation(object);

                    break;
                case "fade":
                    closeFadeAnimation(object);
                    break;
                default:
                    closeSizeAnimation(object);
                    object.height('').width('').css({padding: '', opacity: '1'});
                    break;
            }
        }

        function closeSlideAnimation(object) {

            var height = object.outerHeight(true);
            object.stop(true).animate({height: 0, opacity: '0.3'}, {duration: settings.closeSpeed, complete: function () {

                object.height('').width('').css({padding: '', opacity: '1'}).hide();

                if (closeCallback) {
                    closeCallback();
                }


            }});

            //object.stop(true).slideUp(settings.closeSpeed, closeCallback);
        }

        function closeFadeAnimation(object) {
            object.stop(true).fadeOut(settings.closeSpeed, function () {
                $(this).removeClass("open");
                $(this).prev().removeClass("selected");
            });
        }

        function closeSizeAnimation(object) {
            object.stop(true).hide(settings.closeSpeed, function () {
                $(this).removeClass("open");
                $(this).prev().removeClass("selected");
            });
        }

        function closeAllMenus() {
            var items = menu.find("li ul.submenu.open,li div.submenu.open");
            $.each(items, function (i) {
                $(items[i]).css("z-index", "").stop();
                closeMenu($(items[i]));
            });
        }

        function closeCallback(object) {
            $(this).removeClass("open");
            if ($(this).prev().hasClass("selected"))
                settings.onHidden.call(this);
            $(this).prev().removeClass("selected");

        }

        return this;
    }

})(jQuery);


function findSyntaxes() {
    if (typeof SyntaxHighlighter != 'undefined') {
        SyntaxHighlighter.config.clipboardSwf = 'html/js/syntax/scripts/clipboard.swf';
        SyntaxHighlighter.config.bloggerMode = true;
        SyntaxHighlighter.all();
        SyntaxHighlighter.highlight();
    }
}
function prepareTeaser() {
    $('.teaser').each(function () {
        // $(this).truncate({max_length: 150});

    });
}

function prepareNavigation() {
    $("#navbar").find('.menuitem-container').addClass('submenu').hide();
    $("#navbar").find('.main-nav ul').addClass('submenu').hide();

    $('#navbar .megamenu').removeClass('submenu').show().parent().parent().removeClass('submenu').addClass('megamenu-container');
    $('#navbar .megamenu .submenu').removeClass('submenu').show();


    var locations = document.location.href;
    locations = locations.replace(/http(s)?:\/\//, '');
    var elements = locations.split('/');
    delete elements[0];
    var str = elements.join('/'), naviLinks = $("#navbar .main-nav").find('a');
    var stop = false, skip = true, useClass = false;


    naviLinks.each(function () {
        var href = $(this).attr('href');

        if (!href) {
            $(this).attr('href', '/');
        }

        if (!useClass && href) {

            var regex = new RegExp(href, 'g');
            if (document.location.href.match(regex) && $(this).parents('li:first').hasClass('active')) {
                useClass = true;
            }
        }
    });


    naviLinks.each(function () {
        var href = $(this).attr('href');
        if (!stop && typeof href === 'string') {
            var hhref = '/' + href;


            if (useClass && $(this).parents('li:first').hasClass('active')) {
                console.log('naviLinks use class active');

                var baseLI = $(this).parents('li:first');

                // $("#navbar").find("ul.main-nav").find('.active').removeClass('active');
                $(baseLI).addClass('active').parents('li').addClass('active');
                stop = true;
            }


            //  console.log(href + ' ' + hhref + ' = ' + document.location.href + ' = ' + str);
            if (!stop && href != '' && href != '#' && (href == str || hhref == str || document.location.href === href ||
                (str == '/' && href == 'main')


                )) {
                console.log('naviLinks use  href');

                $("#navbar").find("ul.main-nav").find('.active').removeClass('active');
                $(this).parents('li:last').addClass('active');

                stop = true;
            }

        }
    });


    // if not set then use breadcrumb urls to set the active menu
    if (!stop) {

        console.log('if not set then use breadcrumb urls to set the active menu');

        $('#breadcrumbs').find('a').each(function () {
            if (!skip) {
                var strUrl = $(this).attr('href');
                if (strUrl != '#' && !stop) {
                    strUrl = strUrl.replace(/^\.\//, '/');

                    naviLinks.each(function () {
                        var href = $(this).attr('href');
                        if (typeof href === 'string') {
                            var hhref = '/' + href;
                            //console.log(strUrl + ' ' + document.location.href + ' = ' + hhref);

                            if (href != '' && href != '#' && (href == strUrl || hhref == strUrl)) {
                                $("#navbar").find("ul.main-nav").find('.active').removeClass('active');
                                $(this).parents('li:last').addClass('active');
                                stop = true;
                            }
                        }
                    });
                }
            }
            else {
                skip = false;
            }
        });
    }


    $('#gui-locales').find('a').each(function () {
        if (!$(this).hasClass('active') && $(this).parents('li').length) {
            $(this).addClass('active');
        }
    });

    // remove megamenu in footer
    $('#customfooter').find('.megamenu-container').parents('li').remove();

    $("#navbar ul.main-nav").find('>li').hover(function () {
        if (isMobile) {
            clearTimeout($.data(this, 'timer'));
            return;
        }
        clearTimeout($.data(this, 'timer'));

        var $subMenu = $(this).children('ul');
        if ($subMenu.length) {
            $(this).addClass('sub');
        }
        $(this).addClass('hover');
        ///  }
        $subMenu.hide().stop(true, true).fadeIn(200);
    }, function () {

        if (isMobile) {
            clearTimeout($.data(this, 'timer'));
            return;
        }


        $.data(this, 'timer', setTimeout($.proxy(function () {
            $(this).removeClass('hover');
            $('.submenu:first,.megamenu-container:first', this).parents('li.hover').removeClass('hover');
            $('.submenu:first,.megamenu-container:first', this).removeClass('hover').stop(true, true).fadeOut(50);
        }, this), 100));
    });


}


function setImageMaxWidth() {
    var locat = document.location.href;
    var hostname = URLParser(locat).getHost();

    var images = $('#main img');
    images.each(function () {
        if ($(this).parent().is('a')) {
            var href = $(this).parent().attr('href');
            var imghostname = URLParser(href).getHost();

            if (imghostname && hostname != imghostname) {
                $(this).unwrap();
            }
        }

        if (!$(this).attr('alt')) {
            $(this).attr('alt', 'Image');
        }
        var isFixed = false;

        if ($(this).width() >= 450 || $(this).attr('width') >= 450 && !$(this).hasClass('block')) {
            var p = $(this).parent().get(0).tagName;
            if ($(this).parent().get(0).tagName.toLowerCase() == 'p') {
                isFixed = true;

                $(this).parent().css({
                    'clear': 'both',
                    'display': 'inline-block',
                    'width': "100%",
                    height: 'auto',
                    margin: 0
                });

                $(this).css({
                    'width': '100%',
                    height: 'auto',
                    marginRight: 0,
                    marginBottom: 5
                });
            }
            else {
                $(this).css({
                    'clear': 'both'
                });
            }
        }

        if ($('body').hasClass('News') || $('body').hasClass('Page') && !$(this).hasClass('block')) {
            if (!$(this).parents('header').length && !$(this).parents('.listview-image').lenght) {

                if ($(this).parent().get(0).tagName.toLowerCase() == 'p' && $(this).parent().text().trim() == '') {
                    $(this).unwrap();
                    if ($(this).next().get(0).tagName.toLowerCase() == 'p' && $(this).parent().text().trim() != '') {
                        $(this).next().prepend($(this));
                    }
                }

                $(this).css({
                    'clear': 'none',
                    'float': 'left',
                    marginRight: (isFixed ? 0 : 5),
                    marginBottom: 5
                }); //.addClass('float-left');
            }
        }
    });
}


/**
 *    Resize Flash Videos
 */
function resizeFlashMovies() {
    $('#main .tub-video').each(function () {
        var parentwidth = parseInt($(this).parent().width());

        var object = $(this).find('object');
        objectWidth = $(object).attr('width');
        objectHeight = $(object).attr('height');

        var maxw = parseInt(parentwidth - 10);
        var origwidth = objectWidth;
        var origheight = objectHeight;
        ratio = parseFloat(origwidth) / parseFloat(origheight);

        newwidth = parseInt(origwidth);
        newheight = parseInt(origheight);
        if (maxw > 0 && maxw > origwidth) {
            newwidth = parseInt(maxw);
            newheight = Math.round(newwidth / ratio);
        }

        $(this).find('object').removeAttr('height').append($('<PARAM NAME="scale" VALUE="exactfit" />'));
        $(this).find('object').attr(
            {
                width: newwidth,
                height: newheight
            }).css(
            {
                width: newwidth,
                height: newheight
            });

    });
}

function br2nl(str) {
    return str.replace(/<br\s*\/?>/mg, "\n");
}
function findSyntaxes() {
    var pres = $('pre');

    if (typeof SyntaxHighlighter == 'undefined' && pres.length > 0) {

        Loader.loadCss('html/js/syntax/styles/shCore.css', 'html/js/syntax/styles/shThemeEclipse.css', function () {
            Loader.require('html/js/syntax/src/shCore.js', 'html/js/syntax/scripts/shBrushXml.js', 'html/js/syntax/scripts/shBrushSql.js', 'html/js/syntax/scripts/shBrushPhp.js', 'html/js/syntax/scripts/shBrushCss.js', function () {
                pres.each(function () {
                    $(this).addClass('auto-links: false');
                    $(this).html($(this).html().replace(/<br\s*\/?>\n?/ig, "\n"));
                });

                delete SyntaxHighlighter.toolbar.items.about;
                SyntaxHighlighter.highlight();
            });
        });

    }
    else if (typeof SyntaxHighlighter != 'undefined' && pres.length > 0) {
        Loader.loadCss('html/js/syntax/styles/shCore.css', 'html/js/syntax/styles/shCoreDefault.css', 'html/js/syntax/styles/shThemeDefault.css', function () {

        });
        pres.each(function () {
            $(this).addClass('auto-links: false');
            $(this).html($(this).html().replace(/<br\s*\/?>/ig, "\n"));
        });

        delete SyntaxHighlighter.toolbar.items.about;
        SyntaxHighlighter.highlight();

    }
}


function addPageTop() {
    var settings = {
            button: '#page-top',
            text: '',
            min: 500,
            fadeIn: 500,
            fadeOut: 300,
            scrollSpeed: 1200,
            easingType: 'easeInOutQuint'
        },
        oldiOS = false,
        oldAndroid = false;
    if (/(iPhone|iPod|iPad)\sOS\s[0-4][_\d]+/i.test(navigator.userAgent))
        oldiOS = true;
    if (/Android\s+([0-2][\.\d]+)/i.test(navigator.userAgent))
        oldAndroid = true;

    var de = document.documentElement;
    var Height = window.innerHeight || (de && de.clientHeight) || document.body.clientHeight;


    settings.min = Height / 2;

    var toTop = $('<div id="' + settings.button.substring(1) + '"/>').addClass('page-top');
    toTop.append('<span></span>');
    toTop.appendTo($('body'));


    $(settings.button).unbind().click(function (e) {
        $('html, body').animate({scrollTop: 0}, settings.scrollSpeed, settings.easingType);
        e.preventDefault();
    });

    $(window).unbind('scroll').scroll(function () {
        var position = $(window).scrollTop();
        if (oldiOS || oldAndroid) {
            $(settings.button).css({
                'position': 'absolute',
                'top': position + $(window).height()
            });
        }
        if (position > settings.min)
            $(settings.button).fadeIn(settings.fadeIn);
        else
            $(settings.button).fadeOut(settings.fadeOut);
    });


    var wrapper = $('.container-main');
    if (wrapper.length === 0) {
        wrapper = $('#container-main .container-main');
    }
    if (wrapper.length) {
        toTop.css({
            left: wrapper.offset().left + wrapper.width()
        });
    }
    if ($('html,body').width() < 580) {
        $('#page-top').hide();
    }


    var tt;

    $(window).unbind('resize').resize(function () {

        clearTimeout(tt);
        if ($([window, document]).width() < 580) {
            $('#page-top').hide();
        } else {
            var wrapper = $('.container-main');
            if (wrapper.length === 0) {
                wrapper = $('#container-main .container-main');
            }

            if (wrapper.length) {

                $('#page-top').css({
                    right: '',
                    left: wrapper.offset().left + wrapper.width()
                });

                $('a.logo').css({
                    backgroundSize: wrapper.outerWidth(true) - 20 + ' 80%'
                });
            }
        }


        // mobile view
        if ($([window, document]).width() < 768) {
            $('#navbar ul.main-nav').hide();
            $("#navbar ul.main-nav ul").addClass('force-show');
            isMobile = true;
        }
        else {
            $("#navbar ul.main-nav").find('.force-show').removeClass('force-show');
            $('#navbar ul.main-nav').show();
            isMobile = false;
        }

        $('div.mobile-nav-container').height($(document).height() - $('footer').outerHeight(true));


        var w = parseInt($(window).width(), 10);
        if (w < 900) {
            $('#page-top').css({
                //bottom: 0 + $(settings.button).outerHeight(true),
                left: '',
                right: 10
            });
        }

        if (w >= 768) {
            $('#main').width('');
        }
        else if (w < 768 && w > 490) {
            $('#main').width($(window).width() - 55);
        }
        else if (w < 490) {
            $('#main').width($(window).width() - 40);
        }

    });

    $([document, window]).trigger('resize');

}


function prettyForm() {
    if (typeof $.fn.selectpicker != 'undefined') {
        $('select').selectpicker({container: 'body'});
    }


    $('legend').each(function () {
        var isInFieldset = false;
        if ($(this).parents('fieldset').length == 1) {
            isInFieldset = true;
        }
        if (isInFieldset) {
            var nextTag = $(this).parents('fieldset').find('input,textarea');
            if (nextTag.length == 1) {
                if (( nextTag.is('input') && nextTag.attr('type') != 'hidden' && nextTag.attr('type') != 'radio' && nextTag.attr('type') != 'checkbox' ) || nextTag.is('textarea')) {
                    $(this).parents('fieldset').addClass('is-pretty');
                    if (!nextTag.attr('placeholder')) {
                        nextTag.attr('placeholder', $(this).text().trim());
                        $(this).hide();
                        $('<div class="pretty-from-spacer"/>').insertAfter(nextTag);

                    }
                }
            }
        }
        else {
            var nextTag = $(this).next();
            if ((nextTag.is('input') && nextTag.attr('type') != 'hidden' && nextTag.attr('type') != 'radio' && nextTag.attr('type') != 'checkbox' ) || nextTag.is('textarea')) {
                if (!nextTag.attr('placeholder')) {
                    nextTag.attr('placeholder', $(this).text().trim());
                    $(this).hide();
                }
            }
        }

    });
}

function Anim(obj, classname) {
    obj.removeClass(classname).addClass(classname);
    var wait = window.setTimeout(function () {
        $(obj).removeClass(classname)
    }, 1300);
}

var logoMoveTimeout, logoMoveTimeout2, logoMoveTimeout3, logoHeight;
function aniLogo() {
    clearInterval(logoMoveTimeout2);
    clearInterval(logoMoveTimeout3);
    clearInterval(logoMoveTimeout);

    $('a.logo').unbind().stop().css('zIndex', '-1').animate({
        top: -36, height: "-=36"
    }, 400, function () {
        $(this).stop().delay(1000).addClass('moving').animate({
            left: 300
        }, 3500, function () {

            var s = this;

            $(s).stop().delay(2000).animate({top: -46, height: "+=36"}, 200, function () {
                $(s).css({zIndex: '1'}).removeClass('moving');

                logoMoveTimeout2 = setTimeout(function () {
                    $(s).css('zIndex', '-1').animate({
                        top: -36, height: "-=36"
                    }, 200, function () {
                        $(s).css({zIndex: '-1'});
                        logoMoveTimeout3 = setTimeout(function () {
                            $(s).stop().addClass('moving').animate({left: 5, top: -36, height: "+=36"}, 2500, function () {
                                $(this).stop().delay(2000).animate({top: -46}, 200, function () {
                                    $(this).removeAttr('style').removeClass('moving');
                                    setTimeout(function () {
                                        logoMoveTimeout = setTimeout(function () {
                                            aniLogo(); // restart animation
                                        }, 5000);
                                    }, 10);
                                });
                            });
                        }, 2000)
                    });
                }, 1000)
            });

        });

    });
}


function bindLythBoxes() {
    if (typeof $.fancybox !== 'undefined') {

        $('a[rel="lightbox"],a[rel="fancybox"]').each(function () {

            if ( !$(this).parents('.asgallery:first').length ) {

                $(this).click(function (e) {
                    e.preventDefault();
                    var link = $(this);
                    var url = $(link).attr('href');
                    var title = $(link).attr('title');
                    var descriptionLayout = (title ? '<strong>' + title + '</strong><br/>' : '');

                    $.fancybox({
                        'padding': 10,
                        'transitionIn': 'elastic',
                        'transitionOut': 'elastic',
                        'easingIn': 'swing',
                        'easingOut': 'swing',
                        'speedIn': 700,
                        'speedOut': 500,
                        'titlePosition': 'over',
                        'titleShow': true,
                        'type': 'image',
                        'href': url,
                        onComplete: function (currentArray, currentIndex, currentOpts) {
                            $("#fancybox-inner").unbind('hover').hover(function () {
                                $("#fancybox-title-over").slideUp(300);
                            }, function () {
                                $("#fancybox-title-over").slideDown(300);
                            });
                        },
                        'titleFormat': function (title, currentArray, currentIndex, currentOpts) {

                            // for content gallery
                            if ($(currentArray[currentIndex]).parents('li:first').find('span.description').length) {
                                var descr = $(currentArray[currentIndex]).parents('li:first').find('span.description').html();
                                descriptionLayout += (descr ? '<p>' + descr + '</p>' : '');
                            }

                            return '<span id="fancybox-title-over">' + descriptionLayout + 'Bild ' + (currentIndex + 1) + ' von ' + currentArray.length + '</span>';
                        }
                    });
                });
            }
        });


        $('.asgallery').find('a[rel="lightbox"],a[rel="fancybox"]').fancybox({
            'padding': 10,
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'easingIn': 'swing',
            'easingOut': 'swing',
            'speedIn': 700,
            'speedOut': 500,
            'titlePosition': 'over',
            'titleShow': true,
            'type': 'image',
           // 'href': url,
            onComplete: function (currentArray, currentIndex, currentOpts) {
                $("#fancybox-inner").unbind('hover').hover(function () {
                    $("#fancybox-title-over").slideUp(300);
                }, function () {
                    $("#fancybox-title-over").slideDown(300);
                });
            },
            'titleFormat': function (title, currentArray, currentIndex, currentOpts) {

                var descriptionLayout = (title ? '<strong>' + title + '</strong><br/>' : '');

                // for content gallery
                if ($(currentArray[currentIndex]).parents('li:first').find('span.description').length) {
                    var descr = $(currentArray[currentIndex]).parents('li:first').find('span.description').html();
                    descriptionLayout += (descr ? '<p>' + descr + '</p>' : '');
                }

                return '<span id="fancybox-title-over">' + descriptionLayout + 'Bild ' + (currentIndex + 1) + ' von ' + currentArray.length + '</span>';
            }
        });





        $('img[data-fancybox="true"]').each(function () {

            if ($(this).attr('data-basefile')) {
                $(this).css({
                    cursor: 'pointer'
                });
                $(this).click(function (e) {
                    e.preventDefault();
                    var link = $(this);
                    var url = $(this).attr('data-basefile');
                    var title = $(link).attr('title') || $(link).attr('alt');
                    var descriptionLayout = (title ? '<strong>' + title + '</strong><br/>' : '');

                    $.fancybox({
                        'padding': 10,
                        'transitionIn': 'elastic',
                        'transitionOut': 'elastic',
                        'easingIn': 'swing',
                        'easingOut': 'swing',
                        'speedIn': 700,
                        'speedOut': 500,
                        'titlePosition': 'over',
                        'titleShow': true,
                        'type': 'image',
                        'href': url,
                        onComplete: function (currentArray, currentIndex, currentOpts) {
                            $("#fancybox-inner").unbind('hover').hover(function () {
                                $("#fancybox-title-over").slideUp(300);
                            }, function () {
                                $("#fancybox-title-over").slideDown(300);
                            });
                        },
                        'titleFormat': function (title, currentArray, currentIndex, currentOpts) {
                            return '<span id="fancybox-title-over">' + descriptionLayout + 'Bild ' + (currentIndex + 1) + ' von ' + currentArray.length + '</span>';
                        }
                    });
                });
            }


        });
    }
}

function bindLoginMenu() {


    var btn = $('.authmenu button', $('#navbar'));
    var form = btn.parents('form:first');


    form.find('ul.submenu').removeClass('submenu');

    form.find('input:text,input:password').unbind().bind('keyup', function (e) {
        if (e.keyCode == 13) {
            btn.trigger('click');
        }
    });


    form.find('input').unbind().bind('focus', function (e) {
        form.find('.error').fadeOut(500, function () {
            $(this).remove();
        });
    });

    btn.unbind().click(function (e) {
        form.find('.error').remove();
        form.parents('ul:first').addClass('force-open').mask('Login...');
        var postData = form.serialize();


        postData += '&cp=auth&action=login';

        //   postData.cp = 'auth';
        //    postData.action = 'login';

        setTimeout(function () {
            $.post('index.php', postData, function (data) {
                form.parents('ul:first').unmask();

                if (responseIsOk(data)) {
                    document.location.href = document.location.href;
                }
                else {
                    form.parents('ul:first').removeClass('force-open');

                    if (data && typeof data.msg != 'undefined') {
                        form.append('<div class="error">' + data.msg + '</div>');
                    }
                    else {
                        form.append('<div class="error">Login error</div>');
                    }
                }
            });
        }, 80);

    });
}


function bindUserMenu() {
    var usermenu = $('#user-menu');
    if (usermenu.length) {
        usermenu.find('li .username').click(function (e) {
            if (!$('#user-menu-container').is(':visible')) {
                $('#user-menu-container').css({
                    left: $(this).parent().offset().left + $(this).parent().width() - $('#user-menu-container').width()
                }).show();
            }
        });

        $(document).click(function (e) {
            if ($('#user-menu-container').is(':visible') && !$(e.target).parents('.username').length && !$(e.target).parents('#user-menu').length) {
                $('#user-menu-container').hide();
            }
        });

        $('#user-menu-container').find('a').click(function (e) {

            if (!$(this).attr('href')) {
                e.preventDefault();
                return false;
            }
            else {
                return true;
            }
        });
    }
}


function initFrontpage() {
    if ($('#container-main').find('.content-menu-content').length == 1) {
        $('#container-main').find('.content-menu-content > div:not(:first)').hide();
        $('#container-main').find('ul.content-menu').find('li:first').addClass('active');
        $('#container-main').find('ul.content-menu').find('li').click(function () {
            var index = $(this).index();
            $(this).parent().find('li.active').removeClass('active');
            $(this).addClass('active');
            $('#container-main').find('.content-menu-content > div:visible').hide();
            $('#container-main').find('.content-menu-content > div:eq(' + index + ')').show();
        });
    }
}



function bindShareLinks() {
    "use strict";

    if (typeof $.fn.modal == 'undefined') {
        return;
    }


    var tpl = '<div id="socialShareModal" class="modal fade">'
        + '<div class="modal-dialog">'
        + '<div class="modal-content">'
        + '<div class="modal-header">'
        + '<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'
        + '<h4 class="modal-title">Modal title</h4>'
        + '</div>'
        + '<div class="modal-body">'
        + '<iframe width="100%" height="100%"></iframe>'
        + '</div>'
        + '<div class="modal-footer">'
        + '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>';

    $('body').append($(tpl));

    $('div.socialShare a').unbind().bind('click', function(e) {
        e.preventDefault();

        var modal = $('#socialShareModal');
        modal.find('iframe').attr('src', $(this).attr('href') +'&output=embed');

        $('#socialShareModal').modal('show');

    });
}


function start() {


    // Remove Empty <p> tags
    $( 'p:empty' ).remove();


    $('body.frontpage div.teaser-label h1,body.frontpage div.teaser-label p').wrap('<div></div>');

    $('body.frontpage div.teaser-image').show().addClass('animated bounceInUp').one( $.support.animation.end, function () {
        $('body.frontpage div.teaser-label div').show().addClass('animated bounceInDown');
    });


    var facts = $('#main div.facts').find('>div');
    facts.each(function(i){
        "use strict";
        $(this).addClass('fact-'+ (i+1));
    })

    facts.show().addClass('animated fadeInDown')

    var rh = $('#right > .inside').outerHeight();
    if (rh > $('#main').height()) {
        $('#main').height(rh)
    }

    var baseSearchWidth = $('#search-bar form input[type="text"]').width();
    $('#search-bar form input[type="text"]').on('focus', function() {
        "use strict";
        $(this).stop().animate({
            width: 250
        }, {
            duration: 300
        });
    }).on('blur', function() {
        "use strict";
        $(this).animate({
            width: baseSearchWidth
        }, {
            duration: 150
        });
    });

    // set logo smaller?
    if (!$('section.teaser').find('img').length && (!$('section.teaser').find('div.inside').length || !$('section.teaser').find('div.inside').text().trim())) {
        var useLogo = false, logo = $('#container-main').find('div.logo:first');
        var logoWrap = $('<div class="logo-wrap"></div>');

        if (logo.is(':visible')) {
            logo.appendTo(logoWrap);
            useLogo = true;
        }

        if ($('#breadcrumbs').length) {
            if (useLogo) {
                logoWrap.insertBefore($('#wrapper nav.breadcrumbs'));
            }
            else {
                $('#container-main').addClass('addspace');
            }
        }
        else {
            if (useLogo) {
                logoWrap.insertBefore($('#container-main'));
            }
            else {
                $('#container-main').addClass('addspace');
            }
        }
    }
    else {
        // $('#container-main').addClass('addspace').find('div.logo:first').remove();
    }

    initFrontpage();


    /*
     if ($('#breadcrumbs .navbar:last').length == 1) {
     $('a.logo').append($('<span>').text($('#breadcrumbs .navbar:last').text()));
     }*/

    if (!$('.teaser div:first').children(':first').length) {
        $('.teaser').hide();
    }
    else {

    }


    if (!$('body').hasClass('News')) {
        $('.columnSet3').find('article.column:eq(2)').after($('<div class="column-line-sep"/>'));
    }

    var mainPatch = $('#right').length;
    mainPatch += $('#left').length;

    if (mainPatch === 0 && !$('#wrapper-border').hasClass('News')) {
        $('#main').width('100%');
    }


    prepareTeaser();
    findSyntaxes();

    prettyForm();


    addPageTop();
    prepareNavigation();
    bindLoginMenu();


    $('div.captcha').each(function () {

        var w = $('<div class="captcha-container"></div>');
        w.insertBefore($(this));
        $(this).appendTo(w);

        var wtext = $('<div class="captcha-text"></div>');
        w.append(wtext);

        wtext.append(w.parent().find('input[type=text]:first'));


    });

    $('#feedlyMiniIcon').remove();

    /**
     * Audio Captcha
     */
    $('a.captcha-audio').removeAttr('href').click(function (e) {
        e.preventDefault();
        captchAudio(this, $(this).attr('data-toggle'));
    });


    $('#toggle-menu').bind('click', function (e) {
        e.preventDefault();
        if ($('.mobile-nav-container').is(':visible')) {
            $('.mobile-nav-container').hide();
            $('#wrapper-border,body').removeClass('small-size');
        }
        else {
            $('.mobile-nav-container').show();
            $('#wrapper-border,body').addClass('small-size');
        }


        $(this).toggleClass('active');

    });


    var Menu = $('#navbar nav');
    var mobileMenuToggle = $('<div>').addClass('mobile-menu-toggle');
    mobileMenuToggle.append('<span><span></span></span>');
    mobileMenuToggle.find('span:first').bind('click', function (e) {
        e.preventDefault();
        if ($(this).parent().next().is(':visible')) {
            $(this).parent().next().hide();
        }
        else {
            $(this).parent().next().show();
        }


        $(this).toggleClass('active');
        return false;
    });

    Menu.prepend(mobileMenuToggle);


    if ($([document, window]).width() < 768) {
        $("#navbar ul.main-nav ul").addClass('force-show');
        isMobile = true;
    }

    bindUserMenu();





    // Make navigation smaller on scroll
    jQuery(document).scroll(function()
    {
        if ($(window).width() > 767) {
            var top = $(window).scrollTop()
            if(top > 24)
            {
                $('body').addClass("header-scrolled");
            }
        }
    });

    jQuery(document).scroll(function()
    {
        var top = $(window).scrollTop()
        if(top < 24 || $(window).width() <= 767)
        {
            $('body').removeClass("header-scrolled");
        }
    });

    if ($('textarea').length) {
        $('textarea').tabby();
    }

    if (typeof skinUrl != 'undefined') {
        $('#main').append($('<img src="' + skinUrl + 'img/loading_o.gif" class="loading-img-small loading-indicator" />').css({visibility: 'hidden'}));
    }

    resizeFlashMovies();
    bindShareLinks();

    setTimeout(function () {


        if (!$('#wrapper').hasClass('Plugin')) {
            setImageMaxWidth();
        }
        setTimeout(function () {
            bindLythBoxes();
        }, 500);
        $('div.mobile-nav-container').height($(document).height() - $('footer').outerHeight(true));
        $(window).trigger('resize');


    }, 120);

}

$(document).ajaxComplete(function (event, xhr, settings) {

    if (typeof xhr.responseJSON == 'object') {
        var data = xhr.responseJSON;
        if (typeof data.csrfToken === 'string') {
            token = data.csrfToken;
            $('input[name=token],input[name=token]').val(data.csrfToken);
        }
    }
});

$(document).ajaxSuccess(function (event, xhr, settings) {
    // console.log(xhr);

    if (typeof xhr.responseJSON == 'object') {
        var data = xhr.responseJSON;
        if (typeof data.csrfToken === 'string') {
            token = data.csrfToken;
            $('input[name=token],input[name=token]').val(data.csrfToken);
        }
    }
});


$(function () {

    start();
    return;

    (function ($) {


        $.fn.fadeInsert = function (options) {
            console.log(options);
            //the option is an array : {html:the ajax html, scripts: the scripts that already are in the html, customData:any data you associated to this state during navigate} 
            var that = $(this);


            that.fadeOut(200, function () {
                var style = false, meta = false, link = false, script = false, h = $(options.head);
                // h.find('script[src*="public/simg/hell/js/ajaxnavi.js"]').remove();
                //h.find('script[src*="public/html/js/backend/modernizr.js"]').remove();

                h.each(function () {
                    if ($(this).is('style')) {
                        style = true;
                    }
                    if ($(this).is('link')) {
                        link = true;
                    }
                    if ($(this).is('meta')) {
                        meta = true;
                    }
                    /*
                     if ($(this).is('script')) {

                     var src = $(this).attr('src');
                     if ((src && !$('head').find('script[src="' + src + '"]').length) || $(this).html()) {
                     script = true;
                     }


                     }*/
                });

                if (style || meta || link || script) {
                    $('head').find('style,meta,link').addClass('removeit');


                    h.each(function () {


                        if ($(this).is('title')) {
                            $('head').find('title').replaceWith($(this));
                        }

                        if ($(this).is('style')) {
                            $('head').append($(this));
                        }
                        if ($(this).is('meta')) {
                            $('head').append($(this));
                        }
                        if ($(this).is('link')) {
                            $('head').append($(this));
                        }
                        /*
                         if ($(this).is('script')) {

                         var src = $(this).attr('src');
                         if (src && !$('head').find('script[src="' + src + '"]').length) {
                         if ($('head').find('script[src*="' + src + '"]').length) {
                         $('head').find('script[src*="' + src + '"]').remove();
                         }
                         $('body').prepend($(this));
                         }
                         }
                         */
                    });

                    $('head').find('.removeit').remove();

                }

                var html = $(options.html);
                html.filter('script').each(function () {
                    $(this).remove();
                });

                that.html(html);
                that.append('<!-- Scripts -->');

                var scripts = $(options.scripts), loaded = 0, getScripts = 0, inlineScripts = 0;
                if (scripts.length) {

                    scripts.filter('script[src]').each(function (i) {
                        var src = $(this).attr('src');
                        if (!$('head').find('script[src="' + src + '"]').length) {
                            getScripts++;
                        }
                    });

                    scripts.filter('script').each(function (i) {
                        var src = $(this).attr('src');
                        if (typeof src == 'undefined') {
                            inlineScripts++;
                        }
                    });


                    if (getScripts) {

                        scripts.filter('script[src]').each(function (i) {

                            var src = $(this).attr('src');
                            if (src && !$('head').find('script[src="' + src + '"]').length) {
                                $('head').append(this);
                                $.getScript(src, function () {

                                    if (i >= getScripts - 1) {


                                        var loadedInline = 0;
                                        scripts.filter('script').each(function () {
                                            var src = $(this).attr('src');
                                            if (typeof src === 'undefined') {
                                                loadedInline++;
                                                $(this).appendTo($('body'));

                                                if (loadedInline >= inlineScripts - 1) {
                                                    setTimeout(function () {
                                                        //        start();
                                                    }, 50);
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }
                    else {

                        var loadedInline = 0;
                        scripts.filter('script').each(function () {
                            var src = $(this).attr('src');
                            if (typeof src === 'undefined') {

                                $(this).appendTo($('body'));
                                loadedInline++;
                                if (loadedInline >= inlineScripts - 1) {
                                    setTimeout(function () {
                                        //     start();
                                    }, 50);
                                }
                            }
                        });
                    }

                }


                $('a').each(function () {
                    var href = $(this).attr('href');
                    if (href && !$(this).hasClass('noajaxLink') && !$(this).hasClass('ajaxLink')) {
                        if ($(this).attr('target') === '_blank' || $(this).attr('onclick') || $(this).hasClass('no-ajax') || href.match(/(javascript|mailto)/i)) {
                            $(this).addClass('noajaxLink');
                        }
                        else {
                            $(this).attr('ajax-insert', 'fadeInsert').addClass('ajaxLink');
                        }
                    }
                });

                that.fadeIn(150, function () {
                    that.trigger({type: "finishrefreshinsert"});
                    setTimeout(function () {
                        start();
                    }, 50);
                });
            });
            return this;
        };
    })(jQuery);

    if (typeof jQuery.navigate === 'undefined') {
        $.getScript('public/html/js/backend/modernizr.js', function () {
            $.getScript('public/simg/hell/js/ajaxnavi.js', function () {

                $('body').find('a').each(function () {
                    var href = $(this).attr('href');
                    if (href && !$(this).hasClass('noajaxLink') && !$(this).hasClass('ajaxLink')) {


                        if ($(this).attr('target') === '_blank' || $(this).attr('onclick') || $(this).hasClass('no-ajax') || href.match(/(javascript|mailto)/i)) {
                            $(this).addClass('noajaxLink');
                        }
                        else {
                            $(this).attr('ajax-insert', 'fadeInsert').addClass('ajaxLink');

                        }
                    }
                });

                $.navigate.init({
                    ajaxLinks: 'a.ajaxLink',
                    discreteLinks: 'a:not(.noajaxLink)[rel!="external"][target!="_blank"]'
                });
            });
        });
    }
    else {

        $('a').each(function () {
            var href = $(this).attr('href');
            if (href && !$(this).hasClass('noajaxLink') && !$(this).hasClass('ajaxLink')) {


                if ($(this).attr('target') === '_blank' || $(this).attr('onclick') || $(this).hasClass('no-ajax') || href.match(/(javascript|mailto)/i)) {
                    $(this).addClass('noajaxLink');
                }
                else {
                    $(this).attr('ajax-insert', 'fadeInsert').addClass('ajaxLink');

                }
            }
        });


        $.navigate.init({
            ajaxLinks: 'a.ajaxLink',
            discreteLinks: 'a:not(.noajaxLink)[rel!="external"][target!="_blank"]'
        });
    }
});