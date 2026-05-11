# Foundation, Palette Tokens & Top Toolbar — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the legacy two-strip left-pane header with a full-width 38px dark-gray top toolbar above the existing two-pane layout, introduce `:root` palette tokens for the whole UI-redesign series, and clean up dead CSS.

**Architecture:** The toolbar lives in `main.template` above `#wrapper`, matching the sibling builder's `.tb-navbar` shell pattern. `#wrapper` shrinks to `calc(100% - 38px)`. Palette tokens are declared once on `:root` so sub-projects 2–4 reference token names instead of hex constants. Cleanup deletes unused `.folder`/`.file` selectors and the `file_sprite.png` they referenced.

**Tech Stack:** PHP/Eisodos templates (`.template` files with `<%FUNC%>`/`<%SQL%>` directives), vanilla Bootstrap 5.3.8 from `vendor/bower-asset/`, FontAwesome 7.1 (loaded via `assets/js/fontawesome/*.min.js`), jQuery 3.x, jQuery UI resizable.

**No automated tests** — the repo has no test suite or build step. Every task ends with a manual-verification step at the dev portal:
`https://tholos-editor-docker.offsite-solutions.com:10342/run.php`
The local docker compose project at `/Users/baxi/Work/_docker_images/tholos-editor` bind-mounts this repo, so edits land live without rebuild. Hard-refresh the URL after each commit.

**Spec:** [`docs/superpowers/specs/2026-05-11-foundation-and-toolbar-design.md`](../specs/2026-05-11-foundation-and-toolbar-design.md)
**Mockup:** [`docs/superpowers/mockups/2026-05-11-foundation-and-toolbar.html`](../mockups/2026-05-11-foundation-and-toolbar.html) — open in a browser for the visual target.

**Branch:** `feature/ui-redesign` (already created, currently at commit `66e1b68`).

---

## File Structure

| File | Action | Responsibility |
|---|---|---|
| `assets/css/TholosEditor.css` | Modify | Palette tokens (`:root`), toolbar styles (`.te-*`), shell geometry (`#wrapper`, `#component_tree_container`), dead-code deletions |
| `assets/templates/tholoseditor/main.template` | Modify | Insert `<nav class="te-toolbar">` above `<div id="wrapper">` |
| `assets/templates/tholoseditor/leftframe.main.template` | Modify | Remove lines 1–20 (the `#portalinfo_container` + `#options_container` blocks) |
| `assets/js/TholosEditor.js` | Modify | Add event-delegated Bootstrap tooltip init; rename `fa-refresh` → `fa-arrows-rotate` in the in-scope `showLoading()` spinner |
| `assets/images/tholos-logo.svg` | (exists) | Brand-mark asset; already created in commit `66e1b68` |
| `assets/images/file_sprite.png` | Delete | Unreferenced after CSS cleanup |

Out-of-scope files explicitly NOT touched in this plan:
- Any `rightframe.*.template` (deferred to sub-projects 3 and 4)
- `src/TholosEditor/*` (no PHP changes)
- `vendor/*`

---

## Task 1: Add `:root` palette tokens

**Files:**
- Modify: `assets/css/TholosEditor.css:1` (prepend)

Invisible change — declares CSS custom properties that later tasks and sub-projects reference. App must render identically afterwards.

- [ ] **Step 1: Prepend the palette-token block at the very top of the file**

Open `assets/css/TholosEditor.css`. Insert this block before the existing `html, body { … }` rule (which is currently line 1):

```css
/* =========================================================
   Palette tokens — UI redesign foundation (sub-project 1).
   Used across foundation, treeview, right-pane content,
   and entity-form sub-projects.
   ========================================================= */
:root {
  --te-primary:             #236499;
  --te-primary-hover:       #1d5380;
  --te-success:             #88bd21;
  --te-success-tint-hover:  rgba(136,189,33,0.15);
  --te-success-tint-select: rgba(136,189,33,0.22);
  --te-dark:                #3a3f44;
  --te-pane-bg:             #f8f9fa;
  --te-surface:             #ffffff;
  --te-border:              #dee2e6;
  --te-border-soft:         #f1f3f5;
  --te-text:                #212529;
  --te-text-muted:          #6c757d;
  --te-text-dim:            #adb5bd;
}

```

