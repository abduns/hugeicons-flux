# Changelog

All notable changes to `abduns/hugeicons-flux` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Initial release.
- `HugeiconsFluxServiceProvider` registers the bundled icons into Flux's `flux`
  component namespace under the `hugeicon` prefix.
- `hugeicons:build` Artisan command generates Flux icon components from the
  installed `@hugeicons/core-free-icons` and `@hugeicons-pro/core-*` packages.
- Bundled free **Stroke Rounded** icon set.
- Hybrid `variant` prop: real Hugeicons style names plus Flux's
  `outline` / `solid` / `mini` / `micro` aliases, with graceful fallback.
