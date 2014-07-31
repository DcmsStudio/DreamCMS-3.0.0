
function lib_bwcheck() { //Browsercheck (needed)
    this.ver = navigator.appVersion
    this.agent = navigator.userAgent
    this.dom = $ ? 1 : 0
    this.opera5 = this.agent.indexOf("Opera 5") > -1
    this.ie5 = (this.ver.indexOf("MSIE 5") > -1 && this.dom && !this.opera5) ? 1 : 0;
    this.ie6 = (this.ver.indexOf("MSIE 6") > -1 && this.dom && !this.opera5) ? 1 : 0;
    this.ie4 = (document.all && !this.dom && !this.opera5) ? 1 : 0;
    this.ie = this.ie4 || this.ie5 || this.ie6
    this.mac = this.agent.indexOf("Mac") > -1
    this.ns6 = (this.dom && parseInt(this.ver) >= 5) ? 1 : 0;
    this.ns4 = (document.layers && !this.dom) ? 1 : 0;
    this.bw = (this.ie6 || this.ie5 || this.ie4 || this.ns4 || this.ns6 || this.opera5)
    return this;
}
var lastClickTab = 0;
var oBg = null;
var oMenu = null;
var oArrowRight = null;

// Div class tab
var cssnormal_tab = 'defTab';
var csshover_tab = 'cptab_hover';
var cssactive_tab = 'actTab';


var bw = new lib_bwcheck();
var px = bw.ns4 || window.opera ? "" : "px";
var sLeft = 0;         	//The left placement of the menu
var sTop = 0;        	//The top placement of the menu
var sMenuheight = 20;  	//The height of the menu
var sArrowwidth = 20;  	//Width of the arrows
var sScrollspeed = 20; 	//Scroll speed: (in milliseconds, change this one and the next variable to change the speed)
var sScrollPx = 5;     	//Pixels to scroll per timeout.
var sScrollExtra = 0; 	//Extra speed to scroll onmousedown (pixels)
var pageWidth = 0;
var all_tab_width = 0;
var tim = 0;
var noScroll = true;
var sScrollPxOriginal = sScrollPx;
var ctab = 0;

if (!current_panel)
{
    var current_panel = 0;
}


function actTab(id)
{
    active_tab(id);
}

function active_tab(tab)
{
    for (var i = 0; i < total_tabs; i++)
    {
        if (tab != i)
        {
            $('#maincontent #tc' + i).hide().css('opacity', '1');
            if (current_panel == i)
            {
                $('#maincontent #tab_' + current_panel).removeClass(cssactive_tab).addClass(cssnormal_tab);
            }
        }
    }

    var cur = $('#maincontent #tc' + tab);

    if ($('#maincontent #tc' + tab) && lastClickTab != tab)
    {
        $('#maincontent #tab_' + tab).removeClass(cssnormal_tab).addClass(cssactive_tab);
        lastClickTab = tab;
        $('#maincontent #tc' + current_panel).hide();
        $('#maincontent #tc' + tab).show();
    }
    else
    {
        lastClickTab = tab;
        $('#maincontent #tab_' + tab).removeClass(cssnormal_tab).addClass(cssactive_tab);
        $('#maincontent #tc' + current_panel).hide();
        $('#maincontent #tc' + tab).show();
    }

    current_panel = tab;
}


function hide_tab(htab)
{
    if ($('#maincontent tc' + htab))
    {
        $('#maincontent #tc' + htab).slideToggle();
    }
}

function hover_tab(hovertab) {

    if (hovertab == current_panel) {
    }
    else {
        if ($('#maincontent #tc' + hovertab))
        {
            $('#maincontent #tab_' + hovertab).addClass(csshover_tab);
        }
    }
}

function out_tab(dtab) {

    if (dtab == current_panel)
    {
        if ($('#maincontent #tc' + dtab))
        {
            $('#maincontent #tab_' + dtab).removeClass(cssnormal_tab);
            $('#maincontent #tab_' + dtab).addClass(cssactive_tab);
        }
    }
    else
    {
        if ($('#maincontent #l' + dtab))
        {
            $('#maincontent #tab_' + dtab).removeClass(cssactive_tab);
            $('#maincontent #tab_' + dtab).addClass(cssnormal_tab);
        }
    }
}



