---
name: hugeicons-flux
description: "Use when adding or using a Hugeicons icon - any <flux:icon.hugeicons.*> component, an icon prop like icon=\"hugeicons...\", or when a UI needs an icon that is not in Heroicons or Lucide. Covers resolving the correct Hugeicons name, checking what is already available, and generating Pro-style icons on demand with `php artisan hugeicons:build` so the repo stays lean. Do not use for Heroicons or Lucide icons, which Flux handles natively."
license: MIT
metadata:
  author: abduns
---

# Hugeicons for Flux

This app uses the `abduns/hugeicons-flux` package. Icons are Flux components
named `<flux:icon.hugeicons.{name}>` and work anywhere a Flux icon does
(`<flux:button icon="hugeicons.{name}">`, `<flux:navlist.item icon="...">`, etc.).

## The lean rule (read this first)

The package **bundles the full free Stroke Rounded set** (~5,100 icons). They
render with **nothing committed to this repo**. Only generate an icon file into
this repo when a **Pro style** is actually needed for it.

- **Never** run `php artisan hugeicons:build` with no arguments - that dumps all
  ~5,100 icons into the repo. Always build specific named icons.
- Default styling needs **no build at all**.

## Step 1 - Resolve the icon name

Icon names are kebab-case (`home-01`, `ai-search-02`, `notification-01`). Do not
guess them. The bundled icon files are the canonical name index - grep them:

```bash
ls vendor/abduns/hugeicons-flux/resources/views/flux/icon/hugeicons/ | grep -i search
```

If several candidates exist (`home`, `home-01`, `home-02`, ...), pick by intent or
ask the user. https://hugeicons.com/icons can be used to preview shapes.

## Step 2 - Decide whether a build is needed

| What's needed | Action |
| --- | --- |
| Default look - no `variant`, or `variant="outline"` / `"stroke-rounded"` / `"mini"` / `"micro"` | **No build.** It already renders from the bundled package. Just use the component. |
| A Pro style - `variant="solid-rounded"`, `"bulk-rounded"`, `"duotone-rounded"`, etc. | Go to Step 3. |

## Step 3 - Build the Pro-style icon on demand

Only when a Pro style is needed. First check if it is already built into this
repo:

```bash
test -f resources/views/flux/icon/hugeicons/{name}.blade.php && echo exists || echo missing
```

If missing, build that one icon (fast, ~1s) - build it automatically, no need to
ask:

```bash
php artisan hugeicons:build {name}
```

Build several at once when you know you need them:
`php artisan hugeicons:build home-01 search-01 notification-01`.

This writes `resources/views/flux/icon/hugeicons/{name}.blade.php` into this repo
with every installed style. Flux resolves the repo's own `resources/views/flux`
path **before** the package, so the built file overrides the bundled free
version. Commit these generated files - they are the project's chosen Pro icons.

`hugeicons:build` only needs Node.js and the installed `@hugeicons-pro/*` npm
packages. It does **not** need the licence key - that was only for `npm install`.

## Which Pro styles are available

Check which `@hugeicons-pro/core-*` packages are installed:

```bash
ls node_modules/@hugeicons-pro/ 2>/dev/null
```

`stroke-rounded` is always available (bundled free). Any `variant` whose style
is not installed falls back to `stroke-rounded` and still renders - **no error**.
To add a style, install its `@hugeicons-pro/core-*` npm package (needs a
Hugeicons Pro licence key for `npm install`), then re-run `hugeicons:build` for
the affected icons.

## Step 4 - Use the component

```blade
<flux:icon.hugeicons.home-01 />                              {{-- default --}}
<flux:icon.hugeicons.home-01 variant="solid-rounded" class="size-8 text-blue-500" />
<flux:button icon="hugeicons.notification-01">Alerts</flux:button>
```

## Adding a brand-new icon mid-task

When building a UI and you reach for an icon: resolve the name (Step 1), and if
the design only needs the default style, just drop in
`<flux:icon.hugeicons.{name} />` - done. Reach for `hugeicons:build` only when a
Pro style is on screen.
