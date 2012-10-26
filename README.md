phpdvr
======

A PHP DVR designed to run on PHP 5.2 easily supported by Debian Etch NAS boxes.  It is a simple
PHP 5.2 HTML5 application to provide simple TV recording and web playback capabilities.

Requirements:
1.  Requires php 5.2 
2.  Requires libhdhomerun to be installed ( http://www.silicondust.com/support/hdhomerun/downloads/ )
3.  Requires a schedules direct subscription ( http://www.schedulesdirect.org/ )
4.  Requires a HDHomeRun box to record OTA TV

Install / Setup:

Ideally this would be handled as a package for the various NAS systems.  You can manually install by:
1.  copy files over to web directory
2.  sudo chown -R www-data logs recordings
3.  sudo chgrp -R www-data logs recordings
4.  open up config.php and update the settings as appropriate for your system
5.  Add an .htaccess file to restrict access to this application
6.  Open up your web browser to the appropriate address
7.  Click on 'Setup' in the main menu bar

To Do:
(Under Construction)
1.  Add single show recording
2.  Add seasons pass recording
3.  Add Device/Tuner selection logic
4.  Add HTML5 video streaming from recordings page (maybe spiff it up some?)
5.  Add a dashboard that shows available HD space, scheduled recordings, new shows
6.  Add a hook (for ffmpeg support) that enables re-encoding
7.  (WISHLIST) See about pulling commercial detection code from MythTV and incorporating with re-encoding
8.  (WISHLIST) See about adding live TV streaming (? with down-res encoding for remote viewing ?)

Known Issues
1.  None at this point

Changes/updates: please feel free to send me patches and I'll incorporate them.

Copyright 2012 by Chuck Choukalos, released under the MIT license