function init_tabs()
{
    // if ( !$('#tab_0').text() ) return;
    total_tabs = $('#maincontent #divBg #divMenu').find('li').length;
    window.onresize = sideInit;

    if (!ctab)
        ctab = 0;
    if (current_panel != ctab)
    {
        ctab = current_panel;
    }

    $('#maincontent .tcon').hide();

    for (var i = 0; i < total_tabs; i++)
    {

        if ($('#maincontent #tab_' + i).hasClass(cssactive_tab))
        {
            $('#maincontent #tab_' + i).removeClass(cssnormal_tab).addClass(cssactive_tab);
            $('#maincontent #tc' + i).addClass('tcon').show();
        }
        else
        {
            if (ctab != i)
            {
                $('#maincontent #tab_' + i).removeClass(cssactive_tab).addClass(cssnormal_tab);
                $('#maincontent #tc' + i).hide().addClass('tcon').css('opacity', '1');

            }
            else
            {
                $('#maincontent #tab_' + i).removeClass(cssnormal_tab).addClass(cssactive_tab);
                $('#maincontent #tc' + i).addClass('tcon').show();
            }
        }

        $('#maincontent #tab_' + i).click(function() {
            id = $(this).attr('id');
            id = id.replace(/tab_/, '');
            active_tab(id);
        });
    }

    //sideInit();
    //$('.tabHeader').css({'width': $('.tabHeader').parents('.tabcontainer:first').width() + 'px'});
    //$('.tabHeader ul.tabbedMenu').css({'width': $('.tabHeader').parents('.tabcontainer:first').width() - (42) + 'px'});
    $('.tabHeader ul.tabbedMenu').taboverflow();
}

function getWindowWidth(win) {
    if (win == undefined)
        win = window;
    if (win.innerWidth) {
        return win.innerWidth;
    }
    else {
        if (win.document.documentElement && win.document.documentElement.clientWidth) {
            return win.document.documentElement.clientWidth;
        }
        return win.document.body.offsetWidth;
    }
}

function getWindowHeight(win) {
    if (win == undefined)
        win = window;
    if (win.innerHeight) {
        return win.innerHeight;
    }
    else {
        if (win.document.documentElement && win.document.documentElement.clientHeight) {
            return win.document.documentElement.clientHeight;
        }
        return win.document.body.offsetHeight;
    }
}



function makeObj(obj, nest, menu)
{
    nest = (!nest) ? '' : 'document.' + nest + '.';
    this.elm = $('#maincontent #' + obj)[0];
    if (this.elm)
    {
        this.css = this.elm.style;
        var offset = $('#maincontent #' + obj).offset();

        this.scrollWidth = bw.ns4 ? this.css.document.width : this.elm.offsetWidth;
        this.x = bw.ns4 ? this.css.left : this.elm.offsetLeft;
        this.y = bw.ns4 ? this.css.top : this.elm.offsetTop;
    }

    this.moveBy = b_moveBy;
    this.moveIt = b_moveIt;
    this.clipTo = b_clipTo;

    return this;
}


function mLeft()
{
    if (!noScroll && oMenu.x < sArrowwidth)
    {
        oMenu.moveBy(sScrollPx, 0);
        tim = setTimeout("mLeft()", sScrollspeed);
    }
}

function mRight()
{
    if (!noScroll && oMenu.x > pageWidth - all_tab_width + sArrowwidth)
    {
        oMenu.moveBy(-sScrollPx, 0);
        tim = setTimeout("mRight()", sScrollspeed);
    }
}

function noMove()
{
    clearTimeout(tim);
    noScroll = true;
    sScrollPx = sScrollPxOriginal;
}

function b_moveIt(x, y) {
    if (x != null) {
        this.x = x;
        this.css.left = this.x + px;
    }
    if (y != null) {
        this.y = y;
        this.css.top = this.y + px;
    }
}

function b_moveBy(x, y) {
    this.x = this.x + x;
    this.y = this.y + y;
    this.css.left = this.x + 'px';
    this.css.top = this.y + 'px';
}

function b_clipTo(t, r, b, l) {
    if (bw.ns4)
    {
        this.css.clip.top = t;
        this.css.clip.right = r;
        this.css.clip.bottom = b;
        this.css.clip.left = l;
    }
    else
    {
        this.css.clip = "rect(" + t + "px " + r + "px " + b + "px " + l + "px)";
    }
}



