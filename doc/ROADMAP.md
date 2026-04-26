# Roadmap

This document tracks planned features, ongoing improvements, and known bugs.

> [!NOTE]  
> The information in this document was summarised by AI from my distributed development plans.

## v0.17 — In Progress

### ✅ Completed

- Mobile UI fixes (ambient mode, progress bar, hover states)
- Auth timeout fixes
- UI Overhaul
  - Updgrade to Tailwind V4
  - Convert main Pinia stores to TypeScript
  - Integrate cedar-ui
  - Implement system wide colour palettes
- Embedded subtitle support (SRT / ASS → VTT auto-extraction)
- Playback progress tracking (guest + user)
- Download links
  - Configurable per folder and per library
  - Guest downloads can be toggled
- Media tagging cache
  - Cache ffprobe output to database
  - Improves metadata verification speed
- Logo

### 🔧 In Progress / Planned

- **Two-way audio metadata editing** — apply edits directly to file (Artist, Album, Cover Art, Disk, Track, Year, Composer, Genre)
- **Thumbnail system overhaul**
  - Auto-generated from cover art or video frames
  - Third-party sources (e.g. AniList)
  - User-uploaded or URL-provided thumbnails
- **Metadata & ingestion overhaul** — simplify extraction, stop storing folder structure in JSON, concurrent index jobs
- **Browsing pages** — dedicated Folder, Seasons, Artists, and Albums views based on user feedback
- **Advanced playback stats**
  - Activity feed
  - Most played (daily, weekly, monthly)
  - Personal favourites
  - Average watch time over time
- **Server configuration interface** *(tentative)*
  - Concurrent process limits
  - Global settings (scan frequency, cache location, supported file types, FFmpeg / ExifTool)
- **Audio spectrograph visuals** *(low priority, just for fun)*

## v0.18 — Planned

- **User roles** — Admin / Contributor / Viewer permissions
- **Timed comments** (SoundCloud-style)
- **User profiles & friends system**
- **Activity tracker** — logins, edits, playback start/stop/finish, shares, deletes
- **Playback stats continued**
- Possible: OGP image generation as a microservice (Go or Node)
- Possible: Metadata extraction microservice

## Future Versions

- **Live sync playback** — watch parties
- **Playlists**
- **In-browser lyrics editor** with playback sync (currently paste-only)
- **Library manager** — add libraries via path in browser, track symbolic links
- **Basic transcoding** for unsupported file types

## Ongoing Improvements

- Refactor index and verify metadata jobs — oldest code in the project, needs a full service-based rewrite
- Make feature domains consistent (`library` not `category`, `media` not `video`)
- Fix date inconsistencies — standardise format and timezone across the codebase

## Known Bugs / Investigations

- UUID embedding is bottlenecked by disk speed — investigating cache-disk approach to copy, process, and write back in bulk
