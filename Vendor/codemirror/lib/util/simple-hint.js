(function() {
    CodeMirror.simpleHint = function(editor, getHints, givenOptions) {
        // Determine effective options based on given values and defaults.
        var options = {}, defaults = CodeMirror.simpleHint.defaults;
        for (var opt in defaults)
            if (defaults.hasOwnProperty(opt))
                options[opt] = (givenOptions && givenOptions.hasOwnProperty(opt) ? givenOptions : defaults)[opt];

        function collectHints(previousToken) {
            // We want a single cursor position.
            if (editor.somethingSelected())
                return;

            var tempToken = editor.getTokenAt(editor.getCursor());

            // Don't show completions if token has changed and the option is set.
            if (options.closeOnTokenChange && previousToken != null &&
                    (tempToken.start != previousToken.start || tempToken.type != previousToken.type)) {
                return;
            }

            var result = getHints(editor, givenOptions);
            if (!result || !result.list.length)
                return;
            var completions = result.list;
            function insert(str) {
                editor.replaceRange(str, result.from, result.to);
            }
            // When there is only one completion, use it directly.
            if (options.completeSingle && completions.length == 1) {
                insert(completions[0]);
                return true;
            }

            /*
             // simple wrapper method to get some cursor information from codemirror
             
             var cursor = editor.getCursor();
             var info = editor.lineInfo(cursor.line);        
             
             if ( completions.length > 0 ) cscc.show(info.line, cursor.ch, completions, '');
             else cscc.hide();
             
             return;
             
             
             
             */


            if ($('.CodeMirror-completions:visible').length)
            {
                $('.CodeMirror-completions:visible').hide().remove();
            }


            // Build the select widget
            var complete = document.createElement("div");
            complete.className = "CodeMirror-completions cmc-suggestions";
            //complete.setAttribute('style', 'overflow:auto');



            var subDiv = document.createElement("div");
            subDiv.setAttribute('style', 'overflow-y:auto;overflow-x:hidden');


            var ul = document.createElement("ul");


            var sel = subDiv.appendChild(ul);


            complete.appendChild(subDiv);


            //var sel = complete.appendChild(ul);

            // Opera doesn't move the selection when pressing up/down in a
            // multi-select, but it does properly support the size property on
            // single-selects, so no multi-select is necessary.
            if (!window.opera)
                sel.multiple = false; // mod by dw2k

            for (var i = 0; i < completions.length; ++i) {
                var opt = sel.appendChild(document.createElement("li"));
                opt.setAttribute('rel', completions[i]);
                if (i == 0)
                    opt.setAttribute('class', 'selected');

                opt.appendChild(document.createTextNode(completions[i]));
            }



            sel.firstChild.selected = true;
            sel.size = Math.min(10, completions.length);
            var pos = editor.cursorCoords(options.alignWithWord ? result.from : null);
            complete.style.left = pos.left + "px";
            complete.style.top = pos.bottom + "px";


            document.body.appendChild(complete);

            // If we're at the edge of the screen, then we want the menu to appear on the left of the cursor.
            var winW = window.innerWidth || Math.max(document.body.offsetWidth, document.documentElement.offsetWidth);
            if (winW - pos.left < sel.clientWidth)
                complete.style.left = (pos.left - sel.clientWidth) + "px";

            // Hack to hide the scrollbar.
            if (completions.length <= 10)
                complete.style.width = (sel.clientWidth - 1) + "px";

            var visible = true;
            var done = false;
            var selectedItem;

            function close() {
                if (done)
                    return;
                done = true;
                visible = false;
                document.body.removeChild(complete);
                $(complete).remove();
            }
            function pick() {

                var v = $(complete).find('.selected').attr('rel');
                if (v)
                {
                    insert(v);
                }

                // insert(completions[sel.selectedIndex]);
                close();

                setTimeout(function() {
                    editor.focus();
                }, 50);
            }
            function getPrev()
            {
                var results = complete.getElementsByTagName("li");
                var selectedIndex = -1;
                for (var i = 0; i < results.length; i++) {
                    var result = results[i];
                    if (result.className.indexOf("selected") != -1) {
                        selectedIndex = i;
                        result.className = "";
                    }
                }
                if (selectedIndex > 0)
                    selectedIndex--;
                else
                    selectedIndex = results.length - 1;


                var item = results[selectedIndex];
                if (item) {
                    selectedItem = item;
                    item.className = "selected";
                }

                if (results[selectedIndex].scrollIntoView) { //added by we:willRockYou - will scroll selected item into viewport. the div has now overflow-y:auto, so the currently selected item might be outside of viewport
                    results[selectedIndex].scrollIntoView();
                }
            }



            function getNext()
            {
                var results = complete.getElementsByTagName("li");
                var selectedIndex = -1;
                for (var i = 0; i < results.length; i++) {
                    var result = results[i];
                    if (result.className.indexOf("selected") != -1) {
                        selectedIndex = i;
                        result.className = "";
                    }
                }
                if (selectedIndex < results.length - 1)
                    selectedIndex++;
                else
                    selectedIndex = 0;

                var item = results[selectedIndex];

                if (item) {
                    selectedItem = item;
                    item.className = "selected";
                }

                if (results[selectedIndex].scrollIntoView) { //added by we:willRockYou - will scroll selected item into viewport. the div has now overflow-y:auto, so the currently selected item might be outside of viewport
                    results[selectedIndex].scrollIntoView();
                }

            }


            complete.focus();
            // sel.focus();

            CodeMirror.on("keydown", function(event) {
                var code = event.keyCode;
                switch (code)
                {
                    case 38: // up
                        if (visible) {
                            getPrev();
                            event.stop();
                            return false;
                        }
                        break;
                    case 40: // down
                        if (visible) {
                            getNext();
                            event.stop();
                            return false;
                        }
                        break;
                    case 13: // enter
                    case 9: // tab
                        if (visible) {
                            pick();
                            return false;
                        }
                        break;
                    case 27: // escape
                        if (visible) {
                            close();
                            event.stop();
                            return false;
                        }
                        break;
                }

                return true;
            });
            /*
             CodeMirror.on("keyup", function(event) {
             var k = event.keyCode;
             if (k == 13)
             return; // enter
             if (k == 35 || k == 36 && visible)
             return close(); // home end
             if (k == 37 || k == 39 && visible)
             return close(); // left, right
             if (k == 83 && evt.ctrlKey && visible)
             return close(); // Ctrl + S
             });
             
             
             
             CodeMirror.on(sel, "blur", close);
             
             
             CodeMirror.on(sel, "keydown", function(event) {
             var code = event.keyCode;
             switch (code)
             {
             case 38: // up
             if (visible) {
             getPrev();
             event.stop();
             return false;
             }
             break;
             case 40: // down
             if (visible) {
             getNext();
             event.stop();
             return false;
             }
             break;
             case 9: // tab
             if (visible) {
             pick();
             return false;
             }
             break;
             }
             
             
             // Enter
             if (code == 13) {
             CodeMirror.e_stop(event);
             pick();
             }
             // Escape
             else if (code == 27) {
             CodeMirror.e_stop(event);
             close();
             editor.focus();
             }
             else if (code != 38 && code != 40 && code != 33 && code != 34 && !CodeMirror.isModifierKey(event)) {
             close();
             editor.focus();
             // Pass the event to the CodeMirror instance so that it can handle things like backspace properly.
             editor.triggerOnKeyDown(event);
             // Don't show completions if the code is backspace and the option is set.
             if (!options.closeOnBackspace || code != 8) {
             setTimeout(function() {
             collectHints(tempToken);
             }, 50);
             }
             }
             });
             
             
             */
            CodeMirror.on(sel, "dblclick", pick);




            // Opera sometimes ignores focusing a freshly created node
            if (window.opera)
                setTimeout(function() {
                    if (!done)
                        sel.focus();
                }, 100);
            return true;
        }
        return collectHints();
    };

    CodeMirror.simpleHint.defaults = {
        closeOnBackspace: true,
        closeOnTokenChange: false,
        completeSingle: true,
        alignWithWord: false
    };

    CodeMirror.simpleHint.keyDownEvent = function(event, editor, cm, getHints) {
        var code = event.keyCode, complete = $('.CodeMirror-completions:visible').get(0);
        if (!complete)
            return true;
        
        var options = {}, defaults = CodeMirror.simpleHint.defaults;
        for (var opt in defaults)
            if (defaults.hasOwnProperty(opt))
                options[opt] = defaults[opt];
        
        var result = getHints(editor, options);
        if (!result || !result.list.length)
            return;
        var completions = result.list;
        
        complete.focus();

        var visible = true;
        var done = false;
        var selectedItem;


        function insert(str) {
            editor.replaceRange(str, result.from, result.to);
        }


        function close() {
            if (done)
                return;
            done = true;
            visible = false;
            document.body.removeChild(complete);
            $(complete).remove();
        }
        function pick() {

            var v = $(complete).find('.selected').attr('rel');
            console.log([v]);

            insert(v);


            // insert(completions[sel.selectedIndex]);
            close();

            setTimeout(function() {
                editor.focus();
            }, 50);
        }
        function getPrev()
        {
            var results = complete.getElementsByTagName("li");
            var selectedIndex = -1;
            for (var i = 0; i < results.length; i++) {
                var result = results[i];
                if (result.className.indexOf("selected") != -1) {
                    selectedIndex = i;
                    result.className = "";
                }
            }
            if (selectedIndex > 0)
                selectedIndex--;
            else
                selectedIndex = results.length - 1;


            var item = results[selectedIndex];
            if (item) {
                selectedItem = item;
                item.className = "selected";
            }

            if (results[selectedIndex].scrollIntoView) { //added by we:willRockYou - will scroll selected item into viewport. the div has now overflow-y:auto, so the currently selected item might be outside of viewport
                results[selectedIndex].scrollIntoView();
            }
        }



        function getNext()
        {
            var results = complete.getElementsByTagName("li");
            var selectedIndex = -1;
            for (var i = 0; i < results.length; i++) {
                var result = results[i];
                if (result.className.indexOf("selected") != -1) {
                    selectedIndex = i;
                    result.className = "";
                }
            }
            if (selectedIndex < results.length - 1)
                selectedIndex++;
            else
                selectedIndex = 0;

            var item = results[selectedIndex];

            if (item) {
                selectedItem = item;
                item.className = "selected";
            }

            if (results[selectedIndex].scrollIntoView) { //added by we:willRockYou - will scroll selected item into viewport. the div has now overflow-y:auto, so the currently selected item might be outside of viewport
                results[selectedIndex].scrollIntoView();
            }

        }





        var code = event.keyCode;

        switch (code)
        {
            case 38: // up
                if (visible) {
                    getPrev();
                    event.stop();
                    return false;
                }
                break;
            case 40: // down
                if (visible) {
                    getNext();
                    event.stop();
                    return false;
                }
                break;
            case 13: // enter
            case 9: // tab
                if (visible) {
                    pick();
                    return false;
                }
                break;
            case 27: // escape
                if (visible) {
                    close();
                    event.stop();
                    return false;
                }
                break;
        }

    };


})();
