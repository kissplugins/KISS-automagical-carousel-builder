3. Activate **KISS Automagical Carousel Builder** from *WP Admin → Plugins*.

---

## How it works

* On every page render, the plugin’s content filter looks for `<img>` tags that  
are **immediately next to each other** (whitespace allowed) and groups of
exactly 3 or 4.
* Those groups are replaced with Swiper markup:
* top overlay = file name  
* center overlay = circular *current / total* indicator  
* bottom overlay = WP media‑library caption (if present)
* When at least one carousel is injected, the plugin enqueues:
* Swiper JS + CSS from the official CDN
* a tiny bootstrap script (`kacb.js`)
* a small inline style block for the overlays

Because the transformation happens **before** the HTML reaches any full‑page
cache (e.g. Cloudflare APO, WP Super Cache), the cached copy already contains
the final carousel—no runtime overhead for subsequent visitors.

---

## Usage

1. In the Classic Editor *or* Gutenberg, insert **three or four images** with
identical dimensions so they sit flush against each other in the HTML output  
(no text or blocks in between).
2. Publish or update the post.  
3. View the front end – the images are now an auto‑playing, looping carousel
complete with overlays and a center indicator.

### Example HTML snippet

```html
<!-- Classic editor source view -->
<p>
<img src="/wp-content/uploads/2025/05/slide-1.jpg" class="alignnone size-large wp-image-42" />
<img src="/wp-content/uploads/2025/05/slide-2.jpg" class="alignnone size-large wp-image-43" />
<img src="/wp-content/uploads/2025/05/slide-3.jpg" class="alignnone size-large wp-image-44" />
</p>
