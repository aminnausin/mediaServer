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
    <img src="https://img.shields.io/github/license/aminnausin/mediaserver?color=purple" alt="License">
    <img src="https://sonarcloud.io/api/project_badges/measure?project=aminnausin_mediaServer&metric=ncloc" alt="Lines of Code">
    <a href="https://hub.docker.com/r/aminnausin/mediaserver"><img src="https://img.shields.io/docker/pulls/aminnausin/mediaserver?label=pulls&color=white&logo=docker&logoColor=white" alt="Docker Pulls"></a>
    <img src="https://repo-view-counter.nausin.me/?repo=aminnausin/mediaServer&colour=F9322C&label=views" alt="Repo View Counter">
    <br/>
    <img src="https://sonarcloud.io/api/project_badges/measure?project=aminnausin_mediaServer&metric=duplicated_lines_density" alt="Duplicate Lines of Code">
    <img src="https://sonarcloud.io/api/project_badges/measure?project=aminnausin_mediaServer&metric=sqale_index" alt="Technical Debt">
    <img src="https://sonarcloud.io/api/project_badges/measure?project=aminnausin_mediaServer&metric=code_smells" alt="Code Smells">
    <img src="https://sonarcloud.io/api/project_badges/measure?project=aminnausin_mediaServer&metric=reliability_rating" alt="Reliability Rating">
    <a href="https://demo.mediaserver.nausin.me"><img src="https://img.shields.io/website?url=https%3A%2F%2Fdemo.mediaserver.nausin.me&up_color=0DA60D&down_color=F9322C&label=demo&link=https%3A%2F%2Fdemo.mediaserver.nausin.me&logo=digitalocean&logoColor=white" alt="Demo Status"></a>
    <a href="https://github.com/aminnausin/mediaServer/releases"><img src="https://img.shields.io/github/v/release/aminnausin/mediaserver?logo=github&label=latest" alt="Latest Version"></a>

</p>

<!-- <br/> -->
<!-- <img src="https://img.shields.io/github/downloads/aminnausin/mediaserver/total?logo=github&logoColor=white" alt="GitHub Downloads"/> -->

<p  align="center">
  <a href="#features">Core Features</a> •
  <a href="#demo">Demo</a> •
  <a href="#getting-started">Getting Started</a> •
  <a href="#similar-projects">Similar Projects</a>
</p>

<!-- ![screenshot](.gif) -->

## Overview

**MediaServer** is a lightweight, self-hosted media player for your home server (or NAS).
It scans your folders, indexes your video and audio files, and serves them through a fast, minimalist web interface.

It was built for people who prefer a YouTube-like browsing experience for their collection of media files with metadata customisation.

## How it differs from Jellyfin

Jellyfin is designed primarily for commercial media, is metadata-first and works great for large automatically downloaded libraries.
MediaServer is naturally folder-oriented and great for mixed, personal, or highly customised libraries.

Both can serve shows, movies, and music but with different approaches.

