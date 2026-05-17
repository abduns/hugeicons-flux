# hugeicons-flux

Use any Hugeicons icon as a native <flux:icon.hugeicons.*> component in Flux UI — the free Stroke Rounded set bundled, or generate all 9 Pro styles from your own Hugeicons licence.

[![Tests](https://github.com/abduns/hugeicons-flux/actions/workflows/tests.yml/badge.svg)](https://github.com/abduns/hugeicons-flux/actions)
[![Version](https://img.shields.io/packagist/v/abduns/hugeicons-flux.svg)](https://packagist.org/packages/abduns/hugeicons-flux)
[![Downloads](https://img.shields.io/packagist/dt/abduns/hugeicons-flux.svg)](https://packagist.org/packages/abduns/hugeicons-flux)
[![License](https://img.shields.io/packagist/l/abduns/hugeicons-flux.svg)](LICENSE.md)

---

## Features

- Modern PHP support
- Lightweight and fast
- Integrates Hugeicons seamlessly with Flux UI
- Bundled with 5,100+ free Stroke Rounded icons
- Automatically generate Pro styles from your own license
- Claude Code skill included for on-demand icon workflow

---

## Installation

```bash
composer require abduns/hugeicons-flux
```

---

## Quick Start

```php
// Use it directly in your Blade views
```

Example output:

```blade
<flux:icon.hugeicons.home-01 class="size-8" />
```

---

## Why This Package?

- Missing modern PHP features
- Poor developer experience
- Too framework-coupled

This package focuses on simplicity, interoperability, and modern developer ergonomics to integrate Hugeicons into Laravel Flux UI effortlessly.

---

## Usage

### Basic Usage

Every icon is `flux:icon.hugeicons.{name}`, where `{name}` is the kebab-case
Hugeicons name (`Home01Icon` → `home-01`, `AiSearch02Icon` → `ai-search-02`).

```blade
{{-- Default --}}
<flux:icon.hugeicons.home-01 />

{{-- Sizing & colour — like any Flux icon --}}
<flux:icon.hugeicons.home-01 class="size-10 text-blue-500" />

{{-- As an `icon` prop on other Flux components --}}
<flux:button icon="hugeicons.calendar-03" />
<flux:navlist.item icon="hugeicons.dashboard-square-01">Dashboard</flux:navlist.item>
```

### Advanced Usage

The `variant` prop accepts the real Hugeicons style names, **plus** Flux's own
`outline` / `solid` / `mini` / `micro` as aliases:

```blade
<flux:icon.hugeicons.search-01 variant="solid-rounded" class="size-8 text-amber-500" />
```

| `variant`                                                          | Resolves to                  |
| ------------------------------------------------------------------ | ----------------------------- |
| `outline` (default), `mini`, `micro`, `stroke`, `stroke-rounded`   | `stroke-rounded`              |
| `solid`, `solid-rounded`                                           | `solid-rounded`               |
| `bulk-rounded`, `duotone-rounded`, `twotone-rounded`, `*-sharp`, …  | the matching Hugeicons style  |

If an icon has not been built with a requested style (e.g. you only have the
free set), the `variant` gracefully falls back to `stroke-rounded`.

### Configuration

For Pro icons, configure npm registry and generate icons:

```ini
@hugeicons-pro:registry=https://npm.hugeicons.com/
//npm.hugeicons.com/:_authToken=${HUGEICONS_PRO_LICENSE_KEY}
```

```bash
export HUGEICONS_PRO_LICENSE_KEY="your-hugeicons-token"

npm install --save-optional \
  @hugeicons-pro/core-stroke-rounded \
  @hugeicons-pro/core-solid-rounded

php artisan hugeicons:build
```

---

## Standards / Specifications

References:

- https://hugeicons.com

---

## Supported Features

| Feature | Support |
|---|---|
| Free Stroke Rounded Set | ✅ |
| Pro Styles Generator | ✅ |
| Claude Code Skill | ✅ |

---

## Compatibility

| Platform | Supported |
|---|---|
| PHP 8.2+ | ✅ |
| Laravel 11.0+ | ✅ |
| Flux UI 2 | ✅ |

---

## Design Goals

- Developer experience first
- Predictable APIs
- Minimal dependencies
- Strong typing
- Extensibility
- Interoperability

---

## Architecture

- Blade anonymous-component integration
- Zero footprint for unused Pro icons
- Deferred service provider registration for overriding

---

## Performance

| Operation | Time |
|---|---|
| Render component | < 1ms |

---

## Testing

```bash
composer test
```

---

## Roadmap

- [ ] Add more style fallbacks
- [ ] Livewire auto-completion integration

---

## Contributing

Contributions, issues, and discussions are welcome.

---

## Security

If you discover security issues, please report them responsibly.

---

## License

MIT
