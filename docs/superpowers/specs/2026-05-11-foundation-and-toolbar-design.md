# UI Redesign — Foundation, Palette Tokens, Top Toolbar

**Date:** 2026-05-11
**Branch:** `feature/ui-redesign`
**Status:** Design
**Sub-project:** 1 of 4 (foundation; followed by treeview, right-pane content, entity forms)

---

## 1. Background

Tholos Editor's current left-pane chrome is a stack of two strips above the
component tree:

- `#portalinfo_container` — a blue-gradient brand bar showing `Tholos ::
  Component Type Editor` plus a smaller line with `$MainAddress`, `$DBSyntax`,
  `$AppVersion`, `$devVersion`.
- `#options_container` — a Bootstrap-3 grid row carrying the search input and
  three icon buttons (search, new component type, refresh).

The component tree itself sits at `position: absolute; top: 100px` to clear
those strips — a fragile hard-coded offset that fights every resize and assumes
the strips never change height.

The sibling project Tholos Builder shipped a feature/ui-redesign branch in
2026-05 that establishes a unified visual language anchored on offsite-green
`#88bd21`, offsite-blue `#236499`, and dark-gray `#3a3f44`, with a small kit of
reusable utility classes (`.pf-tabs`, `.pf-table`, `.pf-list-row`,
`.pf-row-actions`, `.pf-help-empty`, `.pf-form`, `.tb-navbar`). This spec is
the first of four sub-projects on the editor that port that visual language to
this repo's surface area.

Editor's surface is narrower than builder's — no login screen, no wizards,
no Build/Compile/Docs/Routes actions — but the **top toolbar** is a direct
analog of builder's `.tb-navbar`. This spec replaces the left-pane two-strip
header with a **full-width 38px toolbar above the two-pane layout**, modeled
on builder's navbar pattern, and uses it as the vehicle for introducing
palette tokens and dead-code cleanup that the remaining three sub-projects
will rely on.

## 2. Scope

**In scope**

