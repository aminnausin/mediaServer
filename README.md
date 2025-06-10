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

**MediaServer** is a lightweight self-hosted media player designed for your home server. It automatically scans and indexes your video and audio libraries and presents it through a modern, minimalist web interface.

Unlike traditional video platforms, MediaServer gives you full control over where and how your content is served — with no buffering and no third-party limitations.

Major features include watch history, music player with lyrics support, extensive metadata and online file management.

## Features

### Key Features

- 🎥 Custom Media Player
- 📁 Video and Folder Sharing
- 🧠 Watch History & View Counts
- 📝 Editable Metadata for Videos, Music & Folders
- 🌗 Dark / Light Mode Toggle
- 🎵 Lyrics Viewer and Editor with LrcLib
- 📊 Server Dashboard
- 🎧 Music Support with Embedded Cover Art Detection
- 🖼️ Open Graph Preview Generator (Anilist-style thumbnails for link sharing)

<details>
<summary>Extended Features</summary>

#### 🖥️ Media Player

- Custom UI with Keyboard Shortcuts:
  - `k`: Play/Pause
  - `j` / `←`: Rewind 10s
  - `l` / `→`: Fast Forward 10s
  - `SHIFT+N`: Next
  - `SHIFT+P`: Previous
  - `m`: Mute
  - `f`: Toggle Fullscreen
  - `l`: Toggle Lyrics / Captions
- Playback Features:
  - Speed Controls
  - Player Statistics
  - Ambient Background (toggleable)
  - Heatmap Visualization (after 5+ seeks)
  - Watch Party IU Demo *(coming soon)*

#### 🔗 Sharing

- Share individual videos or folders via URL (`/library/folder?video={id}` or `/library/{folder-name}`)

#### 🗃️ Metadata

- **Video Metadata**:
  - Thumbnail
  - Description
  - Tags
  - Season/Episode
  - Release Dates
  - Captions
- **Music Metadata**:
  - Cover
  - Description
  - Tags
  - Track/Disk
  - Release Dates
  - Lyrics
  - Artist
  - Album
- **Folder Metadata**:
  - Thumbnail
  - Description
  - Studio
  - Release Dates
- Persistent Metadata Mapping:
  - Retains info even if file is moved or renamed

#### 📈 History & Stats

- Track view counts (per user and total)
- Filterable watch history
- Future: playback frequency analytics

#### 🛠️ Server Dashboard

- Scanning Job Manager
- Library / Folder Manager
- User Manager
- Server Performance Metrics

#### 🖼️ Open Graph Image Previews

