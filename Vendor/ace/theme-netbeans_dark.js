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


ace.define('ace/theme/netbeans_dark', ['require', 'exports', 'module' , 'ace/lib/dom'], function(require, exports, module) {
exports.isDark = true;
exports.cssClass = "ace-netbeans_dark";
exports.cssText = ".ace-netbeans_dark {background: #2B2B2B;color:#DDD; font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro', monospace; }.ace-netbeans_dark .ace_gutter {\
background-color: #3d3d3d;\
background-image: -moz-linear-gradient(left, #3D3D3D, #333);\
background-image: -ms-linear-gradient(left, #3D3D3D, #333);\
background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#3D3D3D), to(#333));\
background-image: -webkit-linear-gradient(left, #3D3D3D, #333);\
background-image: -o-linear-gradient(left, #3D3D3D, #333);\
background-image: linear-gradient(left, #3D3D3D, #333);\
background-repeat: repeat-x;\
border-right: 1px solid #4d4d4d;\
text-shadow: 0px 1px 1px #4d4d4d;\
}\
.ace-netbeans_dark .ace_gutter.ace-gutter-error{\
	background: rgba(202, 51, 51, 0.7)!important;\
}\
.ace-wrapper.ace-netbeans_dark ::-webkit-scrollbar {\
	width: 12px;\
}\
.ace-wrapper.ace-netbeans_dark ::-webkit-scrollbar-thumb {\
	-webkit-border-radius: 10px;\
	border-radius: 10px;\
	background: rgba(0, 0, 0, 0.3);\
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.5);\
}\
.ace-wrapper.ace-netbeans_dark ::-webkit-scrollbar-track {\
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.18);\
	-webkit-border-radius: 10px;\
	border-radius: 10px;\
}\
.ace-netbeans_dark #hints {\
	color: #333;\
}\
.ace-wrapper.ace-netbeans_dark .ace-status-bar {\
background: #3d3d3d!important;\
color:#DDD!important;\
text-shadow: 0px 1px 1px #4d4d4d!important;\
border: 0;\
border-top: 1px solid #4d4d4d!important;\
}\
.ace-netbeans_dark .ace_print-margin {\
width: 1px;\
background: #e8e8e8;\
}\
.ace-netbeans_dark {\
background: #2C2C2C;\
background-image: -moz-linear-gradient(left, #2C2C2C, rgba(12, 12, 12, 0.9));\
background-image: -webkit-linear-gradient(left, #2C2C2C, rgba(12, 12, 12, 0.9));\
background-image: -ms-linear-gradient(left, #2C2C2C, rgba(12, 12, 12, 0.9));\
background-image: -o-linear-gradient(left, #2C2C2C, rgba(12, 12, 12, 0.9));\
}\
.ace-netbeans_dark .ace_fold {\
background-color: #757AD8;\
}\
.ace-netbeans_dark .ace_cursor {\
border-left: 1px solid #7991E8;\
}\
.ace-netbeans_dark .ace_overwrite-cursors .ace_cursor {\
border: 1px solid #FFE300;\
background: #766B13;\
}\
.ace-netbeans_dark .ace_invisible {\
color: rgb(191, 191, 191);\
}\
.ace-netbeans_dark .ace_storage,\
.ace-netbeans_dark .ace_keyword {\
color: #9292D1;\
}\
.ace-netbeans_dark .ace_constant.ace_buildin {\
color: rgb(88, 72, 246);\
}\
.ace-netbeans_dark .ace_constant.ace_language {\
color: #FF3A69;\
}\
.ace-netbeans_dark .ace_constant.ace_library {\
color: rgb(6, 150, 14);\
}\
.ace-netbeans_dark .ace_invalid {\
background-color: rgb(153, 0, 0);\
color: white;\
}\
.ace-netbeans_dark .ace_support.ace_function {\
color: #B7AEFA;\
}\
.ace-netbeans_dark .ace_support.ace_constant {\
color: rgb(6, 150, 14);\
}\
.ace-netbeans_dark .ace_support.ace_type{\
color: #29AC00;\
}\
.ace-netbeans_dark .ace_support.ace_class {\
color: #CFCECE;\
}\
.ace-netbeans_dark .ace_support.ace_php_tag {\
color: #f00;\
}\
.ace-netbeans_dark .ace_keyword.ace_operator {\
color: #FFC200;\
}\
.ace-netbeans_dark .ace_string {\
color: #9E9E9E;\
}\
.ace-netbeans_dark .ace_comment {\
color: #B32929;\
}\
.ace-netbeans_dark .ace_identifier {\n\
color: #D1BCAC;\
}\
.ace-netbeans_dark .ace_comment.ace_doc {\
color: rgb(0, 102, 255);\
}\
.ace-netbeans_dark .ace_comment.ace_doc.ace_tag {\
color: rgb(128, 159, 191);\
}\
.ace-netbeans_dark .ace_constant.ace_numeric {\
color: #FF00FF;\
}\
.ace-netbeans_dark .ace_variable {\
color: #6D3206\
}\
.ace-netbeans_dark .ace_variable {\
color: #8F8F8F\
}\
.ace-netbeans_dark .ace_xml-pe {\
color: rgb(104, 104, 91);\
}\
.ace-netbeans_dark .ace_entity.ace_name.ace_function {\
color: #7575EC;\
}\
.ace-netbeans_dark .ace_heading {\
color: rgb(12, 7, 255);\
}\
.ace-netbeans_dark .ace_list {\
	color:rgb(185, 6, 144);\
}\
.ace-netbeans_dark .ace_marker-layer .ace_selection {\
background:rgba(0, 0, 0, 0.5);\
}\
.ace-netbeans_dark .ace_marker-layer .ace_step {\
background: rgb(252, 255, 0);\
}\
.ace-netbeans_dark .ace_marker-layer .ace_stack {\
background: rgb(164, 229, 101);\
}\
.ace-netbeans_dark .ace_marker-layer .ace_bracket {\
margin: -1px 0 0 -1px;\
border: 1px solid rgb(192, 192, 192);\
}\
.ace-netbeans_dark .ace_marker-layer .ace_active-line {\
background: rgba(99, 99, 99, 0.18);\
}\
.ace-netbeans_dark .ace_marker-layer .ace_selected-word {\
background: rgba(0, 0, 0, 0.5);\
border: 1px solid rgba(245, 255, 0, 0.5);\
}\
.ace-netbeans_dark .ace_meta.ace_tag {\
color:#00A254;\
}\
.ace-netbeans_dark .ace_meta.ace_tag.ace_anchor {\
color:#00A254;\
}\
.ace-netbeans_dark .ace_meta.ace_tag.ace_form {\
color:#00A254;\
}\
.ace-netbeans_dark .ace_meta.ace_tag.ace_image {\
color:#00A254;\
}\
.ace-netbeans_dark .ace_meta.ace_tag.ace_script {\
color:#00A254;\
}\
.ace-netbeans_dark .ace_meta.ace_tag.ace_style {\
color:#00A254;\
}\
.ace-netbeans_dark .ace_meta.ace_tag.ace_table {\
color:#00A254;\
}\
.ace-netbeans_dark .ace_entity.ace_other.ace_attribute-name{\
color:#948ACC;\
}\
.ace-netbeans_dark .ace_string.ace_regex {\
color: rgb(255, 0, 0)\
}\
.ace-netbeans_dark .ace_cpfunction { color:#BEBE16; }\
.ace-netbeans_dark .ace_cpprovider { color:#7FC1EC; }\
.ace-netbeans_dark .ace_cpvariable { color:#D66448; }\
.ace-netbeans_dark .ace_cpconstante { color:#7B48FD; }\
.ace-netbeans_dark .ace_cpconditions { color: #4C98D3; }\
.ace-netbeans_dark .ace_string .ace_string { color: #7A9C97; }\
.ace-netbeans_dark .ace_cptag { color: #8F80FF!important; }\
.ace-netbeans_dark .ace_cpattribute {\
color:#72FF5F!important\
}\
.ace-netbeans_dark .ace_cpstring {\
color:#C9B3F6!important;\
}\
.ace-netbeans_dark .ace_lparen,.ace-netbeans_dark .ace_rparen {\
color: #FF6F6F;\
}\
.ace-netbeans_dark .ace_indent-guide {\
background: url(\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAACCAYAAACZgbYnAAAAEklEQVQImWNQUFD4z6Crq/sfAAuYAuYl+7lfAAAAAElFTkSuQmCC\") right repeat-y;\
}\
\
\
.ace-netbeans_dark .ace-hint-bar {\
background: #CA3333;\
color:#CCC!important;\
text-shadow: 0px 1px 1px #4d4d4d!important;\
border: 0;\
border-bottom: 1px solid #4d4d4d!important;\
}\
.ace-netbeans_dark .ace-hint-bar a {\
	color:#aaa!important;\
}\
.ace-netbeans_dark .ace-hint-bar.no-errors {\
background: #3d3d3d!important;\
}";

var dom = require("../lib/dom");
dom.importCssString(exports.cssText, exports.cssClass);
});
