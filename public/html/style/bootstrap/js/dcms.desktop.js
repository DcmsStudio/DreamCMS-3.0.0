var Desktop = {
    isWindowSkin: false,
    loadTab: function (e, opt) {
        openTab(opt);
    },
    getActiveWindowToolbar: function () {
        return Core.getToolbar();
    },
    refreshAllWindowsAfterChangeContentLang: function (callback) {

        var tabs = $('#main-tabs').find('li');
        tabs.each(function (i) {
            if (i + 1 >= tabs.length) {
                Core.Tabs.refreshTab($(this).attr('id').replace('tab-', ''), callback);
            }
            else {
                Core.Tabs.refreshTab($(this).attr('id').replace('tab-', ''));
            }
        });
    },
    credits: function () {
        $('#' + Win.windowID).find('.credit').show();
        $('#' + Win.windowID).find("div.items").height($('#' + Win.windowID).find('.credit:first').height());

        $('#' + Win.windowID).find('.logo').each(function () {

            var first = $(this).parents('div.credit:first');
            first.show();
            var w = $(this).width();
            var h = $(this).height();
            first.hide();

            if (w >= 120) {
                $(this).width(120);
                var height = Math.ceil(h * (120 / w));
                $(this).height(height);
                h = height;
            }
        });

        setTimeout(function () {
            $('#' + Win.windowID).find("div.credit-scrollable").scrollable({
                steps: 1,
                vertical: true,
                autoplay: true,
                circular: true,
                mousewheel: true,
                speed: 800,
                //	easing: 'easeOutBounce'
            }).autoscroll(2000);

        }, 500);

        $('.credit-link', $('#' + Win.windowID)).click(function (e) {
            e.preventDefault();
            window.open($(this).attr('href'));

        });

    }
};

Desktop.Tools = {

    rebuildTooltips: function () {
        ToolTip.rebuildTooltips();
    }
};

Desktop.Sidepanel = {
    /**
     * Update only the Visible Content Tab Scrollbar
     * @returns {undefined}
     */
    updateScrollbar: function ()
    {
        Core.enablePanelScrollbar();
    },
    /**
     *
     * @returns {undefined}
     */
    disableScrollbar: function ()
    {
        Core.disablePanelScrollbar();
    },
    /**
     *
     * @returns {undefined}
     */
    enableScrollbar: function ()
    {
        Core.enablePanelScrollbar();
    }
};