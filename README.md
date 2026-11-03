# hugeicons-flux

Use any Hugeicons icon as a native `<flux:icon.hugeicons.*>` component in Flux UI — the free Stroke Rounded set bundled, or generate all 10 Pro styles from your own Hugeicons licence.

[![Tests](https://github.com/abduns/hugeicons-flux/actions/workflows/ci.yml/badge.svg)](https://github.com/abduns/hugeicons-flux/actions)
[![Version](https://img.shields.io/packagist/v/abduns/hugeicons-flux.svg)](https://packagist.org/packages/abduns/hugeicons-flux)
[![Downloads](https://img.shields.io/packagist/dt/abduns/hugeicons-flux.svg)](https://packagist.org/packages/abduns/hugeicons-flux)
[![License](https://img.shields.io/packagist/l/abduns/hugeicons-flux.svg)](LICENSE.md)

---

## Features

- **6,100+ free icons, zero setup** — the full Hugeicons free Stroke Rounded set
  (`@hugeicons/core-free-icons` 4.4.0) is bundled as Blade components.
- **Native Flux components** — `<flux:icon.hugeicons.*>` works anywhere a Flux
  icon does, including `icon="hugeicons.*"` props.
- **All 10 Pro styles** — generate Stroke, Solid, Bulk, Duotone and Twotone
  variants from your own Hugeicons Pro licence with `php artisan hugeicons:build`.
- **One `variant` prop** — real Hugeicons style names plus Flux's
  `outline` / `solid` / `mini` / `micro` aliases, with graceful fallback.
- **Agent Skill included** for Laravel Boost, Codex and Claude Code workflows.

---

## Installation

```bash
composer require abduns/hugeicons-flux
```

The service provider is auto-discovered. No config, no npm install, no build
step for the free set.

---

## Quick Start

```blade
<flux:icon.hugeicons.home-01 class="size-8" />

<flux:button icon="hugeicons.add-01">New project</flux:button>
```

Browse icon names at [hugeicons.com/icons](https://hugeicons.com/icons).

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

To find a name, search the bundled files:

```bash
ls vendor/abduns/hugeicons-flux/resources/views/flux/icon/hugeicons | grep -i search
```

### Variants

The `variant` prop accepts the real Hugeicons style names, **plus** Flux's own
`outline` / `solid` / `mini` / `micro` as aliases:

```blade
<flux:icon.hugeicons.search-01 variant="solid-rounded" class="size-8 text-amber-500" />
```

| `variant`                                                        | Resolves to       |
| ---------------------------------------------------------------- | ----------------- |
| `outline` (default), `mini`, `micro`, `stroke`, `stroke-rounded` | `stroke-rounded`  |
| `stroke-sharp`, `stroke-standard`                                | same name         |
| `solid`, `solid-rounded`                                         | `solid-rounded`   |
| `solid-sharp`, `solid-standard`                                  | same name         |
| `bulk`, `bulk-rounded`                                           | `bulk-rounded`    |
| `duotone`, `duotone-rounded`                                     | `duotone-rounded` |
| `duotone-standard`                                               | same name         |
| `twotone`, `twotone-rounded`                                     | `twotone-rounded` |

`mini` and `micro` also apply Flux's smaller default sizes (`size-5` / `size-4`).

If an icon has not been built with a requested style (e.g. you only have the
free set), the `variant` gracefully falls back to `stroke-rounded`.

### Overriding icons

The bundled icons are registered *after* your application's
`resources/views/flux` directory, so any file you place at
`resources/views/flux/icon/hugeicons/{name}.blade.php` wins. That is exactly
where `hugeicons:build` writes Pro icons.

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
export HUGEICONS_PRO_LICENSE_KEY="$(grep '^HUGEICONS_PRO_LICENSE_KEY=' .env | cut -d '=' -f2-)"
```

> **Never commit `.env`** — it is already in `.gitignore` by default in Laravel projects.

---

#### Step 3 — Install the Pro packages and build

With `.npmrc` in place and the environment variable set, install the styles you
want, then generate the icons you use:

```bash
npm install --save-optional \
  @hugeicons-pro/core-stroke-rounded \
  @hugeicons-pro/core-solid-rounded
```

```bash
php artisan hugeicons:build home-01 search-01 notification-01
```

Available Pro packages (install only the styles you need):

| Package                                | Style            |
| -------------------------------------- | ---------------- |
| `@hugeicons-pro/core-stroke-rounded`   | Stroke Rounded   |
| `@hugeicons-pro/core-stroke-sharp`     | Stroke Sharp     |
| `@hugeicons-pro/core-stroke-standard`  | Stroke Standard  |
| `@hugeicons-pro/core-solid-rounded`    | Solid Rounded    |
| `@hugeicons-pro/core-solid-sharp`      | Solid Sharp      |
| `@hugeicons-pro/core-solid-standard`   | Solid Standard   |
| `@hugeicons-pro/core-bulk-rounded`     | Bulk Rounded     |
| `@hugeicons-pro/core-duotone-rounded`  | Duotone Rounded  |
| `@hugeicons-pro/core-duotone-standard` | Duotone Standard |
| `@hugeicons-pro/core-twotone-rounded`  | Twotone Rounded  |

Any other `@hugeicons-pro/core-*` package Hugeicons releases later is picked up
automatically and is selectable by its style name.

#### `hugeicons:build` reference

```bash
php artisan hugeicons:build [icons...] [--styles=...] [--target=...] [--node-modules=...] [--force]
```

| Argument / option | Description |
| --- | --- |
| `icons` | Icons to build, kebab-case (`home-01`) or the JS export name (`Home01Icon`). **Omit to build every icon** — usually not what you want in an app repo. |
| `--styles` | Limit to specific styles, e.g. `--styles=solid-rounded --styles=bulk-rounded`. Defaults to every installed style. |
| `--target` | Output directory. Defaults to `resources/views/flux/icon/hugeicons`. |
| `--node-modules` | Path to `node_modules`. Defaults to the app's base path. |
| `--force` | Overwrite icons that already exist. |

Each generated file contains every installed style, switched by `variant`.
The build needs Node.js and the installed packages only — not the licence key.

---

## Laravel Boost

This package ships a Laravel Boost Agent Skill at:

```text
resources/boost/skills/hugeicons-flux/SKILL.md
```

Laravel Boost can discover it during `boost:install` or `boost:update --discover`.
If you want to publish the skill into your application's `.ai/skills` directory
for customization, run:

```bash
php artisan hugeicons:boost-skill
```

Use `--force` to replace an existing local copy.

---

## Compatibility

| Requirement | Version |
| --- | --- |
| PHP | 8.3+ |
| Laravel | 11, 12, 13 |
| Flux UI | 2.x |
| Node.js | 18+ (only for `hugeicons:build`) |

---

## How it works

- Each icon is a Blade anonymous component in `resources/views/flux/icon/hugeicons`,
  registered into Flux's `flux` namespace once the app has booted, so the host
  app's own `resources/views/flux` path keeps priority.
- `hugeicons:build` reads the plain ESM icon modules shipped in
  `@hugeicons/core-free-icons` / `@hugeicons-pro/core-*` with a tiny Node
  script (`bin/extract.mjs`) and renders them through `stubs/icon.blade.php.stub`.
- Rendering mirrors the official Hugeicons renderers: the root `<svg>` uses
  `fill="none"`, every element carries its own `fill` / `stroke`, and
  semi-transparent secondary layers are drawn first.
- Unused Pro icons cost nothing — only the icons you build are written to your repo.

---

## Development

```bash
composer test      # Pest
composer ci        # php-cs-fixer (dry run) + PHPStan + Pest
```

To refresh the bundled free set, bump `@hugeicons/core-free-icons` in
`package.json`, then run:

```bash
composer icons:bundle
```

---

## Contributing

Contributions, issues, and discussions are welcome.

---

## Security

If you discover a security issue, please report it privately rather than opening a public issue.

---

## License

MIT — see [LICENSE.md](LICENSE.md). Hugeicons are © Hugeicons; the free set is
used under its own licence, and Pro icons require your own Hugeicons Pro licence.
