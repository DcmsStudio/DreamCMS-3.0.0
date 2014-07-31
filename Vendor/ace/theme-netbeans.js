/* 
 * DreamCMS 3.0
 * 
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE Version 2
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-2.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@dcms-studio.de so we can send you a copy immediately.
 * 
 * PHP Version 5.3.6
 * @copyright	Copyright (c) 2008-2013 Marcel Domke (http://www.dcms-studio.de)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 */


ace.define('ace/theme/netbeans', ['require', 'exports', 'module' , 'ace/lib/dom'], function(require, exports, module) {
exports.isDark = false;
exports.cssClass = "ace-netbeans";
exports.cssText = ".ace-netbeans {font-family: monospace, 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro'; }.ace-netbeans .ace_gutter {\
background: #E4E3DB;\
border-right:1px solid #BDBDBD;\
color: #333;\
}\
.ace-netbeans .ace_print-margin {\
width: 1px;\
background: #e8e8e8;\
}\
.ace-netbeans {\
background-color: #FFFFFF;\
}\
.ace-netbeans .ace_fold {\
background-color: #757AD8;\
}\
.ace-netbeans .ace_cursor {\
border-left: 2px solid black;\
}\
.ace-netbeans .ace_overwrite-cursors .ace_cursor {\
border-left: 0px;\
border-bottom: 1px solid black;\
}\
.ace-netbeans .ace_invisible {\
color: rgb(191, 191, 191);\
}\
.ace-netbeans .ace_storage,\
.ace-netbeans .ace_keyword {\
color: blue;\
}\
.ace-netbeans .ace_constant.ace_buildin {\
color: rgb(88, 72, 246);\
}\
.ace-netbeans .ace_constant.ace_language {\
color: rgb(88, 92, 246);\
}\
.ace-netbeans .ace_constant.ace_library {\
color: rgb(6, 150, 14);\
}\
.ace-netbeans .ace_invalid {\
background-color: rgb(153, 0, 0);\
color: white;\
}\
.ace-netbeans .ace_support.ace_function {\
color: rgb(60, 76, 114);\
font-weight:bold;\
}\
.ace-netbeans .ace_support.ace_constant {\
color: rgb(6, 150, 14);\
}\
.ace-netbeans .ace_support.ace_type{\
color: #0000E6;\
}\
.ace-netbeans .ace_support.ace_class {\
color: #000;\
}\
.ace-netbeans .ace_support.ace_php_tag {\
color: #f00;\
}\
.ace-netbeans .ace_keyword.ace_operator {\
color: #333;\
}\
.ace-netbeans .ace_string {\
color: #CE7B00;\
}\
.ace-netbeans .ace_comment {\
color: #969696;\
}\
.ace_identifier {\n\
color: #6D3206;\
}\
.ace-netbeans .ace_comment.ace_doc {\
color: rgb(0, 102, 255);\
}\
.ace-netbeans .ace_comment.ace_doc.ace_tag {\
color: rgb(128, 159, 191);\
}\
.ace-netbeans .ace_constant.ace_numeric {\
color: #FF00FF;\
}\
.ace-netbeans .ace_variable {\
color: #6D3206\
}\
.ace-netbeans .ace_xml-pe {\
color: rgb(104, 104, 91);\
}\
.ace-netbeans .ace_entity.ace_name.ace_function {\
color: #00F;\
}\
.ace-netbeans .ace_heading {\
color: rgb(12, 7, 255);\
}\
.ace-netbeans .ace_list {\
color:rgb(185, 6, 144);\
}\
.ace-netbeans .ace_marker-layer .ace_selection {\
background: rgb(181, 213, 255);\
}\
.ace-netbeans .ace_marker-layer .ace_step {\
background: rgb(252, 255, 0);\
}\
.ace-netbeans .ace_marker-layer .ace_stack {\
background: rgb(164, 229, 101);\
}\
.ace-netbeans .ace_marker-layer .ace_bracket {\
margin: -1px 0 0 -1px;\
border: 1px solid rgb(192, 192, 192);\
}\
.ace-netbeans .ace_marker-layer .ace_active-line {\
background: rgba(224, 153, 241, 0.18);\
}\
.ace-netbeans .ace_marker-layer .ace_selected-word {\
background: rgb(250, 250, 255);\
border: 1px solid rgb(200, 200, 250);\
}\
.ace-netbeans .ace_meta.ace_tag {\
color:#0000E6;\
}\
.ace-netbeans .ace_meta.ace_tag.ace_anchor {\
color:#0000E6;\
}\
.ace-netbeans .ace_meta.ace_tag.ace_form {\
color:#0000E6;\
}\
.ace-netbeans .ace_meta.ace_tag.ace_image {\
color:#0000E6;\
}\
.ace-netbeans .ace_meta.ace_tag.ace_script {\
color:#0000E6;\
}\
.ace-netbeans .ace_meta.ace_tag.ace_style {\
color:#0000E6;\
}\
.ace-netbeans .ace_meta.ace_tag.ace_table {\
color:#0000E6;\
}\
.ace-netbeans .ace_entity.ace_other.ace_attribute-name{\
color:#009900;\
}\
.ace-netbeans .ace_string.ace_regex {\
color: rgb(255, 0, 0)\
}\
.ace-netbeans .ace_cpfunction { color:#BEBE16; }\
.ace-netbeans .ace_cpprovider { color:#7FC1EC; }\
.ace-netbeans .ace_cpvariable { color:#46A8D6; }\
.ace-netbeans .ace_cpconstante { color:#E4729F; }\
.ace-netbeans .ace_cpconditions { color: #4C98D3; }\
.ace-netbeans .ace_string .ace_string { color: #333; }\
.ace-netbeans .ace_indent-guide {\
background: url(\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAACCAYAAACZgbYnAAAAE0lEQVQImWP4////f4bLly//BwAmVgd1/w11/gAAAABJRU5ErkJggg==\") right repeat-y;\
}";

var dom = require("../lib/dom");
dom.importCssString(exports.cssText, exports.cssClass);
});