function sideInit0()
{
    var pageWidth = $('#maincontent').innerWidth();


    /*
     //Making the objects...
     oBg = new makeObj('divBg');
     oMenu = new makeObj('divMenu','divBg',1);
     oArrowRight = new makeObj('divArrowRight','divBg');
     
     all_tab_width = 0;	// Standart end width
     for (var i=0; i<total_tabs; i++)
     {
     all_tab_width += $('#tab_'+i).get(0).offsetWidth;
     }
     
     all_tab_width += (2*(sArrowwidth));
     pageWidth = pageWidth ;
     
     if (pageWidth > all_tab_width)
     {
     $('#maincontent #divArrowLeft').hide();
     $('#maincontent #divArrowRight').hide();
     
     $('#maincontent #divArrowRight').css('overflow','hidden');
     $('#maincontent #divArrowRight').css('overflow','hidden');
     
     $('#maincontent #divBg').css('width','100%');
     $('#maincontent #divBg').css('overflow','hidden');
     
     $('#maincontent #divMenu').css('width','100%');
     $('#maincontent #divMenu').css('left','0px');
     }
     else
     {
     $('#maincontent #divArrowLeft').show();
     $('#maincontent #divArrowRight').css({
     'left': '20px'
     }).show();
     
     $('#maincontent #divBg').css('width',(all_tab_width)+'px');
     $('#maincontent #divMenu').css('left','0px');
     $('#maincontent #divMenu').css('width',(all_tab_width)+'px');
     
     oBg.moveIt(sLeft,sTop);
     oMenu.moveIt(sArrowwidth,null);
     oArrowRight.moveIt(pageWidth-( sArrowwidth+4),null);
     
     oBg.css.overflow = "hidden";
     oMenu.css.position = "relative";
     oBg.css.width = (pageWidth-1)+px;
     oBg.clipTo(0,pageWidth,sMenuheight,0);
     oBg.css.visibility = "visible";
     }
     
     $('#maincontent #divArrowRight').css({
     'left': '20px'
     }).show();
     */
}

$(document).ready(function() {
    setTimeout(init_tabs, 150);
});











function sideInit()
{
    return;

    var pageWidth = $('#maincontent').innerWidth();

    all_tab_width = 0;	// Standart end width
    for (var i = 0; i < total_tabs; i++)
    {
        all_tab_width += $('#tab_' + i).outerWidth();
    }

    //all_tab_width += (2*( parseInt( $('#divArrowLeft').width() ) ));
    pageWidth = pageWidth;

    /*
     $('#maincontent #divBg').css({'width': $('.tabs_bgfooter').width() + 'px', 'overflow': 'hidden'});
     $('#maincontent #divMenu').css({'left': '0px'});
     $('#maincontent #divMenu').css('width', all_tab_width + 'px');
     
     
     
     
     // Enable or leave the keys
     
     if (all_tab_width > $('#maincontent #divBg').width()) {
     // enable the buttons
     $('#maincontent #divArrowLeft').show();
     $('#divArrowRight').show();
     $('#divMenu').css({'margin-left': $('#divArrowLeft').width() + 'px'});
     }
     else
     {
     //  $('#divArrowLeft').hide();
     //  $('#divArrowRight').hide();
     }
     
     
     
     $("#divArrowRight").click(function() {
     //Remove the exist selector
     //Set the width to the widest of either
     var div = $(this)
     var maxoffset = $('#divMenu li:last').width() + $('#divMenu li:last').offset().left;
     var offset = Math.abs(parseInt($('#divMenu div ul').css('marginLeft')));
     var diff = $("#divMenu ul").width();
     //alert('maxoffset:'+maxoffset + ' offset:'+offset +' offset+diff:'+ (offset + diff) );
     if (offset >= maxoffset)
     {
     return;
     }
     else if (offset + diff >= maxoffset)
     {
     diff = maxoffset - offset;
     // Hide this
     $(this).css('visibility', 'hidden');
     }
     
     // enable the other
     $('#divArrowLeft').css('visibility', 'visible');
     
     $("#divMenu div ul").animate({
     marginLeft: "-" + diff
     }, 400, 'swing');
     });
     
     $("#divArrowLeft").click(function() {
     
     var offset = Math.abs(parseInt($('ul', this.parentNode).css('marginLeft')));
     var diff = $('div', this.parentNode).width();
     if (offset <= 0)
     return;
     else if (offset - diff <= 0) {
     $(this).css('visibility', 'hidden');
     diff = offset;
     }
     $('#divArrowRight', this.parentNode).css('visibility', 'visible');
     
     $("ul", $(this).parent()).animate({
     marginLeft: '+=' + diff
     }, 400, 'swing');
     });
     
     
     */

}









