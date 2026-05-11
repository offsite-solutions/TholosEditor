# Right-Pane Read-Only Content Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrate the right-pane tab strip to `.pf-tabs`, wrap each of the six right-pane sections in a `.pf-card` (with the "Add new X" picker in the card header), restyle the five tables with `.pf-table`, and modernize row buttons under `.pf-row-actions`.

**Architecture:** Four ordered commits. (1) Tab strip + global nav-tabs cleanup — single class swap, deletes the legacy blue-block override. (2) New CSS rule blocks introduced — `.pf-card`, `.pf-table`, `.pf-row-actions`, `.pf-add-row`, plus re-scoping of existing `.prop_disabled`/`.prop_mandatory` — all inert until templates consume them. (3) Restructure `rightframe.componenttype.template` to wrap each section in a `.pf-card`. (4) Modernize the three `*.item.template` row templates with `.pf-row-actions` and FA 7 icon names.

**Tech Stack:** PHP/Eisodos templates with `<%FUNC%>` / `<%SQL%>` directives, vanilla Bootstrap 5.3.8, FontAwesome 7.1, jQuery 3.x.

**No automated tests** — verify each task at `https://tholos-editor-docker.offsite-solutions.com:10342/run.php`. The local docker compose at `/Users/baxi/Work/_docker_images/tholos-editor` bind-mounts this repo so edits land live.

**Spec:** [`docs/superpowers/specs/2026-05-11-right-pane-content-design.md`](../specs/2026-05-11-right-pane-content-design.md)
**Mockup:** [`docs/superpowers/mockups/2026-05-11-right-pane-content.html`](../mockups/2026-05-11-right-pane-content.html)
**Branch:** `feature/ui-redesign-rightpane` (off `feature/ui-redesign-treeview`).

---

## File Structure

| File | Action | Responsibility |
|---|---|---|
| `assets/css/TholosEditor.css` | Modify | Delete legacy `.nav-tabs` global overrides; add `.pf-card`, `.pf-table`, `.pf-row-actions`, `.pf-add-row` rule blocks; re-scope `.prop_disabled` / `.prop_mandatory` under `.pf-table`; tune `.componentData_container` background |
| `assets/templates/tholoseditor/rightframe.main.template` | Modify | Add `pf-tabs` class to `<ul id="editortabs">` |
| `assets/templates/tholoseditor/rightframe.componenttype.template` | Modify | Wrap each of six sections in `.pf-card`; swap `table-condensed`→`table-sm pf-table`; move `+ Add` picker into card-header; convert section titles to `.pf-card-title`; modernize add-button icons |
| `assets/templates/tholoseditor/rightframe.property.item.template` | Modify | Wrap action buttons in `.pf-row-actions`; rename `fa-trash` → `fa-trash-can` |
| `assets/templates/tholoseditor/rightframe.event.item.template` | Modify | Same treatment for Remove button |
| `assets/templates/tholoseditor/rightframe.method.item.template` | Modify | Same treatment for Remove button |

Out-of-scope (not touched in this plan): any `rightframe.form.*.template`, any `rightframe.*.ancestor.template`, `dynamic-tabs.js`, `TholosEditor.js`, `main.template`, `leftframe.main.template`, `src/TholosEditor/*`.

---

## Task 1: Migrate right-pane tab strip to `.pf-tabs`

**Files:**
- Modify: `assets/templates/tholoseditor/rightframe.main.template:2`
- Modify: `assets/css/TholosEditor.css` (delete two legacy nav-tabs rules)

Single visible change: the right-pane tab strip switches from blue-block active style to offsite-blue underline. Both changes must land in the same commit — deleting the global before adding `pf-tabs` would leave the right pane unstyled momentarily.

- [ ] **Step 1: Add `pf-tabs` to the right-pane `<ul>`**

In `assets/templates/tholoseditor/rightframe.main.template`, line 2 currently reads:

```html
  <ul id="editortabs" class="nav nav-tabs" data-tabs="tabs">
```

Change to:

```html
  <ul id="editortabs" class="nav nav-tabs pf-tabs" data-tabs="tabs">
```

- [ ] **Step 2: Delete the legacy `.nav-tabs` globals from CSS**

