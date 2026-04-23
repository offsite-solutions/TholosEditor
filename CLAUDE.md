# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Tholos Editor is a PHP Composer library (`offsite-solutions/tholos-editor`) that provides a web-based UI for editing component type definitions stored in a database. It is not a standalone application — it is consumed by host applications that provide database connectivity and the Eisodos framework runtime.

## Dependencies

- **PHP >= 8.4**
- **Eisodos** (`offsite-solutions/eisodos`) — the core framework providing singletons, parameter handling, template engine, rendering, logging, and database abstraction
- **Eisodos SQLParser** (`offsite-solutions/eisodos-sqlparser`) — SQL template parsing
- Frontend assets are managed via `bower-asset/*` packages through Composer (Bootstrap 5, jQuery, jQuery UI, Select2, jsTree, Toastr, Bootbox)

## Build Commands

```bash
# Production dependency update
composer update --no-interaction --prefer-dist --no-ansi --optimize-autoloader

# Development dependency update (uses local symlinked Eisodos repos)
COMPOSER=composer.dev.json composer update --no-interaction --prefer-dist --no-dev --no-ansi --optimize-autoloader
```

There are no test suites, linters, or build steps in this repository.

## Architecture

### PHP Classes (PSR-0 autoloaded under `src/TholosEditor/`)

- **`TholosEditor`** — Bootstrap singleton. Entry point that initializes `TholosEditorApplication`.
- **`TholosEditorApplication`** — The main application class (~1400 lines). Contains all business logic:
  - `run()` connects to the definition database and routes requests via a simple `action` parameter that maps to private methods (e.g., `action=getLeftFrame` calls `$this->getLeftFrame()`).
  - CRUD operations for component types, properties, methods, and events via database stored procedures.
  - Dual database support: Oracle (`oci8`) and PostgreSQL (`pgsql`), with DB object names mapped in the `$DB_Objects` array and resolved via `getDBObject()`.
  - The `definition_schema` is configurable via the `TholosEditor.DefinitionSchema` Eisodos parameter.
- **`TholosEditorCallback`** — Template helper functions (`_eq`, `_neq`, `_case`, `_trim`, etc.) used in Eisodos templates for conditional rendering.

### Frontend Assets (`assets/`)

- `templates/tholoseditor/` — Eisodos template files (`.template`) for the left frame (component tree), right frame (detail forms), and SQL queries. Templates use Eisodos parameter syntax (`[param]`) and callback functions.
- `js/TholosEditor.js` — Main client-side JavaScript managing the two-pane UI (jsTree on the left, detail/form panels on the right) via AJAX calls.
- `js/dynamic-tabs.js` — Bootstrap 5 dynamic tab management library.
- `js/fontawesome/` — FontAwesome 7.1 icon bundles.
- `css/TholosEditor.css` — Application styles.

### Development Setup

`composer.dev.json` symlinks to local sibling directories for Eisodos development:
- `../../_eisodos/Base` — Eisodos core
- `../../_eisodos/Connectors/Oracle` — Oracle connector
- `../../_eisodos/Connectors/PDOPgSQL` — PostgreSQL connector
- `../../_eisodos/SQLParser` — SQL parser

### Key Patterns

- All responses from action methods are JSON (`{success, html, errormsg}`), rendered via `Eisodos::$render->finish()` followed by `exit`.
- Parameters flow through `Eisodos::$parameterHandler` — both request params and internal state.
- HTML output is built by the Eisodos template engine, not by PHP string concatenation.
- The `safeHTML()` method escapes content for safe embedding in templates (handles `[`, `]`, `$`, `^` which are Eisodos template special characters).