- Introduce a `:root` palette token block covering every color token the four
  sub-projects will reference (not just the toolbar's own colors).
- Insert a full-width `<nav class="te-toolbar">` 38px strip at the top of
  `main.template`, above `#wrapper`. Shrink `#wrapper` to
  `height: calc(100% - 38px)` so the existing two-pane layout sits below.
- Remove `#portalinfo_container` and `#options_container` from
  `leftframe.main.template`. Their actions (search, new, refresh, version
  info) move into the new top toolbar.
- Drop the hard-coded `top: 100px` offset on `#component_tree_container` so
  the tree fills its pane cleanly.
- Add the Tholos logo as an SVG asset (`assets/images/tholos-logo.svg`,
  copied from the sibling builder repo) and use it as the toolbar brand mark
  in place of the raster `favicon.png`.
- Move the always-visible version info (`$MainAddress`, `$DBSyntax`,
  `$AppVersion`, `$devVersion`) into a hover tooltip on a new info icon.
- Dead-code cleanup in `assets/css/TholosEditor.css`:
  - `#component_tree .folder`, `#component_tree .file` rules.
  - `#search_result_tree .folder`, `#search_result_tree .file` rules.
  - The commented-out `.nav-tabs > li > a` block.
  - The `#x-portalinfo_container` rule (prefixed with `x-`, dead).
  - The now-orphaned `#portalinfo_container .logo`, `#portalinfo_container
    .portal-info`, and `#options_container` rules.
- Delete `assets/images/file_sprite.png` (unreferenced after the CSS cleanup).
- Initialize Bootstrap 5 tooltips via event-delegation so future tooltips
  anywhere in the app work without per-element wiring.
- Rename three icon classes to FontAwesome 7 idiomatic names:
  - `fa-refresh` → `fa-arrows-rotate`
  - `fa-search` → `fa-magnifying-glass`
  - `fa-plus-square` → `fa-square-plus`

**Out of scope (deferred to follow-up sub-projects on the same branch)**

- jstree theme / treeview restyle, including the `color-*` icon classes,
  `.tree_class_name` badge color rules, and the `.vakata-context` right-click
  context menu (sub-project 2).
- Right-pane content restyle: `.nav-tabs` global override, table-condensed →
  `.pf-table`, BS3 row-action buttons → `.pf-row-actions`, the
  `componentData_container` column layout (sub-project 3).
- Entity form editors (`rightframe.form.componenttype/event/method/property.template`)
  and the BS3 shim removal that depends on them (sub-project 4).
- Any change to `src/TholosEditor/TholosEditorApplication.php` or
  `TholosEditorCallback.php`.

## 3. Architecture

### 3.1 Application shell

The toolbar is full-width across the app, sitting above the existing two-pane
layout — the same pattern the builder ships in its `.tb-navbar` sub-project.

```
main.template
├── <nav class="te-toolbar">             ← NEW: 38px full-width dark-gray strip
└── #wrapper                              ← height: calc(100% - 38px)
    └── #container
        ├── #left_frame.resizable
        │   └── .content
        │       └── #component_tree_container  ← height: 100% (no top offset)
        │           └── #components / tabs / jstree (unchanged)
        └── #right_frame.resizable
            └── .content (unchanged)
```

Two geometry changes flow from putting the toolbar in `main.template`:

1. **`#wrapper`** changes from `height: 100%` to `height: calc(100% - 38px)`.
   The two-pane resizable layout now occupies the viewport minus the
   toolbar.
2. **`#component_tree_container`** drops `position: absolute; top: 100px;
   height: calc(100% - 100px)` — that legacy offset existed only to clear the
   now-gone `#portalinfo_container` + `#options_container` strips. It becomes
   `height: 100%; width: 100%; overflow: auto`, filling its pane cleanly.

Reasoning: the alternative — keeping the toolbar inside the left pane — was
considered during brainstorming and chosen first because it preserved the
host application's embedding geometry. We reversed that decision: visual
parity with the sibling builder is more valuable than embedding-shape
preservation, and the host application already accepts builder's toolbar
geometry alongside this app, so the geometry change is a known quantity.

### 3.2 Palette tokens

Introduced as CSS custom properties on `:root` at the top of `TholosEditor.css`.
The set is sized for all four sub-projects, not just foundation, so later
sub-projects reference token names rather than hex constants:

```css
:root {
  --te-primary:             #236499;   /* offsite-blue */
  --te-primary-hover:       #1d5380;
  --te-success:             #88bd21;   /* offsite-green */
  --te-success-tint-hover:  rgba(136,189,33,0.15);
  --te-success-tint-select: rgba(136,189,33,0.22);
  --te-dark:                #3a3f44;   /* toolbar background */
  --te-pane-bg:             #f8f9fa;   /* tree pane, table even-row stripe */
  --te-surface:             #ffffff;
  --te-border:              #dee2e6;
  --te-border-soft:         #f1f3f5;
  --te-text:                #212529;
  --te-text-muted:          #6c757d;
  --te-text-dim:            #adb5bd;
}
```

Foundation #1 only consumes `--te-dark`, `--te-surface`, `--te-border`,
`--te-text`, `--te-text-muted`. The remainder is declared upfront so sub-projects
2–4 don't each have to extend the token set.

### 3.3 Toolbar slot order

```
[brand mark + "Tholos Editor"]  [search input ─ search btn]  ⟶ flex spacer ⟶
[+ new]  [⟳ refresh]  [ⓘ info]
```

Every action keeps its existing JavaScript binding 1:1. Only the host markup
changes.

| Action | JS call | Notes |
|---|---|---|
| Search | `loadComponentTree(true,'#search_result_tree',$('#searchText').val())` | unchanged |
| Search-on-Enter | `$("#searchText").keyup` handler in `TholosEditor.js` | unchanged (same id preserved) |
| New component type | `createComponentType('','')` | unchanged |
| Refresh tree | `loadComponentTree(true,'#component_tree','')` | unchanged; refresh icon retains `id="globalLoading"` for AJAX spinner |
| Version info | new — Bootstrap 5 tooltip | content from existing `$MainAddress` / `$DBSyntax` / `$AppVersion` / `$devVersion` Eisodos params |

### 3.4 Tooltip initialization

A single event-delegated initializer added once at startup in `TholosEditor.js`:

```js
new bootstrap.Tooltip(document.body, {
  selector: '[data-bs-toggle="tooltip"]'
});
```

This lets any element added later (including from AJAX-injected templates)
become a tooltip simply by carrying `data-bs-toggle="tooltip"` — no per-element
init required.

### 3.5 Why the existing nav-tabs blue-block style stays for now

`TholosEditor.css` currently has two global rules that style every nav-tabs
strip in the app:

```css
.nav-tabs .nav-link        { color: #337ab7; }
.nav-tabs .nav-link.active { color: #fff; background-color: #8cb0cb; }
```

The left pane's "Component Types" / "Search results" tab strip *and* the
right pane's per-component-type tab strip both consume these rules. Replacing
them with the new underline-style `.pf-tabs` pattern would change the right
pane in this commit, which contradicts the sub-project decomposition. Both
rules stay until sub-project 3 (right-pane content) lands, at which point
both surfaces move to `.pf-tabs` together.

## 4. Visual specification

### 4.1 Toolbar (`.te-toolbar`)

| Property | Value |
|---|---|
| Background | `var(--te-dark)` (`#3a3f44`) |
| Min height | `38px` |
| Padding | `.25rem .5rem` |
| Layout | `display: flex; align-items: center; gap: .35rem;` |
| Brand mark | `tholos-logo.svg` rendered at 22px, inside a 28×28 white rounded pill (border-radius 4px) |
| Brand text | 13px, weight 600, color `#fff`, margin-left `.4rem` |
| Search wrapper | `flex: 0 1 280px` (shrinks if window narrows) |
| Search input | Bootstrap `form-control form-control-sm`, font 12px, height 26px |
| Search button | merged via Bootstrap `input-group` — adopts toolbar-btn style |
| Spacer | `flex: 1 1 auto` (pushes new/refresh/info to the right) |
| Toolbar button (`.te-toolbar-btn`) | transparent bg, color `rgba(255,255,255,.85)`, height 26px, padding `.1rem .45rem`, font 12px, border: none, border-radius `.25rem` |
| Toolbar button hover | bg `rgba(255,255,255,.12)`, color `#fff` |
| Toolbar button focus-visible | outline `2px solid rgba(255,255,255,.4)`, outline-offset `1px` |
| Tooltip content (info icon) | three lines: `$MainAddress`, `DB: <b>$DBSyntax</b>`, `App: <b>$AppVersion</b> (dev: $devVersion)` |

### 4.2 Toolbar markup

```html
<nav class="te-toolbar">
  <span class="te-brand">
    <span class="te-brand-mark">
      <img src="$TholosEditorAssetsDir/images/tholos-logo.svg" alt="" width="22" height="22">
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
  <button class="btn te-toolbar-btn te-info-btn" type="button"
          data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true"
          data-bs-title="<div class='te-info-tip'><div>$MainAddress</div><div>DB: <b>$DBSyntax</b></div><div>App: <b>$AppVersion</b> (dev: $devVersion)</div></div>"
          title="Connection info">
    <i class="fa-regular fa-circle-info"></i>
  </button>
</nav>
```

### 4.3 Shell geometry

```css
#wrapper {
  height: calc(100% - 38px);   /* was: 100% */
  margin: 0;
  padding: 0;
  position: absolute;
  width: 100%;
}

#component_tree_container {
  height: 100%;
  width: 100%;
  padding: 0;
  background-color: var(--te-surface);
  overflow: auto;
  /* removed: position: absolute; top: 100px; height: calc(100% - 100px); */
}
```

`#left_frame .content` and `#right_frame .content` are untouched — they
already declare `height: 100%; overflow: auto`, which now resolves against
the shrunken `#wrapper`.

## 5. Files changed

| File | Change |
|---|---|
| `assets/templates/tholoseditor/main.template` | Insert `<nav class="te-toolbar">…</nav>` block (§4.2) above `<div id="wrapper">`. No other changes. |
| `assets/templates/tholoseditor/leftframe.main.template` | Remove lines 1–20 entirely (the `#portalinfo_container` and `#options_container` blocks). The file now starts with `<div id="component_tree_container">`. |
| `assets/images/tholos-logo.svg` | New file. Copied from the sibling builder repo (`_tholos_builder/Base/.superpowers/brainstorm/offsite_logo_only_v3.svg`). Used as the toolbar brand mark. |
| `assets/css/TholosEditor.css` | (a) Prepend `:root { … }` palette tokens (§3.2). (b) Add `.te-toolbar`, `.te-brand`, `.te-brand-mark`, `.te-brand-text`, `.te-search`, `.te-toolbar-btn`, `.te-toolbar-btn:hover`, `.te-toolbar-btn:focus-visible`, `.te-toolbar-spacer`, `.te-info-tip` rule blocks. (c) Change `#wrapper { height: 100% }` → `height: calc(100% - 38px)`. (d) Rewrite `#component_tree_container` — remove `position: absolute; top: 100px; height: calc(100% - 100px)`, use `height: 100%; width: 100%; overflow: auto;`. (e) Delete `#component_tree .folder`, `#component_tree .file`, `#search_result_tree .folder`, `#search_result_tree .file` rules (4 blocks). (f) Delete the commented-out `.nav-tabs > li > a` block. (g) Delete `#x-portalinfo_container`, `#portalinfo_container .logo`, `#portalinfo_container .portal-info`, `#options_container` rules. |
| `assets/js/TholosEditor.js` | Add one initializer near the bottom of the file's startup section: `new bootstrap.Tooltip(document.body, { selector: '[data-bs-toggle="tooltip"]' });` |
| `assets/images/file_sprite.png` | Delete. Unreferenced after the CSS cleanup. |

No changes to any `rightframe.*` template, `src/TholosEditor/*`, or `vendor/`.

## 6. Behavior changes

1. **Two-strip left-pane header is gone; replaced by a full-width 38px top
   toolbar.** Net vertical real estate gain in the left pane ≈ 98px (old
   strips removed). Net loss across the app ≈ 38px (new toolbar above
   `#wrapper`).
2. **Toolbar spans both panes.** The host application embedding this editor
   sees a 38px taller chrome above the two-pane layout. This matches the
   sibling builder's shell pattern; the host already accepts that geometry
   for builder.
3. **Brand text shortens.** `Tholos :: Component Type Editor` →
   `Tholos Editor`. The qualifier "Component Type Editor" disappears from the
   visible brand; the tree already makes the scope obvious.
4. **Brand mark becomes the offsite/Tholos SVG logo.** The raster
   `favicon.png` stays on disk for the browser tab favicon; the toolbar uses
   the new `tholos-logo.svg` at 22px inside a 28×28 white pill.
5. **Version info becomes hover-only.** `$MainAddress`, `$DBSyntax`,
   `$AppVersion`, `$devVersion` move into a Bootstrap 5 tooltip attached to a
   new info icon at the right end of the toolbar. Still inspectable, no
   longer always-visible chrome.
6. **Tree-pane geometry is no longer offset-based.** The hard-coded `top:
   100px` on `#component_tree_container` is removed; the container fills its
   pane via `height: 100%`. Resizing no longer requires keeping a magic
   number in sync with the legacy strip heights.
7. **Icon names migrate to FA 7 idiomatic forms.** `fa-refresh` →
   `fa-arrows-rotate`; `fa-search` → `fa-magnifying-glass`; `fa-plus-square` →
   `fa-square-plus`. Visual rendering is unchanged — the new names map to the
   same glyphs. Implementation step: verify the icons render correctly after
   the swap during manual browser test.
8. **Right-pane tab visual is unchanged.** The legacy blue-block
   `.nav-tabs .nav-link.active` style remains until sub-project 3 ports the
   underline-style `.pf-tabs` to both panes simultaneously.

## 7. Acceptance criteria

1. App loads. A 38px dark-gray (`#3a3f44`) toolbar spans the full window
   width across the top, above both panes. From left to right: Tholos SVG
   brand mark in a white pill + "Tholos Editor" text, search input-group,
   flex spacer, then New / Refresh / Info icon buttons.
2. Below the toolbar, the existing two-pane resizable layout
   (`#left_frame` + `#right_frame`) occupies the remaining viewport. The
   jQuery UI resize handle between panes still works.
3. Pressing Enter in the search input triggers
   `loadComponentTree(true,'#search_result_tree', …)` — same behavior as
   today. The "Search results" tab inside the left pane activates and
   renders results.
4. Clicking New still calls `createComponentType('','')`. Clicking Refresh
   still calls `loadComponentTree(true,'#component_tree','')`. The refresh
   icon retains `id="globalLoading"` so AJAX requests still toggle the spin
   animation as they do today.
5. Hovering the info icon shows a tooltip with three lines: address, DB
   syntax (bold), app version + dev version (bold).
6. The component tree fills its pane below the toolbar. The legacy `top:
   100px` magic offset is gone.
7. After the commit, no template, JS, PHP, or CSS file references
   `file_sprite.png`, `.folder`, `.file`, `#portalinfo_container`,
   `#options_container`, or `#x-portalinfo_container`. The PNG file is gone
   from `assets/images/`; the new `tholos-logo.svg` is present.
8. No JavaScript console errors on load, search, refresh, new-component, or
   tooltip hover.
9. Right-pane content (per-component-type tabs and inner tables) renders
   exactly as it does today — no visual diff. The legacy blue-block tab
   active style is intentionally preserved in this sub-project.

**Manual verification target:**
`https://tholos-editor-docker.offsite-solutions.com:10342/run.php` — the
auto-reloading dev portal. The local docker compose project lives at
`/Users/baxi/Work/_docker_images/tholos-editor` and bind-mounts this repo,
so edits here are live in the running container — no rebuild required.
Hard-refresh the URL and walk through the acceptance criteria above.

## 8. Decisions log

- **Toolbar full-width across both panes (revised).** First considered
  scoping the toolbar to the left pane to preserve the host application's
  embedding shape. Revised after reviewing the visual: the sibling builder
  app uses a full-width `.tb-navbar` and the host already accepts that
  geometry, so visual parity beats embedding-shape conservatism. The
  toolbar now lives in `main.template` above `#wrapper`, exactly matching
  builder's pattern.
- **CSS variables on `:root`, not inline hex.** Builder shipped inline hex
  because its redesign was a retrofit; editor is starting clean and benefits
  from one-place tuning. Token names also document intent
  (`--te-success-tint-hover` is self-explanatory; `rgba(136,189,33,0.15)` is
  not).
- **Full token set introduced upfront.** Sub-projects 2–4 reference tokens
  without having to extend the palette themselves. Cost is a slightly
  larger foundation commit; benefit is no token-creep across sub-projects.
- **Version info as tooltip, not visible chrome.** Diagnostic value
  preserved (still inspectable), no permanent vertical-space cost. Chosen
  during brainstorming over: keep visible (option C-alt), drop entirely
  (option A), move to a footer (option D).
- **Brand text shortened to "Tholos Editor".** "Component Type Editor" is
  redundant when the tree itself displays component types — the qualifier
  was informational filler that no longer earns its width.
- **Defer global `.nav-tabs` rule removal.** Removing it now would change
  the right pane visually in a sub-project that's meant to be left-pane
  only. The rule is harmless until sub-project 3 takes ownership of both
  panes' tab strips simultaneously.
- **Defer BS3 shim removal.** `.btn-default`, `.btn-xs`, `.pull-right`,
  `.well`, `.well-sm` are still consumed by templates that sub-projects 3
  and 4 will rewrite. Removing the shims now would break the entity-form
  templates in the right pane.
- **Rename three icon classes to FA 7 idiomatic forms.** The codebase
  recently moved to FA 7.1 (commit `a1776ef`); leaving aliases in place is
  carrying-on technical debt. We touch those three buttons in this
  sub-project anyway, so the rename is in scope.
- **Event-delegated Bootstrap tooltip init.** Future tooltips anywhere in
  the app (right-pane property cells, future help icons, etc.) work
  automatically by carrying `data-bs-toggle="tooltip"`. No per-element
  wiring or AJAX-load re-init needed.
- **SVG brand mark instead of raster favicon.** The Tholos logo exists as
  an SVG in the builder repo (`offsite_logo_only_v3.svg`) and renders
  crisply at any size. Copied into editor assets as `tholos-logo.svg`. The
  raster `favicon.png` is unchanged on disk (still used for the browser tab
  favicon via `main.template`'s `<link rel="shortcut icon">`).
