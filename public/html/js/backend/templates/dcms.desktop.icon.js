Desktop.Templates = ns('Desktop.Templates');
/*
 Desktop.Templates.DesktopIcon = 
 '<div class="DesktopIconContainer" id="DesktopIcon{id}">'
 +'<table cellpadding="0" cellspacing="0" border="0" title="{WindowTitle}">'
 +'<tr>'
 +'<td class="bg-top"></td>'
 +'</tr>'
 +'<tr>'
 +'<td class="bg-middle">'
 +'<img src="{WindowDesktopIconFile}" width="{DesktopIconWidth}" height="{DesktopIconHeight}"/>'
 +'</td>'
 +'</tr>'
 +'<tr>'
 +'<td class="bg-bg"><p>{DesktopIconCaption}</p></td>'
 +'</tr>'
 +'<tr>'
 +'<td class="bg-bottom"></td>'
 +'</tr>'
 +'</table>'
 +'</div>';
 */
Desktop.Templates.DesktopIcon = '<div class="DesktopIconContainer" id="DesktopIcon{id}" title="{WindowTitle}"><div class="desktop-icon"><img src="{WindowDesktopIconFile}" width="{DesktopIconWidth}" height="{DesktopIconHeight}"/></div><div class="icon-label"><div><span>{DesktopIconCaption}</span><div class="object-info"></div></div></div></div>';







Desktop.Templates.DesktopOptions = 
        '<div style="display:inline-block;width: 95%; margin-bottom: 10px">'
        + '  <div>Symbolgröße</div>'
        + '  <div id="slide-symbolsize" class="slider" style="width: 175px;" from="28" to="96"></div>'
        + '</div>'
        + '<div style="display:inline-block;width: 95%; margin-bottom: 10px">'
        + '  <div>Gitterabstand</div>'
        +'   <div id="slide-guttersize" class="slider" style="width: 175px;" from="80" to="150"></div>'
        + '</div>'
        + '<div class="content-separator"></div>'
        + '<div style="display:inline-block;width: 95%; margin-bottom: 10px">'
        + '     <div>Bezeichnung steht:</div>'
        + '     <div>'
        + '         <label for="pos-bottom"><input id="pos-bottom" type="radio" value="bottom" name="labelpos"{checkiconpos-bottom}/> Unten</label>'
        + '         <label for="pos-right"><input id="pos-right" type="radio" value="right" name="labelpos"{checkiconpos-right}/> Rechts</label>'
        + '     </div>'
        + '</div>'

        + '<div style="display:inline-block;width: 95%; margin-bottom: 10px">'


        + '     <div><label for="icon-objectinfo"><input id="icon-objectinfo" type="checkbox" value="1" name="icon-objectinfo"{checkicon-objectinfo}/> Objekt Infos einblenden</label></div>'


        + '     <div>Ausrichtung:</div>'
        +'      <div>'
        +'       <select name="icon-sorting" id="icon-sorting"><option value="default"{checksort-default}>Ohne</option><option value="none"{checksort-none}>Raster</option><option value="size"{checksort-size}>Größe</option><option value="name"{checksort-name}>Name</option></select>'

/*

        +'      <label for="sort-name"><input id="sort-name" type="radio" value="name" name="icon-sorting"{checksort-name}/> Name</label>'
        +'      <label for="sort-size"><input id="sort-size" type="radio" value="size" name="icon-sorting"{checksort-size}/> Größe</label>'
        +'      <label for="sort-none"><input id="sort-none" type="radio" value="none" name="icon-sorting"{checksort-none}/> Ohne</label>'
*/
        +'      </div>'
        +'</div>';
