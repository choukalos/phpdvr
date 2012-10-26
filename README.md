phpdvr
======

A PHP DVR designed to run on PHP 5.2 easily supported by Debian Etch NAS boxes.  It is a simple
PHP 5.2 HTML5 application to provide simple TV recording and web playback capabilities.

Requirements:  
*  Requires php 5.2 
*  Requires libhdhomerun to be installed ( http://www.silicondust.com/support/hdhomerun/downloads/ )
*  Requires a schedules direct subscription ( http://www.schedulesdirect.org/ )
*  Requires a HDHomeRun box to record OTA TV

Install / Setup
----------------

Ideally this would be handled as a package for the various NAS systems.  You can manually install by:  
*  copy files over to web directory
*  sudo chown -R www-data logs recordings
*  sudo chgrp -R www-data logs recordings
*  open up config.php and update the settings as appropriate for your system
*  Add an .htaccess file to restrict access to this application
*  Open up your web browser to the appropriate address
*  Click on 'Setup' in the main menu bar

To Do
-----

(Under Construction)  
*  Fix setup.php - has issues
*  consolidate db code into one file
*  Add single show recording
*  Add seasons pass recording
*  Add Device/Tuner selection logic
*  Add HTML5 video streaming from recordings page (maybe spiff it up some?)
*  Add a dashboard that shows available HD space, scheduled recordings, new shows
*  Add a hook (for ffmpeg support) that enables re-encoding
*  (WISHLIST) See about pulling commercial detection code from MythTV and incorporating with re-encoding
*  (WISHLIST) See about adding live TV streaming (? with down-res encoding for remote viewing ?)

Known Issues
------------
1.  None at this point  

Changes/updates
---------------
please feel free to send me patches and I'll incorporate them.  

Copyright 2012 by Chuck Choukalos, released under the MIT license