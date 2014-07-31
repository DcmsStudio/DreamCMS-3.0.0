var Navibuilder = {
    
    
    options: {
        menuItemDepthPerLevel: 30,
        maxDepth: 10
    },
    

    init: function()
    {
        this.registerSortEvent();
        this.registerItemEvents();
    },
    
    
    registerSortEvent: function()
    {
        var currentDepth = 0, originalDepth, minDepth, maxDepth,
        prev, next, prevBottom, nextThreshold, helperHeight, transport,
        menuEdge = api.menuList.offset().left,
        body = $('body'), maxChildDepth,
        menuMaxDepth = initialMenuMaxDepth();
        
        

        function initialMenuMaxDepth() {
            if( ! body[0].className ) return 0;
            var match = body[0].className.match(/menu-max-depth-(\d+)/);
            return match && match[1] ? parseInt(match[1]) : 0;
        }
        
        function updateCurrentDepth(ui, depth) {
            ui.placeholder.updateDepthClass( depth, currentDepth );
            currentDepth = depth;
        }
        

        function updateMenuMaxDepth( depthChange ) {
            var depth, newDepth = menuMaxDepth;
            if ( depthChange === 0 ) {
                return;
            } else if ( depthChange > 0 ) {
                depth = maxChildDepth + depthChange;
                if( depth > menuMaxDepth )
                    newDepth = depth;
            } else if ( depthChange < 0 && maxChildDepth == menuMaxDepth ) {
                while( ! $('.menu-item-depth-' + newDepth, Navibuilder.menuList).length && newDepth > 0 )
                    newDepth--;
            }
            // Update the depth class.
            body.removeClass( 'menu-max-depth-' + menuMaxDepth ).addClass( 'menu-max-depth-' + newDepth );
            menuMaxDepth = newDepth;
        }
        
    },
    
    registerItemEvents: function()
    {
        
    },
    
    
    
    depthToPx : function(depth) {
        return depth * this.options.menuItemDepthPerLevel;
    },

    pxToDepth : function(px) {
        return Math.floor(px / this.options.menuItemDepthPerLevel);
    }

};