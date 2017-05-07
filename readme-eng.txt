Thank you for downloading the PG Dating Pro script.

If you have questions, feel free to contact us in live chat: http://www.datingpro.com/ (chat opens in the bottom right corner).

------------------------------
SYSTEM REQUIREMENTS
------------------------------

This is the link to the system requirements in our forum: https://pilotgroup.zendesk.com/entries/29994158-System-requirements

- PHP 5.5 or higher (PHP 5.6 or below for encoded version)
- gd2, iconv, mbstring extensions for PHP
- PHP database extensions: pdo/mysqli/mysql
- MySQL 5.1 or higher
- XML Support
- ionCube PHP Loader v4.0.12 and above enabled on your server (for encoded version)
- mod_rewrite library and support of .htaccess files with RewriteRule attribute
- Cronjobs/scheduler support
- ffmpeg-php extension should be installed (required for video thumbs, video duration, etc.)
- shell_exec should be allowed
- Server OS: Unix, Windows
- IIS is not supported
- Android: 4.0 and above; iOS: 6.0 and above

System requirements for Dating Pro Network:
- pcntl and posix extensions for PHP
	
*****Note: We can also send you a base image for a Docker container. Please email sales@pilotgroup.net or contact us in live chat for more info: http://www.datingpro.com/.


------------------------------
INSTALLATION INSTRUCTIONS
------------------------------

*****View illustrated installation instruction here: https://pilotgroup.zendesk.com/entries/30062777-Installation-instructions or watch this video: https://www.youtube.com/watch?v=7ddx1LgEe9s

1. Download the ZIP archive with the software files, extract the files and upload them to your server. Any FTP client will do, or, you can upload the archive to the server and decompress it there.

*****Note: Make sure to use Binary transfer mode during upload.

2. Create an empty MySQL database and add a user to this database. Database user should have full rights because the script will use this user's parameters to populate the database with tables and data.

*****Note: We recommend making a backup copy of your website database at least once a month, to be on the safe side.

3. Set 777 permissions to the following files:

application/config/landings_module_routes.php
application/config/langs_route.php
application/config/seo_module_routes.php
application/config/seo_module_routes.xml
m/index.html
m/scripts/app.js
robots.txt
sitemap.xml

and directories:

application/libraries/dompdf/lib/fonts/
application/modules/export/models/drivers
application/views/admin/logo
application/views/admin/mobile-logo
application/views/admin/sets
application/views/admin/sets/default
application/views/admin/sets/default/css
application/views/admin/sets/default/img
application/views/default/logo
application/views/default/mobile-logo
application/views/default/sets
application/views/default/sets/default
application/views/default/sets/default/css
application/views/flatty/logo
application/views/flatty/mobile-logo
application/views/flatty/sets
application/views/flatty/sets/default
application/views/flatty/sets/default/css
temp
temp/*
uploads
uploads/*

* means recurse into all subdirectories

*****Note: You will also be prompted to change file permissions to files /application/config/install.php and /config.php along the way. Click 'Refresh' when you are done to update the info.

4. Go to http://www.yourdomain.com/ (where www.yourdomain.com is your domain name connected to the server). You will be taken to the installation page.

5. Read the license agreement and click 'I agree' if you agree.

6. Indicate your FTP access details (host, user and password) and click 'Next' to continue.

7. The next step is to indicate database access details. Click 'Next' to continue.

8. Next comes languages installation. You can select default language here. At least one language version is required.

9.1. The system will start the installation. You will be asked to indicate your order number. You can find it in the root directory, in file order_key.txt.

9.2. Fill out the administrator details. It will be your future login and password to authorize in the admin panel. Plus your name and email will be used for correspondence with the site members. You will be able to edit this information at any later point.

9.3. Add SMTP server details, select mail protocol that is supported on your server.

Click 'Save' and wait while the script completes the installation.

10. Finally, you will be asked to set up cron files.

Make sure to indicate correct path to PHP. On our test server it is /usr/bin/php. Contact your server administrator to find out the path on your server.

11. Click 'Finish'. Your dating site is installed and ready to be configured.

Your site's user mode is available under http://www.yourdomain.com/.
Test user access: will@mail.com / 123456

Your site's admin mode is available under http://www.yourdomain.com/admin.
Admin access: what you have input during step 9.2. as described above. 

*****View this forum for tips of the site configuration and management: https://pilotgroup.zendesk.com/forums/22985306-Manuals


------------------------------
SETTING UP CRONJOBS VIA SSH
------------------------------

   1. Login to the server via SSH
   2. Type in:
      crontab -e
      This will open up a file editor for you to add/edit the crontab.
   3. In the editor you'll have to input the time manually. Each position is noted by a * (asterisk) if you want it to be every unit of that time. You will need 5 time positions in total:
      Minute Hour Day Month Weekday
   4. After the time goes the command.
   5. An example of a cronjob is:
      */10 * * * * php ~/crons/cron.php >> ~/cronlog.log ~/cronerr.err
      That job will run every 10 minutes.
   6. Type in "control-key o" (control-key is the one furthest from the space bar on your keyboard -- this should work on Macs also) to save the cronjob.
   7. Type in "control-key x" to exit the editor.
   8. You should now see:
      crontab: installing new crontab
   9. You are now done with the cron job setup.


------------------------------
SPECIAL CASES
------------------------------

1. File requires ionCube PHP Loader:

It means that the script files are encoded. Install ionCube PHP Loader v4.0.12 or above on your server.

2. Blank page:

Switch on the error display in file config.php in the site root directory:

Replace 
define("DISPLAY_ERRORS", false);

with 
define("DISPLAY_ERRORS", true);

Go from the error description.

3. Installation stops and does not resume:
OR
4. The requested URL ... was not found on this server:

In file /application/config/config.php find URI PROTOCOL and in line 

$config['uri_protocol']    = 'AUTO';

try replacing 'AUTO' with one of the other options: PATH_INFO, QUERY_STRING, REQUEST_URI, ORIG_PATH_INFO.

5. Cannot connect to host (Access denied for user 'username'@'host' (using password: YES)):

Check if the host is available. The error may also have to do with the mistyped db name, db user, and password.

6. Page opens with message 'No input file specified':

Create a backup copy of the file .htaccess in the root directory. 
Try editing .htaccess file by commenting all lines except the line with RewriteRule so that it looks like this:

RewriteRule ^(.*)$ index.php?/$1? [L,QSA]

In file /application/config/config.php replace 'AUTO' with 'REQUEST_URI' in line:

$config['uri_protocol']    = 'REQUEST_URI';



If everything else fails, you know where to find us: http://www.datingpro.com/ (chat opens in the bottom right corner).
