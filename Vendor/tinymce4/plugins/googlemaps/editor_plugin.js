(function() {
    tinymce.PluginManager.requireLangPack('mcegooglemaps');
    tinymce.create('tinymce.plugins.GooglemapsPlugin', { init: function(ed, url) {
        var d = this;
        d.editor = ed;

        ed.addCommand('mceGooglemap', function() {
            ed.windowManager.open({ file: url + '/googlemaps.htm', width: 640 + parseInt(ed.getLang('googlemaps.delta_width', 0)), height: 500 + parseInt(ed.getLang('googlemaps.delta_height', 0)), inline: 1 }, { plugin_url: url, plugin_googleMaps_apiKey: ed.getParam("plugin_googleMaps_apiKey", ""), plugin_googleMaps_coordinates: ed.getParam("plugin_googleMaps_coordinates", ""), plugin_googleMaps_showCoordinates: ed.getParam("plugin_googleMaps_showCoordinates", "true"), plugin_googleMaps_locationLookupEnabled: ed.getParam("plugin_googleMaps_locationLookupEnabled", "true") });
        });

        ed.addCommand('mceGooglemapDelete', function() {
            var gdoc = ed.getDoc();
            ed.dom.remove(gdoc.getElementById('spangooglemaps'));
        });

        ed.addCommand("mceInsertGoogleMap", this._insertGoogleMap, this);

        ed.addButton('mcegooglemaps', { title: 'googlemaps.desc', cmd: 'mceGooglemap', image: url + '/img/map_add.gif' });
        ed.addButton('googlemapsdel', { title: 'googlemaps.deldesc', cmd: 'mceGooglemapDelete', image: url + '/img/map_delete.gif' });
    }, getInfo: function() {
        return { longname: 'MCEGoogleMaps', author: 'Christian Ladewig (Original by: Cees Rijken <http://www.connectcase.nl>); Further development by Jeroen van der Stijl (jeroen@viverosoft.com)', authorurl: 'http://www.klmedien.de', infourl: 'https://sourceforge.net/projects/mcegooglemap', version: "1.0.0 alpha" };
    },
        _insertGoogleMap: function(ui, v) {

            var mapId;
            var i = 0;
            // get a unique id for the div
            do {
                mapId = "divMap" + (i++).toString();
            } while (document.getElementById(mapId) != null)

            strHtml = '<span>\n';
            strHtml += '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' + v.akey + '" type="text/javascript"></script>';
            strHtml += '<script type="text/javascript">\n';

            strHtml += 'function load() \n';
            strHtml += '{\n';
            strHtml += '  if (GBrowserIsCompatible())\n';
            strHtml += '  {\n';
            strHtml += '  var map = new GMap2(document.getElementById("' + mapId + '"))\;\n';
            strHtml += '  var center = new GLatLng(' + v.coordinates + ')\;\n';
            strHtml += '  map.setCenter(center, ' + v.zoom + ')\;\n';
            strHtml += '  map.addOverlay(new GMarker(center))\;\n';

            strHtml += '\n\n';

            switch (v.hud) {
                case "GLargeMapControl3D":
                    strHtml += '  var mapControl = new GLargeMapControl3D();\n';
                    strHtml += '  map.addControl(mapControl);\n';
                    break;
                case "GLargeMapControl":
                    strHtml += '  var mapControl = new GLargeMapControl();\n';
                    strHtml += '  map.addControl(mapControl);\n';
                    break;
                case "GSmallMapControl":
                    strHtml += '  var mapControl = new GSmallMapControl();\n';
                    strHtml += '  map.addControl(mapControl);\n';
                    break;
                case "GSmallZoomControl3D":
                    strHtml += '  var mapControl = new GSmallZoomControl3D();\n';
                    strHtml += '  map.addControl(mapControl);\n';
                    break;
                case "GSmallZoomControl":
                    strHtml += '  var mapControl = new GSmallZoomControl();\n';
                    strHtml += '  map.addControl(mapControl);\n';
                    break;
            }

            if (v.showScale) {
                strHtml += '\n\n';
                strHtml += '  var scaleControl = new GScaleControl();\n';
                strHtml += '  map.addControl(scaleControl);\n';
            }

            if (v.showOverview) {
                strHtml += '\n\n';
                strHtml += '  var overviewControl = new GOverviewMapControl();\n';
                strHtml += '  map.addControl(overviewControl);\n';
            }

            strHtml += '\n\n';

            strHtml += '  var mapTypeControl = new GHierarchicalMapTypeControl();\n';
            strHtml += '  map.addControl(mapTypeControl);\n';
            strHtml += '  map.addMapType(G_PHYSICAL_MAP);\n';

            strHtml += '\n\n';

            switch (v.mapType) {
                case "G_NORMAL_MAP":
                    strHtml += '  map.setMapType(G_NORMAL_MAP);\n';
                    break;
                case "G_SATELLITE_MAP":
                    strHtml += '  map.setMapType(G_SATELLITE_MAP);\n';
                    break;
                case "G_HYBRID_MAP":
                    strHtml += '  map.setMapType(G_HYBRID_MAP);\n';
                    break;
                case "G_PHYSICAL_MAP":
                    strHtml += '  map.setMapType(G_PHYSICAL_MAP);\n';
                    break;
            }

            strHtml += '  }\n';
            strHtml += '}\n';

            strHtml += 'function addLoadEvent(func) \n';
            strHtml += '{\n';
            strHtml += 'var oldonload = window.onload;\n';
            strHtml += '  if (typeof window.onload != \'function\') \n';
            strHtml += '  {\n';
            strHtml += '  window.onload = func;\n';
            strHtml += '  } \n';
            strHtml += '  else \n';
            strHtml += '  {\n';
            strHtml += '  window.onload = function() \n';
            strHtml += '    {\n';
            strHtml += '    if (oldonload) \n';
            strHtml += '    {\n';
            strHtml += '    oldonload();\n';
            strHtml += '    }\n';
            strHtml += '    func();\n';
            strHtml += '}\n';
            strHtml += '}\n';
            strHtml += '}\n';

            strHtml += 'addLoadEvent(load)\;\n';

            strHtml += 'if (window.attachEvent) {\n';
            strHtml += '  window.attachEvent("onunload", function() {\n';
            strHtml += '  GUnload();      // Internet Explorer\n';
            strHtml += '        });\n';
            strHtml += '} else {\n';
            strHtml += 'window.addEventListener("unload", function() {\n';
            strHtml += 'GUnload(); // Firefox and standard browsers\n';
            strHtml += '    }, false);\n';
            strHtml += '}\n';


            strHtml += '</script>\n';

            strHtml += '<div id="' + mapId + '" style="background:#d3d3d3;width: ' + v.width + 'px; height: ' + v.height + 'px;" class="googlemaps_dummy">Placeholder for GoogleMap. Use the preview button to see the real map.</div>\n';
            strHtml += '</span>\n';
            this.editor.execCommand('mceInsertContent', false, strHtml);

        }
    });
    tinymce.PluginManager.add('mcegooglemaps', tinymce.plugins.GooglemapsPlugin);
})();