# KISS Automagical Carousel Builder

Detects runs of **2–4 adjacent images** in post content and converts them, at
render‑time, into a Swiper carousel—fully compatible with page‑caching plugins.

* **Plugin slug:** `kiss‑automagical‑carousel‑builder`
* **Current version:** 1.2.2
* **License:** GPL v2 or later

---

## Installation

1. Copy the folder to `wp-content/plugins/`.
2. Verify it contains  
   ```
   kiss-automagical-carousel-builder.php
   kacb.js
   ```
3. Activate **KISS Automagical Carousel Builder** in *WP Admin → Plugins*.

---

## How it works

* On each front‑end request, the plugin looks for `<img>` tags that are
  **immediately adjacent** (whitespace allowed) and groups of two to four.
* The group is replaced with Swiper markup:
  * **Top overlay** — *file name* (hidden by default)  
  * **Center badge** — `1 / 3`, `2 / 3`, … (hidden by default)  
  * **Bottom overlay** — media‑library caption (only shown when non‑empty)
* A single DOM pass means minimal overhead; the final HTML is what your cache
  stores.
* The Swiper scripts/CSS are enqueued **only** when at least one carousel is
  injected.

---

## Usage

1. In Classic or Gutenberg, place **two, three or four images** of the same size so
   they abut each other.
2. Optionally insert `[kacb width="75%"]` directly before the images to set a
   custom carousel width.
3. Publish/Update → view the front end.

### Optional debug panel

Add the shortcode anywhere in your post:

```text
[kacb debug="true"]
```

and the page will show:

* a green‑text diagnostics block
* file‑name overlay enabled
* center badge enabled

Remove the shortcode when finished.

---

## Recent changes

| Version | Notes |
|---------|-------|
| **1.2.2** | Carousel width can be customised with `[kacb width="50%"]`. |
| **1.1.8** | Navigation arrows use transparent backgrounds. Captions now appear below images and left aligned; slides center images and inherit the body font. |
| **1.1.6** | *Captions* now fall back to `post_excerpt` or attachment **title**; overlay is omitted when caption empty.<br>*Filename* & *center badge* are hidden by default and auto‑re‑enabled only in `debug="true"` mode.<br>Debug panel accuracy improved by deferring its render to `wp_footer`. |
| **1.1.5** | Added debug‑mode badge toggle, caption fallback to `post_excerpt`, badge hidden outside debug. |
| **1.1.4** | Inserted mandatory `.swiper-wrapper` element (proper slide layout). |
| **1.1.3** | Fixed “Wrong Document” errors by keeping all DOM work in one document. |
| **1.1.2** | Skipped filter in admin area and added safe node insertion logic. |
| **1.1.0 – 1.1.1** | Initial public release: automatic carousel creation, Swiper integration, diagnostic shortcode. |

---

## Customisation

* **Show filename permanently** — override with CSS:  
  ```css
  .kacb-filename { display:block !important; }
  ```
* **Change slide counter styling** — target `.kacb-indicator`.
* **Adjust detection window** — edit `>= 2 && <= 4` in the filter if you’d
  like larger carousels.

---

Enjoy smoother galleries with zero editor overhead!
