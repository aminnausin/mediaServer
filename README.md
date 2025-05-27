<h1 align="center" style="display: block; border: none; padding: 0px;">MediaServer</h1>
<!-- <p  align="center"> -->
  <!-- <br>
  <a href="http://www.amitmerchant.com/electron-markdownify"><img src="https://raw.githubusercontent.com/amitmerchant1990/electron-markdownify/master/app/img/markdownify.png" alt="Markdownify" width="200"></a>
  <br> -->
    <!-- # MediaServer -->
  <!-- <br> -->
<!-- </p> -->

#### <p  align="center">A minimalist self-hosted Media Server built with <a href="https://laravel.com/" target="_blank">Laravel</a>. </p>

<p  align="center">
    <img src="https://img.shields.io/badge/vue-v3.5.14-white" alt="Vue">
    <img src="https://img.shields.io/badge/laravel-v11.44-F9322C" alt="Laravel">
    <img src="https://img.shields.io/badge/license-AGPL%20V3-purple" alt="License">
    <img src="https://sonarcloud.io/api/project_badges/measure?project=aminnausin_mediaServer&metric=ncloc" alt="Lines of Code">
    <br/>
    <img src="https://sonarcloud.io/api/project_badges/measure?project=aminnausin_mediaServer&metric=duplicated_lines_density" alt="Duplicate Lines of Code">
    <img src="https://sonarcloud.io/api/project_badges/measure?project=aminnausin_mediaServer&metric=sqale_index" alt="Technical Debt">
    <img src="https://sonarcloud.io/api/project_badges/measure?project=aminnausin_mediaServer&metric=code_smells" alt="Code Smells">
    <img src="https://sonarcloud.io/api/project_badges/measure?project=aminnausin_mediaServer&metric=reliability_rating" alt="Reliability Rating">
</p>

<p  align="center">
  <a href="#features">Key Features</a> •
  <a href="#demo">Demo</a> •
  <a href="#how-to-use">How To Use</a> •
  <a href="#similar-projects">Similar Projects</a>
</p>

<!-- ![screenshot](.gif) -->

## Overview

This is a fast and easily accessible all in one media player for your home server.
It automatically scans and indexes your videos and serves them in a minimalistic and easy to use interface.

The main benefit is that you host content yourself and thus control the speed at which videos can load. No more buffering !
Extra features include watch history, ambient mode, editable video metadata and online file management.

## Features

### Key Features

- Minimalist UI
- Custom Media Player
- Video and Folder Sharing
- Watch History Tracking
- Customisable Metadata
- Dark/Light mode
- Server Dashboard

<details>
<summary>Extended Features</summary>

- Minimalist UI
- Custom Media Player
  - Custom UI
  - Music Support (with cover art)
  - Keybinds
    - k (play/pause)
    - j / LeftArrow (rewind 10s)
    - l / rightArrow (fast forward 10s)
    - SHIFT+N (play next)
    - SHIFT+P (play previous)
    - m (mute/unmute)
    - f (full screen/exit full screen)
  - Speed Controls
  - Player Statistics
  - Ambient Background
    - Provides ambient backlight based on video content in darkmode
    - Can be disabled
  - Video Playback Heatmap
    - Shows up after 5 seeks to any point in a video
    - Ranges from 1 to 25 at any 100th point in the video
    - Can be disabled
  - Watch Party UI Demo (upcoming)
- Video and Folder Sharing
  - With video id or folder name in the URL
- Customisable Video Metadata
  - Thumbnail
  - Description
  - Episode / Season number
  - Release Dates
  - Tags
- Customisable Folder Metadata
  - Thumbnail
  - Description
  - Release Dates
  - Studio
- Watch History Tracking
  - Can filter history by any video or folder attribute
- View Counts
  - See how many times you have viewed a video vs everybody else
- Persistent Metadata
  - Moved or reuploaded videos will be matched with pre-existing metadata
- Dark/Light mode
- Server Dashboard
  - Index process manager
  - Library Manager
  - Folder Manager
  - User Manager
  - Server Statistics
  </details>

### Features to add

- Video playback statistics
  - Most Played in the last X days
  - Your favourite video / folder / category
  - Avg time / count watched per day / week / month