- [ ] **Step 2: Manual verification**

Hard-refresh `https://tholos-editor-docker.offsite-solutions.com:10342/run.php`.

Expected:
- The app renders exactly as before (no visible change).
- DevTools → Elements → `<html>` → Computed → search for `--te-primary` — value `#236499` resolves on `:root`.
- No JavaScript console errors. No CSS parse warnings in DevTools → Console.

- [ ] **Step 3: Commit**

```bash
git add assets/css/TholosEditor.css
git commit -m "$(cat <<'EOF'
Add :root palette tokens for UI redesign

Introduces the --te-* CSS custom properties used across foundation,
treeview, right-pane content, and entity-form sub-projects. Pure
infrastructure — no visible change.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 2: Add toolbar CSS rules

**Files:**
- Modify: `assets/css/TholosEditor.css` (append a new section before the existing `/* boostrap 5 hacks */` comment, around line 274)

Still invisible — adds the rule definitions but no markup consumes them yet.

- [ ] **Step 1: Locate the insertion point**

In `assets/css/TholosEditor.css`, find the comment `/* boostrap 5 hacks */` (currently around line 274). Insert the toolbar CSS block immediately **before** that comment.

- [ ] **Step 2: Insert the toolbar style block**

```css
/* =========================================================
   Top toolbar (.te-toolbar) — full-width 38px strip above
   #wrapper. Replaces the legacy #portalinfo_container +
   #options_container two-strip header.
   ========================================================= */
.te-toolbar {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 38px;
  display: flex;
  align-items: center;
  gap: .35rem;
  padding: .25rem .65rem;
  background-color: var(--te-dark);
  z-index: 10;
}

.te-brand {
  display: inline-flex;
  align-items: center;
  flex: 0 0 auto;
}
.te-brand-mark {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  background: #fff;
  border-radius: 4px;
}
.te-brand-mark img {
  width: 22px;
  height: 22px;
  display: block;
}
.te-brand-text {
  margin-left: .5rem;
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
}

.te-search {
  flex: 0 1 280px;
  min-width: 160px;
  margin-left: .5rem;
}
.te-search .form-control {
  font-size: 12px;
  height: 26px;
  min-height: 0;
  padding: .15rem .5rem;
}

.te-toolbar-spacer { flex: 1 1 auto; }

