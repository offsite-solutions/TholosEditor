# UI Redesign — Foundation, Palette Tokens, Left-Pane Toolbar

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

Editor's surface is narrower than builder's — no login screen, no top app-wide
navbar, no wizards, no Build/Compile/Docs/Routes actions. The closest analog
to builder's `.tb-navbar` sub-project on this app is **the left-pane header
strip**, which this spec collapses into a single compact toolbar and uses as
the vehicle for introducing palette tokens and dead-code cleanup that the
remaining three sub-projects will rely on.

## 2. Scope

**In scope**

- Introduce a `:root` palette token block covering every color token the four
  sub-projects will reference (not just the toolbar's own colors).
- Replace `#portalinfo_container` + `#options_container` with a single
  `<nav class="te-toolbar">` 38px strip at the top of `#left_frame .content`.
- Restructure `#left_frame .content` as a flex column so the tree fills the
  remaining height — removes the hard-coded `top: 100px` offset.
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

The toolbar lives **inside the left pane**, not full-width across the app, so
that the host application embedding this editor sees no change in overall
geometry. `main.template` is untouched.

```
main.template (unchanged)
└── #wrapper
    └── #container
        ├── #left_frame.resizable
        │   └── .content                 ← display: flex; flex-direction: column;
        │       ├── <nav class="te-toolbar">   ← NEW: 38px dark-gray strip
        │       └── #component_tree_container  ← flex: 1; overflow: auto;
        │           └── #components / tabs / jstree (unchanged)
        └── #right_frame.resizable (unchanged)
```

The change to `#component_tree_container` matters: the current CSS uses
`position: absolute; top: 100px; height: calc(100% - 100px);` to clear the
fixed-height legacy strips. After this spec, the container is a flex child of
`.content` and fills whatever space the toolbar leaves. There is no longer a
hard-coded offset that has to match the strip height.

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
| Brand mark | favicon at 18px, inside a 24×24 white rounded pill (border-radius 4px) |
| Brand text | 13px, weight 600, color `#fff`, margin-left `.4rem` |
| Search wrapper | `flex: 0 1 240px` (shrinks on narrow panes) |
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
      <img src="$TholosEditorAssetsDir/images/favicon.png" alt="">
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

### 4.3 Left pane geometry

```css
#left_frame .content {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
  padding: 0;
}

#component_tree_container {
  flex: 1 1 auto;
  min-height: 0;
  width: 100%;
  padding: 0;
  background-color: var(--te-surface);
  overflow: auto;
  /* removed: position: absolute; top: 100px; height: calc(100% - 100px); */
}
```

## 5. Files changed

| File | Change |
|---|---|
| `assets/templates/tholoseditor/leftframe.main.template` | Replace lines 1–20 (the `#portalinfo_container` and `#options_container` blocks) with the single `<nav class="te-toolbar">…</nav>` block from §4.2. Keep `#component_tree_container` and everything below it intact. |
| `assets/css/TholosEditor.css` | (a) Prepend `:root { … }` palette tokens (§3.2). (b) Add `.te-toolbar`, `.te-brand`, `.te-brand-mark`, `.te-brand-text`, `.te-search`, `.te-toolbar-btn`, `.te-toolbar-btn:hover`, `.te-toolbar-btn:focus-visible`, `.te-toolbar-spacer`, `.te-info-tip` rule blocks. (c) Rewrite `#left_frame .content` to flex column (§4.3). (d) Rewrite `#component_tree_container` — remove `position: absolute; top: 100px; height: calc(100% - 100px)`, use `flex: 1; min-height: 0; overflow: auto;`. (e) Delete `#component_tree .folder`, `#component_tree .file`, `#search_result_tree .folder`, `#search_result_tree .file` rules (4 blocks). (f) Delete the commented-out `.nav-tabs > li > a` block. (g) Delete `#x-portalinfo_container`, `#portalinfo_container .logo`, `#portalinfo_container .portal-info`, `#options_container` rules. |
| `assets/js/TholosEditor.js` | Add one initializer near the bottom of the file's startup section: `new bootstrap.Tooltip(document.body, { selector: '[data-bs-toggle="tooltip"]' });` |
| `assets/images/file_sprite.png` | Delete. Unreferenced after the CSS cleanup. |

No changes to `assets/templates/tholoseditor/main.template`, any `rightframe.*`
template, `src/TholosEditor/*`, or `vendor/`.

## 6. Behavior changes

1. **Two-strip header collapses into one 38px toolbar.** Net vertical real
   estate gain in the left pane ≈ 60px (old strips ~98px combined → new
   toolbar 38px).
2. **Brand text shortens.** `Tholos :: Component Type Editor` →
   `Tholos Editor`. The qualifier "Component Type Editor" disappears from the
   visible brand; the tree already makes the scope obvious.
3. **Version info becomes hover-only.** `$MainAddress`, `$DBSyntax`,
   `$AppVersion`, `$devVersion` move into a Bootstrap 5 tooltip attached to a
   new info icon at the right end of the toolbar. Still inspectable, no
   longer always-visible chrome.
4. **Tree-pane geometry is no longer offset-based.** The hard-coded `top:
   100px` offset on `#component_tree_container` is replaced by flex layout.
   Resizing the splitter and toolbar-height tweaks no longer require keeping
   two numbers in sync.
5. **Icon names migrate to FA 7 idiomatic forms.** `fa-refresh` →
   `fa-arrows-rotate`; `fa-search` → `fa-magnifying-glass`; `fa-plus-square` →
   `fa-square-plus`. Visual rendering is unchanged — the new names map to the
   same glyphs. Implementation step: verify the icons render correctly after
   the swap during manual browser test.
6. **Right-pane tab visual is unchanged.** The legacy blue-block
   `.nav-tabs .nav-link.active` style remains until sub-project 3 ports the
   underline-style `.pf-tabs` to both panes simultaneously.

## 7. Acceptance criteria

1. App loads. Left pane shows a 38px dark-gray (`#3a3f44`) toolbar with brand
   mark + "Tholos Editor" text on the left, search input-group, a flex
   spacer, then New / Refresh / Info icon buttons on the right.
2. Pressing Enter in the search input triggers
   `loadComponentTree(true,'#search_result_tree', …)` — same behavior as
   today. The "Search results" tab below activates and renders results.
3. Clicking New still calls `createComponentType('','')`. Clicking Refresh
   still calls `loadComponentTree(true,'#component_tree','')`. The refresh
   icon retains `id="globalLoading"` so AJAX requests still toggle the spin
   animation as they do today.
4. Hovering the info icon shows a tooltip with three lines: address, DB
   syntax (bold), app version + dev version (bold).
5. The component tree (`#component_tree_container`) fills the rest of the
   left pane below the toolbar. Resizing the splitter horizontally does not
   distort the layout. No vertical scrollbar appears on the left pane chrome
   itself — only on the tree container when content overflows.
6. After the commit, no template, JS, PHP, or CSS file references
   `file_sprite.png`, `.folder`, `.file`, `#portalinfo_container`,
   `#options_container`, or `#x-portalinfo_container`. The PNG file is gone
   from `assets/images/`.
7. No JavaScript console errors on load, search, refresh, new-component, or
   tooltip hover.
8. Right-pane content (per-component-type tabs and inner tables) renders
   exactly as it does today — no visual diff. The legacy blue-block tab
   active style is intentionally preserved in this sub-project.

## 8. Decisions log

- **Toolbar inside the left pane, not full-width.** The editor is consumed
  by a host application; keeping the toolbar inside the left pane preserves
  the embedding shape (`main.template` and `#wrapper` geometry untouched).
  The builder's full-width `.tb-navbar` would have required changing the
  shell, which is a different conversation with the host owners.
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
