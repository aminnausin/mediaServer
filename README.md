# This is a simple web based media hosting server built with Laravel.
It is still incomplete.



## Overview

Videos are grouped by folders and listed in a table with the file name and date. 
This is good for a home server and provides fast and easy access to media. 
The website is responsive by design and works on mobile devices. HVEC support depends on the device used. 

### Installation

Requires Apache or NGINX, PostgreSQL and PHP 8. You can use Laragon to run in a single application.

To connect to your file server, create a Symbolic link between your remote location and ./storage/app/public/media
To see any of your videos, you must run a file scan or start the Laravel CRON job which scans every 6 hours.
You can run a file scan manually via the "Index Files" option in the dropdown under your username.

Different folders are accessed via url/folder/subfolder. Using just URL/folder will open the first subfolder scanned.

The default folder is defined on line 18 of ./routes/web.php as an automatic redirect to the default /folder/subfolder.
This is shown when the user does not specify a folder.

Supported File Types:
- MP4
- MKV

### Demo

Screenshots of the current webpage on Desktop and on Android.

!
!

<!-- ![image](https://github.com/aminnausin/mediaServer/assets/83550431/495ba4cb-0e30-45e3-91b7-d3a3dae454b6) -->
<!-- ![image](https://github.com/aminnausin/mediaServer/assets/83550431/7df9dbe1-efec-4aad-ae64-df857f718480) -->
|![Dark](https://github.com/aminnausin/mediaServer/assets/83550431/bdd531b0-85f9-499e-8f96-5d853f080cad)|![Light](https://github.com/aminnausin/mediaServer/assets/83550431/5e99db0d-ca0d-477e-add4-fd2144790165)|
|:-:|:-:|
|Dark Mode on Desktop|Light Mode on Android|