.te-toolbar-btn {
  --bs-btn-padding-x: .45rem;
  --bs-btn-padding-y: .1rem;
  --bs-btn-font-size: 12px;
  --bs-btn-line-height: 1;
  height: 26px;
  color: rgba(255,255,255,.85);
  background: transparent;
  border: none;
  border-radius: .25rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.te-toolbar-btn:hover {
  background: rgba(255,255,255,.12);
  color: #fff;
}
.te-toolbar-btn:focus-visible {
  outline: 2px solid rgba(255,255,255,.4);
  outline-offset: 1px;
}
/* Search button is inside the input-group, so it adopts a
   form-control-bordered look instead of the toolbar-btn look. */
.te-search .te-toolbar-btn {
  border: 1px solid var(--bs-border-color);
  border-left: 0;
  background: #fff;
  color: var(--te-text-muted);
  border-top-right-radius: .25rem;
  border-bottom-right-radius: .25rem;
}
.te-search .te-toolbar-btn:hover {
  background: var(--te-pane-bg);
  color: var(--te-primary);
}

.te-info-tip {
  text-align: left;
  font-size: 11px;
  line-height: 1.4;
}
.te-info-tip b { font-weight: 600; }

```

- [ ] **Step 3: Manual verification**

Hard-refresh the dev portal.

Expected:
- App renders exactly as before — no `<nav class="te-toolbar">` exists in the DOM yet, so the rules apply to nothing.
- DevTools → Sources → `TholosEditor.css` — the new block is present.
- No CSS parse errors in DevTools → Console.

- [ ] **Step 4: Commit**

```bash
git add assets/css/TholosEditor.css
git commit -m "$(cat <<'EOF'
Add .te-toolbar CSS rules

Defines the top-toolbar styles (brand mark, search input-group, action
buttons, spacer, tooltip-content helper). No markup consumes them yet
— activated in the next commit.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 3: Activate toolbar — swap header chrome

This task is one logical unit (three file edits in one commit) because activating the toolbar requires the new markup in `main.template`, removal of the old strips in `leftframe.main.template`, AND the geometry change to `#wrapper` to land together. Splitting them would leave at least one commit in a visibly broken intermediate state.

**Files:**
- Modify: `assets/templates/tholoseditor/main.template:15` (insert toolbar above `<div id="wrapper">`)
- Modify: `assets/templates/tholoseditor/leftframe.main.template:1-20` (delete the two old strips)
- Modify: `assets/css/TholosEditor.css` (update `#wrapper` height and `#component_tree_container` geometry)

### Step 1: Insert the toolbar block in `main.template`

- [ ] In `assets/templates/tholoseditor/main.template`, locate line 15 (`<body>`) and line 16 (`<div id="wrapper" class="container-fluid">`).

Insert the following block **between** `<body>` and `<div id="wrapper">`:

```html
<nav class="te-toolbar">
  <span class="te-brand">
    <span class="te-brand-mark">
      <img src="$TholosEditorAssetsDir/images/tholos-logo.svg" alt="Tholos" width="22" height="22">
    </span>
    <span class="te-brand-text">Tholos Editor</span>
  </span>

  <div class="te-search input-group input-group-sm">
    <input type="text" id="searchText" class="form-control" placeholder="Search…">
    <button class="btn te-toolbar-btn" type="button"
            onclick="loadComponentTree(true,'#search_result_tree',$('#searchText').val());"
            title="Search">
      <i class="fa-regular fa-magnifying-glass"></i>
    </button>
  </div>

  <span class="te-toolbar-spacer"></span>

  <button class="btn te-toolbar-btn" type="button"
          onclick="createComponentType('','');" title="New component type">
    <i class="fa-regular fa-square-plus"></i>
  </button>
  <button class="btn te-toolbar-btn" type="button"
          onclick="loadComponentTree(true,'#component_tree','');" title="Refresh">
    <i class="fa-regular fa-arrows-rotate" id="globalLoading"></i>
  </button>
  <button class="btn te-toolbar-btn" type="button"
          data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true"
          data-bs-title="<div class='te-info-tip'><div>$MainAddress</div><div>DB: <b>$DBSyntax</b></div><div>App: <b>$AppVersion</b> (dev: $devVersion)</div></div>"
          title="Connection info">
    <i class="fa-regular fa-circle-info"></i>
  </button>
</nav>
```

### Step 2: Remove the old strips from `leftframe.main.template`

- [ ] Open `assets/templates/tholoseditor/leftframe.main.template`. Delete lines 1–20 (the `#portalinfo_container` block and the `#options_container` block in their entirety).

The file should now start directly with line 22 of the original:

```html
<div id="component_tree_container">
  <div id="components">
    <ul id="component_tabs" class="nav nav-tabs" data-tabs="tabs">
…
```

Verify with `head -3 assets/templates/tholoseditor/leftframe.main.template` — first line should be `<div id="component_tree_container">`.

### Step 3: Update shell geometry CSS

- [ ] In `assets/css/TholosEditor.css`, find the existing `#wrapper { … }` rule (currently lines 17–23):

```css
#wrapper {
  height: 100%;
  margin: 0px;
  padding: 0px;
  position: absolute;
  width: 100%;
}
```

Change `height: 100%;` to `height: calc(100% - 38px);` and add `top: 38px;`:

```css
#wrapper {
  height: calc(100% - 38px);
  margin: 0px;
  padding: 0px;
  position: absolute;
  top: 38px;
  width: 100%;
}
```

- [ ] Find the existing `#component_tree_container { … }` rule (currently lines 97–104):

```css
#component_tree_container {
  position: absolute;
  top: 100px;
  height: calc(100% - 100px);
  width: 100%;
  padding: 0px;
  background-color: #fff;
}
```

Replace its body with:

```css
#component_tree_container {
  height: 100%;
  width: 100%;
  padding: 0;
  background-color: var(--te-surface);
  overflow: auto;
}
```

The selector and brace stay; only the body changes. Note: `position: absolute` and `top: 100px` are gone.

### Step 4: Manual verification

- [ ] Hard-refresh the dev portal.

Expected — match all of these against the mockup at `docs/superpowers/mockups/2026-05-11-foundation-and-toolbar.html`:

1. A 38px dark-gray (`#3a3f44`) toolbar spans the full window width across the top.
2. Left side of toolbar: Tholos logo (the green/blue/grey SVG, 22px) inside a white 28×28 rounded pill, followed by **"Tholos Editor"** in white 13px bold.
3. Toolbar middle: a 280px-wide search input with a magnifying-glass button glued to its right edge (input-group).
4. Toolbar right side: three icon buttons — square-plus (New), arrows-rotate (Refresh), circle-info (Info) — with subtle white hover backgrounds.
5. Hovering any toolbar button lightens its background to `rgba(255,255,255,.12)`.
6. The two-pane layout (`#left_frame` + `#right_frame`) sits below the toolbar with the jQuery UI vertical resize handle still working between them.
7. The component tree (`#component_tree_container`) fills the left pane below the legacy `Component Types`/`Search results` tab strip — no gap at the top.
8. Pressing Enter in the search input fetches search results (legacy `keyup` handler on `#searchText` still bound). Switching to the `Search results` tab shows them.
9. Clicking New (square-plus) opens the new-component-type dialog (`createComponentType('','')`).
10. Clicking Refresh (arrows-rotate) reloads the tree. Verify the icon enters a spin during the AJAX request by watching DevTools → Network for the `loadComponentTree` POST — the icon should have `fa-spin` class during the call (added by `showLoading()`).
11. Hovering the Info icon shows nothing yet — tooltip JS lands in Task 4.
12. No JavaScript console errors. No 404 in Network for `tholos-logo.svg`.
13. Right pane content is **visually unchanged** from before — same per-component-type tab strip with legacy blue-block active style, same tables.

### Step 5: Commit

- [ ] Run:

```bash
git add assets/templates/tholoseditor/main.template assets/templates/tholoseditor/leftframe.main.template assets/css/TholosEditor.css
git commit -m "$(cat <<'EOF'
Activate top toolbar; remove legacy left-pane header strips

Inserts <nav class="te-toolbar"> above #wrapper in main.template,
removes #portalinfo_container + #options_container from
leftframe.main.template, shrinks #wrapper to calc(100% - 38px), and
removes the legacy top:100px offset on #component_tree_container.

The info-icon tooltip is not yet functional — its JS init lands in
the next commit.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 4: Tooltip init + spinner icon rename in JS

**Files:**
- Modify: `assets/js/TholosEditor.js:3` (rename `fa-refresh` → `fa-arrows-rotate` in `showLoading`)
- Modify: `assets/js/TholosEditor.js` (append tooltip init near the end of the file)

### Step 1: Rename the spinner icon in `showLoading()`

- [ ] In `assets/js/TholosEditor.js`, find line 3:

```js
    container_.html('<div class="text-center"><i class="fa-regular fa-refresh fa-spin fa-lg"></i></div>');
```

Replace `fa-refresh` with `fa-arrows-rotate`:

```js
    container_.html('<div class="text-center"><i class="fa-regular fa-arrows-rotate fa-spin fa-lg"></i></div>');
```

This is the only other in-scope use of `fa-refresh`; the four legacy-name occurrences in `rightframe.componenttype.template` are out of scope for this sub-project (deferred to sub-project 3, which rewrites that whole template).

### Step 2: Add the event-delegated tooltip initializer

- [ ] At the very end of `assets/js/TholosEditor.js` (after the last line of code, currently around line 1042), append:

```js

// Bootstrap 5 tooltip — event-delegated so AJAX-injected templates
// pick up tooltips automatically via data-bs-toggle="tooltip".
new bootstrap.Tooltip(document.body, {
  selector: '[data-bs-toggle="tooltip"]'
});
```

### Step 3: Manual verification

- [ ] Hard-refresh the dev portal.

Expected:
1. Hover the ⓘ (circle-info) button at the right end of the toolbar — a dark Bootstrap tooltip appears below it with three lines:
   - The portal address (whatever `$MainAddress` resolved to)
   - `DB: pgsql` (or `oci8`) — second word bold
   - `App: <version>` (dev: `<devversion>`) — first version bold
2. The tooltip has a dark background (Bootstrap 5 default) and a small caret pointing up at the icon.
3. Click Refresh: during the AJAX request, the refresh icon spins. Confirm in DevTools → Elements that `<i id="globalLoading">` carries `fa-spin` while loading and loses it on completion.
4. Trigger a left-pane reload (e.g., right-click → refresh or call `refreshLeftFrame()` from the console). The center spinner that briefly replaces the left pane content renders as an arrows-rotate icon spinning, not a broken/missing glyph.
5. No JavaScript console errors.

### Step 4: Commit

- [ ] Run:

```bash
git add assets/js/TholosEditor.js
git commit -m "$(cat <<'EOF'
Tooltip init + spinner icon rename

Adds an event-delegated Bootstrap 5 tooltip initializer so any element
carrying data-bs-toggle="tooltip" (now the toolbar's info icon, later
property cells and other tooltip targets) becomes a tooltip without
per-element wiring or AJAX-load re-init.

Renames the fa-refresh spinner in showLoading() to fa-arrows-rotate
to match the toolbar's refresh icon and the FA 7 idiomatic name.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 5: Dead-code cleanup

**Files:**
- Modify: `assets/css/TholosEditor.css` (delete six rule blocks)
- Delete: `assets/images/file_sprite.png`

All deletions are verified-unreferenced. No visible change should result.

### Step 1: Verify nothing else references the asset

- [ ] Run a repo-wide search to confirm the legacy selectors and asset are unreferenced:

```bash
grep -rn 'file_sprite\.png\|#x-portalinfo_container\|#portalinfo_container\|#options_container' \
  --include='*.template' --include='*.js' --include='*.css' --include='*.php' \
  assets src 2>/dev/null
```

Expected output: only matches in `assets/css/TholosEditor.css` (the rules we're about to delete). If `leftframe.main.template` still shows matches, Task 3 didn't land cleanly — go back and resolve.

```bash
grep -rn '\.folder\|\.file\b' \
  --include='*.template' --include='*.js' --include='*.php' \
  assets src 2>/dev/null
```

Expected output: no matches in templates, JS, or PHP. (The CSS `#component_tree .folder` / `#component_tree .file` and `#search_result_tree .folder` / `#search_result_tree .file` rules existed solely for jstree theming that never ran.)

### Step 2: Delete the six CSS rule blocks

- [ ] In `assets/css/TholosEditor.css`, delete each of the following rule blocks. They appear in roughly this order in the current file:

(a) `#x-portalinfo_container { … }` — the `x-` prefix indicates a dead rule (currently lines 71–74):

```css
#x-portalinfo_container {
  position: absolute;
  width: 100%;
}
```

(b) `#portalinfo_container .logo { … }` (currently lines 76–81):

```css
#portalinfo_container .logo {
  background-color: #3d6c91;
  color: #fff;
  font-size: 14pt;
  padding-left: 5px;
}
```

(c) `#portalinfo_container .portal-info { … }` (currently lines 83–88):

```css
#portalinfo_container .portal-info {
  background-color: #8cb0cb;
  color: #fff;
  font-size: 10px;
  padding-left: 5px;
}
```

(d) `#options_container { … }` (currently lines 90–93):

```css
#options_container {
  margin-bottom: 0px;
  padding: 3px;
}
```

(e) The four `.folder` / `.file` rule blocks (currently lines 120–141):

```css
#component_tree .folder {
  background: url('/assets/images/file_sprite.png') right bottom no-repeat;
}

#component_tree .file {
  background: url('/assets/images/file_sprite.png') 0 0 no-repeat;
}
```

```css
#search_result_tree .folder {
  background: url('/assets/images/file_sprite.png') right bottom no-repeat;
}

#search_result_tree .file {
  background: url('/assets/images/file_sprite.png') 0 0 no-repeat;
}
```

(f) The commented-out `.nav-tabs > li > a` block (currently lines 143–148):

```css
/* .nav-tabs > li > a {
  padding: 5px;
  font-size: 12px;
}

 */
```

### Step 3: Delete `file_sprite.png`

- [ ] Run:

```bash
git rm assets/images/file_sprite.png
```

### Step 4: Manual verification

- [ ] Hard-refresh the dev portal.

Expected:
1. The app renders identically to the state after Task 4. No visible change.
2. DevTools → Network → filter by `Img`: no entry for `file_sprite.png`, no 404.
3. DevTools → Sources → `TholosEditor.css`: none of the deleted selectors appear. The commented-out `.nav-tabs > li > a` block is gone.
4. The tree, the search-results tab, and the right-pane tab strip are unchanged.
5. No JavaScript console errors.

### Step 5: Commit

- [ ] Run:

```bash
git add assets/css/TholosEditor.css
git commit -m "$(cat <<'EOF'
Dead-code cleanup: legacy header rules, .folder/.file, file_sprite.png

Deletes six CSS rule blocks made unreachable by Task 3 (#x-portalinfo_container,
#portalinfo_container .logo/.portal-info, #options_container, the four
#component_tree/#search_result_tree .folder/.file rules, and the
commented-out .nav-tabs > li > a block). Removes assets/images/file_sprite.png,
which was the only thing the .folder/.file rules pointed to.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Final Verification

After Task 5 commits, walk through every spec acceptance criterion (§7 of the design doc) end-to-end:

- [ ] Toolbar dimensions, palette, slot order match the mockup
- [ ] Two-pane resizable layout works below the toolbar
- [ ] Search Enter-key still triggers `loadComponentTree(true,'#search_result_tree', …)`
- [ ] New button still calls `createComponentType('','')`
- [ ] Refresh button still calls `loadComponentTree(true,'#component_tree','')` and spins during AJAX
- [ ] Info icon tooltip shows address / DB / version
- [ ] No file_sprite.png in `assets/images/`; new `tholos-logo.svg` is present
- [ ] No JavaScript console errors anywhere in the walkthrough
- [ ] Right-pane content is visually identical to its state before this sub-project

If everything passes, foundation #1 is complete. The next sub-project (treeview restyle) begins from this branch's HEAD.

---

## Self-review notes

- Every spec deliverable from §2 (in-scope) maps to a task:
  - Palette tokens → Task 1
  - Top toolbar + brand mark → Task 2 (CSS) + Task 3 (markup)
  - Remove old strips → Task 3
  - `tholos-logo.svg` already in tree from commit `66e1b68` → referenced in Task 3
  - `#wrapper` height + tree-container geometry → Task 3
  - Tooltip init → Task 4
  - Icon renames → Task 3 (three toolbar icons) + Task 4 (showLoading spinner)
  - Dead-code cleanup → Task 5
  - `file_sprite.png` delete → Task 5
- All code blocks contain complete, runnable content — no placeholders.
- Identifier consistency check:
  - `#searchText`, `#globalLoading`, `loadComponentTree`, `createComponentType` preserved everywhere they appear (in Task 3 markup, Task 4 verification, Final Verification).
  - CSS variable names match the spec (`--te-primary`, `--te-success`, `--te-dark`, `--te-pane-bg`, `--te-surface`, `--te-border`, `--te-border-soft`, `--te-text`, `--te-text-muted`, `--te-text-dim`, `--te-success-tint-hover`, `--te-success-tint-select`, `--te-primary-hover`).
