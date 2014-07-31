Copyright: Cees Rijken, Christian Ladewig, Jeroen van der Stijl
License: GPL
(c)2009 All rights reserved.
================================================
Version: 1.2                   Date: 05/oct/2009
================================================

-------------------------------------------------------------------------
Information
-------------------------------------------------------------------------

You should have gotten this plugin from: https://sourceforge.net/projects/mcegooglemap
Go there for the latest version, help, hints, feature requests, complaints, bugreports and kudos
And if you use this plugin professionally: Don't be cheap, donate. We spend our precious (spare) time 
on developing this free gem.

-------------------------------------------------------------------------
MCEGoogleMaps for tinyMCE installation
-------------------------------------------------------------------------

   1. Create a folder inside the plugins folder of tinyMCE named 'mcgooglemaps'.
   2. Copy all the files from this archive to that folder.
   3. Add the plugin to tinyMCE:

	tinyMCE.init({ 
		... 
		plugins : "mcegooglemaps", 
		theme_advanced_buttons3_add : "mcegooglemaps",
	
	});
	
	4. An icon should appear that will show you the dialog where you can select the location on the map.
	
	
-------------------------------------------------------------------------
Parameters
-------------------------------------------------------------------------

Name: plugin_googleMaps_apiKey
Description: Put your API key you got from the Goolge Maps website.
Type: string
Required: yes
Example: "QIAAAAh3Y2QoSqdcXL9jo1hf2_qxQ9sdfsdUz7H1ndauxi_u81aUINhLhQcghghghJCUhECPRpMPaaU9UCCQ"
Default value: ""

Name: plugin_googleMaps_coordinates
Description: The initial coordinates the plugin dialog will show.
Type: string
Required: no
Example: "52.38901106223455, 4.89990234375
Default value: ""

Name: plugin_googleMaps_locationLookupEnabled
Description: 
Whether the user can select a location in the plugins dialog. If set to no be sure to specify initial coordinates and
also the address lookup textfield will then be hidden
Type: boolean
Required: no
Default value: yes


-------------------------------------------------------------------------
Known bugs / issues
-------------------------------------------------------------------------

- After using set button for manual coordinates map doesn't center properly

-------------------------------------------------------------------------
Todo
-------------------------------------------------------------------------

- Globalization

-------------------------------------------------------------------------
History
-------------------------------------------------------------------------

V 1.2

- Moved API Key parameter from dialog.js to tinyMCE.init
- Added coordinates parameter for initial coordinates
- moved coordinates to advanced tab in dialog
- Added parameter to turn on/off dragging of pointer on map in dialog (hide search address/coordinates)
- Removed field 'div id' in dialog advanced settings. It now finds a unique id for itself. so multiple maps can be used
- Removed API key field from dialog advanced settings.
- Removed zoom factor field. The value is directly take from the maps settings
- Removed map type field. The value is directly taken from the maps settings
- Added more HUD controls
- Removed delete icon. Works with delete button on keyboard?