- Timed video comments (like on soundcloud)
- Uploadable images (With supabase or local storage based on admin preference)
- User profiles
- User friends system
- Synchronised playback with a user party system
- User access levels (Admin, Contributor, General)
- Automatic scrubbing of metadata with admin selected source API
- Embedded Music Lyrics
- Captions

## Demo

> Demo URL Coming Soon...

Below are screenshots of the current webpage on Desktop and Android.

<!-- ![image](https://github.com/aminnausin/mediaServer/assets/83550431/495ba4cb-0e30-45e3-91b7-d3a3dae454b6) -->
<!-- ![image](https://github.com/aminnausin/mediaServer/assets/83550431/7df9dbe1-efec-4aad-ae64-df857f718480) -->
<!-- (https://github.com/aminnausin/mediaServer/assets/83550431/bdd531b0-85f9-499e-8f96-5d853f080cad)-->
<!-- (https://github.com/aminnausin/mediaServer/assets/83550431/5e99db0d-ca0d-477e-add4-fd2144790165)-->
<!-- |![Dark](https://github.com/user-attachments/assets/f0db341f-c3c8-44d0-8faf-a16e6f958726)|![Light](https://github.com/user-attachments/assets/ed82c114-940b-4ca1-ad8d-d2bab62f1851)| -->
<!--| ![Dark](https://github.com/user-attachments/assets/70c17425-96f2-4516-a7ce-c046d45f90c4) | ![Light](https://github.com/user-attachments/assets/b17d374c-9334-457e-9c49-768d2d38c291) | -->

<!-- ![image](https://github.com/user-attachments/assets/37313603-68b2-46f4-8190-5a0a692acecf)
![image](https://github.com/user-attachments/assets/b7a10430-d98c-4d5c-9a8f-a550434eb9c1) -->

![image](https://github.com/user-attachments/assets/b0b33874-6643-47e7-bcbd-4c16bcfa3f50)

<!--
![Dark](https://github.com/user-attachments/assets/8813ac95-3874-44a5-b1e2-5fc7ef73e768)
![Light](https://github.com/user-attachments/assets/2da8c1ce-41f4-4462-afdb-bf9bc5856db8) -->

<p align="center">
Android
</p>

<p  align="center">
    <img src="https://github.com/user-attachments/assets/dba26693-265f-4fe8-a1b1-c3d62e5f0974" alt="Dark Mode">
    <img src="https://github.com/user-attachments/assets/d6e47258-f6a5-4bc5-9363-a9bff154e813" alt="Light Mode">
</p>

<details>
<summary>Other Pages</summary>

### Music Player

<!-- ![image](https://github.com/user-attachments/assets/6b20b784-e781-45f9-bf3d-5f31947329de)
![image](https://github.com/user-attachments/assets/2b1da093-d026-4db1-b4a4-741be37510e7) -->

![image](https://github.com/user-attachments/assets/59a335c7-9b19-42ba-9bc0-8d0f3c2bf3cf)

### Player Options

![image](https://github.com/user-attachments/assets/23feedaf-74b0-4fe4-b239-804bb4d0f1fe)
![image](https://github.com/user-attachments/assets/05a2e4fd-e1c4-4fce-baed-31c850315a4c)

### Setup Page

![image](https://github.com/user-attachments/assets/6953e236-e93d-45a4-b044-12f973781730)

### Analytics

<!-- ![image](https://github.com/user-attachments/assets/625e29b7-506f-4cf6-890f-ebdff50c6ea0) -->

![image](https://github.com/user-attachments/assets/1b69bd5e-5356-4ef0-931b-b7231b7bb638)

### Library Management

<!--![image](https://github.com/user-attachments/assets/ed5b4cf5-2155-4f90-8d81-b86893ace9c1) -->

![image](https://github.com/user-attachments/assets/01d59dfb-c511-4786-ae92-4784667db84d)

### User Management

<!--![image](https://github.com/user-attachments/assets/ad1addff-e949-48ac-9f8c-f070deda1002 -->

![image](https://github.com/user-attachments/assets/988aa053-9ca8-4ae7-8235-fc344fbd0d0c)

### Task Dashboard

<!--![image](https://github.com/user-attachments/assets/e38cedee-3f74-4c77-8311-74e5a35d7b4e) -->

![image](https://github.com/user-attachments/assets/d7749c4c-b6c1-4e55-8550-535126c2538d)

</details>

## How To Use

### Installation

#### Docker

You can download the zip archive for your platform from the latest or beta docker release.

- Unzip it to the location of your choice
  - Make sure that location has sufficient read/write/execute permissions for docker
- Run `./startDocker.bat` (Windows) or `sudo bash startDocker.sh` and let it create/copy the required files
- Visit `https://app.test` and follow the setup instructions there
- Place your media in `./data/media/CATEGORY/FOLDER/VIDEO.MP4`
  - Media must be grouped by a folder (category) and subfolder (folder) in order to show up on the website
  - There are certain names that you cannot use for folders or videos
    - `More to come...`

#### Standard

To run this yourself, you require a webserver such as [Caddy](https://caddyserver.com/) or [NGINX](https://nginx.org/en/index.html), [PostgreSQL](https://www.postgresql.org/), A build of Vue and Tailwind with [Node](https://nodejs.org/en), [FFmpeg](https://www.ffmpeg.org/), [ExifTool](https://exiftool.org/) (Optional) and PHP 8.3+. You can use [Laragon](https://laragon.org/) or the docker compose file to run in a single application. A valid SSL certificate is required to use some metadata features.

```bash
# Clone
git clone https://github.com/aminnausin/mediaServer.git

# Open
cd mediaServer

# Install dependencies
composer install

npm i

# Setup a database of your choice and input the required details in the .env file.
# Example:
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mediaServer
DB_USERNAME=postgres
DB_PASSWORD=

# Setup Laravel
php artisan key:generate

php artisan migrate

# Link storage folders
php artisan link

# Setup Laravel Reverb (Websocket) Environment (Or fill out the Reverb Variables Yourself)
php artisan install:broadcasting

# Build the app
npm run build

# Set up your App URL in the .env file
# Example:
APP_URL=https://app.test:8080
SANCTUM_STATEFUL_DOMAINS=app.test:8080,0.0.0.0:6001,app.test:6001

SESSION_DOMAIN=app.test

REVERB_HOST=app.test

# Run locally with Laravel
npm run vite:php
```

### Operating Instructions

To connect to your file server, create a Symbolic link between your storage folder and ./storage/app/public/media.
To see any of your videos, you must `INDEX FILES` from the user dropdown menu or start the Laravel scheduler which scans
every 6 hours. `SYNC FILES` will synchronise local cache json files with the database. The `VERIFY FILES` job will pull metadata
from the files using FFprobe.

Different folders are accessed via url/folder/subfolder. Using just URL/folder will open the first subfolder scanned.
You can share direct links to both videos and folders with the share buttons in the UI. Access to any category requires you to know its name by design.

#### Default File Types

- MP4 (ExifTool/FFmpeg)
- MKV (FFmpeg)
- MP3 (FFmpeg)
- OGG (FFmpeg)
- FLAC (FFmpeg)

> **Note:**
> You can add more by changing the code inside `./app/jobs/VerifyFiles.php` under the `generateVideos` function and in `./app/jobs/EmbedUidInMetadata.php` under the `handleEmbed` function.
> _Eventually_, I will make this dependant on values from the database which can be configured from a settings dashboard

## Activity

![Alt](https://repobeats.axiom.co/api/embed/fece6050fc62da0ebd2d200f904abaa3d09900dd.svg 'Repobeats analytics image')

## Similar Projects

Some similar projects that serve the same purpose but were not direct sources of inspiration for this project include:

- [Jellyfin](https://github.com/jellyfin/jellyfin) &nbsp; <img src="https://static-00.iconduck.com/assets.00/jellyfin-icon-2048x2048-4rlr467k.png" alt="jellyfin" width=14 height=14>

## Services Used

[![SonarQube Cloud](https://sonarcloud.io/images/project_badges/sonarcloud-dark.svg)](https://sonarcloud.io/summary/new_code?id=aminnausin_mediaServer)
