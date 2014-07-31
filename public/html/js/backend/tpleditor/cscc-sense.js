/* CSCC-Sense - the dictionary format for CSCC suggestions
 *
 * Written in 2010 by Martin Kool (@mrtnkl) of Q42 (@q42) for Quplo (@quplo).
 *
 * This format is exremely simple. It allows you to specify what tags you want,
 * what attributes are in that tag and the possible values per attribute.
 *
 * The order in which you write stuff is the order in which it is presented.
 * This is intended behavior, as it speeds up your typing.
 *
 * There's a simple mechanism in place to re-use attribute sets, so you don't
 * have to overspecify your elements, tags and values. Every return value can
 * be an javascript function, which allows you to return results dynamically,
 * based on whatever you prefer.
 *
 * For example, in our online HTML prototyping environment Quplo (http://quplo.com)
 * we offer an xml / html hybrid format which has <layout> and <page> tags. 
 * The <page> tag has a layout attribute (<page layout="foo">), and its values are
 * generated on the fly based on the layouts that you have written.
 */

// csccSense is the dictionary for suggesting tags, attributes and values

var csccSense = {
    csccObj: null,
    cssDictionary: {
    },
    xmlDictionary: {
    },
    // this is just my custom array of elements that have common attributes and nothing more
    commonElements: "div,p,span,br,hr,h1,h2,h3,h4,h5,h6,blockquote,code,ol,ul,li,fieldset,legend,em,strong,em,dl,dd,dt,pre,q,small,big,sup,sub,thead,tbody,tfoot,tr,th,center".split(","),
    // autoSelfClosers is taken directly from CodeMirror parsexml.js
    autoSelfClosers: "br,img,hr,link,input,meta,col,frame,base,area".split(","),
    isSelfClose: function(tagName)
    {
        for (var i = 0, l = csccSense.autoSelfClosers.length; i < l; i++)
        {
            if (csccSense.autoSelfClosers[i] == tagName)
                return true;
        }
        return false;
    },
    // This is a simple helper function, and you can create as many as you want.
    // In this case, it simply adds id, class, style and title as attributes, to the
    // given set of custom attributes that you pass in the obj parameter
    commonAttributes: function(obj)
    {
        var result =
                {
                };
        // if you pass custom attributes, add them first so they show up first
        if (obj)
            for (var n in obj)
                result[n] = obj[n];
        // second, add the common attributes
        for (var n in {
        "id": 2,
                "class": 2,
                "style": 2,
                "title": 2
        })
            result[n] = 2;
        // return the function that results the results
        return new
                function()
                {
                    // did you know that you can dynamically check for some arbitrary condition,
                    // and return 0 in order for this resultset NOT to show up?
                    return result;
                }
    },
    // upon initialization, fill the dictionary
    init: function(csccObj)
    {
        this.csccObj = csccObj;

        if (top.dcms_selfclosetags)
        {
            var arr = top.dcms_selfclosetags.split(",");

            for (var i = 0; i < arr.length; i++) {
                this.autoSelfClosers.push(arr[i]);
            }
        }

        this.cssDictionary =
                {
                    "background": function(line)
                    {
                        return csccSense.getMedia(line, true);
                    },
                    "background-color": {
                        "#|": 2,
                        "transparent": 2
                    },
                    "background-image": function(line)
                    {
                        return csccSense.getMedia(line, true);
                    },
                    "background-repeat": 1,
                    "background-position": 1,
                    "background-attachment": {
                        "scroll": 2,
                        "fixed": 2
                    },
                    "border": {
                        "solid |": 2,
                        "dashed |": 2,
                        "dotted |": 2
                    },
                    "border-top": 1,
                    "border-right": 1,
                    "border-bottom": 1,
                    "border-left": 1,
                    "border-color": 1,
                    "border-width": 1,
                    "border-style": 1,
                    "border-spacing": 1,
                    "border-collapse": {
                        "collapse": 2,
                        "separate": 2
                    },
                    "bottom": 1,
                    "clear": {
                        "left": 2,
                        "right": 2,
                        "both": 2,
                        "none": 2
                    },
                    "clip": 1,
                    "color": {
                        "#|": 2,
                        "transparent": 2
                    },
                    "content": 1,
                    "cursor": {
                        "default": 2,
                        "pointer": 2,
                        "move": 2,
                        "text": 2,
                        "wait": 2,
                        "help": 2,
                        "progress": 2,
                        "n-resize": 2,
                        "ne-resize": 2,
                        "e-resize": 2,
                        "se-resize": 2,
                        "s-resize": 2,
                        "sw-resize": 2,
                        "w-resize": 2,
                        "nw-resize": 2
                    },
                    "display": {
                        "none": 2,
                        "block": 2,
                        "inline": 2,
                        "inline-block": 2,
                        "table-cell": 2
                    },
                    "empty-cells": {
                        "show": 2,
                        "hide": 2
                    },
                    "float": {
                        "left": 2,
                        "right": 2,
                        "none": 2
                    },
                    "font-family": {
                        "Arial": 2,
                        "Comic Sans MS": 2,
                        "Consolas": 2,
                        "Courier New": 2,
                        "Courier": 2,
                        "Georgia": 2,
                        "Monospace": 2,
                        "Sans-Serif": 2,
                        "Segoe UI": 2,
                        "Tahoma": 2,
                        "Times New Roman": 2,
                        "Trebuchet MS": 2,
                        "Verdana": 2
                    },
                    "font-size": 1,
                    "font-weight": {
                        "bold": 2,
                        "normal": 2
                    },
                    "font-style": {
                        "italic": 2,
                        "normal": 2
                    },
                    "font-variant": {
                        "normal": 2,
                        "small-caps": 2
                    },
                    "font": 1,
                    "height": 1,
                    "left": 1,
                    "letter-spacing": {
                        "normal": 2
                    },
                    "line-height": {
                        "normal": 2
                    },
                    "list-style": 1,
                    "list-style-image": 1,
                    "list-style-position": 1,
                    "list-style-type": {
                        "none": 2,
                        "disc": 2,
                        "circle": 2,
                        "square": 2,
                        "decimal": 2,
                        "decimal-leading-zero": 2,
                        "lower-roman": 2,
                        "upper-roman": 2,
                        "lower-greek": 2,
                        "lower-latin": 2,
                        "upper-latin": 2,
                        "georgian": 2,
                        "lower-alpha": 2,
                        "upper-alpha": 2
                    },
                    "margin": 1,
                    "margin-right": 1,
                    "margin-left": 1,
                    "margin-top": 1,
                    "margin-bottom": 1,
                    "max-height": 1,
                    "max-width": 1,
                    "min-height": 1,
                    "min-width": 1,
                    "outline": 1,
                    "outline-color": 1,
                    "outline-style": 1,
                    "outline-width": 1,
                    "overflow": {
                        "hidden": 2,
                        "visible": 2,
                        "auto": 2,
                        "scroll": 2
                    },
                    "overflow-x": {
                        "hidden": 2,
                        "visible": 2,
                        "auto": 2,
                        "scroll": 2
                    },
                    "overflow-y": {
                        "hidden": 2,
                        "visible": 2,
                        "auto": 2,
                        "scroll": 2
                    },
                    "padding": 1,
                    "padding-top": 1,
                    "padding-right": 1,
                    "padding-bottom": 1,
                    "padding-left": 1,
                    "page-break-after": {
                        "auto": 2,
                        "always": 2,
                        "avoid": 2,
                        "left": 2,
                        "right": 2
                    },
                    "page-break-before": {
                        "auto": 2,
                        "always": 2,
                        "avoid": 2,
                        "left": 2,
                        "right": 2
                    },
                    "page-break-inside": 1,
                    "position": {
                        "absolute": 2,
                        "relative": 2,
                        "fixed": 2,
                        "static": 2
                    },
                    "right": 1,
                    "table-layout": {
                        "fixed": 2,
                        "auto": 2
                    },
                    "text-decoration": {
                        "none": 2,
                        "underline": 2,
                        "line-through": 2,
                        "blink": 2
                    },
                    "text-align": {
                        "left": 2,
                        "right": 2,
                        "center": 2,
                        "justify": 2
                    },
                    "text-indent": 1,
                    "text-transform": {
                        "capitalize": 2,
                        "uppercase": 2,
                        "lowercase": 2,
                        "none": 2
                    },
                    "top": 1,
                    "vertical-align": {
                        "top": 2,
                        "bottom": 2
                    },
                    "visibility": {
                        "hidden": 2,
                        "visible": 2
                    },
                    "white-space": {
                        "nowrap": 2,
                        "normal": 2,
                        "pre": 2,
                        "pre-line": 2,
                        "pre-wrap": 2
                    },
                    "width": 1,
                    "word-spacing": {
                        "normal": 2
                    },
                    "z-index": 1,
                    // opacity
                    "opacity": 1,
                    "opacity": 1,
                            "filter": {
                        "alpha(opacity=|100)": 2
                    },
                    "text-shadow": {
                        "|2px 2px 2px #777": 2
                    },
                    "text-overflow": {
                        "ellipsis-word": 2,
                        "clip": 2,
                        "ellipsis": 2
                    },
                    // border radius
                    "border-radius": 1,
                    "-moz-border-radius": 1,
                    "-moz-border-radius-topright": 1,
                    "-moz-border-radius-bottomright": 1,
                    "-moz-border-radius-topleft": 1,
                    "-moz-border-radius-bottomleft": 1,
                    "-webkit-border-radius": 1,
                    "-webkit-border-top-right-radius": 1,
                    "-webkit-border-top-left-radius": 1,
                    "-webkit-border-bottom-right-radius": 1,
                    "-webkit-border-bottom-left-radius": 1,
                    // transitions
                    "transition": {
                        "all .3s ease-out": 2
                    },
                    "-webkit-transition": {
                        "all .3s ease-out": 2
                    },
                    "-moz-transition": {
                        "all .3s ease-out": 2
                    },
                    // dropshadows
                    "-moz-box-shadow": 1,
                    "-webkit-box-shadow": 1,
                    // transformations
                    "transform": {
                        "rotate(|0deg)": 2,
                        "skew(|0deg)": 2
                    },
                    "-moz-transform": {
                        "rotate(|0deg)": 2,
                        "skew(|0deg)": 2
                    },
                    "-webkit-transform": {
                        "rotate(|0deg)": 2,
                        "skew(|0deg)": 2
                    }
                };

        // tags = 1, attributes = 2, values = 3
        this.xmlDictionary =
                {
                    // quplo specific tags
                    "page": {
                        "url": 2,
                        "type": {
                            "html5": 3,
                            "xhtml": 3,
                            "html": 3,
                            "css": 3,
                            "js": 3,
                            "text": 3,
                            "xml": 3,
                            "less": 3
                        },
                        "layout": function()
                        {
                            var layouts = csccSense.getLayouts();
                            // return -1 when no layouts were found, so no intellisense is shown!
                            return (layouts == 2) ? -1 : layouts;
                        },
                        "method": {
                            "get": 3,
                            "post": 3
                        }
                    },
                    "layout": {
                        "name": 2,
                        "type": {
                            "html": 3,
                            "html5": 3,
                            "css": 3,
                            "js": 3,
                            "text": 3,
                            "xml": 3
                        }
                    },
                    "part": {
                        "name": function(line)
                        {
                            return csccSense.getPartNames(line);
                        }
                    },
                    "var": {
                        "name": 2,
                        "value": 2
                    },
                    "write": {
                        "value": 2,
                        "part": function()
                        {
                            return csccSense.getParts();
                        },
                        "xml": 2
                    },
                    "do": {
                        "action": {
                            "redirect('/|somewhere')": 3,
                            "set('|name', 'value')": 3,
                            "login()": 3,
                            "logout()": 3
                        }
                    },
                    "what": {
                        "if": 2
                    },
                    "else": 1,
                    "for": {
                        "each": 2,
                        "orderby": 2,
                        "dir": {
                            "ascending": 3,
                            "descending": 3
                        }
                    },
                    // tags should return 1
                    "html": 1,
                    "head": 1,
                    // but if they have some attributes to suggest, they don't need to return 1
                    // and simply return an object with those attributes, returning 2
                    "link": {
                        // however, this attribute wants to return values, so again it returns an
                        // object, containing values that are marked as 3
                        "type": {
                            "text/css": 3,
                            "image/png": 3,
                            "image/jpeg": 3,
                            "image/gif": 3
                        },
                        "rel": {
                            "stylesheet": 3,
                            "icon": 3
                        },
                        "href": function(line)
                        {
                            return csccSense.getStyleSheets(line);
                        },
                        "media": {
                            "all": 3,
                            "screen": 3,
                            "print": 3
                        }
                    },
                    "script": {
                        "type": {
                            "text/javascript": 3
                        },
                        "src": function(line)
                        {
                            return csccSense.getScripts(line);
                        }

                    },
                    "title": 1,
                    "style": {
                        "type": {
                            "text/css": 3
                        },
                        "media": {
                            "all": 3,
                            "screen": 3,
                            "print": 3
                        }
                    },
                    "meta": {
                        "name": {
                            "description": 3,
                            "keywords": 3
                        },
                        "content": {
                            "text/html; charset=UTF-8": 3
                        },
                        "http-equiv": {
                            "content-type": 3
                        }
                    },
                    // body returns all common attributes, but shows "onload" as first suggestion
                    "body": csccSense.commonAttributes(
                            {
                                "onload": 2
                            }),
                    "a": csccSense.commonAttributes(
                            {
                                "href": 2,
                                "target": {
                                    "_blank": 3,
                                    "top": 3
                                }
                            }),
                    "img": csccSense.commonAttributes(
                            {
                                "src": function(line)
                                {
                                    return csccSense.getMedia(line);
                                },
                                "alt": 2,
                                "width": 2,
                                "height": 2
                            }),
                    "form": csccSense.commonAttributes(
                            {
                                "method": {
                                    "get": 3,
                                    "post": 3
                                },
                                "action": 2,
                                "enctype": {
                                    "multipart/form-data": 3,
                                    "application/x-www-form-urlencoded": 3
                                },
                                "onsubmit": 2
                            }),
                    "input": csccSense.commonAttributes(
                            {
                                "type": {
                                    "text": 3,
                                    "password": 3,
                                    "hidden": 3,
                                    "checkbox": 3,
                                    "submit": 3,
                                    "radio": 3,
                                    "file": 3,
                                    "button": 3,
                                    "reset": 3,
                                    "image": 3
                                },
                                "name": 2,
                                "value": 2,
                                "checked": {
                                    "checked": 3
                                },
                                "maxlength": 2,
                                "disabled": {
                                    "disabled": 3
                                },
                                "readonly": {
                                    "readonly": 3
                                }
                            }),
                    "select": csccSense.commonAttributes(
                            {
                                "name": 2,
                                "size": 2,
                                "multiple": {
                                    "multiple": 3
                                },
                                "disabled": {
                                    "disabled": 3
                                },
                                "readonly": {
                                    "readonly": 3
                                }
                            }),
                    "option": {
                        "value": 2,
                        "selected": {
                            "selected": 3
                        }
                    },
                    "optgroup": {
                        "label": 2
                    },
                    "label": csccSense.commonAttributes(
                            {
                                "for": 2
                            }),
                    "textarea": csccSense.commonAttributes(
                            {
                                "name": 2,
                                "cols": 2,
                                "rows": 2,
                                "wrap": {
                                    "on": 3,
                                    "off": 3
                                },
                                "disabled": {
                                    "disabled": 3
                                },
                                "readonly": {
                                    "readonly": 3
                                }
                            }),
                    "button": csccSense.commonAttributes(
                            {
                                "onclick": 2
                            }),
                    "table": csccSense.commonAttributes(
                            {
                                "border": {
                                    "0": 3
                                },
                                "cellpadding": {
                                    "0": 3
                                },
                                "cellspacing": {
                                    "0": 3
                                },
                                "width": 2,
                                "height": 2,
                                "summary": 2
                            }),
                    "thead": 1,
                    "tbody": 1,
                    "td": csccSense.commonAttributes(
                            {
                                "colspan": 2,
                                "rowspan": 2,
                                "width": 2,
                                "height": 2,
                                "valign": {
                                    "top": 3,
                                    "bottom": 3,
                                    "baseline": 3,
                                    "middle": 3
                                }
                            }),
                    "iframe": this.commonAttributes(
                            {
                                "src": 2,
                                "frameborder": {
                                    "0": 3
                                }
                            }),
                    "article": 1,
                    "header": 1,
                    "footer": 1,
                    "section": 1,
                    "aside": 1,
                    "nav": 1,
                    "base": {
                        "href": 2
                    }
                };

        // add the common elements to the dictionary
        for (var i = 0; i < this.commonElements.length; i++)
        {
            this.xmlDictionary[this.commonElements[i]] = this.commonAttributes();
        }

        // really simple context definitions
        this.xmlContext =
                {
                    "html": ["head", "body"],
                    "head": ["title", "meta", "link", "script", "style", "base"],
                    "select": ["option", "optgroup"],
                    "optgroup": ["option"],
                    "table": ["thead", "tbody", "tfoot"],
                    "thead": ["tr"],
                    "tbody": ["tr"],
                    "tfoot": ["tr"],
                    "tr": {
                        "thead": ["th"],
                        "*": ["td"]
                    },
                    // quplo specific
                    "/": ["page", "part", "var", "layout"],
                    "page": {
                        "@layout": ["part"],
                        "*": ["html"]
                    }
                }




        // add the common elements to the dictionary
        for (var i = 0; i < this.commonElements.length; i++)
        {


            if (typeof this.commonElements[i] != 'undefined' && this.commonElements[i] != null)
            {
                this.xmlDictionary[this.commonElements[i]] = this.commonAttributes();
            }

        }

        // Utils.registerApiListener('GetMedia', function (a, b, c) { csccSense.getMediaCallback(a, b, c) });

        // do an async call once to get the media
        // csccSense.refreshMedia();
    },
    // quplo proprietary

    layoutsInOtherSheets: [],
    partsInOtherSheets: [],
    jsFilesInOtherSheets: [],
    cssFilesInOtherSheets: [],
    partNamesInLayoutsInOtherSheets: [],
    // array containing stringarrays (order is same as layoutsInOtherSheets)
    mediaFiles: [],
    getLayouts: function()
    {
        var xml = cscc.editor.getCode();
        var lines = xml.split("\n");
        var items =
                {
                };
        for (var j = 0; j < csccSense.layoutsInOtherSheets.length; j++)
        {
            var name = csccSense.layoutsInOtherSheets[j];
            items[name] = 3;
        }
        var count = csccSense.layoutsInOtherSheets.length;
        for (var j = 0; j < lines.length; j++)
        {
            var curLine = lines[j];
            if (curLine.indexOf("<layout ") != -1)
            {
                var name = curLine.replace(/^\s*<layout.*?name=\"(.*?)\".*?$/, "$1");
                items[name] = 3;
                count++;
            }
        }
        return !count ? 2 : items;
    },
    getParts: function()
    {
        var xml = cscc.editor.getCode();
        var lines = xml.split("\n");
        var items =
                {
                };
        for (var j = 0; j < csccSense.partsInOtherSheets.length; j++)
        {
            var name = csccSense.partsInOtherSheets[j];
            items[name] = 3;
        }
        var count = csccSense.partsInOtherSheets.length;
        var inPage = false;
        for (var j = 0; j < lines.length; j++)
        {
            var curLine = lines[j];
            // only show parts that are not inside a page (that has layouts)
            if (curLine.indexOf("<page ") != -1)
                inPage = true;
            if (curLine.indexOf("</page>") != -1)
                inPage = false;
            if (inPage)
                continue;
            if (curLine.indexOf("<part ") != -1)
            {
                var name = curLine.replace(/^\s*<part.*?name=\"(.*?)\".*?$/, "$1");
                items[name] = 3;
                count++;
            }
        }
        return !count ? 2 : items;
    },
    getStyleSheets: function(line)
    {
        if (!line || (line.indexOf('text/css') == -1 && line.indexOf('stylesheet') == -1))
            return 2;

        var xml = cscc.editor.getCode();
        var lines = xml.split("\n");
        var items =
                {
                };
        for (var j = 0; j < csccSense.cssFilesInOtherSheets.length; j++)
        {
            var url = csccSense.cssFilesInOtherSheets[j];
            items[url] = 3;
        }
        var count = csccSense.cssFilesInOtherSheets.length;
        for (var j = 0; j < lines.length; j++)
        {
            var curLine = lines[j];
            if (curLine.indexOf("<page ") != -1)
            {
                var type = curLine.replace(/^.*?type=['|"](.*?)['|"].*$/gi, "$1");
                var url = curLine.replace(/^.*?url=["|'](.*?)["|'].*$/gi, "$1");
                if ((type == "css" || type == "less" || url.match(/\.css$/i)) && url != "")
                {
                    items[url] = 3;
                    count++;
                }
            }
        }
        return !count ? 2 : items;
    },
    getScripts: function(line)
    {
        var xml = this.csccObj.editor.getCode();
        var lines = xml.split("\n");
        var items =
                {
                };
        for (var j = 0; j < csccSense.jsFilesInOtherSheets.length; j++)
        {
            var url = csccSense.jsFilesInOtherSheets[j];
            items[url] = 3;
        }
        var count = csccSense.jsFilesInOtherSheets.length;
        for (var j = 0; j < lines.length; j++)
        {
            var curLine = lines[j];
            if (curLine.indexOf("<page ") != -1)
            {
                var type = curLine.replace(/^.*?type=['|"](.*?)['|"].*$/gi, "$1");
                var url = curLine.replace(/^.*?url=["|'](.*?)["|'].*$/gi, "$1");
                if ((type == "js" || url.match(/\.js$/i)) && url != "")
                {
                    items[url] = 3;
                    count++;
                }
            }
        }
        return !count ? 2 : items;
    },
    // this is used when a part/@name is written inside a page with @layout
    getPartNames: function(text)
    {
        var context = this.csccObj.currentContextTree[0];
        if (context && context.name == "page" && context.attributes.layout)
        {
            var layoutName = context.attributes.layout;
            var layouts = csccSense.getLayoutParts();
            var partNames = layouts[layoutName];
            var returnDict =
                    {
                    };
            for (var i = 0; i < partNames.length; i++)
                returnDict[partNames[i]] = 3;
            return returnDict;
        }
        return 2;
    },
    getLayoutParts: function()
    {
        // get the list of parts that are defined globally
        var globalParts = csccSense.getParts();

        var xml = this.csccObj.editor.getCode();
        var lines = xml.split("\n");
        var layoutTree =
                {
                };
        for (var j = 0; j < csccSense.layoutsInOtherSheets.length; j++)
        {
            var name = csccSense.layoutsInOtherSheets[j];
            layoutTree[name] = [];
            var arrayOfPartsInsideThisLayout = csccSense.partNamesInLayoutsInOtherSheets[j];
            if (arrayOfPartsInsideThisLayout)
            {
                for (var i = 0; i < arrayOfPartsInsideThisLayout.length; i++)
                {
                    var partNameInThisLayout = arrayOfPartsInsideThisLayout[i];
                    if (!globalParts[partNameInThisLayout])
                        layoutTree[name].push(partNameInThisLayout);
                }
            }
        }
        var count = csccSense.layoutsInOtherSheets.length;
        var inLayout = false;
        var curLayout = "";
        for (var j = 0; j < lines.length; j++)
        {
            var curLine = lines[j];
            if (curLine.indexOf("<layout ") != -1)
            {
                inLayout = true;
                curLayout = curLine.replace(/^\s*<layout.*?name=\"(.*?)\".*?$/, "$1");
                layoutTree[curLayout] = [];
                count++;
            }
            if (curLine.indexOf("</layout>") != -1)
                inLayout = false;
            if (!inLayout)
                continue;

            // if we're inside a layout and a part is written
            if (curLine.indexOf("<write part") != -1)
            {
                var partName = curLine.replace(/^.*?<write.*?part=\"(.*?)\".*?$/, "$1");
                // check if the partname written isn't a globally defined one
                if (!globalParts[partName])
                {
                    layoutTree[curLayout].push(partName);
                }
            }
        }
        return layoutTree;
    },
    getMedia: function(line, asCss)
    {
        if (asCss)
        {
            var dict =
                    {
                    };
            for (var mediaFile in csccSense.media)
                dict['url(' + mediaFile + ')'] = 2;
            return dict;
        }
        return csccSense.media;
    },
    refreshMedia: function()
    {
        Utils.makeApiCall('GetMedia', webAddress)
    },
    getMediaCallback: function(action, value, result)
    {
        var files = result.split(',').sort();
        var dict =
                {
                };
        for (var i = 0; i < files.length; i++)
        {
            dict[files[i]] = 3;
        }
        csccSense.media = dict;
    }
};
