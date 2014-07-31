var Recent = function(opts) {
    
    var self = this;
    var menuItem = opts && opts.menuitem || false;
    this.recentUl = null;
    
    this.prepare = function() {
        if (!menuItem) {
            return;
        }
        
        if ( this.recentUl === null ) {
            this.recentUl = $('<ul>');
        }
        
        menuItem.append(this.recentUl);
        
        if (Desktop.ajaxData && typeof Desktop.ajaxData.recentitems == 'object') {
            for(var i=0;i<Desktop.ajaxData.recentitems.length;++i){
                var data = Desktop.ajaxData.recentitems[i], li = $('<li rel="'+ data.url +'">');
                li.append(  '<span>'+ data.title +'</span>'   );
                this.recentUl.append(li);
            }
        }
    };
    
    this.bindEvents = function() {
        this.recentUl.find('li').click(function() {
            var url = $(this).attr('rel');
            console.log(url);
        });
    };
    
    
    
    this.update = function() {
        if (!menuItem) {
            return;
        }
    };
};