In `assets/css/TholosEditor.css`, locate the existing rules (around lines 122–127 after sub-project 1's edits):

```css
.nav-tabs .nav-link {
  color: #337ab7;
}

.nav-tabs .nav-link.active {
  color: #fff;
  background-color: #8cb0cb;
}
```

Delete both rule blocks entirely.

- [ ] **Step 3: Verify no other consumer references the legacy globals**

Run:

```bash
grep -rn 'nav-tabs .nav-link' \
  --include='*.css' --include='*.template' --include='*.js' --include='*.php' \
  assets src 2>/dev/null
```

Expected: zero matches. (The `.pf-tabs .nav-link` rules are different — they have the `.pf-tabs` ancestor.)

- [ ] **Step 4: Manual verification**

Hard-refresh the dev portal. Open at least one component type so the right-pane tab strip is visible.

Expected:
1. The right-pane tab strip (per-component-type tabs) renders with the underline-active style — offsite-blue underline + bold offsite-blue text — identical to the left-pane "Component Types / Search results" strip.
2. The chunky `#8cb0cb` blue-block active background is gone everywhere.
3. Clicking a tab still switches tabs (handled by `dynamic-tabs.js`, untouched).
4. No console errors.

- [ ] **Step 5: Commit**

```bash
git add assets/templates/tholoseditor/rightframe.main.template assets/css/TholosEditor.css
git commit -m "$(cat <<'EOF'
Right-pane tab strip → .pf-tabs; delete legacy globals

Adds the pf-tabs class to <ul id="editortabs"> and deletes the legacy
.nav-tabs .nav-link / .nav-link.active global rules. Both nav-tabs
strips in the app now use the underline-style .pf-tabs — the blue-block
active style sub-project 1 deferred is finally gone.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 2: Add new CSS rule blocks

**Files:**
- Modify: `assets/css/TholosEditor.css`

Inert change — defines `.pf-card`, `.pf-table`, `.pf-row-actions`, `.pf-add-row` plus re-scopes `.prop_mandatory` / `.prop_disabled`. Nothing consumes the new rules until Task 3, so the app must render identically afterwards.

- [ ] **Step 1: Find the insertion point**

In `assets/css/TholosEditor.css`, locate the existing `/* === .pf-tabs ... */` block (added in sub-project 1, currently around line 350). Insert the new blocks **after** the `.pf-tabs .nav-link.active:hover { … }` rule (the last `.pf-tabs` rule) and **before** the `/* Selected and hovered tree rows tinted with the offsite brand green */` comment.

- [ ] **Step 2: Insert `.pf-card` block**

Paste this immediately after the last `.pf-tabs` rule:

```css
/* =========================================================
   .pf-card — section wrapper for prop-editor surfaces.
   Ported from builder's .wiz-card pattern. Header carries
   the section title + icon (offsite-blue) plus a compact
   "+ Add new X" picker on the right; body holds the table.
   ========================================================= */
.pf-card {
  background: var(--te-surface);
  border: 1px solid var(--te-border);
  border-radius: .35rem;
  margin-bottom: 10px;
  overflow: hidden;
}
.pf-card .card-header {
  background-color: var(--te-pane-bg);
  border-bottom: 1px solid var(--te-border);
  padding: .35rem .55rem;
  display: flex;
  align-items: center;
  gap: .5rem;
}
.pf-card-title {
  font-size: 12px;
  font-weight: 600;
  color: var(--te-primary);
  margin: 0;
  display: flex;
  align-items: center;
  gap: .4rem;
  flex: 0 0 auto;
}
.pf-card-title .fa-regular,
.pf-card-title .fa-solid,
.pf-card-title .svg-inline--fa {
  color: var(--te-primary);
  font-size: 12px;
}
.pf-card .card-body {
  padding: 0;
}
.pf-card .card-body > .pf-table thead th:first-child { padding-left: .55rem; }
.pf-card .card-body > .pf-table tbody tr:last-child td { border-bottom: 0; }

```

- [ ] **Step 3: Insert `.pf-table` block**

Append after the `.pf-card` block:

```css
/* =========================================================
   .pf-table — read-only data table for Properties /
   Events / Methods. Light-gray header, alternating row
   stripes, offsite-green hover/select.
   ========================================================= */
table.pf-table {
  font-size: 12px;
  margin: 0;
  table-layout: fixed;
  border-collapse: collapse;
  width: 100%;
}
table.pf-table thead th {
  background: #e9ecef;
  color: #495057;
  font-weight: 600;
  border-bottom: 1px solid var(--te-border);
  padding: .35rem .55rem;
}
table.pf-table tbody td {
  padding: .25rem .55rem;
  vertical-align: top;
  border-bottom: 1px solid var(--te-border-soft);
  overflow: hidden;
  word-break: break-word;
}
table.pf-table tbody tr:nth-child(odd)  td { background: var(--te-surface); }
table.pf-table tbody tr:nth-child(even) td { background: var(--te-pane-bg); }
table.pf-table tbody tr:hover td          { background: var(--te-success-tint-hover); }
table.pf-table tbody tr.is-selected td    {
  background: var(--te-success-tint-select);
  box-shadow: inset 2px 0 0 var(--te-success);
}
table.pf-table tbody tr.prop_mandatory td { font-weight: 600; }
table.pf-table tbody tr.prop_disabled td  { color: var(--te-text-dim); }
table.pf-table tbody tr.prop_disabled a   { color: var(--te-text-dim); }

/* Property toggle column (first column of Properties table). */
table.pf-table .prop_toggles { white-space: nowrap; }
table.pf-table .prop_toggles a {
  color: var(--te-text);
  padding: 0 2px;
  font-size: 12px;
  text-decoration: none;
}
table.pf-table .prop_toggles a:hover { color: var(--te-primary); }

```

- [ ] **Step 4: Insert `.pf-row-actions` block**

Append:

```css
/* =========================================================
   .pf-row-actions — grouped icon buttons on the right of a
   table row. Compact, neutral border, hover lighten.
   ========================================================= */
.pf-row-actions {
  float: right;
  display: inline-flex;
  gap: .15rem;
  margin-left: .35rem;
}
.pf-row-actions .btn {
  --bs-btn-padding-x: .4rem;
  --bs-btn-padding-y: .1rem;
  --bs-btn-font-size: 11px;
  --bs-btn-line-height: 1;
  color: var(--te-text-muted);
  background: var(--te-surface);
  border: 1px solid var(--te-border);
}
.pf-row-actions .btn:hover {
  background: #e9ecef;
  border-color: #ced4da;
  color: var(--te-text);
}

```

- [ ] **Step 5: Insert `.pf-add-row` block**

Append:

```css
/* =========================================================
   .pf-add-row — "Add new X" picker that lives in each
   .pf-card's header on the right side.
   ========================================================= */
.pf-add-row {
  display: flex;
  gap: .25rem;
  align-items: center;
  max-width: 200px;
  margin-left: auto;
}
.pf-add-row .form-select {
  font-size: 11px;
  height: 24px;
  min-height: 0;
  padding: .1rem 1.5rem .1rem .4rem;
  border-color: var(--te-border);
  flex: 1 1 auto;
  min-width: 0;
}
.pf-add-row .btn {
  --bs-btn-padding-x: .35rem;
  --bs-btn-padding-y: .05rem;
  --bs-btn-font-size: 11px;
  --bs-btn-line-height: 1;
  height: 24px;
  color: var(--te-text-muted);
  background: var(--te-surface);
  border: 1px solid var(--te-border);
  flex: 0 0 auto;
}
.pf-add-row .btn:hover {
  background: var(--te-border-soft);
  color: var(--te-primary);
}

```

- [ ] **Step 6: Delete the legacy `.prop_disabled` / `.prop_mandatory` rules**

The current `TholosEditor.css` (after sub-project 1) has rules like:

```css
tr.prop_disabled td {
  background-color: #888;
  color: #ccc;
}

a.propdef { … }

tr.prop_disabled a.propdef {
  color: #ccc;
}

td.prop_mandatory {
  font-weight: bold;
}
```

The `tr.prop_disabled td` rule fights the new `.pf-table` row stripes. The new `.pf-table tr.prop_disabled td` rule in Step 3 supersedes it.

Delete these four rule blocks entirely:
- `tr.prop_disabled td { … }` (the `background-color: #888; color: #ccc` block)
- `a.propdef { … }` (the `color: #000; font-weight: normal` block)
- `tr.prop_disabled a.propdef { color: #ccc; }`
- `td.prop_mandatory { font-weight: bold; }`

Use `grep -n 'prop_disabled\|prop_mandatory\|propdef' assets/css/TholosEditor.css` to find their current line numbers before editing.

- [ ] **Step 7: Update `.componentData_container` background**

Find the existing `.componentData_container` rule (around line 178 after sub-project 1):

```css
.componentData_container {
  padding: 10px;
  height: 100%;
  width: 100%;
}

.componentData_container > div {
  border-right: 1px solid #eee;
  height: 100%;
}
```

Change to:

```css
.componentData_container {
  padding: 10px;
  height: 100%;
  width: 100%;
  background: var(--te-pane-bg);
}

.componentData_container > [class^="col-"] {
  padding: 0 6px;
}
```

Note: the original `.componentData_container > div` rule applied `border-right` to every direct child div (which over-fired on nested rows). The new `> [class^="col-"]` selector targets the four column wrappers only and drops the border-right because cards now provide their own visual separation.

Also delete the `p.title` rule block (it's superseded by `.pf-card-title`):

```css
p.title {
  font-weight: bold;
  background-color: #bbb;
  color: #fff;
  padding: 3px;
  border-radius: 3px;
}
```

- [ ] **Step 8: Manual verification**

Hard-refresh the dev portal.

Expected:
- The app renders identically to the post-Task-1 state. No `.pf-card`, `.pf-table`, `.pf-row-actions`, `.pf-add-row` markup exists yet, so the new rules apply to nothing.
- The Properties / PHP events / Database events / GUI events / Methods / Editor section titles in the right pane currently rendered by `<p class="title">` look **unstyled** now (just plain bold text on white background) since we deleted the `p.title` rule. This is intentional — Task 3 replaces them with `.pf-card-title` headings inside card headers.
- `.componentData_container` shows the new pane-bg color behind the columns; the previous per-div border-right may have disappeared from inner rows (also intentional — columns get their own card-based separation in Task 3).
- No CSS parse errors in DevTools → Console.

This task INTENTIONALLY leaves the right pane in a transient "ugly" state. The next task fixes it. Don't be alarmed.

- [ ] **Step 9: Commit**

```bash
git add assets/css/TholosEditor.css
git commit -m "$(cat <<'EOF'
Add .pf-card, .pf-table, .pf-row-actions, .pf-add-row CSS

Defines the rule blocks needed for the right-pane card layout. None
of these classes appear in markup yet — the templates consume them in
the next commit. Also re-scopes .prop_mandatory / .prop_disabled under
.pf-table so they compose with the new stripes, drops the legacy
.componentData_container > div border rule (cards replace that
visual separator), and removes the unused p.title rule.

The right pane looks transiently ugly between this commit and the
next — the legacy <p class="title"> renderings lose their grey-block
background. Restored when Task 3 lands.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 3: Restructure `rightframe.componenttype.template` into `.pf-card` sections

**Files:**
- Modify: `assets/templates/tholoseditor/rightframe.componenttype.template` (whole-file restructure)

The current template wraps each section as:

```
<div class="col-sm-N" style="height: 100%; overflow: auto;">
  <div class="row">         <!-- picker row -->
    <div class="col-sm-10"><select id="newXID">…</select></div>
    <div class="col-sm-2"><button class="btn …">+</button></div>
  </div>
  <div class="row">         <!-- title + table row -->
    <div class="col-sm-12">
      <p class="title">Section</p>
      <table class="table table-condensed">…</table>
    </div>
  </div>
</div>
```

We rewrite each as:

```
<div class="col-sm-N">
  <div class="pf-card">
    <div class="card-header">
      <h5 class="pf-card-title"><i class="fa-regular fa-rectangle-list"></i>Section</h5>
      <div class="pf-add-row">
        <select id="newXID" class="form-select form-select-sm">…</select>
        <button class="btn …">…</button>
      </div>
    </div>
    <div class="card-body">
      <table class="table table-sm pf-table">…</table>
    </div>
  </div>
</div>
```

All `<%FUNC%>` / `<%SQL%>` blocks stay byte-for-byte where they are inside the new wrappers. Only the surrounding HTML changes. The five tables keep their `<thead>` / `<tbody>` markup, just gain the `pf-table` class and drop `table-condensed` for `table-sm`.

Since this template is 380+ lines and the change is mechanical, work column by column.

### Step 1: Replace the outer container row classes

- [ ] In `rightframe.componenttype.template` line 1, the file currently starts:

```html
<div class="row componentData_container">
  <div class="col-sm-4" style="height: 100%; overflow: auto;">
```

Change to:

```html
<div class="row g-0 componentData_container">
  <div class="col-sm-4">
```

(Drop the inline `style="height: 100%; overflow: auto;"` — `.componentData_container` handles it via CSS. Add Bootstrap 5's `g-0` to remove the inter-column gutter.)

### Step 2: Restructure the Properties column (the col-sm-4 outer)

- [ ] Replace the contents of the outer `<div class="col-sm-4">` (lines 2–98 of the original file) with this single block. The two inner `<%FUNC%>` blocks (the SQL select dropdown + the SQL row-rendering loop) are preserved verbatim — only the surrounding HTML changes:

```html
  <div class="col-sm-4">
    <div class="pf-card">
      <div class="card-header">
        <h5 class="pf-card-title"><i class="fa-regular fa-rectangle-list"></i>Properties</h5>
        <div class="pf-add-row">
          <select id="newPropertyID" class="form-select form-select-sm">
            <option value="">* New property…</option>
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_cases
param=DBSyntax
oci8>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/sqls.select;
SQL=
select p.id,
       p.name||' : '||
       case when p.type='COMPONENT' then ct.class_name else p.type end as text
  from def_properties p
       left outer join def_component_types ct on ct.id=p.component_type_id
 where p.id not in (select distinct id
                      from tholos.def_component_type_prop_v
                     where root_component_id=$p_component_type_id~='-1';)
       and p.enabled='Y'
 order by p.name
%SQL%>
<<
pgsql>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/sqls.select;
SQL=
select p.id,
       p.name||' : '||
       case when p.type='COMPONENT' then ct.class_name else p.type end as text
  from def.properties p
       left outer join def.component_types ct on ct.id=p.component_type_id
 where p.id not in (select distinct id
                      from def.component_type_prop_v
                     where root_component_id=$p_component_type_id~='-1';)
       and p.enabled='Y'
 order by p.name
%SQL%>
<<
%FUNC%>
          </select>
          <button class="btn" type="button"
                  onclick="if ($('#tab_$p_component_type_id #newPropertyID').val()=='') addNewProperty($p_component_type_id); else addProperty($p_component_type_id,$('#tab_$p_component_type_id #newPropertyID').val());"
                  title="Add new property">
            <i class="fa-regular fa-square-plus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <table class="table table-sm pf-table">
          <thead>
            <tr>
              <th style="width: 22%;">&nbsp;</th>
              <th style="width: 38%;">Name</th>
              <th style="width: 30%;">Type</th>
              <th style="width: 10%;">&nbsp;</th>
            </tr>
          </thead>
          <tbody>
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_cases
param=DBSyntax
oci8>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/rightframe.property.item;
SQL=
select id,name,type,type_desc,component_type_id,
       component_type_name,ancestor_class_name,ancestor_id,root_component_id,link_id,
       def_link_id, default_value, mandatory, disabled, runtime, nodata
  from def_component_type_prop_v ctp
 where ctp.root_component_id=$p_component_type_id~='-1';
 order by 2
%SQL%>
<<
pgsql>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/rightframe.property.item;
SQL=
select id,name,type,type_desc,component_type_id,
       component_type_name,ancestor_class_name,ancestor_id,root_component_id,link_id,
       def_link_id, default_value, mandatory, disabled, runtime, nodata
  from def.component_type_prop_v ctp
 where ctp.root_component_id=$p_component_type_id~='-1';
 order by 2
%SQL%>
<<
%FUNC%>
          </tbody>
        </table>
      </div>
    </div>
  </div>
```

Column widths in the `<thead>` are tightened (5/50/40/5 → 22/38/30/10) because the first column (toggle icons) needs slightly more width and the trailing actions column should accommodate the new grouped `.pf-row-actions` buttons.

### Step 3: Restructure the Events column (the col-sm-2 outer with three stacked sub-tables)

- [ ] Replace the outer `<div class="col-sm-2" style="height: 100%; overflow: auto;">` block that starts around line 99 in the original (everything from line 99 through line 274 — the closing `</div>` before the Methods column). The structure becomes one `<div class="col-sm-3">` wrapping three sequential `.pf-card` blocks. Widen this column from `col-sm-2` to `col-sm-3` to give the events more breathing room.

Replace the entire Events column with:

```html
  <div class="col-sm-3">
    <div class="pf-card">
      <div class="card-header">
        <h5 class="pf-card-title"><i class="fa-regular fa-code"></i>PHP events</h5>
        <div class="pf-add-row">
          <select id="newEventID" class="form-select form-select-sm">
            <option value="">* New event…</option>
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_cases
param=DBSyntax
oci8>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/sqls.select;
SQL=
select e.id,
       e.name||' : '||e.type as text
  from def_events e
 where e.id not in (select distinct id
                      from def_component_type_event_v
                     where root_component_id=$p_component_type_id)
       and e.enabled='Y'
 order by e.name
%SQL%>
<<
pgsql>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/sqls.select;
SQL=
select e.id,
       e.name||' : '||e.type as text
  from def.events e
 where e.id not in (select distinct id
                      from def.component_type_event_v
                     where root_component_id=$p_component_type_id)
       and e.enabled='Y'
 order by e.name
%SQL%>
<<
%FUNC%>
          </select>
          <button class="btn" type="button"
                  onclick="if ($('#tab_$p_component_type_id #newEventID').val()=='') addNewEvent($p_component_type_id); else addEvent($p_component_type_id,$('#tab_$p_component_type_id #newEventID').val());"
                  title="Add new event">
            <i class="fa-regular fa-square-plus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <table class="table table-sm pf-table">
          <thead><tr><th style="width: 78%;">Name</th><th style="width: 22%;">&nbsp;</th></tr></thead>
          <tbody>
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_cases
param=DBSyntax
oci8>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/rightframe.event.item;
SQL=
select id,name,type,ancestor_class_name,ancestor_id,root_component_id,link_id
  from def_component_type_event_v ctp
 where ctp.root_component_id=$p_component_type_id~='-1';
       and type='PHP'
 order by 2
%SQL%>
<<
pgsql>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/rightframe.event.item;
SQL=
select id,name,type,ancestor_class_name,ancestor_id,root_component_id,link_id
  from def.component_type_event_v ctp
 where ctp.root_component_id=$p_component_type_id~='-1';
       and type='PHP'
 order by 2
%SQL%>
<<
%FUNC%>
          </tbody>
        </table>
      </div>
    </div>

    <div class="pf-card">
      <div class="card-header">
        <h5 class="pf-card-title"><i class="fa-regular fa-database"></i>Database events</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm pf-table">
          <thead><tr><th style="width: 78%;">Name</th><th style="width: 22%;">&nbsp;</th></tr></thead>
          <tbody>
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_cases
param=DBSyntax
oci8>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/rightframe.event.item;
SQL=
select id,name,type,ancestor_class_name,ancestor_id,root_component_id,link_id
  from def_component_type_event_v ctp
 where ctp.root_component_id=$p_component_type_id~='-1';
       and type='DB'
 order by 2
%SQL%>
<<
pgsql>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/rightframe.event.item;
SQL=
select id,name,type,ancestor_class_name,ancestor_id,root_component_id,link_id
  from def.component_type_event_v ctp
 where ctp.root_component_id=$p_component_type_id~='-1';
       and type='DB'
 order by 2
%SQL%>
<<
%FUNC%>
          </tbody>
        </table>
      </div>
    </div>

    <div class="pf-card">
      <div class="card-header">
        <h5 class="pf-card-title"><i class="fa-regular fa-display"></i>GUI events</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm pf-table">
          <thead><tr><th style="width: 78%;">Name</th><th style="width: 22%;">&nbsp;</th></tr></thead>
          <tbody>
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_cases
param=DBSyntax
oci8>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/rightframe.event.item;
SQL=
select id,name,type,ancestor_class_name,ancestor_id,root_component_id,link_id
  from def_component_type_event_v ctp
 where ctp.root_component_id=$p_component_type_id~='-1';
       and type='GUI'
 order by 2
%SQL%>
<<
pgsql>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/rightframe.event.item;
SQL=
select id,name,type,ancestor_class_name,ancestor_id,root_component_id,link_id
  from def.component_type_event_v ctp
 where ctp.root_component_id=$p_component_type_id~='-1';
       and type='GUI'
 order by 2
%SQL%>
<<
%FUNC%>
          </tbody>
        </table>
      </div>
    </div>
  </div>
```

Note: only the **PHP events** card carries the `.pf-add-row` picker — the underlying `newEventID` select is the same input regardless of which event type the user picks (the SQL `where … type='PHP'/'DB'/'GUI'` only filters the *display*). Putting the picker in just one card avoids three duplicate `id="newEventID"` collisions in the DOM. Users who want to add a DB or GUI event use the same picker; the new event's type is determined by the `def_events.type` column of whichever option they select.

### Step 4: Restructure the Methods column

- [ ] Replace the existing `<div class="col-sm-2" style="height: 100%; overflow: auto;">` Methods block (lines 276–362 of the original) with:

```html
  <div class="col-sm-2">
    <div class="pf-card">
      <div class="card-header">
        <h5 class="pf-card-title"><i class="fa-regular fa-code-branch"></i>Methods</h5>
        <div class="pf-add-row">
          <select id="newMethodID" class="form-select form-select-sm">
            <option value="">* New method…</option>
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_cases
param=DBSyntax
oci8>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/sqls.select;
SQL=
select m.id,
       m.name as text
  from def_methods m
 where m.id not in (select distinct id
                      from def_component_type_method_v
                     where root_component_id=$p_component_type_id)
       and m.enabled='Y'
 order by m.name
%SQL%>
<<
pgsql>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/sqls.select;
SQL=
select m.id,
       m.name as text
  from def.methods m
 where m.id not in (select distinct id
                      from def.component_type_method_v
                     where root_component_id=$p_component_type_id)
       and m.enabled='Y'
 order by m.name
%SQL%>
<<
%FUNC%>
          </select>
          <button class="btn" type="button"
                  onclick="if ($('#tab_$p_component_type_id #newMethodID').val()=='') addNewMethod($p_component_type_id); else addMethod($p_component_type_id,$('#tab_$p_component_type_id #newMethodID').val());"
                  title="Add new method">
            <i class="fa-regular fa-square-plus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <table class="table table-sm pf-table">
          <thead><tr><th style="width: 75%;">Name</th><th style="width: 25%;">&nbsp;</th></tr></thead>
          <tbody>
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_cases
param=DBSyntax
oci8>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/rightframe.method.item;
SQL=
select id,name,ancestor_class_name,ancestor_id,root_component_id,link_id
  from def_component_type_method_v ctp
 where ctp.root_component_id=$p_component_type_id~='-1';
 order by 2
%SQL%>
<<
pgsql>>=
<%SQL%
DB=Tholos.DefinitionDBIndex;
ROW=tholoseditor/rightframe.method.item;
SQL=
select id,name,ancestor_class_name,ancestor_id,root_component_id,link_id
  from def.component_type_method_v ctp
 where ctp.root_component_id=$p_component_type_id~='-1';
 order by 2
%SQL%>
<<
%FUNC%>
          </tbody>
        </table>
      </div>
    </div>
  </div>
```

### Step 5: Restructure the Editor column

- [ ] Replace the existing `<div class="col-sm-4">` Editor block (lines 368–382 of the original) with:

```html
  <div class="col-sm-3">
    <div class="pf-card">
      <div class="card-header">
        <h5 class="pf-card-title"><i class="fa-regular fa-pen-to-square"></i>Editor</h5>
        <span class="pf-row-actions">
          <button class="btn" type="button"
                  onclick="openComponentType($p_component_type_id,'');" title="Refresh">
            <i class="fa-regular fa-arrows-rotate"></i>
          </button>
        </span>
      </div>
      <div class="card-body" style="padding: .65rem;">
        <div class="componentTypeEditor_container">
$templateabs_tholoseditor__rightframe_form_componenttype
        </div>
      </div>
    </div>
  </div>
```

The Refresh button uses `.pf-row-actions` for styling consistency with the row buttons elsewhere (instead of the raw `btn-default btn-sm pull-right` it had before). The Editor card-body keeps a non-zero padding because the inner entity form needs margin around its fields.

Column-width sum check: 4 (Properties) + 3 (Events) + 2 (Methods) + 3 (Editor) = 12 (full Bootstrap row width). The original was 4 + 2 + 2 + 4 = 12 also; we've shifted 1 column of width from Editor to Events.

### Step 6: Close the outer container

- [ ] The file should end with the closing `</div>` of the `<div class="row g-0 componentData_container">`. Verify with `tail -2 assets/templates/tholoseditor/rightframe.componenttype.template` after the edits — last line should be `</div>`.

### Step 7: Manual verification

- [ ] Hard-refresh the dev portal. Open a component type that has properties, events of all three types, and methods (`TGrid` or `TContainer` is a good choice).

Expected against the mockup at `docs/superpowers/mockups/2026-05-11-right-pane-content.html`:

1. Each section renders as a `.pf-card` — white body, 1px border, `#f8f9fa` header bar, offsite-blue title with leading icon.
2. The four columns appear in this order with these widths: Properties (33%), Events stacked 3-deep (25%), Methods (17%), Editor (25%).
3. Properties card-header shows "Properties" with `fa-rectangle-list` icon on the left and the `+ Add new property` picker on the right.
4. PHP events card-header shows "PHP events" with `fa-code` icon + the `+ Add new event` picker on the right.
5. Database events and GUI events cards have only the title (no `.pf-add-row` picker) — those picks happen via the PHP events card's picker, but the rows still render in the correct cards via SQL `type=` filter.
6. Methods card-header shows "Methods" with `fa-code-branch` icon and the `+ Add new method` picker.
7. Editor card-header shows "Editor" with `fa-pen-to-square` icon and the Refresh button on the right (no picker). The body contains the existing entity form — visually identical inside (sub-project 4 will restyle it).
8. Tables inside each card have alternating row stripes (`#fff` / `#f8f9fa`), `#e9ecef` header bar, hover tints offsite-green at 15%.
9. Choosing an option from a picker and clicking the `+` button still fires the existing JS handlers (`addProperty`, `addEvent`, `addMethod`, or their `addNewX` zero-value counterparts).
10. Existing row-action buttons (Remove, Default value, four property toggles) still appear with their old `btn btn-default btn-sm` styling — Task 4 modernizes them.
11. No JavaScript console errors.

### Step 8: Commit

- [ ] Run:

```bash
git add assets/templates/tholoseditor/rightframe.componenttype.template
git commit -m "$(cat <<'EOF'
Restructure componenttype template into .pf-card sections

Wraps each of the six sections (Properties, PHP events, Database
events, GUI events, Methods, Editor) in a Bootstrap card with a
header carrying the section title + icon (offsite-blue) and the
"Add new X" picker on the right. Card body holds the read-only
table flush against the edges.

Column widths rebalanced: 4/3/2/3 (was 4/2/2/4) — events column gains
1 col so the three stacked event cards have breathing room.

Tables switch table-condensed → table-sm and gain the pf-table
class. The existing <%FUNC%>/<%SQL%> blocks and ID-bearing inputs
(#newPropertyID, #newEventID, #newMethodID) are preserved verbatim —
all click handlers continue working.

Row-action buttons inside the rendered rows still wear their legacy
btn-default btn-sm styling — modernized in the next commit.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Task 4: Modernize row templates with `.pf-row-actions` + FA 7 icon names

**Files:**
- Modify: `assets/templates/tholoseditor/rightframe.property.item.template`
- Modify: `assets/templates/tholoseditor/rightframe.event.item.template`
- Modify: `assets/templates/tholoseditor/rightframe.method.item.template`

Single conceptual change across three small files (≤ 55 lines each). Combined in one commit because the visual diff requires all three.

### Step 1: Update `rightframe.property.item.template`

- [ ] Open the file (it has 55 lines after sub-project 1). The trailing actions cell currently looks like:

```html
  <td class="text-right" nowrap>
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_eqs
param=sqlancestor_id
value=
false=
true=<button class="btn btn-default btn-sm" onclick="removeProperty($p_component_type_id,$sqllink_id);" title="Remove"><i class="fa-regular fa-trash"></i></button>
%FUNC%><button class="btn btn-default btn-sm" onclick="defaultValueProperty($p_component_type_id,$sqlid,'$sqldefault_value');" title="Default value"><i class="fa-regular fa-at"></i></button>
  </td>
```

Replace **only the actions cell** with:

```html
  <td class="text-right" nowrap>
    <span class="pf-row-actions">
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_eqs
param=sqlancestor_id
value=
false=
true=<button class="btn" type="button" onclick="removeProperty($p_component_type_id,$sqllink_id);" title="Remove"><i class="fa-regular fa-trash-can"></i></button>
%FUNC%><button class="btn" type="button" onclick="defaultValueProperty($p_component_type_id,$sqlid,'$sqldefault_value');" title="Default value"><i class="fa-regular fa-at"></i></button>
    </span>
  </td>
```

Two changes: wrap both buttons in `<span class="pf-row-actions">…</span>`, drop `btn-default btn-sm` from each, and rename `fa-trash` → `fa-trash-can`. The `<%FUNC%>` directive's `true=` payload inside the `pf-row-actions` span stays as a single line — the Eisodos `<%FUNC%>` block doesn't tolerate the `true=` value spanning multiple lines.

### Step 2: Update `rightframe.event.item.template`

- [ ] Open the file (21 lines). Replace the trailing actions cell:

```html
  <td class="text-right" nowrap>
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_eqs
param=sqlancestor_id
value=
false=&nbsp;
true=<button class="btn btn-default btn-sm" onclick="removeEvent($p_component_type_id,$sqllink_id);" title="Remove"><i class="fa-regular fa-trash"></i></button>
%FUNC%>
  </td>
```

with:

```html
  <td class="text-right" nowrap>
    <span class="pf-row-actions">
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_eqs
param=sqlancestor_id
value=
false=&nbsp;
true=<button class="btn" type="button" onclick="removeEvent($p_component_type_id,$sqllink_id);" title="Remove"><i class="fa-regular fa-trash-can"></i></button>
%FUNC%>
    </span>
  </td>
```

### Step 3: Update `rightframe.method.item.template`

- [ ] Open the file (21 lines, identical shape to event.item). Replace the trailing actions cell with the same pattern — wrap in `.pf-row-actions`, drop `btn-default btn-sm`, rename `fa-trash` → `fa-trash-can`:

```html
  <td class="text-right" nowrap>
    <span class="pf-row-actions">
<%FUNC%
_function_name=TholosEditor\TholosEditorCallback::_eqs
param=sqlancestor_id
value=
false=&nbsp;
true=<button class="btn" type="button" onclick="removeMethod($p_component_type_id,$sqllink_id);" title="Remove"><i class="fa-regular fa-trash-can"></i></button>
%FUNC%>
    </span>
  </td>
```

### Step 4: Verify no legacy markers remain in touched files

- [ ] Run:

```bash
grep -n 'btn-default\|btn-sm\|fa-trash\b\|pull-right' \
  assets/templates/tholoseditor/rightframe.property.item.template \
  assets/templates/tholoseditor/rightframe.event.item.template \
  assets/templates/tholoseditor/rightframe.method.item.template
```

Expected: zero matches. (BS3 shim classes that may still appear in *other* files — like `rightframe.form.*.template` — are intentionally untouched in this sub-project.)

### Step 5: Manual verification

- [ ] Hard-refresh the dev portal. Open a component type with at least one property that has both an ancestor link (so the Remove button appears) and a `default_value` set.

Expected:
1. Property rows show the four toggle icons (disable/mandatory/runtime/no-hidden-data) in column 1 — unchanged. Name in column 2. Type in column 3.
2. The trailing actions cell renders Remove (trash-can) + Default-value (at) buttons grouped inside a `.pf-row-actions` span. Buttons are ~22px tall with neutral border, hover lightens to `#e9ecef`.
3. Event-row Remove button uses the trash-can icon. Method-row Remove button uses the trash-can icon.
4. Clicking Remove (any row type) calls the original handler (`removeProperty` / `removeEvent` / `removeMethod`).
5. Clicking Default-value still fires `defaultValueProperty`.
6. Inherited-only rows (those with `sqlancestor_id` empty) show no Remove button — only the Default-value button on properties, nothing on events/methods.
7. Mandatory rows render bold; disabled rows render in `var(--te-text-dim)` gray. Both interact correctly with the new alternating row stripes.
8. No JavaScript console errors.

### Step 6: Commit

- [ ] Run:

```bash
git add assets/templates/tholoseditor/rightframe.property.item.template assets/templates/tholoseditor/rightframe.event.item.template assets/templates/tholoseditor/rightframe.method.item.template
git commit -m "$(cat <<'EOF'
Row buttons → .pf-row-actions + FA 7 icon names

Wraps the action-button cells in property/event/method row templates
in <span class="pf-row-actions">. Drops the legacy btn-default btn-sm
classes from each button so they pick up the new compact-icon styling
from .pf-row-actions .btn. Renames fa-trash → fa-trash-can to match
the FA 7 idiomatic name (consistent with sub-project 1's renames).

BS3 shims (.btn-default, .btn-xs, .pull-right, .well, .well-sm) stay
in CSS — still consumed by rightframe.form.*.template (sub-project 4).

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Final Verification

After Task 4 commits, walk through every acceptance criterion in §7 of the spec end-to-end:

- [ ] Right-pane tab strip uses underline-style (matches left pane)
- [ ] Six `.pf-card` blocks: Properties, PHP events, Database events, GUI events, Methods, Editor — each with offsite-blue title + icon
- [ ] PHP events card carries the event picker in its header; DB/GUI event cards do not
- [ ] Properties / PHP events / Methods / Editor cards each have their add/refresh picker on the right of the header
- [ ] Tables: alternating stripes, `#e9ecef` header, hover offsite-green tint
- [ ] Mandatory rows bold; disabled rows dim
- [ ] Property-row toggle icons (column 1) still fire `disableProperty` / `mandatoryProperty` / `runtimeProperty` / `nodataProperty`
- [ ] Row-action buttons (Remove, Default-value) render in `.pf-row-actions` flex group with neutral border + hover lighten
- [ ] All click handlers fire their original JS functions
- [ ] No JavaScript console errors
- [ ] Editor card's inner form is visually unchanged
- [ ] Legacy globals `.nav-tabs .nav-link` and `.nav-tabs .nav-link.active` gone
- [ ] BS3 shims still in CSS (still needed by sub-project 4)

If all pass, sub-project 3 is complete. Sub-project 4 (entity forms) begins from this branch's HEAD.

---

## Self-review notes

- Spec deliverables (§2 in-scope) mapped to tasks:
  - Tab strip → `.pf-tabs` → Task 1
  - Delete legacy nav-tabs globals → Task 1
  - `.pf-card` CSS → Task 2; markup application → Task 3
  - `.pf-table` CSS → Task 2; markup application → Task 3
  - `.pf-row-actions` CSS → Task 2; markup application → Task 4
  - `.pf-add-row` CSS → Task 2; markup application → Task 3
  - `table-condensed` → `table-sm` → Task 3
  - Six section-title replacement → Task 3
  - Icon renames (`fa-trash` → `fa-trash-can`, `fa-plus-square` → `fa-square-plus`, `fa-refresh` → `fa-arrows-rotate`) → Task 3 (add buttons + Editor refresh button) + Task 4 (row Remove buttons)
  - Re-scope `.prop_mandatory` / `.prop_disabled` → Task 2 (CSS) + Task 3 (markup classes preserved on rows)
- No TBD / TODO / placeholder strings anywhere.
- Identifier consistency: `#newPropertyID`, `#newEventID`, `#newMethodID`, `addProperty`, `addNewProperty`, `addEvent`, `addNewEvent`, `addMethod`, `addNewMethod`, `removeProperty`, `removeEvent`, `removeMethod`, `defaultValueProperty`, `disableProperty`, `mandatoryProperty`, `runtimeProperty`, `nodataProperty`, `openComponentType` all preserved across tasks.
- CSS variable names match foundation tokens: `--te-primary`, `--te-success`, `--te-surface`, `--te-pane-bg`, `--te-border`, `--te-border-soft`, `--te-text`, `--te-text-muted`, `--te-text-dim`, `--te-success-tint-hover`, `--te-success-tint-select`.
- Class names match across plan and spec: `.pf-card`, `.pf-card-title`, `.pf-table`, `.pf-row-actions`, `.pf-add-row`, `.pf-tabs`.
- Column-width math sums to 12 (4 + 3 + 2 + 3) — verified.
