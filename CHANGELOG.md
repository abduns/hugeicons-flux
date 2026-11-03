# Changelog

All notable changes to `abduns/hugeicons-flux` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-11-03

### Added

- Bundled free set updated to `@hugeicons/core-free-icons` **4.4.0**: 979 new
  icons (6,100 total) and 865 redrawn upstream. No icons were removed or renamed.
- `duotone-standard` Pro style (`@hugeicons-pro/core-duotone-standard`), for all
  10 Hugeicons Pro styles.
- Any `@hugeicons-pro/core-*` style the package does not know yet is selectable
  by its own name through the `variant` prop.
- `hugeicons:build` accepts the JS export name as well as kebab-case
  (`Home01Icon` → `home-01`).
- `composer icons:bundle` and a dev-only `package.json` that pins the bundled
  free-icons version.
- `.gitattributes` so the Composer dist archive no longer ships tests, CI and
  tooling config.

### Fixed

- Generated Pro icons set `fill="currentColor"` on the root `<svg>` for every
  non-stroke style, which flooded stroke-only layers of bulk, duotone and
  twotone icons. The root `<svg>` now uses `fill="none"` like the official
  Hugeicons renderers, and semi-transparent secondary layers are drawn first.
  **Re-run `php artisan hugeicons:build --force` for icons you generated with 1.0.x.**
- An extractor failure in `hugeicons:build` now reports an error and exits
  non-zero instead of throwing an uncaught exception.

### Removed

- **Laravel 11 support.** Laravel 11 is end-of-life and every 11.x release is
  covered by the unpatched CVE-2026-48019, so Composer 2.9+ refuses to install
  it by default. Laravel 11 apps keep resolving to 1.0.x.

### Changed

- CI: Node 22, `actions/checkout@v5`, `actions/setup-node@v5`; dropped the
  no-op `npm install` step.
- README: accurate requirements (PHP 8.3+), all 10 Pro packages, a
  `hugeicons:build` reference, and removal of placeholder sections.

## [1.0.1] - 2026-05-15

### Fixed

- Corrected component prefix from `hugeicon` to `hugeicons` (`<flux:icon.hugeicons.*>`).

## [1.0.0] - 2026-05-15

### Added

- Initial release.
- `HugeiconsFluxServiceProvider` registers the bundled icons into Flux's `flux`
  component namespace under the `hugeicons` prefix.
- `hugeicons:build` Artisan command generates Flux icon components from the
  installed `@hugeicons/core-free-icons` and `@hugeicons-pro/core-*` packages.
- Bundled free **Stroke Rounded** icon set (5,100+ icons).
- Hybrid `variant` prop: real Hugeicons style names plus Flux's
  `outline` / `solid` / `mini` / `micro` aliases, with graceful fallback.
- Claude Code skill (`.claude/skills/hugeicons-flux`) for the lean, on-demand
  icon workflow.
