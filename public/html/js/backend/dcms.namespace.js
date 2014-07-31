
/**
 *  Use:
 *  var model = namespace('MyCompany.MyApplication.Model');
 *
 */
function namespace(namespaceString) {
    var parts = namespaceString.split('.'),
    parent = window,
    currentPart = '';    
        
    for(var i = 0, length = parts.length; i < length; i++) {
        currentPart = parts[i];
        parent[currentPart] = parent[currentPart] || {};
        parent = parent[currentPart];
    }
    
    return parent;
}

function ns(namespaceString)
{
    return namespace(namespaceString);
}



ns('Win');
ns('Doc');
ns('Desktop');
ns('Grid');
ns('Application');