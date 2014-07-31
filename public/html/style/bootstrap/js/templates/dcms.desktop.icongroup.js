Desktop.Templates = ns('Desktop.Templates');
/*
Desktop.Templates.DesktopFolder = '<div class="DesktopIconContainer-Folder" id="DesktopFolder{id}">' 
    +'<table cellpadding="0" cellspacing="0" border="0" title="">' 
    +'<tr>' 
        +'<td class="bg-top"></td>' 
    +'</tr>' 
    +'<tr>' 
        +'<td class="bg-middle">' 
            +'<div class="folder-wrapper">' 
                +'<div class="folder"></div>' 
             +'</div>' 
        +'</td>' 
    +'</tr>'
    +'<tr>' 
        +'<td class="bg-bg"><p>{label}</p></td>' 
    +'</tr>' 
    +'<tr>' 
        +'<td class="bg-bottom"></td>' 
    +'</tr>' 
    +'</table>' 
+'</div>';

*/

Desktop.Templates.DesktopFolder = '<div class="DesktopIconContainer-Folder" id="DesktopFolder{id}">' 
            +'<div class="folder-wrapper">' 
                +'<div class="folder"></div>' 
             +'</div>'
     +'<div class="folder-label"><div><span>{label}</span><div class="object-info"></div></div></div></div>';