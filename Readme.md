ScoutNet Kalender Plugin for TYPO3
==================================
[ ![Codeship Status for scoutnet/plugins.typo3.sh_scoutnet_kalender](https://app.codeship.com/projects/0b78d0d0-da9e-0134-84fb-369bc7fb7901/status?branch=master)](https://img.shields.io/codeship/0b78d0d0-da9e-0134-84fb-369bc7fb7901/master.svg)
This is the official ScoutNet.de Kalender Plugin for TYPO3. If you are a scouting Group from Germany you can use our Service and manage your events on our Servers.

Install
-------
To install You can either use the version from the TER, or install this git repo to 

<TYPO3 Dir>/typo3conf/ext/sh_scoutnet_kalender

For the Kalender Plugin to work, you need the sh_scoutnet_webservice extension in a Version > 2.0.


Setup
-----
For the Backend function to work, you have to set your SSID (the id of your Scouting Group) in the extension.

For the Frontend to work, you need to include the static file into your template. Then you can add a new content Element. 
There you can choose which ssids to be shown, which additional calenders to show and which kategories you want to display.

Update
------
If you update from a Version < 3.0 please note, that the whole extension was rewritten. And is now based on Extbase. 
You need to resetup the frontend Plugin and all templates do not work anymore.

You can easily change the CSS with the constant editor. If you want to change the Templates, please set how to change templates with extbase.
The be_user database fields are changed, so you need to update your database. And reconnect all backend accounts. But this should be done by the 
Backend users themself.

Development
-----------
If you want to contribute, please make a pull request on bitbucket. The Repo is located here:

https://bitbucket.org/scoutnet/plugins.typo3.sh_scoutnet_kalender


Author
------
If you have any questions reganding this software, you can send me an email to muetze@scoutnet.de

License
-------
(c) 2016 Stefan "Mütze" Horst <muetze@scoutnet.de>
All rights reserved

This script is part of the TYPO3 project. The TYPO3 project is
free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

The GNU General Public License can be found at
http://www.gnu.org/copyleft/gpl.html.

This script is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

This copyright notice MUST APPEAR in all copies of the script!
