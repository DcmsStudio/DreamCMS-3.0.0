var Dimension = {
    winData: null,
    _getWin: function(win)
    {
        var winObj = null;
        if (typeof win === 'string')
        {
            winObj = $('#' + win);
        }
        else if (typeof win === 'object')
        {
            winObj = win;
        }
        else
        {
            winObj = $('#' + Win.windowID);
        }
        return winObj.length ? winObj : null;
    },
    getWindoBodySize: function(win)
    {
        var winObj = this._getWin(win);
        if (winObj === null)
        {
            Debug.log('Invalid window');
            return {width: null, height: null};
        }
        return {height: winObj.height(), width: winObj.width()};
    },
    getWindowBodySize: function(win)
    {
        var winObj = this._getWin(win);
        if (winObj === null)
        {
            Debug.log('Invalid window');
            return {width: null, height: null};
        }
        this.winData = winObj.data('WindowManager');
        return {height: this.winData.getContentHeight(), width: winObj.width()};
    },
    getWindowToolbarSize: function(win)
    {
        var winObj = this._getWin(win);
        if (winObj === null)
        {
            Debug.log('Invalid window');
            return {width: null, height: null};
        }
        this.winData = winObj.data('WindowManager');
        return {height: this.winData.getToolbarHeight(), width: winObj.width()};
    },
    getWindowStatusbarSize: function(win)
    {
        var winObj = this._getWin(win);
        if (winObj === null)
        {
            Debug.log('Invalid window');
            return {width: null, height: null};
        }
        this.winData = winObj.data('WindowManager');
        return {height: this.winData.getStatusbarHeight(), width: winObj.width()};
    },
    getWindowHeaderSize: function(win)
    {
        var winObj = this._getWin(win);
        if (winObj === null)
        {
            Debug.log('Invalid window');
            return {width: null, height: null};
        }
        this.winData = winObj.data('WindowManager');
        return {height: this.winData.getHeaderHeight(), width: winObj.width()};
    }
};