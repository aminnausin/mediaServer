# 🎯 Feature: Lyrics Search & Submission via LRCLib

## 📚 Overview

<!-- What's the goal of this feature? What problem does it solve? -->

When a song has no lyrics, users can search LRCLib for matches, preview options, and confirm the correct lyrics to save.

---

### 🧭 User Flow

<!-- Step-by-step of what the user does -->

1. Song has no lyrics → user opens lyric viewer and sees related message
2. User presses edit lyrics button → opens dedicated modal for lyrics with a built in mp3 player and import / save / cancel buttons
3. User clicks → fetch results from LRCLib.
4. Display possible matches (`artist - title`).
5. User selects one → save it locally and load it in the viewer.
6. User confirms → save lyrics to video.

---

### 🧱 Technical Plan

#### Components Involved

- `LyricsViewer.vue`: Entry point, shows search button
- `LyricsEditorForm.vue`: Edit, Confirm, Cancel, Reset
- `LyricsGenerator.vue`: Search, Select
- `lyricsService.ts`: Fetches from LRCLib API
- `ExternalMetadataController.php` : Fetches metadata from external sources (LRCLib, anidb, imdb)
- `MetadataController.php`: Saves lyrics to metadata (use existing function)

#### API Endpoints

- `GET /api/metadata/:id/import/lyrics` (proxy fetch and parse full metadata serverside)

---

### ✅ Acceptance Criteria

- [ ] "Import Lyrics" button appears in edit modal if no lyrics exist
- [ ] Modal allows listing of multiple matches with the styling of lrclib (lots of metadata)
- [ ] User can confirm and attach lyrics
- [ ] Lyrics appear immediately in viewer once added
- [ ] Lyrics are saved to database upon saving modal
- [ ] Errors and edge cases are handled (e.g., no results, network failure on import or save)
- [ ] User must be warned if they are replacing existing lyrics

---

### 🧪 Testing Plan

#### Unit

- [ ] `lyricsService` fetch returns correct data
- [ ] Modal state changes on selection/confirm

#### Integration

- [ ] End-to-end search → confirm → display flow

#### Manual Test

- [ ] Try with a known match
- [ ] Try with no match
- [ ] Cancel mid-flow

---

### 📌 Notes / Questions

<!-- Open questions, blockers, tradeoffs -->

- Should users be allowed to edit the lyrics after confirmation?
- Cache LRCLib results locally?
