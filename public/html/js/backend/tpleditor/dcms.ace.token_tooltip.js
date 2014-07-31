
ace.define('dcms_ace/token_tooltip', ['require', 'exports', 'module', 'ace/lib/dom', 'ace/lib/event', 'ace/range'], function (require, exports, module) {


    var dom = require("ace/lib/dom");
    var event = require("ace/lib/event");
    var Range = require("ace/range").Range;

    var tooltipNode;

    var TokenTooltip = function (editor) {
        if (editor.tokenTooltip)
            return;
        editor.tokenTooltip = this;
        this.editor = editor;

        editor.tooltip = tooltipNode || this.$init();

        this.update = this.update.bind(this);
        this.onMouseMove = this.onMouseMove.bind(this);
        this.onMouseOut = this.onMouseOut.bind(this);
        
        event.removeListener(editor.renderer.scroller, "mousemove", this.onMouseMove);
        event.removeListener(editor.renderer.scroller, "mouseout", this.onMouseOut);
        
        
        event.addListener(editor.renderer.scroller, "mousemove", this.onMouseMove);
        event.addListener(editor.renderer.content, "mouseout", this.onMouseOut);
    };

    
    (function () {
        this.token = {};
        this.range = new Range();

        this.update = function () {
            this.$timer = null;

            var r = this.editor.renderer;
            if (this.lastT - (r.timeStamp || 0) > 1000) {
                r.rect = null;
                r.timeStamp = this.lastT;
                this.maxHeight = innerHeight;
                this.maxWidth = innerWidth;
            }

            var canvasPos = r.rect || (r.rect = r.scroller.getBoundingClientRect());
            var offset = (this.x + r.scrollLeft - canvasPos.left - r.$padding) / r.characterWidth;
            var row = Math.floor((this.y + r.scrollTop - canvasPos.top) / r.lineHeight);
            var col = Math.round(offset);

            var screenPos = {row: row, column: col, side: offset - col > 0 ? 1 : -1};
            var session = this.editor.session;
            var docPos = session.screenToDocumentPosition(screenPos.row, screenPos.column);
            var token = session.getTokenAt(docPos.row, docPos.column);
            
            if (!token) {
                session.removeMarker(this.marker);
                tooltipNode.style.display = "none";
                this.isOpen = false;
                return;
            }
            
            var name = token.value.substr(0,3);
            
            if ( name.toLowerCase() !== 'cp:' ){
                session.removeMarker(this.marker);
                tooltipNode.style.display = "none";
                this.isOpen = false;
                return;
            }
            
            if (typeof dcms_tags == 'undefined' ) {
                session.removeMarker(this.marker);
                tooltipNode.style.display = "none";
                this.isOpen = false;
                return;
            }
            
            if (typeof dcms_tags[token.value] == 'undefined')
            {
                dcms_tags[token.value] = { desc: '<em>Keine Beschreibung vorhanden...</em>' };
            }
            
            
            var dcmsTag = dcms_tags[token.value];

            if (!token && !session.getLine(docPos.row)) {
                token = {
                    type: "",
                    value: "",
                    state: session.bgTokenizer.getState(0)
                };
            }
            
            if (!token) {
                session.removeMarker(this.marker);
                tooltipNode.style.display = "none";
                this.isOpen = false;
                return;
            }
            
            if (!this.isOpen) {
                tooltipNode.style.display = "";
                this.isOpen = true;
            }

            var tokenText = '<p><strong>'+ token.value + '</strong></p>';

            if (dcmsTag.desc) {
                tokenText += "<p>"+ dcmsTag.desc + '</p>';
            }
            if (token.state || token.merge || token.stateTransitions)
            {
                tokenText += '<p>';
            }
            if (token.state)
                tokenText += "<br/>State: " + token.state + '';
            
            if (token.merge)
                tokenText += "<br/>merge";
            
            if (token.stateTransitions)
                tokenText += "<br/>" + token.stateTransitions.join("<br/>");
            
            if (token.state || token.merge || token.stateTransitions)
            {
                tokenText += '</p>';
            }
            

            if (this.tokenText != tokenText) {
                tooltipNode.innerHTML = tokenText;               
                this.tooltipWidth = tooltipNode.offsetWidth;
                this.tooltipHeight = tooltipNode.offsetHeight;
                this.tokenText = tokenText;
            }

            this.updateTooltipPosition(this.x, this.y);

            this.token = token;
            session.removeMarker(this.marker);
            this.range = new Range(docPos.row, token.start, docPos.row, token.start + token.value.length);
            this.marker = session.addMarker(this.range, "ace_bracket", "text");
        };

        this.onMouseMove = function (e) {
            this.x = e.clientX;
            this.y = e.clientY;
            if (this.isOpen) {
                this.lastT = e.timeStamp;
                this.updateTooltipPosition(this.x, this.y);
            }
            if (!this.$timer)
                this.$timer = setTimeout(this.update, 100);
        };

        this.onMouseOut = function (e) {
            var t = e && e.relatedTarget;
            var ct = e && e.currentTarget;
            while (t && (t = t.parentNode)) {
                if (t == ct)
                    return;
            }
            tooltipNode.style.display = "none";
            this.editor.session.removeMarker(this.marker);
            this.$timer = clearTimeout(this.$timer);
            this.isOpen = false;
        };

        this.updateTooltipPosition = function (x, y) {
            var st = tooltipNode.style;
            
            if (x + 10 + this.tooltipWidth > this.maxWidth)
                x = innerWidth - this.tooltipWidth - 10;
            
            if (y > innerHeight * 0.75 || y + 5 + this.tooltipHeight > this.maxHeight)
                y = y - this.tooltipHeight - 5;

            st.left = x + 10 + "px";
            st.top = y + 5 + "px";
        };

        this.$init = function () {
            //tooltipNode = document.documentElement.appendChild(dom.createElement("div"));

			var node = $('<div style="position:fixed;display: none;z-index: 1000"></div>');
			node.appendTo($('#'+ Config.get('fullscreenContainerId') ));
			tooltipNode = node.get(0);




            var st = tooltipNode.style;
            st.position = "fixed";
            st.display = "none";
            st.background = "#FFFFD7";
            st.borderRadius = "";
            st.border = "1px solid #D0D0D0";
            st.padding = "5px";
            st.maxWidth = "300px";
            st.zIndex = 1000;
            //st.fontFamily = "monospace";
            st.whiteSpace = "pre-line";
            return tooltipNode;
        };

        this.destroy = function () {
            this.onMouseOut();
            event.removeListener(this.editor.renderer.scroller, "mousemove", this.onMouseMove);
            event.removeListener(this.editor.renderer.content, "mouseout", this.onMouseOut);
            delete this.editor.tokenTooltip;
        };

    }).call(TokenTooltip.prototype);

    exports.TokenTooltip = TokenTooltip;

});


