# hugeicons-flux

Use any Hugeicons icon as a native <flux:icon.hugeicons.*> component in Flux UI — the free Stroke Rounded set bundled, or generate all 9 Pro styles from your own Hugeicons licence.

[![Tests](https://github.com/abduns/hugeicons-flux/actions/workflows/ci.yml/badge.svg)](https://github.com/abduns/hugeicons-flux/actions)
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

### Configuration (Pro Icons)

To use Pro icon styles you need to:

1. Tell npm where to download Hugeicons Pro packages from (`.npmrc`)
2. Provide your licence key as an environment variable
3. Install the Pro packages and generate the icons

#### Step 1 — Create or edit `.npmrc`

`.npmrc` is a plain-text config file that npm reads before installing packages.
Create (or open) a file called **`.npmrc`** in the **root of your project** (same folder as `package.json`) and add these two lines:

```ini
@hugeicons-pro:registry=https://npm.hugeicons.com/
//npm.hugeicons.com/:_authToken=${HUGEICONS_PRO_LICENSE_KEY}
```

> **What this does**
> - Line 1 tells npm to fetch every `@hugeicons-pro/*` package from the Hugeicons registry instead of the public npm registry.
> - Line 2 authenticates you with your licence key. The `${HUGEICONS_PRO_LICENSE_KEY}` placeholder is replaced automatically by npm using the environment variable of the same name — **you never hard-code your key in this file**.

> **Tip — commit `.npmrc`, never your key**
> It is safe (and recommended) to commit `.npmrc` to version control because it contains no secrets — only the `${…}` placeholder. Your actual key lives only in the environment.

---

#### Step 2 — Set the `HUGEICONS_PRO_LICENSE_KEY` environment variable

Your licence key is available in your [Hugeicons dashboard](https://hugeicons.com).

Choose the method that fits your workflow:

**macOS / Linux — current terminal session**

```bash
export HUGEICONS_PRO_LICENSE_KEY="your-licence-key-here"
```

Run this once per terminal session before you run `npm install`. The key is gone when you close the tab.

**macOS / Linux — permanent (shell profile)**

Add the export line to your shell config so it is always available:

```bash
# ~/.zshrc  (zsh, default on modern macOS)
# ~/.bashrc  (bash)
echo 'export HUGEICONS_PRO_LICENSE_KEY="your-licence-key-here"' >> ~/.zshrc
source ~/.zshrc
```

**Windows — PowerShell (current session)**

```powershell
$env:HUGEICONS_PRO_LICENSE_KEY = "your-licence-key-here"
```

**Windows — permanent (System Environment Variables)**

1. Open **Start → search "Environment Variables" → Edit the system environment variables**
2. Click **Environment Variables…**
3. Under *User variables*, click **New**
4. Variable name: `HUGEICONS_PRO_LICENSE_KEY`
5. Variable value: `your-licence-key-here`
6. Click **OK** and restart your terminal

**CI / CD (GitHub Actions, etc.)**

Store the key as a repository secret (e.g. `HUGEICONS_PRO_LICENSE_KEY`) and expose it as an environment variable in your workflow:

```yaml
- name: Install dependencies
  run: npm ci
  env:
    HUGEICONS_PRO_LICENSE_KEY: ${{ secrets.HUGEICONS_PRO_LICENSE_KEY }}
```

**.env file (Laravel)**

If you prefer keeping all project secrets in one place, add it to your Laravel `.env` file:

```ini
HUGEICONS_PRO_LICENSE_KEY=your-licence-key-here
```

Then export it for npm by running:

```bash
export HUGEICONS_PRO_LICENSE_KEY="$(grep HUGEICONS_PRO_LICENSE_KEY .env | cut -d '=' -f2)"
```

> **Never commit `.env`** — it is already in `.gitignore` by default in Laravel projects.

---

#### Step 3 — Install the Pro packages and build

With `.npmrc` in place and the environment variable set, run:

```bash
npm install --save-optional \
  @hugeicons-pro/core-stroke-rounded \
  @hugeicons-pro/core-solid-rounded

php artisan hugeicons:build
```

Available Pro packages (install only the styles you need):

| Package | Style |
|---|---|
| `@hugeicons-pro/core-stroke-rounded` | Stroke Rounded |
| `@hugeicons-pro/core-solid-rounded` | Solid Rounded |
| `@hugeicons-pro/core-bulk-rounded` | Bulk Rounded |
| `@hugeicons-pro/core-duotone-rounded` | Duotone Rounded |
| `@hugeicons-pro/core-twotone-rounded` | Twotone Rounded |

After `hugeicons:build` completes, the generated Blade components are ready to use via the `variant` prop.

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
