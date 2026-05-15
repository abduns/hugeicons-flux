# abduns/hugeicons-flux

Use any [Hugeicons](https://hugeicons.com) icon as a native Flux UI component:

```blade
<flux:icon.hugeicon.home-01 />
<flux:icon.hugeicon.search-01 variant="solid-rounded" class="size-8 text-amber-500" />
<flux:button icon="hugeicon.notification-01">Alerts</flux:button>
```

The free **Stroke Rounded** set (5,100+ icons) is bundled — it works the moment
you install the package, with nothing to generate. If you own **Hugeicons Pro**,
one command regenerates icons with all 9 styles, pulled from *your* licensed npm
packages, so no Pro artwork is ever redistributed by this package.

---

## Installation

```bash
composer require abduns/hugeicons-flux
```

The service provider is auto-discovered — no manual registration. The bundled
free **Stroke Rounded** set (5,100+ icons) is immediately available across your
whole app. Verify by rendering:

```blade
<flux:icon.hugeicon.home-01 class="size-8" />
```

That's the entire free-tier setup.

---

## Usage

Every icon is `flux:icon.hugeicon.{name}`, where `{name}` is the kebab-case
Hugeicons name (`Home01Icon` → `home-01`, `AiSearch02Icon` → `ai-search-02`).

```blade
{{-- Default --}}
<flux:icon.hugeicon.home-01 />

{{-- Sizing & colour — like any Flux icon --}}
<flux:icon.hugeicon.home-01 class="size-10 text-blue-500" />

{{-- As an `icon` prop on other Flux components --}}
<flux:button icon="hugeicon.calendar-03" />
<flux:navlist.item icon="hugeicon.dashboard-square-01">Dashboard</flux:navlist.item>
```

### Variants

The `variant` prop accepts the real Hugeicons style names, **plus** Flux's own
`outline` / `solid` / `mini` / `micro` as aliases:

| `variant`                                                          | Resolves to                  |
| ------------------------------------------------------------------ | ----------------------------- |
| `outline` (default), `mini`, `micro`, `stroke`, `stroke-rounded`   | `stroke-rounded`              |
| `solid`, `solid-rounded`                                           | `solid-rounded`               |
| `bulk-rounded`, `duotone-rounded`, `twotone-rounded`, `*-sharp`, …  | the matching Hugeicons style  |

If an icon has not been built with a requested style (e.g. you only have the
free set), the `variant` gracefully falls back to `stroke-rounded` — so
`<flux:button icon="hugeicon.home-01" />` never throws.

---

## Hugeicons Pro styles (optional)

The bundled set is Stroke Rounded only. To use the other 8 styles you need a
[Hugeicons Pro](https://hugeicons.com/pricing) licence. Pro artwork is **not**
shipped with this package — you install the Pro npm packages yourself and
generate the icons locally.

### 1. Configure the Hugeicons npm registry

Add this to your app's `.npmrc` (next to `package.json`):

```ini
@hugeicons-pro:registry=https://npm.hugeicons.com/
//npm.hugeicons.com/:_authToken=${HUGEICONS_PRO_LICENSE_KEY}
```

Using `${HUGEICONS_PRO_LICENSE_KEY}` keeps the token out of the committed file.

### 2. Provide your licence key

npm reads the token from a real **environment variable** — it does *not* read
Laravel's `.env`. Export it in the shell before installing:

```bash
export HUGEICONS_PRO_LICENSE_KEY="your-hugeicons-token"
```

(Or commit the token directly into a git-ignored `.npmrc` instead of the
`${...}` placeholder.)

### 3. Install the style packages you want

Each style is its own package — install only the ones you need:

```bash
npm install --save-optional \
  @hugeicons-pro/core-stroke-rounded \
  @hugeicons-pro/core-stroke-sharp \
  @hugeicons-pro/core-stroke-standard \
  @hugeicons-pro/core-solid-rounded \
  @hugeicons-pro/core-solid-sharp \
  @hugeicons-pro/core-solid-standard \
  @hugeicons-pro/core-bulk-rounded \
  @hugeicons-pro/core-duotone-rounded \
  @hugeicons-pro/core-twotone-rounded
```

`--save-optional` records them in `package.json` but lets a key-less
`npm install` (CI, other machines) skip them gracefully instead of failing.

### 4. Generate the icons

```bash
# Every icon, every installed style — writes ~5,100 files into your app
php artisan hugeicons:build

# Or just the icons you actually use — keeps the repo lean
php artisan hugeicons:build home-01 search-01 notification-01
```

This writes Blade files into `resources/views/flux/icon/hugeicon/` in your app.
Flux resolves your app's own `resources/views/flux` path *before* this package,
so your generated files transparently override the bundled free icons. Commit
them — they are your project's chosen icons.

`hugeicons:build` only needs **Node.js** and the installed `@hugeicons-pro/*`
packages — it does not need the licence key (that was only for `npm install`).

### `hugeicons:build` reference

| Argument / option | Description                                                               |
| ----------------- | ------------------------------------------------------------------------- |
| `icons` (args)    | Specific icons to build, e.g. `home-01 search-01`. Builds all if omitted.  |
| `--target=`       | Output directory. Default: `resources/views/flux/icon/hugeicon`.           |
| `--node-modules=` | Path to `node_modules`. Default: the application base path.                |
| `--styles=`       | Limit to specific styles, e.g. `--styles=solid-rounded`. Repeatable.       |
| `--force`         | Overwrite icons that already exist.                                        |

---

## Claude Code skill

The package ships a [Claude Code](https://claude.com/claude-code) skill that
teaches the agent the lean, on-demand icon workflow: resolve the real Hugeicons
name, use the bundled free icon when the default style is enough, and run
`hugeicons:build` for a *specific* icon only when a Pro style is actually needed
— so your repo never fills up with thousands of unused icon files.

Install it by copying the skill into your app's `.claude/skills/` directory:

```bash
cp -r vendor/abduns/hugeicons-flux/.claude/skills/hugeicons-flux .claude/skills/
```

Or symlink it instead, so it stays in sync with the package on every update:

```bash
ln -s ../../vendor/abduns/hugeicons-flux/.claude/skills/hugeicons-flux \
      .claude/skills/hugeicons-flux
```

The skill activates automatically whenever icon work comes up — no slash command
needed. After installing, you can tweak its "Which Pro styles are available"
note to match the exact `@hugeicons-pro/*` packages your project uses.

---

## How it works

- The service provider registers the package's bundled icons into Flux's `flux`
  anonymous-component namespace under the `hugeicon` prefix.
- Registration is deferred until all providers have booted, so your app's own
  `resources/views/flux` path keeps priority — meaning any icon you generate (or
  hand-edit) in your app overrides the bundled version of the same name.
- Each icon is a single Blade file whose `variant` prop `@switch`es between the
  styles that were available when it was built.

## Requirements

- PHP 8.2+
- Laravel 11–13
- Flux UI 2 (`livewire/flux`)
- Node.js — only for running `hugeicons:build`

## Licence

This package is MIT licensed. The bundled Stroke Rounded icons are generated
from [`@hugeicons/core-free-icons`](https://www.npmjs.com/package/@hugeicons/core-free-icons)
(MIT). Hugeicons Pro is a separate commercial product — this package never
redistributes Pro artwork; Pro users generate it locally from their own licensed
packages.
