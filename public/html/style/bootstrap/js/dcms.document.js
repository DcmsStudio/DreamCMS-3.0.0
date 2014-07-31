/**
 * Created by marcel on 30.05.14.
 */

function mcefilebrowser(field_name, url, type, win) {
    var fileBrowserURL = Config.get('tinymceFileBrowserUrl', '') + type;
    var url = Config.get('portalurl');

    if (fileBrowserURL.match(/^\//g)) {
        fileBrowserURL = fileBrowserURL.substr(1);
    }

    tinyMCE.activeEditor.windowManager.open({
            title: "PDW File Browser",
            url: (url + '/' + fileBrowserURL),
            width: 950,
            height: 650,
            inline: 0,
            maximizable: 1,
            close_previous: 0,
            toolbar: 'yes',
            menubar: 'yes',
            location: 'yes'
        }, {
            window: win,
            input: field_name
        }
    );
}

var Doc = {
    runTimeoutTimer: null,
    inited: false,
    headTag: null,
    titleTag: null,
    mainContent: null,
    documentSettings: null,
    editor_onmenu: false,
    windowID: null,
    allowSidePanel: false,
    lastActiveTinyMCE: null,
    lastActiveTinyMCESelect: null,
    loadedDiffMirror: false,
    tinyMceConfigs: [],

    /**
     * @deprecated
     */
    tinyMceSetup: {
        apply_source_formatting: true,
        gecko_spellcheck: false,
        keep_styles: true,

        accessibility_focus: true,
        tabfocus_elements: ':prev,:next',
        forced_root_block: 'p',

        savecallback: false,
        submit_patch: true,
        paste_text_use_dialog: true,
        paste_strip_class_attributes: false,
        paste_remove_spans: false,
        paste_remove_styles: false,
        visualblocks_default_state: true,
        end_container_on_empty_block: true,

        // Schema is HTML5 instead of default HTML4
        schema: "html5",

        // End container block element when pressing enter inside an empty block
        end_container_on_empty_block: true,
        fix_list_elements: true,
        fix_table_elements: true,
        convert_urls: false,
        relative_urls: true,
        remove_script_host: true,
        remove_linebreaks: false,
        cleanup_on_startup: false,
        convert_fonts_to_spans: true,
        save_enablewhendirty: true,

        mode: "exact",
        theme: "advanced",
        skin: 'dcms',
        inlinepopups_skin: 'dcms',
        language: "{language}",
        plugins: "{plugins}",
        theme_advanced_buttons1: '',
        theme_advanced_buttons2: '',
        theme_advanced_buttons3: '',
        theme_advanced_buttons4: '',

        content_css: 'html/css/tinymce.css,html/css/subcols.css,html/css/subcols_extended.css{extraTemplateCss}',
        template_templates: [],
        file_browser_callback: "mcefilebrowser",
        script_url: '{url}/Vendor/tinymce/tiny_mce.js',

        // PDW Toggle Toolbars settings
        pdw_toggle_on: '1',
        pdw_toggle_toolbars: "2,3,4",

        /* disable the gecko spellcheck since AtD provides one */
        gecko_spellcheck: false,
        /* the URL to the button image to display */
        atd_button_url: "Vendor/tinymce/plugins/AtD/atdbuttontr.gif",
        /* the URL of your proxy file */
        atd_rpc_url: "Vendor/tinymce/plugins/AtD/server/proxy.php?lang=%s&url=",
        /* set your API key */
        atd_rpc_id: "dashnine",
        /* edit this file to customize how AtD shows errors */
        atd_css_url: "Vendor/tinymce/plugins/AtD/css/content.css",
        /* this list contains the categories of errors we want to show */
        atd_show_types: 'Bias Language,Cliches,Complex Expression,Diacritical Marks,Double Negatives,Hidden Verbs,Jargon Language,Passive voice,Phrases to Avoid,Redundant Expression',
        /* strings this plugin should ignore */
        atd_ignore_strings: 'AtD,rsmudge',
        /* enable "Ignore Always" menu item, uses cookies by default. Set atd_ignore_rpc_url to a URL AtD should send ignore requests to. */
        atd_ignore_enable: 'false',

        entity_encoding: 'raw',
        disk_cache: false,

        event_elements: 'a,div,h1,h2,h3,h4,h5,h6,img,p,span',
        entities: '160,nbsp,60,lt,62,gt,173,shy',
        save_on_tinymce_forms: true,
        advimage_update_dimensions_onchange: true,
        spellchecker_languages: "+Deutsch=de,Englisch=en,Spanisch=es,Französisch=fr,Griechisch=el,Polnisch=pl",
        theme_advanced_blockformats: 'div,p,address,pre,h1,h2,h3,h4,h5,h6',
        theme_advanced_font_sizes: '8px,9px,10px,11px,12px,13px,14px,15px,16px,17px,18px,19px,20px,21px,22px,23px,24px',
        save_callback: 'TinyCallback.cleanXHTML',
        init_instance_callback: 'TinyCallback.getScrollOffset',

        object_resizing: true,

        theme_advanced_toolbar_location: "external",
        theme_advanced_toolbar_align: "left",
        theme_advanced_statusbar_location: "bottom",
        theme_advanced_resizing: true,
        theme_advanced_resize_horizontal: false,
        extended_valid_elements: "div[*],a[*],pre[*],p[*],h1[*],h2[*],h3[*],h4[*],h5[*],h6[*],php[*]",
        custom_tag_ns_prefix: "",
        custom_tags: "pageindex,php",
        onchange_callback: "TinyCallback.onChangeHandler",
        style_formats: [
            {title: 'Headers'},
            {title: 'h1', block: 'h1'},
            {title: 'h2', block: 'h2'},
            {title: 'h3', block: 'h3'},
            {title: 'h4', block: 'h4'},
            {title: 'h5', block: 'h5'},
            {title: 'h6', block: 'h6'},

            {title: 'Blocks' },
            {title: 'p', block: 'p'},
            {title: 'div', block: 'div'},
            {title: 'pre', block: 'pre'},

            {title: 'Source Code', block: 'code', classes: 'prettyprint', exact: true},

            {title: 'Containers'},
            {title: 'header', block: 'header', wrapper: true},
            {title: 'footer', block: 'footer', wrapper: true},
            {title: 'nav', block: 'nav', wrapper: true, merge_siblings: false},
            {title: 'section', block: 'section', wrapper: true, merge_siblings: false},
            {title: 'article', block: 'article', wrapper: true, merge_siblings: false},
            {title: 'blockquote', block: 'blockquote', wrapper: true},
            {title: 'hgroup', block: 'hgroup', wrapper: true},
            {title: 'aside', block: 'aside', wrapper: true},
            {title: 'figure', block: 'figure', wrapper: true}

        ],
        setup: function (ed) {

            ed.addCommand('mceDirtySet', function () {
                ed.dirty = true;
                ed.mceDirtySetRuntimeTimer = window.setTimeout(function () {
                    Form.setDirty(false, $('#' + ed.id).parents('form:first'));
                }, 500);

            });

            ed.onExecCommand.add(function (ed, cmd, ui, val) {
                console.debug('Command was executed: ' + cmd);
            });

            ed.onKeyUp.add(function (ed, e) {
                if (!ed.dirty) {
                    if (ed.mceDirtySetRuntimeTimer) { // Wenn ein Timeout existiert, dieses zurücksetzen
                        window.clearTimeout(ed.mceDirtySetRuntimeTimer);
                    }
                    ed.execCommand('mceDirtySet');
                }
            });

            var configKeyUpEvent = (typeof Config == 'object' && typeof Config.get('onTinyMCEKeyUp') === 'function' ? Config.get('onTinyMCEKeyUp') : false);
            if (typeof configKeyUpEvent === 'function') {
                ed.onKeyUp.add(function (ed, e) {
                    configKeyUpEvent(ed, e);
                });
            }

            $('#' + ed.id + '_tbl').find('iframe:first').contents().on('resize', function () {
                console.log('resize');
            });

            var t1, t2, t3, t4;

            ed.onContextMenu.add(function (ed, e) {
                clearTimeout(t2);
                clearTimeout(t4);
                t4 = setTimeout(function () {
                    var area = $('#' + ed.id);
                    var toolbar = $('#' + ed.id + '_external');
                    var tbpos = $('#' + ed.id).attr('toolbarpos'), editorid = ed.id, windowID = $('#' + ed.id).parents('.core-tab-content:first').attr('id');

                    if (!$('#' + ed.id).hasClass('internal') || area.attr('toolbar') == 'external' || tbpos == 'external' || tbpos == 'extern') {

                        clearTimeout(t2);
                        var windowID = $('#' + ed.id).parents('.core-tab-content:first').attr('id');
                        if ($('#' + windowID).find('#' + ed.id).length) {
                            Doc.enableTinyMceToolbar(toolbar, e, ed.id, windowID, 'extern');
                            t2 = setTimeout(function () {
                                Win.refreshContentHeight();
                            }, 10);
                        }

                        e.preventDefault();
                    }
                    if ($('#' + ed.id).hasClass('internal') || $('#' + ed.id).attr('toolbar') == 'internal' || tbpos == 'internal') {
                        //  console.log('creating internal editor');

                        clearTimeout(t4);
                        windowID = $('#' + ed.id).parents('.core-tab-content:first').attr('id');
                        if ($('#' + windowID).find('#' + ed.id).length) {
                            Doc.enableTinyMceToolbar(toolbar, e, ed.id, windowID, 'intern');
                            t4 = setTimeout(function () {
                                Win.refreshContentHeight();
                            }, 10);
                        }
                    }
                }, 5);
            });

            ed.onInit.add(function (ed, e) {
                ed.dirty = false;
                var area = $('#' + ed.id);
                var toolbar = $('#' + ed.id + '_external');
                var tbpos = $('#' + ed.id).attr('toolbarpos'), editorid = ed.id, windowID = $('#' + ed.id).parents('.core-tab-content:first').attr('id');
                var t1, t2, t3, t4;

                toolbar.mouseover(function () {
                    Doc.editor_onmenu = true;
                });

                toolbar.mouseout(function () {
                    Doc.editor_onmenu = false;
                });

                if (!$('#' + ed.id).hasClass('internal') || area.attr('toolbar') == 'external' || tbpos == 'external' || tbpos == 'extern') {
                    if (toolbar) {
                        Doc.setTinyMceToolbar(toolbar, ed, ed.id, 'extern');
                    }

                    Doc.disableTinyMceToolbar(toolbar, e, ed.id, 'extern');

                    tinymce.dom.Event.add(ed.getWin(), 'blur', function (e) {
                        clearTimeout(t1);
                        windowID = $('#' + ed.id).parents('.core-tab-content:first').attr('id');
                        if ($('#' + windowID).find('#' + ed.id).length) {
                            Doc.disableTinyMceToolbar(toolbar, e, ed.id, windowID, 'extern');
                            t1 = setTimeout(function () {
                                Win.refreshContentHeight();
                            }, 10);
                        }
                    });
                    tinymce.dom.Event.add(ed.getWin(), 'focus', function (e) {
                        clearTimeout(t2);
                        windowID = $('#' + ed.id).parents('.core-tab-content:first').attr('id');
                        if ($('#' + windowID).find('#' + ed.id).length) {
                            Doc.enableTinyMceToolbar(toolbar, e, ed.id, windowID, 'extern');
                            t2 = setTimeout(function () {
                                Win.refreshContentHeight();
                            }, 10);
                        }

                    });

                    //  ed.onContextMenu.add(function(ed, e) {

                    tinymce.dom.Event.add(ed.getWin(), 'context', function (e) {
                        clearTimeout(t2);
                        windowID = $('#' + ed.id).parents('.core-tab-content:first').attr('id');
                        if ($('#' + windowID).find('#' + ed.id).length) {
                            Doc.enableTinyMceToolbar(toolbar, e, ed.id, windowID, 'extern');
                            t2 = setTimeout(function () {
                                Win.refreshContentHeight();
                            }, 10);
                        }

                    });
                }

                if ($('#' + ed.id).hasClass('internal') || $('#' + ed.id).attr('toolbar') == 'internal' || tbpos == 'internal') {
                    //  console.log('creating internal editor');

                    if (toolbar) {
                        Doc.setTinyMceToolbar(toolbar, ed, ed.id, 'intern');
                    }

                    Doc.disableTinyMceToolbar(toolbar, e, ed.id, 'intern');

                    tinymce.dom.Event.add(ed.getWin(), 'blur', function (e) {
                        clearTimeout(t3);
                        windowID = $('#' + ed.id).parents('.core-tab-content:first').attr('id');
                        if ($('#' + windowID).find('#' + ed.id).length) {
                            Doc.disableTinyMceToolbar(toolbar, e, ed.id, windowID, 'intern');
                            t3 = setTimeout(function () {
                                Win.refreshContentHeight();
                            }, 10);
                        }
                    });
                    tinymce.dom.Event.add(ed.getWin(), 'focus', function (e) {
                        clearTimeout(t4);
                        windowID = $('#' + ed.id).parents('.core-tab-content:first').attr('id');
                        if ($('#' + windowID).find('#' + ed.id).length) {
                            Doc.enableTinyMceToolbar(toolbar, e, ed.id, windowID, 'intern');
                            t4 = setTimeout(function () {
                                Win.refreshContentHeight();
                            }, 10);
                        }
                    });
                }
            });
        }
    },
    setCursorPosition : function(editor, index) {
        //get the content in the editor before we add the bookmark...
        //use the format: html to strip out any existing meta tags
        var content = editor.getContent({format: "html"});

        //split the content at the given index
        var part1 = content.substr(0, index);
        var part2 = content.substr(index);

        //create a bookmark... bookmark is an object with the id of the bookmark
        var bookmark = editor.selection.getBookmark(0);

        //this is a meta span tag that looks like the one the bookmark added... just make sure the ID is the same
        var positionString = '<span id="'+bookmark.id+'_start" data-mce-type="bookmark" data-mce-style="overflow:hidden;line-height:0px"></span>';
        //cram the position string inbetween the two parts of the content we got earlier
        var contentWithString = part1 + positionString + part2;

        //replace the content of the editor with the content with the special span
        //use format: raw so that the bookmark meta tag will remain in the content
        editor.setContent(contentWithString, ({format: "raw"}));

        //move the cursor back to the bookmark
        //this will also strip out the bookmark metatag from the html
        editor.selection.moveToBookmark(bookmark);

        //return the bookmark just because
        return bookmark;
    },
    getCursorPosition : function(editor) {
        //set a bookmark so we can return to the current position after we reset the content later
        var bm = editor.selection.getBookmark(0);

        //select the bookmark element
        var selector = "[data-mce-type=bookmark]";
        var bmElements = editor.dom.select(selector);

        //put the cursor in front of that element
        editor.selection.select(bmElements[0]);
        editor.selection.collapse();

        //add in my special span to get the index...
        //we won't be able to use the bookmark element for this because each browser will put id and class attributes in different orders.
        var elementID = "######cursor######";
        var positionString = '<span id="'+elementID+'"></span>';
        editor.selection.setContent(positionString);

        //get the content with the special span but without the bookmark meta tag
        var content = editor.getContent({format: "html"});
        //find the index of the span we placed earlier
        var index = content.indexOf(positionString);

        //remove my special span from the content
        editor.dom.remove(elementID, false);

        //move back to the bookmark
        editor.selection.moveToBookmark(bm);

        return index;
    },


    /**
     *
     */
    tinyMce4Setup: {

        menubar: false,
        selector: "div.inline-mce",
        theme: "modern",
        skin: "dcms",
        toolbar_items_size: "small",
        // entity_encoding: "raw",
        onchange_callback: "TinyCallback.onChangeHandler",
        language: "{language}",
        plugins: "{plugins}",
        content_css: "html/css/bootstrap/bootstrap.min.css,html/css/tinymce.css,html/css/subcols.css,html/css/subcols_extended.css{extraTemplateCss}",
        baseURL: '{url}/Vendor/tinymce4',
        script_url: '{url}/Vendor/tinymce4/tinymce.gzip.php',

        /*
        inline: true,
        add_unload_trigger: false,
        statusbar: true,
        inlinestatusbar: true,
        convert_urls: false,
        relative_urls: true,
        fix_list_elements: true,
        remove_trailing_brs: true,
        indent: true,
*/

        schema: "html5",
        toolbar_items_size: 'small',

        inline: true,
        cleanup: true,
        add_unload_trigger: false,
        statusbar: true,
        inlinestatusbar: true,
        entity_encoding : "html",
        convert_urls: false,
        relative_urls: true,
        entity_encoding: "raw",
        remove_trailing_brs: false,
        padd_empty_editor: false,// Remove empty contents


        schema: "html5",
        element_format: 'xhtml',

        gApiKey: '{googleApiKey}',
        code_dialog_width: ($(window).width() / 1.5),
        plugin_preview_width: ($(window).width() / 1.5),
        plugin_preview_height: ($(window).height() / 1.2),
        custom_undo_redo_levels: 40,
        isNotDirtyCalled: false,
        auto_focus: false,
        use_fancybox: true,
        nowrap : false,
        convert_fonts_to_spans : true,







        indent_before: 'p,em,small,span,a,h1,h2,h3,h4,h5,h6,blockquote,div,title,style,pre,script,td,ul,li,area,table,thead,tfoot,tbody,tr,section,article,hgroup,aside,figure,option,optgroup,datalist',
        indent_after: 'p,em,small,span,a,h1,h2,h3,h4,h5,h6,blockquote,div,title,style,pre,script,td,ul,li,area,table,thead,tfoot,tbody,tr,section,article,hgroup,aside,figure,option,optgroup,datalist',
        style_formats_merge: true,
        style_formats: [
            {title: 'Headers', items: [
                {title: 'Header 1', block: 'h1'},
                {title: 'Header 2', block: 'h2'},
                {title: 'Header 3', block: 'h3'},
                {title: 'Header 4', block: 'h4'},
                {title: 'Header 5', block: 'h5'},
                {title: 'Header 6', block: 'h6'}
            ]},
            {title: 'Inline', items: [
                {title: 'Bold', icon: "bold", inline: 'strong'},
                {title: 'Italic', icon: "italic", inline: 'em'},
                {title: 'Underline', icon: "underline", inline: 'span', styles: {'text-decoration': 'underline'}},
                {title: 'Strikethrough', icon: "strikethrough", inline: 'span', styles: {'text-decoration': 'line-through'}},
                {title: 'Superscript', icon: "superscript", inline: 'sup'},
                {title: 'Subscript', icon: "subscript", inline: 'sub'},
                {title: 'Code', icon: "code", inline: 'code'}
            ]},
            {title: 'Blocks', items: [
                {title: 'Paragraph', block: 'p'},
                {title: 'Blockquote', block: 'blockquote'},
                {title: 'Div', block: 'div'},
                {title: 'Pre', block: 'pre'}
            ]},
            {title: 'Alignment', items: [
                {title: 'Left', icon: "alignleft", block: 'div', styles: {'text-align': 'left'}},
                {title: 'Center', icon: "aligncenter", block: 'div', styles: {'text-align': 'center'}},
                {title: 'Right', icon: "alignright", block: 'div', styles: {'text-align': 'right'}},
                {title: 'Justify', icon: "alignjustify", block: 'div', styles: {'text-align': 'justify'}}
            ]}
        ],

        // valid_children: '*[*]',
        /*
        valid_elements: ""
            + "@[accesskey|draggable|style|class|hidden|tabindex|contenteditable|id|title|contextmenu|lang|dir<ltr?rtl|spellcheck|"
            + "onabort|onerror|onmousewheel|onblur|onfocus|onpause|oncanplay|onformchange|onplay|oncanplaythrough|onforminput|onplaying|onchange|oninput|onprogress|onclick|oninvalid|onratechange|oncontextmenu|onkeydown|onreadystatechange|ondblclick|onkeypress|onscroll|ondrag|onkeyup|onseeked|ondragend|onload|onseeking|ondragenter|onloadeddata|onselect|ondragleave|onloadedmetadata|onshow|ondragover|onloadstart|onstalled|ondragstart|onmousedown|onsubmit|ondrop|onmousemove|onsuspend|ondurationmouseout|ontimeupdate|onemptied|onmouseover|onvolumechange|onended|onmouseup|onwaiting],"
            + "a[target<_blank?_self?_top?_parent|ping|media|href|hreflang|type"
            + "|rel<alternate?archives?author?bookmark?external?feed?first?help?index?last?license?next?nofollow?noreferrer?prev?search?sidebar?tag?up"
            + "],"
            + "abbr,"
            + "address,"
            + "area[alt|coords|shape|href|target<_blank?_self?_top?_parent|ping|media|hreflang|type|shape<circle?default?poly?rect"
            + "|rel<alternate?archives?author?bookmark?external?feed?first?help?index?last?license?next?nofollow?noreferrer?prev?search?sidebar?tag?up"
            + "],"
            + "article,"
            + "aside,"
            + "audio[src|preload<none?metadata?auto|autoplay<autoplay|loop<loop|controls<controls|mediagroup],"
            + "blockquote[cite],"
            + "body,"
            + "br,"
            + "button[autofocus<autofocus|disabled<disabled|form|formaction|formenctype|formmethod<get?put?post?delete|formnovalidate?novalidate|"
            + "formtarget<_blank?_self?_top?_parent|name|type<reset?submit?button|value],"
            + "canvas[width,height],"
            + "caption,"
            + "cite,"
            + "code,"
            + "col[span],"
            + "colgroup[span],"
            + "command[type<command?checkbox?radio|label|icon|disabled<disabled|checked<checked|radiogroup|default<default],"
            + "datalist[data],"
            + "dd,"
            + "del[cite|datetime],"
            + "details[open<open],"
            + "dfn,"
            + "div,"
            + "dl,"
            + "dt,"
            + "em/i,"
            + "embed[src|type|width|height],"
            + "eventsource[src],"
            + "fieldset[disabled<disabled|form|name],"
            + "figcaption,"
            + "figure,"
            + "footer,"
            + "form[accept-charset|action|enctype|method<get?post?put?delete|name|novalidate<novalidate|target<_blank?_self?_top?_parent],"
            + "-h1,-h2,-h3,-h4,-h5,-h6,"
            + "header,"
            + "hgroup,"
            + "hr,"
            + "iframe[name|src|srcdoc|seamless<seamless|width|height|sandbox],"
            + "img[alt=|src|ismap|usemap|width|height],"
            + "input[accept|alt|autocomplete<on?off|autofocus<autofocus|checked<checked|disabled<disabled"
            + "|form|formaction|formenctype|formmethod<get?put?post?delete|formnovalidate?novalidate|formtarget<_blank?_self?_top?_parent"
            + "|height|list|max|maxlength|min|multiple<multiple|name|pattern|placeholder|readonly<readonly|required<required"
            + "|size|src|step|type<hidden?text?search?tel?url?email?password?datetime?date?month?week?time?datetime-local?number?range?color"
            + "?checkbox?radio?file?submit?image?reset?button?value|width],"
            + "ins[cite|datetime],"
            + "kbd,"
            + "keygen[autofocus<autofocus|challenge|disabled<disabled|form|name],"
            + "label[for|form],"
            + "legend,"
            + "li[value],"
            + "mark,"
            + "map[name],"
            + "menu[type<context?toolbar?list|label],"
            + "meter[value|min|low|high|max|optimum],"
            + "nav,"
            + "noscript,"
            + "object[data|type|name|usemap|form|width|height],"
            + "ol[reversed|start],"
            + "optgroup[disabled<disabled|label],"
            + "option[disabled<disabled|label|selected<selected|value],"
            + "output[for|form|name],"
            + "-p,"
            + "param[name,value],"
            + "-pre,"
            + "progress[value,max],"
            + "q[cite],"
            + "ruby,"
            + "rp,"
            + "rt,"
            + "samp,"
            + "script[src|async<async|defer<defer|type|charset],"
            + "section,"
            + "select[autofocus<autofocus|disabled<disabled|form|multiple<multiple|name|size],"
            + "small,"
            + "source[src|type|media],"
            + "span,"
            + "-strong/b,"
            + "-sub,"
            + "summary,"
            + "-sup,"
            + "table,"
            + "tbody,"
            + "td[colspan|rowspan|headers],"
            + "textarea[autofocus<autofocus|disabled<disabled|form|maxlength|name|placeholder|readonly<readonly|required<required|rows|cols|wrap<soft|hard],"
            + "tfoot,"
            + "th[colspan|rowspan|headers|scope],"
            + "thead,"
            + "time[datetime],"
            + "tr,"
            + "ul,"
            + "var,"
            + "video[preload<none?metadata?auto|src|crossorigin|poster|autoplay<autoplay|"
            + "mediagroup|loop<loop|muted<muted|controls<controls|width|height],"
            + "wbr",
*/



        /* disable the gecko spellcheck since AtD provides one */
        gecko_spellcheck: false,
        /* the URL to the button image to display */
        atd_button_url: "Vendor/tinymce4/plugins/atd/atdbuttontr.gif",
        /* the URL of your proxy file */
        atd_rpc_url: "Vendor/tinymce4/plugins/atd/proxy.php?lang=%s&url=",
        /* set your API key */
        atd_rpc_id: "dashnine",
        /* edit this file to customize how AtD shows errors */
        atd_css_url: "Vendor/tinymce4/plugins/atd/css/content.css",
        /* this list contains the categories of errors we want to show */
        atd_show_types: 'Bias Language,Cliches,Complex Expression,Diacritical Marks,Double Negatives,Hidden Verbs,Jargon Language,Passive voice,Phrases to Avoid,Redundant Expression',
        /* strings this plugin should ignore */
        atd_ignore_strings: ['AtD', 'rsmudge'],
        /* enable "Ignore Always" menu item, uses cookies by default. Set atd_ignore_rpc_url to a URL AtD should send ignore requests to. */
        atd_ignore_enable: false,

        image_advtab: true,
        file_browser_url: 'admin.php?adm=fileman&mode=tinymce',
        baseUrl: '',

        // spellchecker_rpc_url: '/Vendor/tinymce4/plugins/spellchecker/tinymce_spellchecker/spellchecker.php',


        onFullscreenResize: function (editor, tb, editorContain, realContainer, fullscreenContainer) {
            var h = $(window).height() - tb.outerHeight(true);
            tb.width($(window).width());

            fullscreenContainer.find('.resize_mce').height(h).width($(window).width()).parent().height(h).width($(window).width());
            fullscreenContainer.find('.nano').height(h).nanoScroller({contentClass: 'inline-mce'});

        },
        onFullscreen: function (editor, toFullscreen, tb, editorContain, realContainer, fullscreenContainer) {
            if (toFullscreen) {
                var h = $(window).height() - tb.outerHeight(true);
                tb.width($(window).width());

                fullscreenContainer.find('.resize_mce').height(h).width($(window).width());
                fullscreenContainer.find('.nano').height(h).nanoScroller({contentClass: 'inline-mce'});
                fullscreenContainer.filter('ui-resizable').resizable('disable');

            }
            else {
                realContainer.height('').width('');
                realContainer.find('.nano').nanoScroller({contentClass: 'inline-mce'});
                realContainer.filter('ui-resizable').resizable('enable');
            }
        },
        init_instance_callback: function (ed) {


            if (ed.settings.inline) {
                ed.fire('focus');
/*
                ed.isNotDirty = true;
                ed.undoManager.clear();
                ed.settings.isNotDirtyCalled = true;

                ed.setContent(ed.settings.value, {format: 'html'});
                ed.startContent = ed.getContent({format: 'raw'});
                ed.nodeChanged({initial: true});
                ed.isNotDirty = true;
                ed.settings.isNotDirtyCalled = true;
                ed.undoManager.add();

                var bm = ed.selection.getBookmark(0);

                var content = ed.getContent();
                var cursorIndex = Doc.getCursorPosition(ed);
                //var new_content = content.replace(ed.settings.value, "");
                ed.setContent(ed.settings.value);
                Doc.setCursorPosition(ed, cursorIndex);
                ed.nodeChanged({initial: true});
                ed.undoManager.add();

                ed.settings.isNotDirtyCalled = false;
                ed.isNotDirty = true;
                //ed.fire('reset');
            */
                tinymce.execCommand('mceFocus', false, ed.id );

                setTimeout(function() {
                    $(ed.settings.fixed_toolbar_container).find('div.mce-tinymce').removeClass('forceVisible').hide();
                    $(ed.settings.fixed_toolbar_container).find('div.mce-tinymce:first').addClass('forceVisible').show();
                    ed.fire('blur');
                    $(  ed.settings.fixed_toolbar_container ).find('div').height('').width('');
                    $('#' + ed.id ).parent().nanoScroller({contentClass: 'inline-mce'});


                }, 50);
            }
            else {
                $('#' + ed.id).addClass('tinymce-basic');

                if (ed.theme.panel._id) {
                    $('#' + ed.theme.panel._id).addClass('tinymce-basic');
                }
            }

            $('#' + ed.id).parents('form:first').unbind('reset').unbind('submit');


            setTimeout(function() { $(window).trigger('resize'); }, 120);
        },

        setup: function (editor) {
            /**
             * Only for the Preview Mode
             */
            if (typeof editor.previewScripts == 'undefined') {
                editor.previewScripts = [];
            }

            $('script[src*="/jquery-"]').each(function () {
                if ($(this).attr('src')) {
                    editor.previewScripts.push($(this).attr('src'));
                }
            });
            $('script[src*="/bootstrap.js"]').each(function () {
                editor.previewScripts.push($(this).attr('src'));
            });
            $('script[src*="/dcms.bootstrap."]').each(function () {
                editor.previewScripts.push($(this).attr('src'));
            });

            editor.previewScripts.push('https://maps.googleapis.com/maps/api/js?sensor=true');
            editor.previewScripts.push('html/js/dcms.googlemap.js');

            $('link[href*="bootstrap.css"]').each(function () {
                editor.contentCSS.push($(this).attr('href'));
            });
            $('link[href*="bootstrap-"]').each(function () {
                editor.contentCSS.push($(this).attr('href'));
            });
            $('link[href*="contentgrid.css"]').each(function () {
                editor.contentCSS.push($(this).attr('href'));
            });

            var configKeyUpEvent = (typeof Config == 'object' && typeof Config.get('onTinyMCEKeyUp') === 'function' ? Config.get('onTinyMCEKeyUp') : false);
            var t;


            if (typeof configKeyUpEvent === 'function') {
                editor.on('keyUp', function (e) {
                    clearTimeout(Form.autosaveT); // clear autosave timeout

                    if (e.keyCode == 13) {
                        $(e.target).parent().nanoScroller({contentClass: 'inline-mce'});
                    }
                    configKeyUpEvent(editor, e);
                });
            }
            else {
                editor.on('keyUp', function (e) {
                    if (e.keyCode == 13) {
                        clearTimeout(Form.autosaveT); // clear autosave timeout
                        $(e.target).parent().nanoScroller({contentClass: 'inline-mce'});
                    }
                });
            }


            editor.on('change', function (ed) {
                //clearTimeout(t);

                //t = setTimeout(function() {
                //	if ( ed.target.lastString != ed.target.getContent()) {
                // little patch for startup dirty
                if (ed.target.isDirty() && !ed.target.dirty && ed.target.settings.isNotDirtyCalled === true) {
                    ed.target.dirty = true;
                    // clearTimeout( Form.autosaveT ); clear autosave timeout

                    var id = ed.target.id.replace('inline-', '');
                    var field = $('#' + id);

                    Form.setDirty(false, field.parents('form:last'), Win.windowID);
                }

                ed.target.settings.isNotDirtyCalled = true;
                //Doc.lastActiveTinyMCESelect = tinymce.activeEditor.selection.getBookmark();

                //	}
                //}, 200);
            });





            editor.on('init', function (ed, e) {
                //var area = $('#' + ed.target.id);

                if (ed.target.settings.inline)
                {
                    ed.target.fire('focus');
                    ed.target.execCommand('mceAddControl', true, ed.target.id);
                    ed.target.fire('focus');
                }

                /*
                 if (ed.target.settings.fixed_toolbar_container && ed.target.settings.inline) {
                 setTimeout(function () {
                 ed.target.lastString = area.val();
                 ed.target.fire('focus');
                 // ed.target.fire('blur');

                 var visible = $(ed.target.settings.fixed_toolbar_container).find('div.mce-tinymce:visible').length;

                 if (visible > 1) {
                 $(ed.target.settings.fixed_toolbar_container).find('div.mce-tinymce').removeClass('forceVisible').hide();
                 if (typeof ed.target.theme.panel != 'undefined') {
                 if (typeof ed.target.theme.panel._id != 'undefined') {
                 $(ed.target.settings.fixed_toolbar_container).find('#' + ed.target.theme.panel._id).addClass('forceVisible').show();
                 }
                 }
                 }
                 else if (!visible) {

                 if (typeof ed.target.theme.panel == 'undefined') {
                 ed.target.execCommand('mceAddControl', true, ed.target.id);
                 ed.target.show();
                 if (typeof ed.target.theme.panel != 'undefined') {
                 if (typeof ed.target.theme.panel._id != 'undefined') {
                 $(ed.target.settings.fixed_toolbar_container).find('#' + ed.target.theme.panel._id).addClass('forceVisible').show();
                 }
                 }
                 }
                 else {
                 if (typeof ed.target.theme.panel != 'undefined') {
                 if (typeof ed.target.theme.panel._id != 'undefined') {
                 $(ed.target.settings.fixed_toolbar_container).find('#' + ed.target.theme.panel._id).addClass('forceVisible').show();
                 }
                 }
                 }
                 }
                 }, 5);
                 }
                 */
            });

            editor.on('focus', function (ed) {
                // ed.target.show();
                Doc.lastActiveTinyMCE = ed.target.id;
                if (ed.blurredEditor) {
                    //Doc.lastActiveTinyMCESelect = null;
                }

                if (ed.target.settings.inline) {
                    $(ed.target.settings.fixed_toolbar_container).removeClass('disable')
                    $('div.mce-tinymce', $(ed.target.settings.fixed_toolbar_container)).removeClass('forceVisible').hide();

                    if (ed.blurredEditor) {
                         $('#' + ed.blurredEditor.id).parent().removeClass('edit-focus');
                    }

                     $('#' + ed.target.id).parent().addClass('edit-focus');

                    if (ed.target.theme.panel) {
                        $(ed.target.settings.fixed_toolbar_container).find('#' + ed.target.theme.panel._id).addClass('forceVisible');
                    }
                }
            });

            editor.on('blur', function (ed) {

                if (ed.target.settings.inline) {
                    $('#' + ed.target.id).parent().removeClass('edit-focus');
                    $(ed.target.settings.fixed_toolbar_container).addClass('disable');

                    if (ed.target.theme.panel) {
                        var toolbarObj = $(ed.target.settings.fixed_toolbar_container).find('#' + ed.target.theme.panel._id); //.find('toolbar');
                        setTimeout(function () {
                            if (!$(ed.target.settings.fixed_toolbar_container).find('div.mce-tinymce:visible').length) {

                                toolbarObj.addClass('forceVisible').show();
                            }
                        });
                    }
                }
            });
        }

    },

    prepareTinyMceSetup: function (config, isreload) {
        /*
         * --- FOR TINYMCE 3
         * var opts = this.tinyMceSetup;
         */
        var opts = this.tinyMce4Setup;


        opts.baseUrl = Config.get('portalurl', '') + '/';
        tinymce.baseURL = opts.baseURL.replace('{url}', Config.get('portalurl'));
        opts.script_url = opts.script_url.replace('{url}', Config.get('portalurl'));
        // opts.spellchecker_rpc_url = opts.spellchecker_rpc_url.replace( '{url}', Config.get( 'portalurl' ) );

        if (Config.get('googleapikey', false)) {
            opts.gApiKey = Config.get('googleapikey');
        }
        else {
            delete opts.gApiKey;
        }

        if (typeof config.plugins != 'undefined') {
            if (typeof config.plugins == 'object') {
                var outArray = Tools.convertObjectToArray(config.plugins);
                // outArray.push('dcmsfilemanager');
                // outArray.push('dcmsfilemanager');

                opts.plugins = outArray.join(',');
            }
            else {
                opts.plugins = config.plugins;
            }
        }


        if (typeof config.language != 'undefined') {
            opts.language = config.language;
        }

        if (typeof config.content_css != 'undefined') {
            opts.content_css = opts.content_css.replace('{extraTemplateCss}', config.content_css);
        }
        else {
            opts.content_css = opts.content_css.replace('{extraTemplateCss}', '');
        }

        if (typeof config.templates != 'undefined' && config.templates.length) {
            opts.templates = [];
            for (var i = 0; i < config.templates.length; ++i) {
                if (config.templates[i].title && config.templates[i].content) {
                    opts.templates.push(config.templates[i]);
                }
            }
        }

        /*
         --- FOR TINYMCE 3
         @deprecated

         if ( config.toolbar_1 && config.toolbar_1.length ) {
         opts.theme_advanced_buttons1 = config.toolbar_1.join( ',' );
         }
         if ( config.toolbar_2 && config.toolbar_2.length ) {
         opts.theme_advanced_buttons2 = config.toolbar_2.join( ',' );
         }
         if ( config.toolbar_3 && config.toolbar_3.length ) {
         opts.theme_advanced_buttons3 = config.toolbar_3.join( ',' );
         }
         if ( config.toolbar_4 && config.toolbar_4.length ) {
         opts.theme_advanced_buttons4 = config.toolbar_4.join( ',' );
         }
         */



        if (typeof config.toolbar_1 == 'object') {
            var toolbar_1 = Tools.convertObjectToArray(config.toolbar_1);
            if (toolbar_1 && toolbar_1.length) {
                toolbar_1.pop();
                opts.toolbar1 = toolbar_1.join(',');
            }
        }

        if (typeof config.toolbar_2 == 'object') {
            var toolbar_2 = Tools.convertObjectToArray(config.toolbar_2);
            if (toolbar_2 && toolbar_2.length) {
                toolbar_2.pop();
                opts.toolbar2 = toolbar_2.join(',');
            }
        }

        if (typeof config.toolbar_3 == 'object') {
            var toolbar_3 = Tools.convertObjectToArray(config.toolbar_3);
            if (toolbar_3 && toolbar_3.length) {
                toolbar_3.pop();
                opts.toolbar3 = toolbar_3.join(',');
            }
        }

        if (typeof config.toolbar_4 == 'object') {
            var toolbar_4 = Tools.convertObjectToArray(config.toolbar_4);
            if (toolbar_4 && toolbar_4.length) {
                toolbar_4.pop();
                opts.toolbar4 = toolbar_4.join(',');
            }
        }

        delete window.tinymceConfig;
        var t;
        // @see theme advanced/editor_template_src.js
        opts.onResize = function () {
            clearTimeout(t);
            t = setTimeout(function () {
                $(window).trigger('resize');
            }, 300);
        };

        // @see theme advanced/editor_template_src.js
        opts.onResizeStop = function () {
            clearTimeout(t);
            $(window).trigger('resize');
        };

        window.tinymceConfig = opts;
        return;
        // load plugins
        if (window.tinymce != 'undefined') {
            /*
             var plugins = opts.plugins;
             if (opts.plugins.match(/ /)) {
             var pl = opts.plugins.split(' ');
             plugins = pl.join(',');
             }
             */
            jQuery.getScript(opts.script_url + '?js=true&disk_cache=true&plugins=' + opts.plugins + '&src=true&core=true&languages=' + opts.language + '&themes=' + opts.theme, function () {
                "use strict";
            });
        }
    },

    refreshTinyMceConfig: function () {
        var self = this;
        $.get('admin.php?tinymce=getconfig', function (data) {
            if (Tools.responseIsOk(data) && data.tinymce) {
                self.prepareTinyMceSetup(data.tinymce, true);
            }
        });
    },
    t: null,
    repaintTinyMceDelay: function (callback) {
        var self = this;
        if (!Win.tinyMCELoaded) {
            this.t = setTimeout(function () {
                self.repaintTinyMceDelay(callback);
            }, 100);
        }
        else {
            clearTimeout(this.t);
            callback();
        }
    },

    repaintTinyMceEditors: function (callback) {
        var self = this;

        if (Win.windowID) {
            var baseHash = Win.windowID.replace('tab-', '').replace('content-', '');
            if (baseHash) {

                var tabs = $('#main-tabs li');
                if (tabs.length) {
                    tabs.each(function (i) {
                        var tab = $(this);
                        setTimeout(function () {
                            var hash = tab.attr('id').replace('tab-', '');
                            var editors = $('#content-' + hash).find('textarea.tinymce-editor');

                            if (editors.length) {
                                // tab.trigger('click');


                                editors.removeClass('loaded');

                                Win.setActive(hash);
                                self.unloadTinyMce($('#content-' + hash));

                                $('#buttons-' + hash).find('.tinyMCE-Toolbar').empty();
                                tab.removeClass('loaded');

                                self.loadTinyMce($('#content-' + hash), false, function () {
                                    $('#tab-' + hash).addClass('loaded');
                                    $('#buttons-' + hash).find('div.mce-tinymce-inline').hide().removeClass('forceVisible');
                                    $('#buttons-' + hash).find('div.mce-tinymce-inline:first').show().addClass('forceVisible');
                                    // Win.setActive(baseHash);
                                    //tab.trigger('click');

                                    if (i + 1 >= tabs.length) {
                                        // $('#tab-' + baseHash).trigger('click');
                                        Win.setActive(baseHash);
                                        if (callback) {
                                            callback();
                                        }
                                    }
                                });

                                // Win.setActive(baseHash);
                            }
                            else {
                                if (i + 1 >= tabs.length) {
                                    Win.setActive(baseHash);
                                    // $('#tab-' + baseHash).trigger('click');
                                    if (callback) {
                                        callback();
                                    }
                                }
                            }
                        }, 5);
                    });
                }
                else {
                    if (callback) {
                        callback();
                    }
                }
            }
            else {
                if (callback) {
                    callback();
                }
            }
        }
    },

    resetDocumentSettings: function (windowID) {
        var hash;
        if (!windowID.match(/^tab-/) && !windowID.match(/^content-/)) {
            return;
        }

        hash = windowID.replace('tab-', '').replace('content-', '');

        if ($('#meta-' + hash).find('form').length) {
            $('#meta-' + hash).find('form').get(0).reset();
            $('#meta-' + hash).find('select.inputS,input.inputR,input.inputC').each(function () {
                if ($(this).hasClass('inputS')) {
                    $(this).SelectBox('reset');
                }
                else if ($(this).hasClass('inputR') || $(this).hasClass('inputC')) {
                    var self = $(this), name = $(this).attr('name');

                    if ($(this).attr('default') == 'on' && !$(this).prop('checked')) {
                        $(this).prop('checked', true);
                        $(this).attr('checked', 'checked');
                        this.checked = true;
                    }

                    if ($(this).attr('default') == 'off' && $(this).prop('checked')) {
                        $(this).prop('checked', false);
                        $(this).removeAttr('checked');
                        this.checked = false;
                    }

                    $(this).trigger('change');
                    setTimeout(function () {
                        $(self).next().triggerHandler('doReset');// trigger the Zebra_TransForm
                    }, 5);
                }
            });
        }
    },
    lock: function (contentid, modul, action, pk, table, title, editlocation, callback) {
        if (modul && contentid) {
            $.ajax({
                type: "POST",
                url: 'admin.php',
                'data': {
                    action: 'lock',
                    unlock: true,
                    modul: modul,
                    modulaction: action,
                    contentid: contentid,
                    pk: pk,
                    table: table,
                    title: title,
                    location: editlocation
                },
                timeout: 10000,
                dataType: 'json',
                cache: false,
                async: false,
                success: function (data) {
                    if (callback) {
                        callback();
                    }
                }
            });
        }
        else {
            if (callback) {
                callback();
            }
        }
    },
    unlock: function (contentid, modul, action, pk, table, callback) {
        if (modul && contentid) {
            $.ajax({
                type: "POST",
                url: 'admin.php',
                'data': {
                    action: 'unlock',
                    unlock: true,
                    modul: modul,
                    modulaction: action,
                    pk: pk,
                    table: table,
                    contentid: contentid
                },
                timeout: 10000,
                dataType: 'json',
                cache: false,
                async: false,
                success: function (data) {
                    if (callback) {
                        callback();
                    }
                }
            });
        }
        else {
            if (callback) {
                callback();
            }
        }
    },
    unload: function (hash, useContent, fullUnload, callback) {
        $('.ace-intellisense').hide();

        $('#panel-documentsettings #meta-' + hash + ',#buttons-' + hash).find('select').each(function () {
            var sb = $(this).attr('sb');
            if (sb) {
                $(this).SelectBox('destroy');
                $(this).removeClass('inputS').removeAttr('sb');
                $('#' + sb).remove();
            }
        });

        // remove ui
        if (fullUnload) {

            var content = $('#content-' + hash);

            content.find('select').each(function () {
                var sb = $(this).attr('sb');
                if (sb) {
                    $(this).SelectBox('destroy');
                    $(this).removeClass('inputS').removeAttr('sb');
                    $('#' + sb).remove();
                }
            });

            Doc.unloadAce(content);
            Doc.unloadTinyMce(content);

            var forms = content.find('form');
            if (forms.length) {
                forms.each(function () {
                    if ($(this).attr('id')) {
                        $(this).unload();
                        Form.destroy($(this), $(this).attr('id'));
                    }
                });
            }


        }
        else {
            if ( useContent ) {
                useContent.find('select').each(function () {
                    var sb = $(this).attr('sb');
                    if (sb) {
                        $(this).SelectBox('destroy');
                        $(this).removeClass('inputS').removeAttr('sb');
                        $('#' + sb).remove();
                    }
                });

                var content = useContent;
                Doc.unloadTinyMce(content);
                Doc.unloadAce(content);

                var forms = content.find('form');
                if (forms.length) {
                    forms.each(function () {
                        if ($(this).attr('id')) {
                            Form.destroy($(this), $(this).attr('id'));
                        }
                    });
                }
            }
            else {
                var content = $('#content-' + hash);
                useContent.find('select').each(function () {
                    var sb = $(this).attr('sb');
                    if (sb) {
                        $(this).SelectBox('destroy');
                        $(this).removeClass('inputS').removeAttr('sb');
                        $('#' + sb).remove();
                    }
                });

                var content = useContent;
                Doc.unloadTinyMce(content);
                Doc.unloadAce(content);

                var forms = content.find('form');
                if (forms.length) {
                    forms.each(function () {
                        if ($(this).attr('id')) {
                            Form.destroy($(this), $(this).attr('id'));
                        }
                    });
                }
            }

        }


    },
    unloadAce: function (win) {
        var editors = win.find('textarea.sourceEdit');
        if (editors.length) {
            editors.each(function () {
                if ($(this).data('ace')) {
                    $(this).data('ace').destroy();
                }
            });
        }
    },

    loadTinyMceConfig: function (win, callback, reloadConfig) {
        // reset all other configs
        if (typeof window.tinymceConfig != 'undefined' && reloadConfig === true) {
            window.tinymceConfig = {};
        }

        if (typeof tinyMCE == 'undefined') {
            $.get('../Vendor/tinymce/tiny_mce_src.js', function () {
                $.get('admin.php?tinymce=getconfig', function () {
                    if (typeof callback === 'function') {
                        callback(win);
                    }
                }, 'script');
            }, 'script');
        }
        else {
            if (reloadConfig || (typeof window.tinymceConfig == 'undefined' || typeof window.tinymceConfig.skin == 'undefined')) {
                $.get('admin.php?tinymce=getconfig', function () {
                    if (typeof callback === 'function') {
                        callback(win);
                    }
                }, 'script');
            }
            else {
                if (typeof callback === 'function') {
                    callback(win);
                }
            }
        }
    },

    loadTinyMce: function (win, isPopup, callback) {
        var self = this;

        if (typeof win != 'undefined' && win.length == 1) {
            Win.tinyMCELoaded = false;
            var o = window.tinymceConfig;
            var edit = $(win).find('textarea.tinymce-editor').not('.loaded');
            var hash = Win.windowID.replace('content-', '');


            if (!edit.length) {
                Win.tinyMCELoaded = true;

                if (callback) {
                    callback();
                }

                return;
            }

            var x = 0;
            edit.each(function (i) {

                var id = $(this).attr('id');
                if (typeof id != 'string') {
                    id = 'tinymce-' + new Date().getTime();
                    $(this).attr('id', id);
                }

                if (!$(this).hasClass('loaded')) {

                    $(this).addClass('loaded').show();
                    var container = $('<div><div class="inline-mce editable mce-content-body" id="inline-' + id + '" contenteditable="true"></div></div>').height($(this).height());
                    var mceResize = $('<div class="resize_mce" rel="inline-' + id + '"></div>');

                    mceResize.append(container);
                    mceResize.insertAfter($(this));

                    mceResize.resizable({
                        handles: "s",
                        maxWidth: $(this).parent().outerWidth() + 10,
                        minHeight: $(this).height(),
                        resize: function (e, ui) {
                            var fc = $(this).children(':first');
                            fc.height(ui.size.height - (parseInt(fc.css('paddingTop'), 10) * 3))
                        },
                        stop: function () {
                            $(this).children(':first').nanoScroller({contentClass: 'inline-mce'});
                        }
                    });


                    var tbarpos = $(this).attr('toolbarpos');
                    var tbar = $(this).attr('toolbar');
                    var opt = $.extend({}, o, {selector: 'div#inline-' + id});


                    opt.value = $(this).text();

                    if (isPopup || tbarpos == 'internal' || tbarpos == 'intern' || $(this).hasClass('internal') || tbar === 'internal') {
                        $((id ? '#' + id : this )).attr('toolbar', 'internal');

                        /*
                         var disabler = $( '<div/>' ).attr( 'class', 'disabler' );
                         var internalToolbar = $( '<div id="mce-internal-' + id + '"></div>' ).show();
                         internalToolbar.insertBefore( $( this ) );
                         internalToolbar.append(disabler);
                         */
                        opt.fixed_toolbar_container = null;
                        opt.selector = 'textarea#' + id;
                        opt.inline = false;

                        mceResize.remove();
                    }
                    else {
                        if ($('#main-content-buttons #buttons-' + hash).find('.tinyMCE-Toolbar').length == 0) {
                            var disabler = $('<div/>').attr('class', 'disabler');
                            var _toolbar = $('<div/>').addClass('tinyMCE-Toolbar');
                            $('#main-content-buttons #buttons-' + hash).append(_toolbar);
                            _toolbar = null;

                            tb = $('#main-content-buttons #buttons-' + hash).find('.tinyMCE-Toolbar');
                            tb.empty().append(disabler).show();
                        }
                        else {
                            $('#main-content-buttons #buttons-' + hash).find('.tinyMCE-Toolbar').show();
                        }

                        opt.fixed_toolbar_container = '#buttons-' + hash + ' .tinyMCE-Toolbar';
                        opt.selector = 'div#inline-' + id;

                        // $('#inline-' + id).parent().nanoScroller( {contentClass: 'inline-mce'} );
                    }


                    if (opt.inline) {
                        $('#inline-' + id).html($( this ).text().replace('&lt;', '<').replace('&gt;', '>') );

                        if (!opt.value) {
                            opt.value = '<p> </p>';
                        }
                        else {
                            //opt.value = opt.value.replace(/\u00a0/g, ' ');
                            //opt.value = opt.value.replace(/\x00A0/g, ' ');
                            opt.value = opt.value.replace(/<pre([^>]*)>\s*\t*\n*/g, '<pre$1>');
                        }
                    }

                    opt.editornum = x++;

                    var xself = this;
                    tinymce.init(opt);

                    $(this).addClass('hide'); // hide textarea
                }


                if (i >= edit.length - 1) {

                    setTimeout(function () {
                        tinymce.execCommand('mceFocus', false, $(win).find('textarea.tinymce-editor').eq(0).attr('id'));
                        // Tools.scrollBar($('#inline-' + id));

                        Win.tinyMCELoaded = true;

                        if (callback) {
                            callback();
                        }
                    }, 5);
                }
            });


        }
        else {
            Win.tinyMCELoaded = true;

            if (callback) {
                callback();
            }
        }
    },

    unloadTinyMce: function (inobject) {
        //       Debug.log('Unload TinyMce...');
        var self = this;
        var areas = $(inobject).find('textarea.tinymce-editor');


        if ( tinymce )
        {
            tinymce.triggerSave();
            var len = tinymce.editors.length;
            for (var i = 0; i<tinymce.editors.length;++i) {
                if (typeof tinymce.editors[i] !== 'undefined') {
                    var id = tinymce.editors[i].id;
                    tinymce.execCommand('mceRemoveControl', false, tinymce.editors[i].id);
                    $('#' + id).parents('.resize_mce:first').remove();

                    delete tinymce.editors[i];
                    delete tinymce.editors[id];
                    len--;

                    var idname = id.replace('inline-', '');
                    $('#'+ idname).removeClass('loaded').removeClass('hide').show();
                }
            }


            if (tinymce.editors.length != len && len >= 0) {
                tinymce.editors.length = len;
            }
        }



        var obj = $(inobject);
        if (!obj.hasClass('core-tab-content')) {
            obj = obj.parents('div.core-tab-content:first');
        }

        if (obj.length == 1) {
            var hash = obj.attr('id').replace('content-', '').replace('tab-', '');
            if (!$('#buttons-' + hash).find('div.forceVisible').length) {
                $('#buttons-' + hash).find('div.mce-tinymce-inline').hide().removeClass('forceVisible');
                $('#buttons-' + hash).find('div.mce-tinymce-inline:first').show().addClass('forceVisible');
            }
        }

        /*

        if (areas.length) {
            var editors = tinymce.editors;

            areas.each(function (y) {

                var inputid = $(this).attr('id');

                if (y == 0) {
                    updateTextareaFields($(this).parents('form:first'));
                }

                tinymce.execCommand('mceRemoveControl', true, $(this).attr('id'));
                delete tinymce.editors[i];
                $('#inline-' + inputid).parents('.resize_mce:first').remove();
                $(this).removeClass('loaded').removeClass('hide').show(); //.removeData();
            });

            var obj = $(inobject);
            if (!obj.hasClass('core-tab-content')) {
                obj = obj.parents('div.core-tab-content:first');
            }

            if (obj.length == 1) {
                var hash = obj.attr('id').replace('content-', '').replace('tab-', '');
                if (!$('#buttons-' + hash).find('div.forceVisible').length) {
                    $('#buttons-' + hash).find('div.mce-tinymce-inline').hide().removeClass('forceVisible');
                    $('#buttons-' + hash).find('div.mce-tinymce-inline:first').show().addClass('forceVisible');
                }
            }
        }
        */
    },

    removeTinyMceToolbar: function () {
        this.unloadTinyMce($('#' + Win.windowID));
    },
    loadedMcs: 0,
    setTinyMceToolbar: function (tinymceToolbar, ed, editorid, pos) {
        var self = this, tb, winID = Win.windowID, disabler = $('<div/>').attr('id', 'disabler');

        if (pos == 'extern') {

            var hash = winID.replace('content-', '');

            if ($('#main-content-buttons #buttons-' + hash).find('.tinyMCE-Toolbar').length == 0) {
                var _toolbar = $('<div/>').addClass('tinyMCE-Toolbar');
                $('#main-content-buttons #buttons-' + hash).append(_toolbar);
                _toolbar = null;

                tb = $('#main-content-buttons #buttons-' + hash).find('.tinyMCE-Toolbar');
                tb.empty().append(disabler.hide());
                tb.show();
            }
            else {
                tb = $('#main-content-buttons #buttons-' + hash).find('.tinyMCE-Toolbar');
                tb.show();
            }

            if ($('#' + winID).length == 1) {
                // var externalToolbar = false;
                if (this.tinyMceEditors > 1 && $('#main-content-buttons #buttons-' + hash).find('.mceExternalToolbar').length > 1) {
                    $('#main-content-buttons #buttons-' + hash).find('.mceExternalToolbar').hide();
                    // externalToolbar = true;
                }

                if (this.tinyMceEditors > 1 && $('#main-content-buttons #buttons-' + hash).find('.mceExternalToolbar').length > 1) {
                    $('#main-content-buttons #buttons-' + hash).find('.mceExternalToolbar').hide();
                    // externalToolbar = true;
                }

                // if (externalToolbar)
                //  {
                tb.append($(tinymceToolbar).addClass('mceEditor dcmsSkin').show()).show();
                tb.find('td.mceToolbar').removeClass('mceToolbar');
                // }

                $('#' + editorid + '_external').show();
                $('#' + editorid).next().find('iframe:first').show();

                if (tb.find('.mceExternalToolbar').length > 1) {
                    tb.find('.mceExternalToolbar').hide();
                    tb.find('.mceExternalToolbar:first').show();
                }

                self.loadedMcs++;
            }

        }
        else {
            if (!$('#' + editorid).prev().is('.tinyMCE-Toolbar')) {
                var _toolbar = $('<div/>').addClass('tinyMCE-Toolbar internal');
                _toolbar.insertBefore($('#' + editorid));
                tb = $('#' + editorid).prev();
                tb.empty().append(disabler.hide());
                tb.show();
            }
            else {
                tb = $('#' + editorid).prev();
                tb.show();
            }

            if ($('#' + winID).length == 1) {
                // if (externalToolbar)
                //  {
                tb.append($(tinymceToolbar).addClass('mceEditor dcmsSkin').show()).show();
                tb.find('td.mceToolbar').removeClass('mceToolbar');
                // }

                $('#' + editorid + '_external').show();
                $('#' + editorid).next().find('iframe:first').show();

                self.loadedMcs++;
            }
        }

    },
    enableTinyMceToolbar: function (tinymceToolbar, e, editorid, windowID, pos) {
        var winID = $('#' + editorid).parents('.core-tab-content').attr('id');

        if (windowID !== winID) {
            winID = windowID;
        }

        if (winID && $('#' + winID).length == 1) {
            var area = $('#' + editorid);
            var hash = winID.replace('content-', '');

            if ($('#main-content-buttons #buttons-' + hash).find('.tinyMCE-Toolbar').length) {
                var tb = $('#main-content-buttons #buttons-' + hash).find('.tinyMCE-Toolbar');

                $('#' + winID).find('table.mceLayout').removeClass('focused');

                tb.find('.mceExternalToolbar').hide();

                $('#' + editorid + '_external').removeClass('disabled').show();

                if (!tb.find('.mceExternalToolbar:visible').length) {
                    tb.find('.mceExternalToolbar:first').show();
                }

                this.lastActiveTinyMCE = editorid;

                $('#' + editorid).css({display: ''});

                $('#' + editorid + '_tbl').addClass('focused');

                tb.removeClass('disabled');
                tb.find('#disabler').hide();
                tb.find('.mceExternalToolbar').removeClass('disabled');
            }
            else {
                var tb = $('#' + editorid).prev();
                tb.find('.mceExternalToolbar').hide();
                $('#' + editorid).parent().find('table.mceLayout').removeClass('focused');
                $('#' + editorid + '_external').removeClass('disabled').show();

                if (!tb.find('.mceExternalToolbar:visible').length) {
                    tb.find('.mceExternalToolbar:first').show();
                }

                $('#' + editorid).css({display: ''});

                $('#' + editorid + '_tbl').addClass('focused');

                tb.removeClass('disabled');
                tb.find('#disabler').hide();
                tb.find('.mceExternalToolbar').removeClass('disabled');

            }

            if (area.attr('toolbar') == 'internal') {
                area.prev().removeClass('disabled').find('#disabler').removeClass('disabled').hide();
            }

        }
        else {
            //  console.log('enableTinyMceToolbar winID not found');
        }

    },
    disableTinyMceToolbar: function (tinymceToolbar, e, editorid, windowID) {
        if (this.editor_onmenu) {
            return;
        }
        var area = $('#' + editorid);
        var winID = $('#' + editorid).parents('.core-tab-content').attr('id');

        if (windowID !== winID) {
            winID = windowID;
        }

        if (winID && $('#' + winID).length == 1 && area.attr('toolbar') != 'internal') {
            var hash = winID.replace('content-', '');

            var tb = $('#main-content-buttons #buttons-' + hash).find('.tinyMCE-Toolbar');
            tb.addClass('disabled').find('#disabler').addClass('disabled').show();
            $('#' + editorid + '_tbl').removeClass('focused');

            if (tb.find('.mceExternalToolbar').length > 1) {
                $('#' + editorid + '_external').addClass('disabled').hide();
            }
            else {
                $('#' + editorid + '_external').addClass('disabled').show();
            }

            tb.show();

            if (tb.find('.mceExternalToolbar').length > 1 && !tb.find('.mceExternalToolbar:visible').length) {
                tb.find('.mceExternalToolbar:first').show();
            }
        }

        if (area.attr('toolbar') == 'internal') {

            area.prev().addClass('disabled').find('#disabler').addClass('disabled').show();
            $('#' + editorid + '_tbl').removeClass('focused');

        }

    },
    doInsertRichText: function (noedit, attributes, label, tag, isStatic) {


        if ($('#' + Win.windowID).find('.tinymce-editor').length > 0 && this.lastActiveTinyMCE)
        {
            tinymce.execCommand('mceFocus', false, this.lastActiveTinyMCE );

            if (tinyMCE.activeEditor != null)
            {
                // var cursorIndex = this.getCursorPosition(this.activeEditor);
                var bookmark = tinyMCE.activeEditor.selection.getBookmark()

                if ( bookmark )
                {
                    tinymce.activeEditor.selection.moveToBookmark(bookmark, true);
                }



                var sel = tinyMCE.activeEditor.selection.getContent();
                var str = '';

                if (sel) {
                    str += '<a href="#" ' + attributes + ' data-notitle="true">' + sel + '</a>';
                }
                else {
                    str += '<a href="#" ' + attributes + '>' + label + '</a>';
                }

                if (isStatic === true) {
                    $.post('admin.php', {adm: 'dashboard', action: 'getstaticlink', linkcode: str}, function (data) {
                        if (Tools.responseIsOk(data)) {
                            tinyMCE.activeEditor.selection.setContent(data.link);
                        }
                    }, 'json');
                }
                else {
                    tinyMCE.activeEditor.selection.setContent(str);
                }
            }
        }
    },

    diffUndo: function () {
        var dv = $('#' + Win.windowID).data('dv');
        if (dv) {
            //   dv.edit.undo();
            dv.right.orig.undo();
            dv.refresh();
        }
    },
    diffRedo: function () {
        var dv = $('#' + Win.windowID).data('dv');
        if (dv) {
            // dv.edit.redo();
            dv.right.orig.redo();
            dv.refresh();
        }
    },
    triggerDiffChangeTinyMCE: function (diff) {
        var opener = $('#' + Win.windowID).attr('opener');
        if (opener) {
            var id = $('#' + opener).find('.tinymce-editor:eq(0)').attr('id');
            if (id && tinyMCE) {
                tinyMCE.get(id).setContent(diff.orig.getValue());
            }
        }
    },
    loadedDiffMirror: false,

    initDiff: function (containerID, currentvalue, targetvalue, panes) {
        var dv;
        panes = panes || 2;
        if (currentvalue == null || targetvalue == null) {
            currentvalue = currentvalue != null ? currentvalue : $('#' + containerID + '-source').get(0).innerHTML;
            targetvalue = targetvalue != null ? targetvalue : $('#' + containerID + '-target').get(0).innerHTML;
        }

        var self = this;
        var target = document.getElementById(containerID);
        target.innerHTML = "";

        var srcEdit = new _dcmsSourceEditor();
        var config = srcEdit.getConfig('xml');
        config.gutters = ["CodeMirror-linenumbers"];

        if (!self.loadedDiffMirror) {
            Tools.loadScript('Vendor/codemirror/mode/htmlmixed/htmlmixed.js', function () {
                Tools.loadScript('Vendor/codemirror/mode/xml/xml.js', function () {
                    self.loadedDiffMirror = true;
                    config.value = targetvalue;
                    config.origLeft = panes == 3 ? targetvalue : null;
                    config.orig = currentvalue;

                    config.onAfterChange = function (diff) {
                        self.triggerDiffChangeTinyMCE(diff);
                    };

                    $('#merge-view .CodeMirror-merge-pane:first').prepend('<div class="source-label">' + cmslang.versionTarget + '</div>');
                    $('#merge-view .CodeMirror-merge-pane:last').prepend('<div class="target-label">' + cmslang.versionSource + '</div>');

                });
            });
        }
        else {


            config.value = targetvalue;
            config.origLeft = panes == 3 ? targetvalue : null;
            config.orig = currentvalue;
            config.onAfterChange = function (diff) {
                self.triggerDiffChangeTinyMCE(diff);
            };


            $('#merge-view .CodeMirror-merge-pane:first').prepend('<div class="source-label">' + cmslang.versionTarget + '</div>');
            $('#merge-view .CodeMirror-merge-pane:last').prepend('<div class="target-label">' + cmslang.versionSource + '</div>')
        }
    }
};