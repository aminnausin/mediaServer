# Web based Media hosting website/server

Built with:
- Laravel
- Vue.js
- Tailwind
- Nginx
- Postgresql

Features to add:
- User profiles
- Uploadable images (With supabase or local storage based on admin preference)
- User access levels (Admin, Contributor, General)
- User friends system
- Synchronised playback with a user party system
- Automatic scrubbing of metadata with admin selected source API
- Category management
- Video playback statistics and heatmaps
- Video player UI
- Tags system

## Overview

This is a fast and easily accessible all in one media player for your home server. 
It automatically scans and indexes your videos and serves them in a minimalistic and easy to use interface.

The main benefit is that you host content yourself and thus control the speed at which videos can load. No more buffering !
Extra features include watch history, ambient mode, editable video metadata and online file management.

### Installation

Requires Apache or NGINX, PostgreSQL, A build of Vue and Tailwind with Node, FFmpeg and PHP 8. You can use Laragon to run in a single application.

To connect to your file server, create a Symbolic link between your remote location and ./storage/app/public/media
To see any of your videos, you must INDEX FILES from the user dropdown menu or start the Laravel CRON job which scans 
every 6 hours. Sync files will synchronise local cache json files with the database. The verify job will pull metadata 
from the files using FFprobe.

Different folders are accessed via url/folder/subfolder. Using just URL/folder will open the first subfolder scanned.
You can share direct links to both videos and folders with the share buttons in the UI. Access to any category requires
you to know its name by design. 

Supported File Types:
- MP4
- MKV

## Demo

Below are screenshots of the current webpage on Desktop and Android.

!
!

<!-- ![image](https://github.com/aminnausin/mediaServer/assets/83550431/495ba4cb-0e30-45e3-91b7-d3a3dae454b6) -->
<!-- ![image](https://github.com/aminnausin/mediaServer/assets/83550431/7df9dbe1-efec-4aad-ae64-df857f718480) -->
<!-- (https://github.com/aminnausin/mediaServer/assets/83550431/bdd531b0-85f9-499e-8f96-5d853f080cad)-->
<!-- (https://github.com/aminnausin/mediaServer/assets/83550431/5e99db0d-ca0d-477e-add4-fd2144790165)-->
<!-- |![Dark](https://github.com/user-attachments/assets/f0db341f-c3c8-44d0-8faf-a16e6f958726)|![Light](https://github.com/user-attachments/assets/ed82c114-940b-4ca1-ad8d-d2bab62f1851)| -->
|![Dark](https://github.com/user-attachments/assets/f0db341f-c3c8-44d0-8faf-a16e6f958726)|![Light](https://github.com/user-attachments/assets/ed82c114-940b-4ca1-ad8d-d2bab62f1851)|
|:-:|:-:|
|Dark Mode on Desktop|Light Mode on Android|