| Feature | Jellyfin | MediaServer |
|---------|----------|-------------|
| **Content Focus** | Official metadata-first; built for movies/TV/Music | Folder-first; great for mixed/personal content and TV shows / music |
| **Watch History** | Only resume + watched flag | Full watch history with timestamps, re-watch counts, total view counts, per-user history, and playback heatmaps |
| **Player Experience** | Fullscreen; no browsing while playing | YouTube-style; browse folders while watching |
| **Libraries** | Unified global search | Library-scoped and account based access control |
| **Sharing** | No shareable URLs for videos/folders/shows | Direct and readable folder/video links with rich [open graph previews](#%EF%B8%8F-open-graph-preview-example) |

<!-- | **Organization** | Metadata-first (requires proper naming/structure) | Folder-first (your existing folder structure) | -->
<!-- | **Metadata** | Auto-scraping; breaks on moves/renames | Manual; survives moves/renames via embedded UUID | -->
<!-- | **Performance** | Heavy CPU-intensive transcoding | Direct file serving and scraped album art and thumbnails (what you upload is what you get) | -->

## Features

### Core Features

- 🎥 Fully custom-built media player (no native browser controls)
- 📁 Folder-based browsing & sharing
- 🧠 Watch history, view counts and playback analytics
- 💬 Subtitle support (VTT) with auto-extraction from embedded and external SRT / ASS
- 🎧 Music support with embedded cover art detection
- 🎵 Music player with lyrics viewer/editor based on LrcLib
- 📝 Editable metadata for videos, folders, and albums
- 🐋 Docker-based deployment with automatic releases
- 📊 Server dashboard for library management and background task queue
- 🖼️ Open Graph preview generator (Anilist-style)
- 🌗 Fully responsive UI with Dark/Light mode

<details>
<summary>Extended Features</summary>

#### 🖥️ Media Player

- Custom UI with Keyboard Shortcuts:
  - `k`: Play/Pause
  - `j` / `←`: Rewind 10s
  - `l` / `→`: Fast Forward 10s
  - `SHIFT+N`: Play Next
  - `SHIFT+P`: Play Previous
  - `m`: Mute
  - `f`: Toggle Fullscreen
  - `c`: Toggle Lyrics / Captions
  - `p`: Toggle playlist (autoplay)
- Playback Features:
  - Speed Controls
  - Player Statistics
  - Ambient Background (toggleable)
  - Heatmap Visualisation (after 5+ seeks)
  - Watch Party UI Demo *(coming soon)*
  - Auto-Scrolling Lyrics Viewer
  - Media Session API Integration

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
  - Disk/Track
  - Release Dates
  - Lyrics
  - Artist
  - Album
- **Folder Metadata**:
  - Thumbnail
  - Description
  - Studio
  - Release Dates
- **Persistent Metadata Mapping**:
  - Retains info even if file is moved or renamed when metadata tagging is enabled

#### 📈 History & Stats

- Track view counts (per user and total)
- Filterable watch history
- Future: playback frequency analytics

#### 💬 Subtitles & Captions

- Automatic extraction of embedded subtitles (SRT / ASS → VTT)
- Manual extraction of external subtitles next to media (in format ```/media/library/folder/{filename}.{lang}.{extension}```)
- Subtitle size controls in player

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

#### V0.17 (Coming Soon)

- 📊 Advanced Playback Stats
  - Activity
  - Most Played (daily, weekly, monthly)
  - Personal Favorites
  - Average watch time over time
- 📝 Captions/Subtitles Support
  - Auto embedded subtitle extraction
- Two-way audio metadata editing (apply deep metadata edits to file)
  - Ex/ Artist, Album, Cover-Art, Disk, Track, Year, Composer, Genre  
  - Real World Ex/ Musicolet Editor
- 🖼️ Image Extraction / Generation / Upload System
  - Implement a better system for image metadata
  - Have 3 different levels
    - embedded (cover art or auto thumbnails)
    - auto (from 3rd party metadata sources)
    - user (uploaded / provided via url by the user)
- Download Links
  - Must be optional and per library or folder
- Indexing Overhaul
- Media Tagging Cache
  - Put tagged media in cache, ideally on a different disk to reduce thrashing and increase speed
- Server Configuration Interface (Tentative Placement)
  - Configure Concurrent Process Limits
  - Manage global settings
    - Scan Frequency
    - Cache Location
    - Supported File Types
    - FFmpeg / ExifTool Settings
- Audio Spectograph Visuals (Low Priority)
  - Just for fun

#### V0.18

- 💬 Timed Comments (like SoundCloud)
- 🔐 User Roles (Admin / Contributor / Viewer)
- 👤 User Profiles & Friends System
- Activity Tracker
  - Logins / Logouts
  - Edits
  - Playback Start / Stop / Finish
  - Shares
  - Deletes

#### Future Versions

- 🎉 Live Sync Playback (Parties)
- In Browser Lyrics Editor/Generator with Playback (currently can only paste in timed lyrics but not generate them)
- Playlists
- Library Manager
  - Add libraries in browser via path
    - Mount path in docker and then point to it
    - Similar to Immich
  - Track symbolic links

### Ongoing Improvements

- 🛠️ Refactor Index and Verify Metadata Jobs (MAJOR) (Oldest code in the project)
  - Break into service structure with concurrent index jobs
  - Simplify metadata extraction and stop storing folder structure in JSON files
  - Implement cache disk feature to reduce time taken to tag media
- Make feature domains consistent
  - `library` instead of `category`
  - `media` instead of `video` (maybe a better name exists)
- Fix date inconsistencies and store everything in the same format / time-zone

</details>

## Demo

> [!NOTE]  
> The demo is running the latest beta image with static media. User accounts and edits are reset automatically every 15 minutes.

[![Current Beta](https://img.shields.io/github/v/release/aminnausin/mediaserver?include_prereleases&display_name=tag&label=latest%20beta)](https://github.com/aminnausin/mediaServer/releases)

- 🚀 [Live Demo](https://demo.mediaserver.nausin.me)
- 📦 [Docker Image](https://hub.docker.com/r/aminnausin/mediaserver)

Below are screenshots of the current webpage on Desktop and Android.

<!-- ![image](https://github.com/aminnausin/mediaServer/assets/83550431/495ba4cb-0e30-45e3-91b7-d3a3dae454b6) -->
<!-- ![image](https://github.com/aminnausin/mediaServer/assets/83550431/7df9dbe1-efec-4aad-ae64-df857f718480) -->
<!-- (https://github.com/aminnausin/mediaServer/assets/83550431/bdd531b0-85f9-499e-8f96-5d853f080cad)-->
<!-- (https://github.com/aminnausin/mediaServer/assets/83550431/5e99db0d-ca0d-477e-add4-fd2144790165)-->
<!-- |![Dark](https://github.com/user-attachments/assets/f0db341f-c3c8-44d0-8faf-a16e6f958726)|![Light](https://github.com/user-attachments/assets/ed82c114-940b-4ca1-ad8d-d2bab62f1851)| -->
<!--| ![Dark](https://github.com/user-attachments/assets/70c17425-96f2-4516-a7ce-c046d45f90c4) | ![Light](https://github.com/user-attachments/assets/b17d374c-9334-457e-9c49-768d2d38c291) | -->

<!-- ![image](https://github.com/user-attachments/assets/37313603-68b2-46f4-8190-5a0a692acecf)
![image](https://github.com/user-attachments/assets/b7a10430-d98c-4d5c-9a8f-a550434eb9c1)
![image]( https://github.com/user-attachments/assets/b0b33874-6643-47e7-bcbd-4c16bcfa3f50) -->

### 🖥️ Desktop View

<p align="center">
  <img src="https://github.com/user-attachments/assets/c163a27d-0c14-47e8-bb5e-cda06a9c635c" width="700" alt="Desktop Home View" />
</p>

<!--
![Dark](https://github.com/user-attachments/assets/8813ac95-3874-44a5-b1e2-5fc7ef73e768)
![Light](https://github.com/user-attachments/assets/2da8c1ce-41f4-4462-afdb-bf9bc5856db8) -->

### 📱 Android View

<!-- 
![image](https://github.com/user-attachments/assets/dba26693-265f-4fe8-a1b1-c3d62e5f0974)
![image](https://github.com/user-attachments/assets/d6e47258-f6a5-4bc5-9363-a9bff154e813) 
-->

<p align="center">
  <img src="https://github.com/user-attachments/assets/083daf6b-b4c0-49f8-823b-228a32060e7b" width="250" alt="Android Dark Mode" />
  &nbsp;
  <img src="https://github.com/user-attachments/assets/e010eec1-7aef-40ab-ab1b-4c1054a83e1b" width="250" alt="Android Light Mode" />
</p>

### 🖼️ Open Graph Preview Example

<p align="center">
    <img src="https://github.com/user-attachments/assets/2b4ebe41-c515-41c9-a76e-0fcd5db3d2f6" width="700" alt=Open Graph Preview>
</p>

<details>
<summary>Other Pages</summary>

### 🎵 Music Player

<!-- ![image](https://github.com/user-attachments/assets/6b20b784-e781-45f9-bf3d-5f31947329de)
![image](https://github.com/user-attachments/assets/2b1da093-d026-4db1-b4a4-741be37510e7) 
![image](https://github.com/user-attachments/assets/59a335c7-9b19-42ba-9bc0-8d0f3c2bf3c)
-->

<p align="center">
  <img src="https://github.com/user-attachments/assets/54deff73-3d34-45e0-bc38-ef88ef2a93d7" width="700" alt="Music Player" />
</p>

### 🎵 Lyrics Editor

<p align="center">
  <img src="https://github.com/user-attachments/assets/b3e626cd-7664-4980-8ea1-323c1acbcf42" height="600" alt="Lyrics Editor" />
</p>

### 📖 Lyrics Viewer

<p align="center">
  <img src="https://github.com/user-attachments/assets/4183b000-cd35-40f4-853f-57c1e66339ae" width="700" alt="Lyrics Viewer Dark" />
  <img src="https://github.com/user-attachments/assets/61099fa4-f009-4e5f-bb78-e2ed42ea6a9c" width="700" alt="Lyrics Viewer Light" />
</p>

### ⚙️ Player Options

<p align="center">

<!--![image](https://github.com/user-attachments/assets/23feedaf-74b0-4fe4-b239-804bb4d0f1fe)-->

  <img src="https://github.com/user-attachments/assets/6ca6378a-ad38-47f0-851f-c95d70aed984" width="700" alt="Player Option 1" />
  <img src="https://github.com/user-attachments/assets/05a2e4fd-e1c4-4fce-baed-31c850315a4c" width="700" alt="Player Option 2" />
</p>

### 🧰 Setup Page

<p align="center">
  <img src="https://github.com/user-attachments/assets/6953e236-e93d-45a4-b044-12f973781730" width="700" alt="Setup Page" />
</p>

### 📊 Analytics
<!-- ![image](https://github.com/user-attachments/assets/625e29b7-506f-4cf6-890f-ebdff50c6ea0) -->

<p align="center">
  <img src="https://github.com/user-attachments/assets/2d3984bb-6512-4c0c-a304-6619393e1ed3" width="700" alt="Analytics Dashboard" />
</p>

### 📁 Library Management

<!--![image](https://github.com/user-attachments/assets/ed5b4cf5-2155-4f90-8d81-b86893ace9c1) -->

<p align="center">
  <img src="https://github.com/user-attachments/assets/dd4888a2-2003-4183-8cae-2a45d78dd115" width="700" alt="Library Manager" />
</p>

### 👥 User Management

<!--![image](https://github.com/user-attachments/assets/ad1addff-e949-48ac-9f8c-f070deda1002 -->

<p align="center">
  <img src="https://github.com/user-attachments/assets/d1195561-dcae-4dad-bfa4-18f9bfee7aea" width="700" alt="User Manager" />
</p>

### 📝 Task Dashboard

<!--![image](https://github.com/user-attachments/assets/e38cedee-3f74-4c77-8311-74e5a35d7b4e) -->

<p align="center">
  <img src="https://github.com/user-attachments/assets/f73f27de-6035-46e6-a67d-f892e6bba67d" width="700" alt="Task Dashboard" />
</p>

### 🎛️ Site Preferences

<p align="center">
  <img src="https://github.com/user-attachments/assets/91d2a538-ede4-4ab9-9e06-06dfa0ca2d3a" width="700" alt="Site Preferences" />
</p>

### 👤 User Account Settings

<p align="center">
  <img src="https://github.com/user-attachments/assets/bd7b210b-2737-4101-bf27-3549a381526d" width="700" alt="User Account Settings" />
</p>

</details>

## Getting Started

[![Current Build](https://img.shields.io/github/v/release/aminnausin/mediaserver?logo=github&label=latest)](https://github.com/aminnausin/mediaServer/releases)

MediaServer can be run via Docker (recommended) or a standard manual installation.

### 🐳 Docker Installation (Recommended)

> [!WARNING]  
> Use the beta image to get the latest features. The main image is a couple of months behind.

1. Download the latest or beta **Docker release ZIP** for your platform.

    - It includes a `docker-compose.yaml` file that automatically sets up the (latest by default) [required images](https://hub.docker.com/r/aminnausin/mediaserver).

2. Unzip it to a folder with generous **read/write/execute** permissions.

    - The server will perform the following in this directory:
        - Rewrite your media with embedded UUID's for tracking (ffmpeg copy codec).
        - Read and write extracted album art and thumbnails from music and videos.
        - Read and write generated preview images.
        - Read and write Laravel + NGINX logs.
    - Permissions must stay consistent in this folder to prevent server errors.
    - The container uses the UID and GID 9999 under www-data.

3. Run the startup script (with Docker running) and let it create/copy the required files:

    - Windows: `startDocker.bat`
    - Linux/macOS: `sudo bash startDocker.sh`

4. Visit [`https://app.test`](https://app.test) in your browser and follow the setup wizard.

    - You will need to add app.test to your hosts file if you don't have a real url or set your APP_HOST to localhost manually.
        - There is a powershell script included to do this automatically.
        - On Linux, you are given the command.

5. Add your media to `./data/media/LIBRARY/FOLDER/VIDEO.MP4`

    - Media must be grouped by a folder (library) and subfolder (folder) in order to show up on the website
    - There are certain names that you cannot use for folders or videos such as
        - profile
        - settings
        - history
        - dashboard
        - log-viewer
        - pulse
        - horizon
        - __debug
        - api
        - ...
    - `More to come...`

### ⚙️ Manual Installation

> [!WARNING]  
> This setup is probably outdated and may need some tinkering to get right. I have since moved to docker for production installations but still use this method on my main instance (Linux).

To set up MediaServer without Docker, you’ll need:

- 🖥️ A web server: [Caddy](https://caddyserver.com/) or [NGINX](https://nginx.org/en)
- 🧠 Backend: [PHP 8.3+](https://www.php.net/), [PostgreSQL](https://www.postgresql.org/)
- 🎨 Frontend (for compiling only): [Node.js](https://nodejs.org/en) with [Vue 3](https://vuejs.org/) + [Tailwind CSS](https://tailwindcss.com/)
- 📼 Media tools: [FFmpeg](https://www.ffmpeg.org/) *(required)*, [ExifTool](https://exiftool.org/) *(optional)*
- 🔐 HTTPS: A valid SSL certificate is required to enable certain metadata features

> [!TIP]  
> You can use [Laragon](https://laragon.org/) to simplify local setup. This will only be available on the host machine.

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

# Edit the .env file with your PostgreSQL DB info
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

## Activity

![Repobeats Analytics](https://repobeats.axiom.co/api/embed/fece6050fc62da0ebd2d200f904abaa3d09900dd.svg)

<a href="https://www.star-history.com/#aminnausin/mediaserver&Date">
 <picture>
   <source media="(prefers-color-scheme: dark)" srcset="https://api.star-history.com/svg?repos=aminnausin/mediaserver&type=Date&theme=dark" />
   <source media="(prefers-color-scheme: light)" srcset="https://api.star-history.com/svg?repos=aminnausin/mediaserver&type=Date" />
   <img alt="Star History Chart" src="https://api.star-history.com/svg?repos=aminnausin/mediaserver&type=Date" />
 </picture>
</a>

## Similar Projects

Some similar projects that serve the same purpose but were not direct sources of inspiration include:

[![jellyfin](https://jellyfin.org/images/logo.svg)](https://github.com/jellyfin/jellyfin)

## Services Used

[![SonarQube Cloud](https://sonarcloud.io/images/project_badges/sonarcloud-dark.svg)](https://sonarcloud.io/summary/new_code?id=aminnausin_mediaServer)

## Disclaimer

MediaServer is intended for personal use only. It is designed to help users organise and access **their own legally obtained media** on a self-hosted server.

This project **does not condone or support piracy**, and I do not encourage the use of MediaServer for unauthorised distribution or consumption of copyrighted material.

Please respect local laws and content ownership rights when using this software.
