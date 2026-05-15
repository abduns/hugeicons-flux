# Changelog

All notable changes to `abduns/hugeicons-flux` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