MediaServer automatically generates [Open Graph](https://ogp.me/) images for media and folders, styled similarly to platforms like [AniList](https://anilist.co).

These previews are embedded when sharing links on platforms like:

- Discord
- Twitter / X
- Reddit
- Facebook
- Telegram

Each image includes:

- 📸 Thumbnail from the media or folder
- 🎞️ Title, studio, description and metadata (e.g. season, tags, release date)
- 🎨 Custom layout designed to look clean and modern

The preview is rendered server-side using **Browsershot** and cached for performance.

> Example:  
> Sharing `https://yourserver.com/library/show` will show a rich preview card with folder art and metadata.

---

✅ Works even on private, self-hosted domains as long as your Open Graph routes are accessible.

</details>

### Planned Features

- 📊 Advanced Playback Stats
  - Most Played (daily, weekly, monthly)
  - Personal Favorites
  - Average watch time over time
- 💬 Timed Comments (like SoundCloud)
- 🖼️ Uploadable Images (For profiles, thumbnails, etc)
- 👤 User Profiles & Friends System
- 🎉 Live Sync Playback (Parties)
- 🔐 User Roles (Admin / Contributor / Viewer)
- 🌐 Metadata Auto-Scraper from APIs
- 📝 Captions/Subtitles Support

</details>

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

### 🖥️ Desktop View

<p align="center">
  <img src="https://github.com/user-attachments/assets/b0b33874-6643-47e7-bcbd-4c16bcfa3f50" width="700" alt="Desktop Home View" />
</p>

<!--
![Dark](https://github.com/user-attachments/assets/8813ac95-3874-44a5-b1e2-5fc7ef73e768)
![Light](https://github.com/user-attachments/assets/2da8c1ce-41f4-4462-afdb-bf9bc5856db8) -->

### 📱 Android View

<p align="center">
  <img src="https://github.com/user-attachments/assets/dba26693-265f-4fe8-a1b1-c3d62e5f0974" width="250" alt="Android Dark Mode" />
  &nbsp;
  <img src="https://github.com/user-attachments/assets/d6e47258-f6a5-4bc5-9363-a9bff154e813" width="250" alt="Android Light Mode" />
</p>

<details>
<summary>Other Pages</summary>

### 🎵 Music Player

<!-- ![image](https://github.com/user-attachments/assets/6b20b784-e781-45f9-bf3d-5f31947329de)
![image](https://github.com/user-attachments/assets/2b1da093-d026-4db1-b4a4-741be37510e7) -->

<p align="center">
  <img src="https://github.com/user-attachments/assets/59a335c7-9b19-42ba-9bc0-8d0f3c2bf3cf" width="700" alt="Music Player" />
</p>

### ⚙️ Player Options

<p align="center">
  <img src="https://github.com/user-attachments/assets/23feedaf-74b0-4fe4-b239-804bb4d0f1fe" width="320" alt="Player Option 1" />
  &nbsp;
  <img src="https://github.com/user-attachments/assets/05a2e4fd-e1c4-4fce-baed-31c850315a4c" width="320" alt="Player Option 2" />
</p>

### 🧰 Setup Page

<p align="center">
  <img src="https://github.com/user-attachments/assets/6953e236-e93d-45a4-b044-12f973781730" width="700" alt="Setup Page" />
</p>

### 📊 Analytics
<!-- ![image](https://github.com/user-attachments/assets/625e29b7-506f-4cf6-890f-ebdff50c6ea0) -->

<p align="center">
  <img src="https://github.com/user-attachments/assets/1b69bd5e-5356-4ef0-931b-b7231b7bb638" width="700" alt="Analytics Dashboard" />
</p>

### 📁 Library Management

<!--![image](https://github.com/user-attachments/assets/ed5b4cf5-2155-4f90-8d81-b86893ace9c1) -->

<p align="center">
  <img src="https://github.com/user-attachments/assets/01d59dfb-c511-4786-ae92-4784667db84d" width="700" alt="Library Manager" />
</p>

### 👥 User Management

<!--![image](https://github.com/user-attachments/assets/ad1addff-e949-48ac-9f8c-f070deda1002 -->

<p align="center">
  <img src="https://github.com/user-attachments/assets/988aa053-9ca8-4ae7-8235-fc344fbd0d0c" width="700" alt="User Manager" />
</p>

### 📝 Task Dashboard

<!--![image](https://github.com/user-attachments/assets/e38cedee-3f74-4c77-8311-74e5a35d7b4e) -->

<p align="center">
  <img src="https://github.com/user-attachments/assets/d7749c4c-b6c1-4e55-8550-535126c2538d" width="700" alt="Task Dashboard" />
</p>

</details>

## How To Use

MediaServer can be run via Docker (recommended) or a standard manual installation.

### 🐳 Docker Installation (Recommended)

1. Download the latest or beta **Docker release ZIP** for your platform.
2. Unzip it to a folder with full **read/write/execute** permissions.
3. Run the startup script (with Docker running) and let it create/copy the required files:

    - Windows: `startDocker.bat`
    - Linux/macOS: `sudo bash startDocker.sh`

4. Visit [`https://app.test`](https://app.test) in your browser and follow the setup wizard.
5. Add your media to `./data/media/LIBRARY/FOLDER/VIDEO.MP4`

    - Media must be grouped by a folder (library) and subfolder (folder) in order to show up on the website
    - There are certain names that you cannot use for folders or videos
    - `More to come...`

### ⚙️ Manual Installation

To set up MediaServer without Docker, you’ll need:

- 🖥️ A web server: [Caddy](https://caddyserver.com/) or [NGINX](https://nginx.org/en)
- 🧠 Backend: [PHP 8.3+](https://www.php.net/), [PostgreSQL](https://www.postgresql.org/)
- 🎨 Frontend (for compiling only): [Node.js](https://nodejs.org/en) with [Vue 3](https://vuejs.org/) + [Tailwind CSS](https://tailwindcss.com/)
- 📼 Media tools: [FFmpeg](https://www.ffmpeg.org/) *(required)*, [ExifTool](https://exiftool.org/) *(optional)*
- 🔐 HTTPS: A valid SSL certificate is required to enable certain metadata features

> 💡 Tip: You can use [Laragon](https://laragon.org/) or the included `docker-compose.yml` to simplify local setup.

```bash
# 1. Clone the repository
git clone https://github.com/aminnausin/mediaServer.git
cd mediaServer

# 2. Install backend dependencies
composer install

# 3. Install frontend dependencies
npm install

# 4. Set up your database and .env
cp .env.example .env

# Edit the .env file with your Postgress DB info
# Example:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=mediaServer
# DB_USERNAME=postgres
# DB_PASSWORD=

# 5. Generate Laravel app and reverb keys
php artisan key:generate
php artisan reverb:generate

# 6. Run database migrations
php artisan migrate

# 7. Link storage (for thumbnails, posters, etc.)
php artisan storage:link

# 9. Build frontend assets
npm run build

# 9. Set app domain in .env (required for Sanctum & WebSockets) (6001 for reverb)
# Example:
# APP_URL=https://app.test:8080
# SANCTUM_STATEFUL_DOMAINS=app.test:8080,0.0.0.0:6001,app.test:6001
# SESSION_DOMAIN=app.test
# REVERB_HOST=app.test

# 10. Run the app in development mode
npm run vite:php
```

## 📚 Additional Documentation

- [🛠️ Operations Guide](doc/OPERATIONS.md) – Symbolic linking, scanning, metadata jobs, supported formats

### Default File Types

- MP4 (ExifTool/FFmpeg)
- MKV (FFmpeg)
- MP3 (FFmpeg)
- OGG (FFmpeg)
- FLAC (FFmpeg)

> **Note:**
> You can add more by changing the code inside `./app/jobs/VerifyFiles.php` under the `generateVideos` function and in `./app/jobs/EmbedUidInMetadata.php` under the `handleEmbed` function.
> *Eventually*, I will make this dependant on values from the database which can be configured from a settings dashboard

## Activity

![Alt](https://repobeats.axiom.co/api/embed/fece6050fc62da0ebd2d200f904abaa3d09900dd.svg 'Repobeats analytics image')

## Similar Projects

Some similar projects that serve the same purpose but were not direct sources of inspiration for this project include:

- [Jellyfin](https://github.com/jellyfin/jellyfin) &nbsp; <img src="https://static-00.iconduck.com/assets.00/jellyfin-icon-2048x2048-4rlr467k.png" alt="jellyfin" width=14 height=14>

## Services Used

[![SonarQube Cloud](https://sonarcloud.io/images/project_badges/sonarcloud-dark.svg)](https://sonarcloud.io/summary/new_code?id=aminnausin_mediaServer)
