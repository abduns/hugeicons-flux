# abduns/hugeicons-flux

Use any [Hugeicons](https://hugeicons.com) icon as a native Flux UI component:

```blade
<flux:icon.hugeicon.home-01 />
<flux:icon.hugeicon.search-01 variant="solid-rounded" class="size-8 text-amber-500" />
<flux:button icon="hugeicon.notification-01">Alerts</flux:button>
```

The free **Stroke Rounded** set (4,500+ icons) is bundled and works the moment
you install the package. If you own **Hugeicons Pro**, one command regenerates
the same icons with all 9 styles — pulled from *your* licensed npm packages, so
no Pro artwork is ever redistributed by this package.

## Install

```bash
composer require abduns/hugeicons-flux
```

That's it for the free tier. The package's service provider registers its
bundled icons into Flux's `flux` component namespace under the `hugeicon`
prefix.

## Usage

Every icon is `flux:icon.hugeicon.{name}`, where `{name}` is the kebab-case
Hugeicons name (`Home01Icon` → `home-01`, `AiSearch02Icon` → `ai-search-02`):

```blade
{{-- Default --}}
<flux:icon.hugeicon.home-01 />

{{-- Sizing & colour, like any Flux icon --}}
<flux:icon.hugeicon.home-01 class="size-10 text-blue-500" />

{{-- As a prop on other Flux components --}}
<flux:button icon="hugeicon.calendar-03" />
<flux:navlist.item icon="hugeicon.dashboard-square-01">Dashboard</flux:navlist.item>
```

### Variants

The `variant` prop accepts the real Hugeicons style names, plus Flux's own
`outline` / `solid` / `mini` / `micro` as aliases:

| `variant`                                                            | Resolves to                  |
| -------------------------------------------------------------------- | ---------------------------- |
| `outline` (default), `mini`, `micro`, `stroke`, `stroke-rounded`     | `stroke-rounded`             |
| `solid`, `solid-rounded`                                             | `solid-rounded`              |
| `bulk-rounded`, `duotone-rounded`, `twotone-rounded`, `*-sharp`, …    | the matching Hugeicons style |

If an icon was built without a requested style (e.g. you only have the free
set), the `variant` gracefully falls back to `stroke-rounded` — so
`<flux:button icon="hugeicon.home-01" />` never errors.

## Hugeicons Pro — all 9 styles

Pro styles are **not** shipped with this package. To use them, install the Pro
packages you are licensed for and regenerate the icons into your own app:

```bash
# 1. Install the Pro style packages you want (needs your Hugeicons npm token)
npm i @hugeicons-pro/core-stroke-rounded @hugeicons-pro/core-solid-rounded \
      @hugeicons-pro/core-bulk-rounded @hugeicons-pro/core-duotone-rounded \
      @hugeicons-pro/core-twotone-rounded

# 2. Regenerate — every installed style is detected automatically
php artisan hugeicons:build
```

This writes Blade files into `resources/views/flux/icon/hugeicon/` in your
application. Flux resolves the app's own `resources/views/flux` path *before*
this package, so your Pro builds transparently override the bundled free icons.

### `hugeicons:build` options

| Option            | Description                                                              |
| ----------------- | ------------------------------------------------------------------------ |
| `icons` (args)    | Specific icons to build, e.g. `home-01 search-01`. Builds all if omitted. |
| `--target=`       | Output directory. Default: `resources/views/flux/icon/hugeicon`.          |
| `--node-modules=` | Path to `node_modules`. Default: the application base path.               |
| `--styles=`       | Limit to specific styles, e.g. `--styles=solid-rounded`. Repeatable.      |
| `--force`         | Overwrite icons that already exist.                                       |

`hugeicons:build` requires Node.js on `PATH` — the Hugeicons npm packages are
plain ES modules and are imported directly to read their path data.

## Requirements

- PHP 8.2+
- Laravel 11–13
- Flux UI 2 (`livewire/flux`)
- Node.js (only for running `hugeicons:build`)

## Licence

This package is MIT licensed. The bundled Stroke Rounded icons are generated
from `@hugeicons/core-free-icons` (MIT). Hugeicons Pro is a separate commercial
product — this package never redistributes Pro artwork; Pro users generate it
locally from their own licensed packages.
