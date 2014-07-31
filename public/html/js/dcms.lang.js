var Lang = {
    
    guiLanguage: null,
    
    languageStrings: [],
    
    init: function()
    {
        this.guiLanguage = Config.get('guiLang');        
        this.languageStrings[this.guiLanguage] = {};        
    },
    
    setLanguage: function(code)
    {
        this.guiLanguage = code;
    },    
    
    trans: function(str)
    {
        
    }
    
};