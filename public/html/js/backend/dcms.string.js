var Strings = {
    defaultTruncateLength: 10,
    
    truncate: function(string, len)
    {        
        string = Strings.utf8encode(string);
        string += '';
        
        var start = 0, end = string.length;        
        
        if ( !end )
        {
            return string;
        }
        
        if (typeof len === 'undefined')
        {
            if (this.defaultTruncateLength > end )
            {
                len = end;
            }
            else
            {
                len = this.defaultTruncateLength
            }
        }
        else if (len < 0)
        {
            len = (len < 0 ? len + end : len + start);
        }
        
        end = len;
        
        // PHP returns false if start does not fall within the string.
        // PHP returns false if the calculated end comes before the calculated start.
        // PHP returns an empty string if start and end are the same.
        // Otherwise, PHP returns the portion of the string from start to end.
        return start >= string.length || start < 0 || start > end ? !1 : string.slice(start, end);
    },
    
    utf8encode: function(string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";
 
        for (var n = 0; n < string.length; n++) {
 
            var c = string.charCodeAt(n);
 
            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
 
        }
 
        return utftext;
    }
};