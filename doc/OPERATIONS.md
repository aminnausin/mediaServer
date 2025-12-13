# 🛠️ MediaServer Operations Guide

This guide explains how to manage and operate MediaServer after installation.

## 📂 File Linking (Incompatible with Docker)

To connect your media storage to MediaServer, create a symbolic link from your media directory to Laravel's public storage:

```bash
ln -s /your/media/root ./storage/app/public/media
```

> Ensure this directory is accessible by the Laravel process and has read/write/execute permissions (for writing metadata).

## 🔄 File Scanning & Metadata Jobs

MediaServer supports multiple background jobs for managing your media library.

### Index Files

* Use this to detect and register new media files in your library.
* Triggered manually from the UI (`User Dropdown → Index Files`) or automatically by the Laravel scheduler.

### Sync Files

* Syncs the local `.json` metadata files with the database.
* Ensures consistency between file-based caches and the DB state.

### Verify Files

* Extracts technical metadata from files using **FFprobe** (from FFmpeg).
* This includes duration, resolution, codecs, and more.

### Verify Folders

* Generates metadata from folders based on the files within it.
* This metadata includes primary media type (for different UI on videos vs audio), total file size, file counts.
* Downloads external thumbnails and stores them locally for faster loading times.

### Scan Files

* Runs Index and Verify Files / Folders.
* Generates all content and metadata in one go.
* You should use this most of the time unless you are only filling missing data (like folder metadata).

### ⏰ Scheduled Automation

These jobs are also triggered automatically every 6 hours by Laravel’s scheduler.

To start the scheduler manually in dev environments:

```bash
php artisan schedule:work
```

## 🌐 URL Structure & Navigation

MediaServer follows a strict URL hierarchy:

```
/LIBRARY/FOLDER?VIDEO=VIDEO.ID
```

| Level      | Description                       |
| ---------- | --------------------------------- |
| `LIBRARY`  | Top-level folder name             |
| `FOLDER`   | Subfolder (e.g., a show or album) |
| `VIDEO`    | Media file (MP4, MP3, etc.)       |

### 🧭 Routing Behavior

* Visiting `/library/subfolder` shows the contents of a folder.
* Visiting `/library` automatically opens the **first scanned subfolder** that you have access to.
* Access to any **library** (top-level folder) requires **knowledge of its name** — this is intentional for ease of sharing.

## 🔗 Sharing Media

You can share direct links via the UI using the **Share** button:

* 🎞️ Individual videos / songs
* 📁 Folders

Shared links preserve access restrictions — unauthorized users cannot view private folders.

## 🎵 Supported Media Formats

| Format  | Metadata Extractor |
| ------- | ------------------ |
| `.mp4`  | FFmpeg + ExifTool  |
| `.m4a`  | FFmpeg             |
| `.mkv`  | FFmpeg             |
| `.mp3`  | FFmpeg             |
| `.ogg`  | FFmpeg             |
| `.wav`  | FFmpeg             |
| `.aac`  | FFmpeg             |
| `.flac` | FFmpeg             |

Everything else defaults to .mp4 when scanned by FFmpeg and may not work.

You also require a compatible browser to play content with certain codecs like HVEC. There is no transcoding.

## ➕ Adding Support for New Formats

To support more formats, update the following backend jobs:

* `app/Jobs/VerifyFiles.php` → `extractMimeType()`
* `app/Jobs/EmbedUidInMetadata.php` → `handleEmbed()`

> *Eventually*, I will make this dependant on values from the database which can be configured from a settings dashboard
